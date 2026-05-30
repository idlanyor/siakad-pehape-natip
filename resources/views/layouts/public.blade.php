<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', setting('sekolah_nama', 'SMK Negeri 48')) — @yield('page_title', 'Website Resmi')</title>
  <meta name="description" content="@yield('meta_desc', setting('sekolah_tagline', ''))">

  {{-- Open Graph --}}
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="{{ url()->current() }}">
  <meta property="og:title"       content="@yield('title', setting('sekolah_nama'))">
  <meta property="og:description" content="@yield('meta_desc', setting('sekolah_tagline', ''))">
  @if(setting_url('logo'))
  <meta property="og:image"       content="{{ setting_url('logo') }}">
  @endif
  <meta property="og:site_name"   content="{{ setting('sekolah_nama') }}">
  <link rel="canonical"           href="{{ url()->current() }}">

  {{-- Bootstrap 5 --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  {{-- Font Awesome --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  {{-- Google Fonts --}}
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #0f3c5c; /* Deep Blue, formal for schools */
      --primary-light: #185a8a;
      --accent: #f39c12; /* Golden/Orange accent */
      --text-dark: #2c3e50;
      --text-muted: #7f8c8d;
      --bg-light: #f8f9fa;
      --bg-gray: #e9ecef;
    }

    body {
      font-family: 'Roboto', sans-serif;
      color: var(--text-dark);
      -webkit-font-smoothing: antialiased;
    }

    h1, h2, h3, h4, h5, h6, .nav-link, .btn {
      font-family: 'Poppins', sans-serif;
    }

    /* ── Top Bar ── */
    .topbar {
      background-color: var(--primary);
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.85rem;
      padding: 0.5rem 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .topbar a {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      transition: color 0.2s;
    }
    .topbar a:hover { color: var(--accent); }

    /* ── Navbar ── */
    .navbar-public {
      background: #ffffff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      padding: 0.8rem 0;
      transition: all 0.3s ease;
    }
    .navbar-public.scrolled {
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 0.5rem 0;
    }
    .navbar-brand span {
      font-weight: 700;
      color: var(--primary);
      font-size: 1.25rem;
      line-height: 1.2;
    }
    .navbar-brand small {
      display: block;
      font-size: 0.75rem;
      font-weight: 400;
      color: var(--text-muted);
    }
    .navbar-public .nav-link {
      color: var(--primary) !important;
      font-weight: 600;
      font-size: 0.95rem;
      padding: 0.5rem 1rem !important;
      transition: color 0.2s;
    }
    .navbar-public .nav-link:hover, .navbar-public .nav-link.active {
      color: var(--accent) !important;
    }
    .navbar-public .btn-ppdb {
      background: var(--accent);
      color: #fff !important;
      border-radius: 5px;
      font-weight: 600;
      padding: 0.5rem 1.25rem !important;
      transition: background 0.3s;
    }
    .navbar-public .btn-ppdb:hover {
      background: #e67e22;
      color: #fff !important;
    }

    /* ── Dropdown ── */
    .navbar-public .dropdown-menu {
      border: none;
      border-top: 3px solid var(--accent);
      border-radius: 0 0 8px 8px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      padding: 0.5rem 0;
      margin-top: 0;
    }
    .navbar-public .dropdown-item {
      font-family: 'Poppins', sans-serif;
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--text-dark);
      padding: 0.5rem 1.25rem;
      transition: all 0.2s;
    }
    .navbar-public .dropdown-item:hover, .navbar-public .dropdown-item.active-item {
      background: rgba(243, 156, 18, 0.1);
      color: var(--accent);
    }

    /* ── Utilities ── */
    .section-title { font-size: 2.2rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; }
    .section-subtitle { color: var(--text-muted); font-size: 1.1rem; font-family: 'Roboto', sans-serif; }
    .section-divider { width: 60px; height: 4px; background: var(--accent); margin: 0 auto 2rem; border-radius: 2px; }

    /* ── Footer ── */
    .footer-main { background: #0b2e46; color: rgba(255,255,255,0.7); padding: 4rem 0 2rem; }
    .footer-main h5 { color: #fff; font-weight: 600; text-transform: uppercase; font-size: 1.1rem; margin-bottom: 1.5rem; letter-spacing: 0.5px; }
    .footer-main a { color: rgba(255,255,255,0.7); text-decoration: none; transition: color 0.2s; }
    .footer-main a:hover { color: var(--accent); }
    .footer-main ul li { margin-bottom: 0.75rem; }
    .footer-bottom { background: #072133; color: rgba(255,255,255,0.5); padding: 1.2rem 0; font-size: 0.85rem; }

    .social-icons a {
      display: inline-flex; align-items: center; justify-content: center;
      width: 38px; height: 38px; border-radius: 50%;
      background: rgba(255,255,255,0.1); margin-right: 0.5rem;
      color: #fff; transition: all 0.3s;
    }
    .social-icons a:hover { background: var(--accent); transform: translateY(-3px); }

    #backToTop { position: fixed; bottom: 30px; right: 30px; width: 45px; height: 45px; border-radius: 50%; background: var(--accent); color: #fff; border: none; display: none; align-items: center; justify-content: center; z-index: 999; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s; }
    #backToTop:hover { background: #e67e22; transform: translateY(-3px); }
    #backToTop.show { display: flex; }
  </style>
  @stack('styles')
</head>
<body style="padding-top: 110px;"> {{-- Offset for fixed topbar + navbar --}}

{{-- Top Bar --}}
<div class="topbar fixed-top d-none d-lg-block">
  <div class="container d-flex justify-content-between align-items-center">
    <div>
      <i class="fas fa-phone-alt me-2 text-warning"></i> {{ setting('sekolah_no_telp', '-') }}
      <span class="mx-3 text-muted">|</span>
      <i class="fas fa-envelope me-2 text-warning"></i> {{ setting('sekolah_email', '-') }}
    </div>
    <div>
      <a href="{{ setting('sosmed_facebook') }}" class="me-3"><i class="fab fa-facebook-f"></i></a>
      <a href="{{ setting('sosmed_instagram') }}" class="me-3"><i class="fab fa-instagram"></i></a>
      <a href="{{ setting('sosmed_youtube') }}" class="me-3"><i class="fab fa-youtube"></i></a>
      <a href="{{ route('login') }}" class="fw-semibold ms-2"><i class="fas fa-lock me-1"></i> Portal Internal</a>
    </div>
  </div>
</div>

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg navbar-light navbar-public fixed-top" id="mainNavbar" style="top: 38px;">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-3" href="{{ route('landing.index') }}">
      @if(setting_url('logo'))
        <img src="{{ setting_url('logo') }}" height="50" alt="Logo">
      @else
        <div style="width:50px;height:50px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center">
          <i class="fas fa-graduation-cap" style="color:#fff;font-size:1.5rem"></i>
        </div>
      @endif
      <div>
        <span>{{ setting('sekolah_nama', 'SMK Negeri 48') }}</span>
        <small>{{ setting('sekolah_tagline', 'Unggul, Berkarakter, Berbudaya') }}</small>
      </div>
    </a>
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('landing.index') ? 'active' : '' }}" href="{{ route('landing.index') }}">Beranda</a>
        </li>

        {{-- Dropdown: Profil Sekolah --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->routeIs('landing.tentang','landing.program','landing.organisasi') ? 'active' : '' }}"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Profil
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item {{ request()->routeIs('landing.tentang') ? 'active-item' : '' }}" href="{{ route('landing.tentang') }}">Tentang Sekolah</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('landing.program') ? 'active-item' : '' }}" href="{{ route('landing.program') }}">Program Keahlian</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('landing.organisasi') ? 'active-item' : '' }}" href="{{ route('landing.organisasi') }}">Struktur Organisasi</a></li>
          </ul>
        </li>

        {{-- Dropdown: Informasi --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->routeIs('berita.*','galeri.*') ? 'active' : '' }}"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Informasi
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item {{ request()->routeIs('berita.*') ? 'active-item' : '' }}" href="{{ route('berita.index') }}">Berita & Artikel</a></li>
            <li><a class="dropdown-item {{ request()->routeIs('galeri.*') ? 'active-item' : '' }}" href="{{ route('galeri.index') }}">Galeri Foto</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('ppdb.*') ? 'active' : '' }}" href="{{ route('ppdb.landing') }}">PPDB</a>
        </li>

        @if(setting('ppdb_aktif') == '1')
          <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
            <a class="nav-link btn-ppdb" href="{{ route('ppdb.index') }}">
              <i class="fas fa-edit me-1"></i> Daftar PPDB
            </a>
          </li>
        @endif

        <li class="nav-item ms-lg-2 d-lg-none mt-2">
          <a class="nav-link text-primary" href="{{ route('login') }}">
            <i class="fas fa-sign-in-alt me-1"></i> Portal Internal
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

