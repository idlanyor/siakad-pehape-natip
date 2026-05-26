<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', setting('sekolah_nama', 'SMK Negeri 48')) — @yield('page_title', 'Website Sekolah')</title>
  <meta name="description" content="@yield('meta_desc', setting('sekolah_tagline', ''))">
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #1a3a6b;
      --primary-light: #2d5f9e;
      --accent: #e8a020;
      --text: #2d3748;
      --text-light: #718096;
      --bg-light: #f7f9fc;
    }
    * { font-family: 'Inter', sans-serif; }
    body { color: var(--text); }

    /* ── Navbar ── */
    .navbar-public {
      background: var(--primary) !important;
      padding: .9rem 0;
      transition: box-shadow .3s;
    }
    .navbar-public.scrolled { box-shadow: 0 4px 20px rgba(0,0,0,.15); }
    .navbar-public .navbar-brand { font-weight: 800; font-size: 1.1rem; letter-spacing: -.3px; }
    .navbar-public .nav-link { color: rgba(255,255,255,.85) !important; font-weight: 500; font-size: .9rem; padding: .4rem .8rem !important; }
    .navbar-public .nav-link:hover, .navbar-public .nav-link.active { color: #fff !important; }
    .navbar-public .btn-ppdb { background: var(--accent); color: #fff !important; border-radius: 6px; font-weight: 600; padding: .4rem 1.1rem !important; }
    .navbar-public .btn-ppdb:hover { background: #d4911a; }

    /* ── Sections ── */
    .section-title { font-size: 2rem; font-weight: 800; color: var(--primary); }
    .section-subtitle { color: var(--text-light); font-size: 1.05rem; }
    .section-divider { width: 50px; height: 4px; background: var(--accent); border-radius: 2px; margin: .75rem auto 1.5rem; }

    /* ── Hero ── */
    .hero-section {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 60%, #1e4d8c 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
    }
    .hero-section::before {
      content: '';
      position: absolute;
      top: -50%; right: -20%;
      width: 600px; height: 600px;
      background: rgba(255,255,255,.04);
      border-radius: 50%;
    }
    .hero-section::after {
      content: '';
      position: absolute;
      bottom: -30%; left: -10%;
      width: 400px; height: 400px;
      background: rgba(255,255,255,.03);
      border-radius: 50%;
    }
    .hero-badge { background: rgba(255,255,255,.15); color: #fff; padding: .35rem .9rem; border-radius: 20px; font-size: .8rem; font-weight: 600; display: inline-block; margin-bottom: 1rem; }
    .hero-title { font-size: clamp(2rem, 5vw, 3.2rem); font-weight: 800; color: #fff; line-height: 1.2; }
    .hero-subtitle { color: rgba(255,255,255,.8); font-size: 1.1rem; line-height: 1.7; }
    .hero-stats { background: rgba(255,255,255,.1); border-radius: 12px; padding: 1.2rem 1.5rem; }
    .hero-stat-num { font-size: 1.6rem; font-weight: 800; color: var(--accent); }
    .hero-stat-label { font-size: .78rem; color: rgba(255,255,255,.7); }

    /* ── Cards ── */
    .jurusan-card { border: none; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); transition: transform .25s, box-shadow .25s; overflow: hidden; }
    .jurusan-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,.12); }
    .jurusan-icon { width: 60px; height: 60px; border-radius: 14px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); display: flex; align-items: center; justify-content: center; }
    .jurusan-icon i { color: #fff; font-size: 1.4rem; }

    .ekskul-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem 1.2rem; transition: border-color .2s, box-shadow .2s; }
    .ekskul-card:hover { border-color: var(--primary-light); box-shadow: 0 4px 12px rgba(26,58,107,.1); }

    .prestasi-badge { font-size: .72rem; font-weight: 600; padding: .25rem .6rem; border-radius: 20px; }

    /* ── PPDB CTA ── */
    .ppdb-cta { background: linear-gradient(135deg, var(--accent) 0%, #d4911a 100%); }
    .ppdb-cta .countdown-box { background: rgba(255,255,255,.2); border-radius: 10px; padding: .8rem 1rem; min-width: 70px; text-align: center; }
    .ppdb-cta .countdown-num { font-size: 2rem; font-weight: 800; color: #fff; line-height: 1; }
    .ppdb-cta .countdown-label { font-size: .7rem; color: rgba(255,255,255,.8); font-weight: 500; }

    /* ── Sambutan ── */
    .sambutan-section { background: var(--bg-light); }
    .kepsek-photo { width: 160px; height: 160px; object-fit: cover; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 4px 20px rgba(0,0,0,.12); }

    /* ── Stats ── */
    .stat-card { text-align: center; padding: 2rem 1rem; }
    .stat-number { font-size: 2.5rem; font-weight: 800; color: var(--primary); }
    .stat-label { color: var(--text-light); font-size: .9rem; }

    /* ── Footer ── */
    footer { background: var(--primary); color: rgba(255,255,255,.8); }
    footer h6 { color: #fff; font-weight: 700; margin-bottom: 1rem; }
    footer a { color: rgba(255,255,255,.7); text-decoration: none; }
    footer a:hover { color: var(--accent); }
    footer .social-link { width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,.1); display: inline-flex; align-items: center; justify-content: center; margin-right: .4rem; transition: background .2s; }
    footer .social-link:hover { background: var(--accent); }
    footer .border-top { border-color: rgba(255,255,255,.15) !important; }

    /* ── Navbar dropdown ── */
    .navbar-public .dropdown-menu {
      background: var(--primary);
      border: none;
      border-top: 3px solid var(--accent);
      border-radius: 0 0 10px 10px;
      min-width: 200px;
      padding: .4rem 0;
      box-shadow: 0 8px 24px rgba(0,0,0,.2);
    }
    .navbar-public .dropdown-item {
      color: rgba(255,255,255,.85);
      font-size: .88rem;
      font-weight: 500;
      padding: .5rem 1.2rem;
    }
    .navbar-public .dropdown-item:hover, .navbar-public .dropdown-item:focus {
      background: rgba(255,255,255,.1);
      color: #fff;
    }
    .navbar-public .dropdown-item.active-item { color: var(--accent); }
    .navbar-public .dropdown-divider { border-color: rgba(255,255,255,.1); }
    #backToTop { position: fixed; bottom: 24px; right: 24px; width: 44px; height: 44px; border-radius: 10px; background: var(--primary); color: #fff; border: none; display: none; align-items: center; justify-content: center; z-index: 999; box-shadow: 0 4px 14px rgba(0,0,0,.2); }
    #backToTop.show { display: flex; }
  </style>
  @stack('styles')
</head>
<body>

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg navbar-dark navbar-public fixed-top" id="mainNavbar">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('landing.index') }}">
      @if(setting_url('logo_putih'))
        <img src="{{ setting_url('logo_putih') }}" height="36" alt="Logo">
      @else
        <div style="width:36px;height:36px;background:rgba(255,255,255,.2);border-radius:8px;display:flex;align-items:center;justify-content:center">
          <i class="fas fa-graduation-cap" style="color:#fff;font-size:1.1rem"></i>
        </div>
      @endif
      <span>{{ setting('sekolah_nama', 'SMK 48') }}</span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
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
            <li>
              <a class="dropdown-item {{ request()->routeIs('landing.tentang') ? 'active-item' : '' }}" href="{{ route('landing.tentang') }}">
                <i class="fas fa-school me-2" style="width:16px;opacity:.7"></i>Tentang Sekolah
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('landing.program') ? 'active-item' : '' }}" href="{{ route('landing.program') }}">
                <i class="fas fa-graduation-cap me-2" style="width:16px;opacity:.7"></i>Program Keahlian
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('landing.organisasi') ? 'active-item' : '' }}" href="{{ route('landing.organisasi') }}">
                <i class="fas fa-sitemap me-2" style="width:16px;opacity:.7"></i>Struktur Organisasi
              </a>
            </li>
          </ul>
        </li>

        {{-- Dropdown: Informasi --}}
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->routeIs('berita.*','galeri.*') ? 'active' : '' }}"
             href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Informasi
          </a>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item {{ request()->routeIs('berita.*') ? 'active-item' : '' }}" href="{{ route('berita.index') }}">
                <i class="fas fa-newspaper me-2" style="width:16px;opacity:.7"></i>Berita & Artikel
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('galeri.*') ? 'active-item' : '' }}" href="{{ route('galeri.index') }}">
                <i class="fas fa-images me-2" style="width:16px;opacity:.7"></i>Galeri Foto
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('ppdb.*') ? 'active' : '' }}" href="{{ route('ppdb.index') }}">PPDB</a>
        </li>

        @if(setting('ppdb_aktif') == '1')
          <li class="nav-item ms-lg-2">
            <a class="nav-link btn-ppdb" href="{{ route('ppdb.index') }}">
              <i class="fas fa-file-alt me-1"></i> Daftar Sekarang
            </a>
          </li>
        @endif

        <li class="nav-item ms-lg-2">
          <a class="nav-link" href="{{ route('login') }}" style="opacity:.7">
            <i class="fas fa-sign-in-alt me-1"></i> Login
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

