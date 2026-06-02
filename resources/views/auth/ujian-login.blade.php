<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login Ujian — {{ setting('sekolah_nama', 'SIAKAD SMK') }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #123c69;
      --primary-2: #1f6f8b;
      --accent: #f4b942;
      --ink: #172033;
    }

    * { font-family: Inter, sans-serif; }

    body {
      min-height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      background:
        linear-gradient(135deg, rgba(18,60,105,.94), rgba(31,111,139,.88)),
        url("{{ setting_url('login_background') ?: asset('images/login-bg.jpg') }}");
      background-size: cover;
      background-position: center;
    }

    .login-shell {
      width: 100%;
      max-width: 430px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 24px 70px rgba(10, 24, 44, .28);
      overflow: hidden;
    }

    .login-head {
      padding: 32px 28px 26px;
      text-align: center;
      background: #f8fafc;
      border-bottom: 1px solid #e8edf3;
    }

    .logo-box {
      width: 76px;
      height: 76px;
      margin: 0 auto 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      background: #fff;
      border: 1px solid #e6edf5;
    }

    .logo-box img {
      max-width: 58px;
      max-height: 58px;
      object-fit: contain;
    }

    .login-head h1 {
      color: var(--ink);
      font-size: 1.25rem;
      font-weight: 800;
      margin: 0;
    }

    .login-head p {
      color: #64748b;
      font-size: .9rem;
      margin: 6px 0 0;
    }

    .login-body { padding: 28px; }

    .form-label {
      color: #334155;
      font-size: .88rem;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .input-wrap { position: relative; }

    .input-wrap i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      font-size: .95rem;
    }

    .form-control {
      border: 1.5px solid #dbe3ee;
      border-radius: 8px;
      color: var(--ink);
      font-size: .96rem;
      min-height: 46px;
      padding-left: 42px;
    }

    .form-control:focus {
      border-color: var(--primary-2);
      box-shadow: 0 0 0 3px rgba(31, 111, 139, .13);
    }

    .toggle-password {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      border: 0;
      background: transparent;
      color: #94a3b8;
      padding: 0;
    }

    .password-input { padding-right: 42px; }

    .btn-ujian {
      width: 100%;
      min-height: 46px;
      border: 0;
      border-radius: 8px;
      background: var(--primary);
      color: #fff;
      font-weight: 800;
      letter-spacing: 0;
    }

    .btn-ujian:hover {
      background: #0f3158;
      color: #fff;
    }

    .alert-login {
      display: flex;
      gap: 10px;
      border-radius: 8px;
      padding: 12px 14px;
      margin-bottom: 18px;
      background: #fff1f2;
      border: 1px solid #fecdd3;
      color: #be123c;
      font-size: .9rem;
    }

    .back-link {
      display: block;
      margin-top: 18px;
      color: #64748b;
      font-size: .86rem;
      text-align: center;
      text-decoration: none;
    }

    .back-link:hover { color: var(--primary); }
  </style>
</head>
<body>
  <main class="login-shell">
    <header class="login-head">
      <div class="logo-box">
        @if(setting_url('logo'))
          <img src="{{ setting_url('logo') }}" alt="Logo">
        @else
          <i class="fas fa-school fa-2x" style="color: var(--primary)"></i>
        @endif
      </div>
      <h1>Login Ujian Siswa</h1>
      <p>{{ setting('sekolah_nama', 'SIAKAD SMK') }}</p>
    </header>

    <section class="login-body">
      @if ($errors->any())
        <div class="alert-login">
          <i class="fas fa-circle-exclamation mt-1"></i>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      <form method="POST" action="{{ route('ujian.login.store') }}">
        @csrf

        <div class="mb-3">
          <label for="nis" class="form-label">NIS</label>
          <div class="input-wrap">
            <i class="fas fa-id-card"></i>
            <input id="nis" type="text" name="nis" class="form-control" value="{{ old('nis') }}"
                   placeholder="Masukkan NIS" required autofocus autocomplete="username">
          </div>
        </div>

        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <div class="input-wrap">
            <i class="fas fa-lock"></i>
            <input id="password" type="password" name="password" class="form-control password-input"
                   placeholder="Masukkan password" required autocomplete="current-password">
            <button type="button" class="toggle-password" aria-label="Tampilkan password">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-ujian">
          <i class="fas fa-right-to-bracket me-2"></i>Masuk Ujian
        </button>
      </form>

      <a class="back-link" href="{{ route('login') }}">Masuk sebagai guru/admin</a>
    </section>
  </main>

  <script>
    const toggle = document.querySelector('.toggle-password');
    const password = document.getElementById('password');

    toggle.addEventListener('click', () => {
      const hidden = password.type === 'password';
      password.type = hidden ? 'text' : 'password';
      toggle.innerHTML = hidden ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
    });
  </script>
</body>
</html>
