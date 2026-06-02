<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Simakad Sekolah SMK')</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Quicksand:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
  <!-- jQuery -->
  <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>

  @stack('styles')
  <style>
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }
    ::-webkit-scrollbar-thumb {
      background: #3c8dbc;
      border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #2a6a8e;
    }
    /* For Firefox */
    * {
      scrollbar-width: thin;
      scrollbar-color: #3c8dbc #f1f1f1;
    }
  </style>
</head>
<body style="font-family: Quicksand;" class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed {{ request()->cookie('dark_mode') === 'on' ? 'dark-mode' : '' }}">
<div class="wrapper">

  @include('layouts.partials.navbar')
  @include('layouts.partials.sidebar')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">@yield('page_title')</h1>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        @include('layouts.partials.flash')
        @yield('content')
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  @include('layouts.partials.footer')
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- Bootstrap 4 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<!-- Toastr -->
<link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
<script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>

<script>
// Toastr config
toastr.options = {
  closeButton: true,
  progressBar: true,
  positionClass: 'toast-top-right',
  timeOut: 4000
};

// Auto-show toast dari flash session
@if(session('success'))
  toastr.success("{{ session('success') }}");
@endif
@if(session('error'))
  toastr.error("{{ session('error') }}");
@endif
@if(session('warning'))
  toastr.warning("{{ session('warning') }}");
@endif
@if(session('info'))
  toastr.info("{{ session('info') }}");
@endif

// Intercept form submit dengan confirm() → ganti dengan SweetAlert2
document.addEventListener('submit', function(e) {
  var form = e.target;
  var onsubmit = form.getAttribute('onsubmit');

  // Deteksi pola: onsubmit="return confirm('...')"
  if (onsubmit && onsubmit.includes('confirm(')) {
    var match = onsubmit.match(/confirm\(['"](.+?)['"]\)/);
    if (match) {
      e.preventDefault();
      Swal.fire({
        title: 'Konfirmasi',
        text: match[1],
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then(function(result) {
        if (result.isConfirmed) {
          form.removeAttribute('onsubmit'); // hindari loop
          form.submit();
        }
      });
    }
  }
});


<script>
// Toggle Dark Mode — simpan preferensi di cookie (1 tahun), terapkan tanpa reload.
(function () {
  var toggle = document.getElementById('dark-mode-toggle');
  if (!toggle) return;
  toggle.addEventListener('click', function (e) {
    e.preventDefault();
    var body = document.body;
    var isDark = body.classList.toggle('dark-mode');
    var nav = document.querySelector('.main-header');
    if (nav) {
      nav.classList.toggle('navbar-white', !isDark);
      nav.classList.toggle('navbar-light', !isDark);
      nav.classList.toggle('navbar-dark', isDark);
      nav.classList.toggle('navbar-secondary', isDark);
    }
    var icon = toggle.querySelector('i');
    if (icon) { icon.className = 'fas ' + (isDark ? 'fa-sun' : 'fa-moon'); }
    document.cookie = 'dark_mode=' + (isDark ? 'on' : 'off') + ';path=/;max-age=' + (60 * 60 * 24 * 365) + ';SameSite=Lax';
  });
})();

// Helper: button loading state (disable + spinner) saat form submit
document.addEventListener('submit', function(e) {
  var form = e.target;
  if (form.dataset.noLoading) return; // skip jika form punya data-no-loading
  var btn = form.querySelector('button[type="submit"]');
  if (btn && !btn.disabled) {
    btn.disabled = true;
    var icon = btn.querySelector('i');
    if (icon) {
      btn.dataset.originalIcon = icon.className;
      icon.className = 'fas fa-spinner fa-spin';
    }
  }
});
</script>

@stack('scripts')
</body>
</html>
