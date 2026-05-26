<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'PPDB') — SMK Negeri 48</title>
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
  <style>
    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .ppdb-card { border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,.15); }
    .ppdb-header { background: linear-gradient(135deg, #1e3a5f, #2d5f8a); color: #fff; border-radius: 12px 12px 0 0; padding: 2rem; text-align: center; }
    .ppdb-header h1 { font-size: 1.6rem; margin-bottom: .5rem; }
    .ppdb-header p { opacity: .85; margin-bottom: 0; }
    .section-title { font-weight: 600; color: #1e3a5f; border-bottom: 2px solid #1e3a5f; padding-bottom: .5rem; margin-bottom: 1rem; }
  </style>
  @stack('styles')
</head>
<body class="d-flex align-items-center justify-content-center py-4">
  <div class="container">
    @yield('content')
  </div>
  <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  @stack('scripts')
</body>
</html>