{{-- Content --}}
<main style="min-height: 60vh;">
  @yield('content')
</main>

{{-- Footer --}}
<footer class="footer-main">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-3 mb-4">
          @if(setting_url('logo_putih'))
            <img src="{{ setting_url('logo_putih') }}" height="60" alt="Logo">
          @else
            <div style="width:60px;height:60px;background:rgba(255,255,255,.1);border-radius:50%;display:flex;align-items:center;justify-content:center">
              <i class="fas fa-graduation-cap" style="color:#fff;font-size:2rem"></i>
            </div>
          @endif
          <div>
            <h4 class="text-white mb-0" style="font-weight: 700;">{{ setting('sekolah_nama', 'SMK Negeri 48') }}</h4>
            <small class="text-warning">NPSN: {{ setting('sekolah_npsn', '-') }}</small>
          </div>
        </div>
        <p class="mb-4" style="line-height: 1.8;">{{ setting('sekolah_tagline', 'Mencetak generasi unggul, berkarakter, dan siap bersaing di era global.') }}</p>
        <div class="social-icons">
          @if(setting('sosmed_facebook'))<a href="{{ setting('sosmed_facebook') }}"><i class="fab fa-facebook-f"></i></a>@endif
          @if(setting('sosmed_instagram'))<a href="{{ setting('sosmed_instagram') }}"><i class="fab fa-instagram"></i></a>@endif
          @if(setting('sosmed_youtube'))<a href="{{ setting('sosmed_youtube') }}"><i class="fab fa-youtube"></i></a>@endif
        </div>
      </div>

      <div class="col-lg-2 col-md-6">
        <h5>Tautan Cepat</h5>
        <ul class="list-unstyled">
          <li><i class="fas fa-chevron-right me-2" style="font-size:0.7rem;color:var(--accent)"></i> <a href="{{ route('landing.index') }}">Beranda</a></li>
          <li><i class="fas fa-chevron-right me-2" style="font-size:0.7rem;color:var(--accent)"></i> <a href="{{ route('landing.tentang') }}">Tentang Sekolah</a></li>
          <li><i class="fas fa-chevron-right me-2" style="font-size:0.7rem;color:var(--accent)"></i> <a href="{{ route('landing.program') }}">Program Keahlian</a></li>
          <li><i class="fas fa-chevron-right me-2" style="font-size:0.7rem;color:var(--accent)"></i> <a href="{{ route('berita.index') }}">Berita Terkini</a></li>
          <li><i class="fas fa-chevron-right me-2" style="font-size:0.7rem;color:var(--accent)"></i> <a href="{{ route('galeri.index') }}">Galeri Foto</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h5>Kontak Kami</h5>
        <ul class="list-unstyled">
          <li class="d-flex align-items-start mb-3">
            <i class="fas fa-map-marker-alt mt-1 me-3" style="color:var(--accent);font-size:1.2rem"></i>
            <span>{{ setting('sekolah_alamat', 'Jl. Pendidikan No. 1, Jakarta Timur') }}</span>
          </li>
          <li class="d-flex align-items-start mb-3">
            <i class="fas fa-phone-alt mt-1 me-3" style="color:var(--accent);font-size:1.2rem"></i>
            <span>{{ setting('sekolah_no_telp', '(021) 1234567') }}</span>
          </li>
          <li class="d-flex align-items-start mb-3">
            <i class="fas fa-envelope mt-1 me-3" style="color:var(--accent);font-size:1.2rem"></i>
            <span>{{ setting('sekolah_email', 'info@smkn48.sch.id') }}</span>
          </li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-12">
        <h5>Lokasi Sekolah</h5>
        @if(setting('maps_embed'))
          <div class="rounded overflow-hidden" style="border: 2px solid rgba(255,255,255,0.1);">
            <iframe src="{{ setting('maps_embed') }}" width="100%" height="150" style="border:0;" allowfullscreen loading="lazy"></iframe>
          </div>
        @else
          <div class="bg-dark rounded d-flex align-items-center justify-content-center" style="height: 150px; border: 2px solid rgba(255,255,255,0.1);">
            <span class="text-muted"><i class="fas fa-map-marked-alt me-2"></i> Peta Belum Diatur</span>
          </div>
        @endif
      </div>
    </div>
  </div>
