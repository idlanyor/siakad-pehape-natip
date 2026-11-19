<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', setting('sekolah_nama', 'SIAKAD SMK')) — @yield('page_title', 'Website Resmi')</title>
  <meta name="description" content="@yield('meta_desc', setting('sekolah_tagline', ''))">
  <meta name="theme-color" content="#0b3a5e">

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
  @if(setting_url('favicon'))
  <link rel="icon" href="{{ setting_url('favicon') }}">
  @endif

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <script>document.documentElement.classList.add('js')</script>

  <style>
    :root {
      /* Token desain. Nama lama (--primary, --accent, ...) tetap dipakai halaman publik lain. */
      --primary: #0b3a5e;
      --primary-light: #1b5d8f;
      --primary-soft: #e8f0f7;
      --accent: #f59e0b;
      --accent-dark: #d97706;
      --accent-soft: #fef3c7;
      --ink: #0f172a;
      --text-dark: #1e293b;
      --text-muted: #64748b;
      --line: #e2e8f0;
      --bg-light: #f6f8fb;
      --bg-gray: #eef2f6;
      --radius: 20px;
      --radius-sm: 12px;
      --shadow-sm: 0 1px 2px rgba(15, 23, 42, .06), 0 1px 3px rgba(15, 23, 42, .04);
      --shadow: 0 10px 30px -12px rgba(15, 23, 42, .18);
      --nav-h: 72px;

      --bs-body-font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
      --bs-body-color: var(--text-dark);
      --bs-link-color-rgb: 27, 93, 143;
      --bs-link-hover-color-rgb: 11, 58, 94;
    }

    html { scroll-behavior: smooth; scroll-padding-top: calc(var(--nav-h) + 16px); }
    @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }

    body {
      background: #fff;
      color: var(--text-dark);
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
      overflow-x: hidden;
    }
    /* Warna heading tidak diset global: header halaman lain mewarisi .text-white dari induknya */
    h1, h2, h3, h4, h5, h6 { letter-spacing: -.02em; }
    ::selection { background: var(--accent-soft); color: var(--ink); }
    :focus-visible { outline: 3px solid rgba(245, 158, 11, .55); outline-offset: 2px; border-radius: 6px; }

    /* Tombol Bootstrap disesuaikan dengan warna sekolah agar halaman lain ikut konsisten */
    .btn { font-weight: 600; border-radius: 999px; }
    .btn-primary {
      --bs-btn-bg: var(--primary); --bs-btn-border-color: var(--primary);
      --bs-btn-hover-bg: #082c48; --bs-btn-hover-border-color: #082c48;
      --bs-btn-active-bg: #072640; --bs-btn-active-border-color: #072640;
    }
    .btn-outline-primary {
      --bs-btn-color: var(--primary); --bs-btn-border-color: var(--primary);
      --bs-btn-hover-bg: var(--primary); --bs-btn-hover-border-color: var(--primary);
    }
    .btn-accent { background: var(--accent); color: var(--ink); border: 0; }
    .btn-accent:hover, .btn-accent:focus { background: var(--accent-dark); color: var(--ink); }
    .text-primary { color: var(--primary) !important; }
    .bg-primary { background-color: var(--primary) !important; }

    /* ── Navbar ── */
    .site-nav {
      position: sticky; top: 0; z-index: 1030;
      background: rgba(255, 255, 255, .82);
      backdrop-filter: saturate(180%) blur(14px);
      -webkit-backdrop-filter: saturate(180%) blur(14px);
      border-bottom: 1px solid transparent;
      transition: border-color .2s, box-shadow .2s;
    }
    .site-nav.scrolled { border-bottom-color: var(--line); box-shadow: 0 6px 24px -18px rgba(15, 23, 42, .35); }
    .site-nav .navbar { min-height: var(--nav-h); padding: .5rem 0; }
    .brand { display: flex; align-items: center; gap: .75rem; text-decoration: none; min-width: 0; }
    .brand-mark {
      width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
      display: grid; place-items: center;
      background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff;
    }
    .brand img { height: 42px; width: auto; }
    .min-w-0 { min-width: 0; }
    .brand-name { font-weight: 800; color: var(--ink); font-size: 1.02rem; line-height: 1.15; letter-spacing: -.01em; }
    .brand-sub { display: block; font-size: .74rem; font-weight: 500; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 260px; }

    .site-nav .nav-link {
      color: var(--text-dark); font-weight: 600; font-size: .93rem;
      padding: .5rem .85rem !important; border-radius: 999px; transition: background .15s, color .15s;
    }
    .site-nav .nav-link:hover { color: var(--primary); background: var(--bg-gray); }
    .site-nav .nav-link.active { color: var(--primary); background: var(--primary-soft); }
    .site-nav .dropdown-menu {
      border: 1px solid var(--line); border-radius: 16px; padding: .4rem;
      box-shadow: var(--shadow); margin-top: .5rem !important; min-width: 230px;
    }
    .site-nav .dropdown-item { border-radius: 10px; font-weight: 500; font-size: .92rem; padding: .55rem .8rem; color: var(--text-dark); }
    .site-nav .dropdown-item i { width: 1.25rem; color: var(--text-muted); }
    .site-nav .dropdown-item:hover, .site-nav .dropdown-item.active-item { background: var(--bg-light); color: var(--primary); }
    .site-nav .dropdown-item.active-item i { color: var(--primary); }
    .nav-actions .btn { padding: .55rem 1.1rem; font-size: .9rem; }
    .navbar-toggler { border: 0; width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; color: var(--ink); }
    .navbar-toggler:focus { box-shadow: none; }

    @media (max-width: 991.98px) {
      .site-nav .navbar-collapse { padding: .75rem 0 1rem; }
      .site-nav .nav-link { padding: .7rem .9rem !important; }
      .site-nav .dropdown-menu { border: 0; box-shadow: none; padding: 0 0 0 .75rem; margin: 0 !important; }
      .nav-actions { border-top: 1px solid var(--line); padding-top: 1rem; margin-top: .5rem; }
      .nav-actions .btn { flex: 1; }
      .brand-sub { max-width: 52vw; }
    }

    /* ── Utilitas section (dipakai juga oleh halaman publik lain) ── */
    .eyebrow {
      display: inline-flex; align-items: center; gap: .5rem;
      font-size: .78rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--accent-dark);
    }
    .eyebrow::before { content: ''; width: 18px; height: 2px; background: currentColor; border-radius: 2px; }
    .section-title { font-size: clamp(1.75rem, 3vw, 2.4rem); font-weight: 800; color: var(--ink); letter-spacing: -.03em; line-height: 1.15; margin-bottom: .5rem; }
    .section-subtitle { color: var(--text-muted); font-size: 1.05rem; line-height: 1.7; }
    .section-divider { display: none; }
    .link-arrow { font-weight: 700; text-decoration: none; color: var(--primary); display: inline-flex; align-items: center; gap: .4rem; white-space: nowrap; }
    .link-arrow i { transition: transform .2s; }
    .link-arrow:hover i { transform: translateX(3px); }

    /* Gutter 3rem lebih lebar dari padding container sehingga halaman bergeser ke samping di HP */
    @media (max-width: 991.98px) { .row.g-5 { --bs-gutter-x: 1.5rem; } }

    /* ── Reveal saat scroll (hanya bila JS aktif & gerak tidak dikurangi) ── */
    @media (prefers-reduced-motion: no-preference) {
      .js .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s cubic-bezier(.2, .7, .2, 1); transition-delay: var(--d, 0s); }
      .js .reveal.in { opacity: 1; transform: none; }
    }

    /* ── Footer ── */
    .site-footer { background: #071f33; color: rgba(255, 255, 255, .68); padding: 4.5rem 0 0; font-size: .95rem; }
    .site-footer h6 { color: #fff; font-weight: 700; font-size: .82rem; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 1.25rem; }
    .site-footer a { color: rgba(255, 255, 255, .72); text-decoration: none; transition: color .15s; }
    .site-footer a:hover { color: #fff; }
    .site-footer .links li { margin-bottom: .7rem; }
    .site-footer .contact li { display: flex; gap: .85rem; margin-bottom: 1rem; line-height: 1.55; }
    .site-footer .contact i { color: var(--accent); margin-top: .25rem; width: 1rem; text-align: center; }
    .footer-brand .brand-mark { background: rgba(255, 255, 255, .1); }
    .footer-brand .brand-name { color: #fff; font-size: 1.15rem; }
    .socials { display: flex; gap: .5rem; }
    .socials a {
      width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center;
      background: rgba(255, 255, 255, .08); color: #fff; transition: background .15s, transform .15s;
    }
    .socials a:hover { background: var(--accent); color: var(--ink); transform: translateY(-2px); }
    .map-frame { border-radius: 16px; overflow: hidden; border: 1px solid rgba(255, 255, 255, .1); height: 170px; }
    .map-frame iframe { width: 100%; height: 100%; border: 0; filter: grayscale(.3); }
    .map-empty { height: 170px; border-radius: 16px; border: 1px dashed rgba(255, 255, 255, .18); display: grid; place-items: center; color: rgba(255, 255, 255, .45); font-size: .9rem; }
    .footer-bottom { border-top: 1px solid rgba(255, 255, 255, .08); margin-top: 3.5rem; padding: 1.4rem 0; font-size: .85rem; color: rgba(255, 255, 255, .5); }

    #backToTop {
      position: fixed; right: 20px; bottom: 20px; z-index: 1020;
      width: 46px; height: 46px; border-radius: 14px; border: 0;
      background: var(--ink); color: #fff; box-shadow: var(--shadow);
      display: grid; place-items: center;
      opacity: 0; visibility: hidden; transform: translateY(8px); transition: all .2s;
    }
    #backToTop.show { opacity: 1; visibility: visible; transform: none; }
    #backToTop:hover { background: var(--primary); }
  </style>
  @stack('styles')
</head>
<body>

<header class="site-nav" id="siteNav">
  <nav class="navbar navbar-expand-lg" aria-label="Navigasi utama">
    <div class="container">
      <a class="brand me-3" href="{{ route('landing.index') }}">
        @if(setting_url('logo'))
          <img src="{{ setting_url('logo') }}" alt="Logo {{ setting('sekolah_nama') }}">
        @else
          <span class="brand-mark" aria-hidden="true"><i class="fas fa-graduation-cap"></i></span>
        @endif
        <span class="min-w-0">
          <span class="brand-name">{{ setting('sekolah_nama', 'SIAKAD SMK') }}</span>
          <span class="brand-sub">{{ setting('sekolah_tagline', 'Unggul, Berkarakter, Berbudaya') }}</span>
        </span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
              aria-controls="navMenu" aria-expanded="false" aria-label="Buka menu">
        <i class="fas fa-bars fa-lg"></i>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-lg-auto gap-lg-1">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('landing.index') ? 'active' : '' }}" href="{{ route('landing.index') }}">Beranda</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ request()->routeIs('landing.tentang','landing.program','landing.organisasi') ? 'active' : '' }}"
               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Profil</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item {{ request()->routeIs('landing.tentang') ? 'active-item' : '' }}" href="{{ route('landing.tentang') }}"><i class="fas fa-school"></i> Tentang Sekolah</a></li>
              <li><a class="dropdown-item {{ request()->routeIs('landing.program') ? 'active-item' : '' }}" href="{{ route('landing.program') }}"><i class="fas fa-layer-group"></i> Program Keahlian</a></li>
              <li><a class="dropdown-item {{ request()->routeIs('landing.organisasi') ? 'active-item' : '' }}" href="{{ route('landing.organisasi') }}"><i class="fas fa-sitemap"></i> Struktur Organisasi</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ request()->routeIs('berita.*','galeri.*') ? 'active' : '' }}"
               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Informasi</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item {{ request()->routeIs('berita.*') ? 'active-item' : '' }}" href="{{ route('berita.index') }}"><i class="far fa-newspaper"></i> Berita & Pengumuman</a></li>
              <li><a class="dropdown-item {{ request()->routeIs('galeri.*') ? 'active-item' : '' }}" href="{{ route('galeri.index') }}"><i class="far fa-images"></i> Galeri Foto</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('ppdb.*') ? 'active' : '' }}" href="{{ route('ppdb.landing') }}">PPDB</a>
          </li>
        </ul>

        <div class="nav-actions d-flex gap-2">
          <a class="btn btn-outline-primary" href="{{ route('login') }}"><i class="fas fa-arrow-right-to-bracket me-1"></i> Masuk</a>
          @if(setting('ppdb_aktif') == '1')
            <a class="btn btn-accent" href="{{ route('ppdb.index') }}">Daftar PPDB</a>
          @endif
        </div>
      </div>
    </div>
  </nav>