{{-- Content --}}
<main>
  @yield('content')
</main>

{{-- Footer --}}
<footer class="py-5 mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <h6>{{ setting('sekolah_nama', 'SMK Negeri 48') }}</h6>
        <p style="font-size:.88rem;line-height:1.7">{{ setting('sekolah_tagline') }}</p>
        <div class="mt-3">
          @if(setting('sosmed_instagram'))
            <a href="{{ setting('sosmed_instagram') }}" target="_blank" class="social-link"><i class="fab fa-instagram"></i></a>
          @endif
          @if(setting('sosmed_facebook'))
            <a href="{{ setting('sosmed_facebook') }}" target="_blank" class="social-link"><i class="fab fa-facebook-f"></i></a>
          @endif
          @if(setting('sosmed_youtube'))
            <a href="{{ setting('sosmed_youtube') }}" target="_blank" class="social-link"><i class="fab fa-youtube"></i></a>
          @endif
          @if(setting('sosmed_tiktok'))
            <a href="{{ setting('sosmed_tiktok') }}" target="_blank" class="social-link"><i class="fab fa-tiktok"></i></a>
          @endif
        </div>
      </div>
      <div class="col-lg-2 col-sm-6">
        <h6>Navigasi</h6>
        <ul class="list-unstyled" style="font-size:.88rem;line-height:2">
          <li><a href="{{ route('landing.index') }}">Beranda</a></li>
          <li><a href="{{ route('landing.tentang') }}">Tentang Kami</a></li>
          <li><a href="{{ route('landing.program') }}">Program Keahlian</a></li>
          <li><a href="{{ route('landing.organisasi') }}">Struktur Organisasi</a></li>
          <li><a href="{{ route('berita.index') }}">Berita</a></li>
          <li><a href="{{ route('galeri.index') }}">Galeri</a></li>
          <li><a href="{{ route('ppdb.index') }}">Portal PPDB</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-sm-6">
        <h6>Kontak</h6>
        <ul class="list-unstyled" style="font-size:.88rem;line-height:2">
          <li><i class="fas fa-map-marker-alt me-2" style="color:var(--accent)"></i>{{ setting('sekolah_alamat') }}</li>
          <li><i class="fas fa-phone me-2" style="color:var(--accent)"></i>{{ setting('sekolah_no_telp') }}</li>
          <li><i class="fas fa-envelope me-2" style="color:var(--accent)"></i>{{ setting('sekolah_email') }}</li>
        </ul>
      </div>
      <div class="col-lg-3">
        <h6>PPDB {{ setting('ppdb_tahun_ajaran') }}</h6>
        @if(setting('ppdb_aktif') == '1')
          <span class="badge" style="background:var(--accent)">Pendaftaran Dibuka</span>
          <p style="font-size:.85rem;margin-top:.75rem">{{ setting('ppdb_info') }}</p>
          <a href="{{ route('ppdb.index') }}" class="btn btn-sm" style="background:var(--accent);color:#fff;border-radius:6px">
            Daftar Sekarang
          </a>
        @else
          <span class="badge bg-secondary">Pendaftaran Ditutup</span>
          <p style="font-size:.85rem;margin-top:.75rem">Pantau informasi PPDB selanjutnya di sini.</p>
        @endif
      </div>
    </div>
    <div class="border-top mt-4 pt-3 d-flex flex-wrap justify-content-between align-items-center" style="font-size:.8rem">
      <span>&copy; {{ date('Y') }} {{ setting('sekolah_nama', 'SMK Negeri 48') }}. Hak cipta dilindungi.</span>
      <span>NPSN: {{ setting('sekolah_npsn', '-') }}</span>
    </div>
  </div>
</footer>

{{-- Back to Top --}}
<button id="backToTop" title="Kembali ke atas"><i class="fas fa-chevron-up"></i></button>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Navbar shadow on scroll
  const nav = document.getElementById('mainNavbar');
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 50);
    const btn = document.getElementById('backToTop');
    btn.classList.toggle('show', window.scrollY > 300);
  });
  document.getElementById('backToTop').addEventListener('click', () => window.scrollTo({top: 0, behavior: 'smooth'}));
</script>
@stack('scripts')
</body>
</html>
