<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — {{ setting('sekolah_nama', 'SIAKAD SMK') }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #1a3a6b;
      --primary-light: #2d5f9e;
      --accent: #e8a020;
    }
    * { font-family: 'Inter', sans-serif; }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 60%, #1e4d8c 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      position: relative;
      overflow: hidden;
    }

    /* Decorative circles */
    body::before {
      content: '';
      position: fixed;
      top: -15%; right: -10%;
      width: 500px; height: 500px;
      background: rgba(255,255,255,.04);
      border-radius: 50%;
      pointer-events: none;
    }
    body::after {
      content: '';
      position: fixed;
      bottom: -20%; left: -10%;
      width: 400px; height: 400px;
      background: rgba(255,255,255,.03);
      border-radius: 50%;
      pointer-events: none;
    }

    .login-card {
      width: 100%;
      max-width: 440px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 24px 64px rgba(0,0,0,.25);
      overflow: hidden;
      position: relative;
      z-index: 1;
    }

    .login-header {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      padding: 2.5rem 2rem;
      text-align: center;
    }
    .login-header .logo-wrap {
      width: 72px; height: 72px;
      border-radius: 18px;
      background: rgba(255,255,255,.15);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1rem;
    }
    .login-header h1 {
      font-size: 1.3rem;
      font-weight: 800;
      color: #fff;
      margin-bottom: .25rem;
    }
    .login-header p {
      font-size: .85rem;
      color: rgba(255,255,255,.7);
      margin: 0;
    }

    .login-body { padding: 2rem; }

    .form-label {
      font-weight: 600;
      font-size: .87rem;
      color: #374151;
      margin-bottom: .4rem;
    }
    .form-control {
      border-radius: 10px;
      border: 1.5px solid #e5e7eb;
      padding: .65rem 1rem;
      font-size: .95rem;
      transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(45,95,158,.12);
    }
    .input-icon { position: relative; }
    .input-icon i {
      position: absolute;
      left: 1rem; top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
      font-size: .9rem;
    }
    .input-icon .form-control { padding-left: 2.6rem; }

    .btn-login {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: .75rem;
      font-size: 1rem;
      font-weight: 700;
      width: 100%;
      transition: opacity .2s, transform .15s;
    }
    .btn-login:hover { opacity: .92; transform: translateY(-1px); color: #fff; }
    .btn-login:active { transform: translateY(0); }

    .divider {
      display: flex; align-items: center; gap: .75rem;
      margin: 1.25rem 0;
      color: #d1d5db;
      font-size: .78rem;
    }
    .divider::before, .divider::after {
      content: ''; flex: 1; height: 1px; background: #e5e7eb;
    }

    .back-link {
      text-align: center;
      margin-top: 1.25rem;
      font-size: .85rem;
      color: #6b7280;
    }
    .back-link a { color: var(--primary); font-weight: 600; text-decoration: none; }
    .back-link a:hover { text-decoration: underline; }

    .alert-error {
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 10px;
      color: #dc2626;
      padding: .75rem 1rem;
      font-size: .87rem;
      margin-bottom: 1.25rem;
      display: flex; align-items: flex-start; gap: .5rem;
    }

    /* Password toggle */
    .toggle-pw {
      position: absolute;
      right: 1rem; top: 50%;
      transform: translateY(-50%);
      background: none; border: none;
      color: #9ca3af; cursor: pointer; padding: 0;
      font-size: .9rem;
    }
    .toggle-pw:hover { color: var(--primary); }
    .pw-wrap { position: relative; }
    .pw-wrap .form-control { padding-right: 2.8rem; }
  </style>
</head>
<body>

<div class="login-card">
  {{-- Header --}}
  <div class="login-header">
    <div class="logo-wrap">
      @if(setting_url('logo_putih'))
        <img src="{{ setting_url('logo_putih') }}" height="42" alt="Logo">
      @elseif(setting_url('logo'))
        <img src="{{ setting_url('logo') }}" height="42" alt="Logo">
      @else
        <i class="fas fa-graduation-cap fa-2x" style="color:#fff"></i>
      @endif
    </div>
    <h1>{{ setting('sekolah_nama', 'SIAKAD SMK') }}</h1>
    <p>Sistem Informasi Akademik — Masuk ke akun Anda</p>
  </div>

  {{-- Body --}}
  <div class="login-body">

    {{-- Session status (e.g. password reset) --}}
    @if (session('status'))
      <div class="alert-error" style="background:#f0fff4;border-color:#bbf7d0;color:#16a34a">
        <i class="fas fa-check-circle mt-1 flex-shrink-0"></i>
        <span>{{ session('status') }}</span>
      </div>
    @endif

    {{-- Error --}}
    @if ($errors->any())
      <div class="alert-error">
        <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <div class="input-icon">
          <i class="fas fa-user"></i>
          <input id="username" type="text" name="username"
                 class="form-control" value="{{ old('username') }}"
                 placeholder="Masukkan username"
                 required autofocus autocomplete="username">
        </div>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="pw-wrap">
          <input id="password" type="password" name="password"
                 class="form-control" placeholder="Masukkan password"
                 required autocomplete="current-password">
          <button type="button" class="toggle-pw" onclick="togglePw()" tabindex="-1">
            <i class="fas fa-eye" id="pwIcon"></i>
          </button>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check m-0">
          <input class="form-check-input" type="checkbox" name="remember" id="remember">
          <label class="form-check-label" for="remember" style="font-size:.87rem;color:#6b7280">
            Ingat saya
          </label>
        </div>
        @if (Route::has('password.request'))
          <a href="{{ route('password.request') }}" style="font-size:.85rem;color:var(--primary);font-weight:600;text-decoration:none">
            Lupa password?
          </a>
        @endif
      </div>

      <button type="submit" class="btn-login">
        <i class="fas fa-sign-in-alt me-2"></i>Masuk
      </button>
    </form>

    <div class="divider">atau</div>

    <div class="back-link">
      <a href="{{ route('ujian.login') }}" class="d-block mb-2">
        <i class="fas fa-laptop-code me-1"></i>Siswa ujian online? Masuk dengan NIS
      </a>
      <a href="{{ route('landing.index') }}">
        <i class="fas fa-arrow-left me-1"></i>Kembali ke Website Sekolah
      </a>
    </div>
  </div>
</div>

<script>
function togglePw() {
  const input = document.getElementById('password');
  const icon  = document.getElementById('pwIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
}
</script>
</body>
</html>