</header>

<main style="min-height: 60vh;">
  @yield('content')
</main>

<footer class="site-footer">
  <div class="container">
    <div class="row g-4 g-lg-5">
      <div class="col-lg-4">
        <div class="brand footer-brand mb-3">
          @if(setting_url('logo_putih'))
            <img src="{{ setting_url('logo_putih') }}" alt="Logo {{ setting('sekolah_nama') }}">
          @else
            <span class="brand-mark" aria-hidden="true"><i class="fas fa-graduation-cap"></i></span>
          @endif
          <span>
            <span class="brand-name">{{ setting('sekolah_nama', 'SIAKAD SMK') }}</span>
            @if(setting('sekolah_npsn'))<span class="d-block small" style="color:var(--accent)">NPSN {{ setting('sekolah_npsn') }}</span>@endif
          </span>
        </div>
        <p class="mb-4" style="line-height:1.75;max-width:360px">{{ setting('sekolah_tagline', 'Mencetak generasi unggul, berkarakter, dan siap bersaing di era global.') }}</p>
        <div class="socials">
          @if(setting('sosmed_instagram'))<a href="{{ setting('sosmed_instagram') }}" aria-label="Instagram" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>@endif
          @if(setting('sosmed_facebook'))<a href="{{ setting('sosmed_facebook') }}" aria-label="Facebook" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>@endif
          @if(setting('sosmed_youtube'))<a href="{{ setting('sosmed_youtube') }}" aria-label="YouTube" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>@endif
          @if(setting('sosmed_tiktok'))<a href="{{ setting('sosmed_tiktok') }}" aria-label="TikTok" target="_blank" rel="noopener"><i class="fab fa-tiktok"></i></a>@endif
        </div>
      </div>

      <div class="col-6 col-lg-2">
        <h6>Jelajahi</h6>
        <ul class="list-unstyled links mb-0">
          <li><a href="{{ route('landing.tentang') }}">Tentang Sekolah</a></li>
          <li><a href="{{ route('landing.program') }}">Program Keahlian</a></li>
          <li><a href="{{ route('berita.index') }}">Berita</a></li>
          <li><a href="{{ route('galeri.index') }}">Galeri</a></li>
          <li><a href="{{ route('ppdb.landing') }}">PPDB</a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-2">
        <h6>Layanan</h6>
        <ul class="list-unstyled links mb-0">
          <li><a href="{{ route('login') }}">Portal SIAKAD</a></li>
          <li><a href="{{ route('ujian.login') }}">Ujian Online</a></li>
          @if(setting('ppdb_aktif') == '1')<li><a href="{{ route('ppdb.index') }}">Daftar PPDB</a></li>@endif
        </ul>
      </div>

      <div class="col-lg-4">
        <h6>Kontak</h6>
        <ul class="list-unstyled contact mb-4">
          <li><i class="fas fa-location-dot"></i><span>{{ setting('sekolah_alamat', 'Jl. Pendidikan No. 1, Jakarta Timur') }}</span></li>
          @if(setting('sekolah_no_telp'))<li><i class="fas fa-phone"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('sekolah_no_telp')) }}">{{ setting('sekolah_no_telp') }}</a></li>@endif
          @if(setting('sekolah_email'))<li><i class="fas fa-envelope"></i><a href="mailto:{{ setting('sekolah_email') }}">{{ setting('sekolah_email') }}</a></li>@endif
        </ul>
        @if(setting('maps_embed'))
          <div class="map-frame"><iframe src="{{ setting('maps_embed') }}" title="Peta lokasi sekolah" loading="lazy" allowfullscreen></iframe></div>
        @endif
      </div>
    </div>

    <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between gap-2">
      <span>&copy; {{ date('Y') }} {{ setting('sekolah_nama', 'SIAKAD SMK') }}. Hak cipta dilindungi.</span>
      <span>Didukung Sistem Informasi Akademik</span>
    </div>
  </div>
</footer>

<button id="backToTop" type="button" aria-label="Kembali ke atas"><i class="fas fa-arrow-up"></i></button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (() => {
    const nav = document.getElementById('siteNav');
    const top = document.getElementById('backToTop');
    const onScroll = () => {
      nav.classList.toggle('scrolled', window.scrollY > 8);
      top.classList.toggle('show', window.scrollY > 600);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    top.addEventListener('click', () => window.scrollTo({ top: 0 }));

    // Elemen .reveal muncul saat masuk layar. Tanpa IntersectionObserver langsung ditampilkan.
    const items = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) { items.forEach(el => el.classList.add('in')); return; }
    const io = new IntersectionObserver(entries => entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
    }), { rootMargin: '0px 0px -8% 0px' });
    items.forEach(el => io.observe(el));
  })();
</script>
@stack('scripts')
</body>
</html>