</footer>
<div class="footer-bottom">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
    <div class="mb-2 mb-md-0">
      &copy; {{ date('Y') }} <strong>{{ setting('sekolah_nama', 'SMK Negeri 48') }}</strong>. Hak cipta dilindungi undang-undang.
    </div>
    <div>
      <span class="badge bg-secondary text-light px-2 py-1">Sistem Informasi Akademik</span>
    </div>
  </div>
</div>

{{-- Back to Top --}}
<button id="backToTop" title="Kembali ke atas"><i class="fas fa-arrow-up"></i></button>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Handle fixed navbar behavior with topbar
  const navbar = document.getElementById('mainNavbar');
  const topbar = document.querySelector('.topbar');
  const bttBtn = document.getElementById('backToTop');

  window.addEventListener('scroll', () => {
    if (window.scrollY > 40) {
      navbar.classList.add('scrolled');
      if (window.innerWidth >= 992) {
        navbar.style.top = '0'; // Move up to cover where topbar was
      }
    } else {
      navbar.classList.remove('scrolled');
      if (window.innerWidth >= 992) {
        navbar.style.top = '38px'; // Push down below topbar
      }
    }

    // Back to top button
    if (window.scrollY > 300) {
      bttBtn.classList.add('show');
    } else {
      bttBtn.classList.remove('show');
    }
  });

  bttBtn.addEventListener('click', () => window.scrollTo({top: 0, behavior: 'smooth'}));

  // Responsive handling for navbar placement
  window.addEventListener('resize', () => {
    if (window.innerWidth < 992) {
      navbar.style.top = '0';
      document.body.style.paddingTop = '70px';
    } else {
      navbar.style.top = window.scrollY > 40 ? '0' : '38px';
      document.body.style.paddingTop = '110px';
    }
  });

  // Initial check
  if (window.innerWidth < 992) {
    navbar.style.top = '0';
    document.body.style.paddingTop = '70px';
  }
</script>
@stack('scripts')
</body>
</html>
