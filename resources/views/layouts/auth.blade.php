<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="robots" content="noindex">
  <meta name="theme-color" content="#0b3a5e">
  <title>@yield('title', 'Masuk') — {{ setting('sekolah_nama', 'SIAKAD SMK') }}</title>
  @if(setting_url('favicon'))<link rel="icon" href="{{ setting_url('favicon') }}">@endif

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #0b3a5e; --primary-light: #1b5d8f; --primary-soft: #e8f0f7;
      --accent: #f59e0b; --accent-dark: #d97706;
      --ink: #0f172a; --text: #1e293b; --muted: #64748b; --line: #e2e8f0; --bg: #f6f8fb;
      --bs-body-font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    }
    html, body { min-height: 100%; }
    body { margin: 0; background: #fff; color: var(--text); -webkit-font-smoothing: antialiased; }
    :focus-visible { outline: 3px solid rgba(245, 158, 11, .55); outline-offset: 2px; }

    .auth { min-height: 100vh; min-height: 100dvh; display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); }

    /* ── Panel brand (desktop) ── */
    .auth-aside {
      position: relative; overflow: hidden; color: #fff; padding: 3rem clamp(2rem, 5vw, 4.5rem);
      display: flex; flex-direction: column; justify-content: space-between; gap: 3rem;
      background:
        radial-gradient(700px 420px at 100% 0%, rgba(245, 158, 11, .3), transparent 60%),
        radial-gradient(600px 420px at 0% 100%, rgba(59, 130, 184, .4), transparent 60%),
        linear-gradient(150deg, #061a2b 0%, var(--primary) 55%, #14507e 100%);
    }
    .auth-aside::before {
      content: ''; position: absolute; inset: 0; pointer-events: none;
      background-image: radial-gradient(rgba(255, 255, 255, .08) 1px, transparent 1px); background-size: 24px 24px;
      mask-image: linear-gradient(160deg, #000 20%, transparent 80%); -webkit-mask-image: linear-gradient(160deg, #000 20%, transparent 80%);
    }
    .auth-aside > * { position: relative; }
    .brand { display: inline-flex; align-items: center; gap: .8rem; color: inherit; text-decoration: none; }
    .brand-mark { width: 44px; height: 44px; border-radius: 13px; display: grid; place-items: center; flex-shrink: 0; background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .18); }
    .brand img { height: 44px; width: auto; }
    .brand strong { display: block; font-size: 1.05rem; font-weight: 800; letter-spacing: -.01em; line-height: 1.2; }
    .brand small { display: block; font-size: .78rem; opacity: .7; }
    .aside-copy h2 { color: #fff; font-size: clamp(2rem, 3.2vw, 2.9rem); font-weight: 800; letter-spacing: -.035em; line-height: 1.1; margin-bottom: 1rem; max-width: 520px; }
    .aside-copy h2 em { font-style: normal; color: var(--accent); }
    .aside-copy p { color: rgba(255, 255, 255, .72); font-size: 1.05rem; line-height: 1.7; max-width: 460px; margin-bottom: 2rem; }
    .aside-list { list-style: none; padding: 0; margin: 0; display: grid; gap: .75rem; max-width: 460px; }
    .aside-list li { display: flex; gap: .85rem; align-items: center; padding: .8rem 1rem; border-radius: 14px; background: rgba(255, 255, 255, .06); border: 1px solid rgba(255, 255, 255, .1); font-size: .93rem; }
    .aside-list i { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; background: rgba(255, 255, 255, .1); color: var(--accent); flex-shrink: 0; }
    .aside-list b { display: block; font-weight: 700; }
    .aside-list span { color: rgba(255, 255, 255, .65); font-size: .84rem; }
    .aside-foot { font-size: .82rem; color: rgba(255, 255, 255, .5); }

    /* ── Area form ── */
    .auth-main { display: flex; flex-direction: column; padding: 2rem clamp(1.25rem, 4vw, 3rem); background: #fff; }
    .auth-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
    .auth-top .brand { color: var(--ink); }
    .auth-top .brand-mark { background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; border: 0; }
    .auth-top .brand small { color: var(--muted); opacity: 1; }
    .back { display: inline-flex; align-items: center; gap: .4rem; font-size: .88rem; font-weight: 600; color: var(--muted); text-decoration: none; padding: .45rem .8rem; border-radius: 999px; }
    .back:hover { background: var(--bg); color: var(--ink); }
    .auth-box { width: 100%; max-width: 400px; margin: auto; padding: 2.5rem 0; }
    .auth-ic { width: 52px; height: 52px; border-radius: 16px; display: grid; place-items: center; background: var(--primary-soft); color: var(--primary); font-size: 1.25rem; margin-bottom: 1.25rem; }
    .auth-box h1 { font-size: clamp(1.6rem, 3vw, 2rem); font-weight: 800; letter-spacing: -.03em; color: var(--ink); margin-bottom: .4rem; }
    .auth-box .lead-sm { color: var(--muted); font-size: .98rem; line-height: 1.6; margin-bottom: 1.75rem; }

    .field { margin-bottom: 1.1rem; }
    .field label { display: block; font-weight: 600; font-size: .88rem; color: var(--text); margin-bottom: .45rem; }
    .field-row { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; }
    .field-row a { font-size: .85rem; font-weight: 600; color: var(--primary-light); text-decoration: none; }
    .field-row a:hover { text-decoration: underline; }
    .ctl { position: relative; }
    .ctl > i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .92rem; pointer-events: none; }
    .ctl .form-control {
      height: 50px; border-radius: 12px; border: 1.5px solid var(--line); padding-left: 2.75rem; font-size: 1rem; color: var(--ink);
      transition: border-color .15s, box-shadow .15s;
    }
    .ctl .form-control.no-ic { padding-left: 1rem; }
    .ctl .form-control::placeholder { color: #a0aec0; }
    .ctl .form-control:focus { border-color: var(--primary-light); box-shadow: 0 0 0 4px rgba(27, 93, 143, .12); }
    .ctl .form-control.is-invalid { border-color: #dc2626; background-image: none; }
    .ctl .form-control.has-toggle { padding-right: 3rem; }
    .pw-toggle {
      position: absolute; right: .4rem; top: 50%; transform: translateY(-50%);
      width: 40px; height: 40px; border: 0; border-radius: 10px; background: transparent; color: #94a3b8;
      display: grid; place-items: center;
    }
    .pw-toggle:hover { background: var(--bg); color: var(--ink); }
    .field-error { color: #dc2626; font-size: .84rem; margin-top: .4rem; }

    .check { display: flex; align-items: center; gap: .55rem; font-size: .9rem; color: var(--muted); cursor: pointer; user-select: none; }
    .check input { width: 18px; height: 18px; accent-color: var(--primary); margin: 0; }

    .btn-main {
      width: 100%; height: 50px; border: 0; border-radius: 12px; font-weight: 700; font-size: 1rem;
      background: var(--primary); color: #fff; display: inline-flex; align-items: center; justify-content: center; gap: .55rem;
      transition: background .15s, transform .1s;
    }
    .btn-main:hover { background: #082c48; }
    .btn-main:active { transform: scale(.99); }
    .btn-main[disabled] { opacity: .75; cursor: progress; }
    .btn-main .spinner-border { width: 1.1rem; height: 1.1rem; border-width: .16em; }

    .alert-x { display: flex; gap: .7rem; padding: .85rem 1rem; border-radius: 12px; font-size: .9rem; line-height: 1.5; margin-bottom: 1.25rem; }
    .alert-x i { margin-top: .2rem; }
    .alert-x.err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .alert-x.ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }

    .alt { margin-top: 1.75rem; padding-top: 1.5rem; border-top: 1px solid var(--line); }
    .alt-link {
      display: flex; align-items: center; gap: .9rem; padding: .9rem 1rem; border-radius: 14px; border: 1px solid var(--line);
      text-decoration: none; color: var(--text); transition: border-color .15s, background .15s;
    }
    .alt-link:hover { border-color: var(--primary-light); background: var(--bg); color: var(--text); }
    .alt-link .ic { width: 38px; height: 38px; border-radius: 11px; display: grid; place-items: center; background: #fef3c7; color: var(--accent-dark); flex-shrink: 0; }
    .alt-link b { display: block; font-size: .93rem; color: var(--ink); }
    .alt-link span { font-size: .83rem; color: var(--muted); }
    .alt-link .go { margin-left: auto; color: var(--muted); }
    .link-btn { background: none; border: 0; padding: 0; color: var(--primary-light); font-weight: 600; font-size: .9rem; }
    .link-btn:hover { text-decoration: underline; }
    .auth-foot { text-align: center; font-size: .8rem; color: var(--muted); }

    @media (max-width: 991.98px) {
      .auth { grid-template-columns: 1fr; }
      .auth-aside { display: none; }
      .auth-main { padding: 1.25rem 1rem 1.5rem; }
      .auth-box { padding: 2rem 0; margin-top: 1rem; }
    }
    @media (min-width: 992px) {
      .auth-top .brand { visibility: hidden; }
      /* Desktop: tepat satu layar. Panel brand dipotong, area form yang bergulir bila layar sangat pendek. */
      .auth { height: 100vh; height: 100dvh; }
      .auth-aside { overflow: hidden; }
      .auth-main { overflow-y: auto; }
    }
    /* Laptop dengan layar pendek (mis. 1366×768, 1280×720): rapatkan spasi agar tetap muat tanpa gulir */
    @media (min-width: 992px) and (max-height: 860px) {
      .auth-main { padding-top: 1.25rem; padding-bottom: 1.25rem; }
      .auth-box { padding: .75rem 0; }
      .auth-ic { width: 44px; height: 44px; border-radius: 13px; font-size: 1.05rem; margin-bottom: .85rem; }
      .auth-box h1 { font-size: 1.7rem; }
      .auth-box .lead-sm { margin-bottom: 1.1rem; }
      .field { margin-bottom: .85rem; }
      .ctl .form-control, .btn-main { height: 46px; }
      .alert-x { padding: .65rem .9rem; margin-bottom: .9rem; }
      .alt { margin-top: 1.1rem; padding-top: 1.1rem; }
      .alt-link { padding: .7rem .9rem; }
      .auth-aside { padding-top: 2rem; padding-bottom: 2rem; gap: 1.5rem; }
      .aside-copy h2 { font-size: clamp(1.8rem, 2.8vw, 2.4rem); }
      .aside-copy p { margin-bottom: 1.25rem; }
      .aside-list { gap: .55rem; }
      .aside-list li { padding: .6rem .9rem; }
    }
    @media (min-width: 992px) and (max-height: 720px) {
      .auth-ic { display: none; }
      .auth-box .lead-sm { font-size: .92rem; }
      .aside-list li span { display: none; }
    }
    @media (min-width: 992px) and (max-height: 620px) {
      .aside-list, .aside-copy p { display: none; }
    }
  </style>
  @stack('styles')
</head>
<body>
<div class="auth">
  <aside class="auth-aside" aria-hidden="true">
    <a class="brand" href="{{ route('landing.index') }}" tabindex="-1">
      @if(setting_url('logo_putih'))
        <img src="{{ setting_url('logo_putih') }}" alt="">
      @else
        <span class="brand-mark"><i class="fas fa-graduation-cap"></i></span>
      @endif
      <span><strong>{{ setting('sekolah_nama', 'SIAKAD SMK') }}</strong><small>Sistem Informasi Akademik</small></span>
    </a>
    <div class="aside-copy">
      @yield('aside')
    </div>
    <div class="aside-foot">&copy; {{ date('Y') }} {{ setting('sekolah_nama', 'SIAKAD SMK') }}</div>
  </aside>

  <main class="auth-main">
    <div class="auth-top">
      <a class="brand" href="{{ route('landing.index') }}">
        @if(setting_url('logo'))
          <img src="{{ setting_url('logo') }}" alt="Logo {{ setting('sekolah_nama') }}">
        @else
          <span class="brand-mark"><i class="fas fa-graduation-cap"></i></span>
        @endif
        <span><strong>{{ setting('sekolah_nama', 'SIAKAD SMK') }}</strong><small>Sistem Informasi Akademik</small></span>
      </a>
      <a class="back" href="{{ route('landing.index') }}"><i class="fas fa-arrow-left"></i> <span class="d-none d-sm-inline">Website sekolah</span></a>
    </div>

    <div class="auth-box">
      @yield('content')
    </div>

    <div class="auth-foot">
      Butuh bantuan? Hubungi admin sekolah
      @if(setting('sekolah_no_telp'))
        di <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('sekolah_no_telp')) }}" class="text-reset">{{ setting('sekolah_no_telp') }}</a>
      @endif
    </div>
  </main>
</div>

<script>
  // Tombol tampilkan/sembunyikan password
  document.querySelectorAll('[data-pw-toggle]').forEach(btn => {
    const input = document.getElementById(btn.dataset.pwToggle);
    btn.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', show);
      btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
      btn.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
      input.focus();
    });
  });
  // Cegah kirim ganda & tampilkan status memproses
  document.querySelectorAll('form[data-busy]').forEach(form => {
    const btn = form.querySelector('.btn-main');
    if (!btn) return;
    const asli = btn.innerHTML;
    form.addEventListener('submit', () => {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border" role="status" aria-hidden="true"></span> ' + form.dataset.busy;
    });
    // Kembali lewat tombol Back (bfcache) jangan sampai tombol tetap terkunci
    window.addEventListener('pageshow', () => { btn.disabled = false; btn.innerHTML = asli; });
  });
</script>
@stack('scripts')
</body>
</html>
