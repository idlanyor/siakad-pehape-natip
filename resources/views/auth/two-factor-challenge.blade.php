<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verifikasi 2FA — {{ setting('sekolah_nama', 'SIAKAD SMK') }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#1a3a6b; }
    body { font-family:'Inter',sans-serif; background:linear-gradient(135deg,#1a3a6b,#2d5a9e); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1rem; }
    .login-card { background:#fff; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,.3); width:100%; max-width:420px; overflow:hidden; }
    .login-header { background:var(--primary); color:#fff; padding:2rem; text-align:center; }
    .login-header h1 { font-size:1.25rem; font-weight:700; margin:.75rem 0 .25rem; }
    .login-header p { font-size:.85rem; opacity:.85; margin:0; }
    .login-body { padding:2rem; }
    .alert-error { display:flex; gap:.5rem; background:#fef2f2; border:1px solid #fecaca; color:#dc2626; padding:.75rem 1rem; border-radius:8px; font-size:.85rem; margin-bottom:1rem; }
    .btn-login { width:100%; background:var(--primary); color:#fff; border:none; padding:.75rem; border-radius:8px; font-weight:600; }
    .btn-login:hover { background:#15305a; }
    .divider { text-align:center; color:#9ca3af; font-size:.8rem; margin:1.25rem 0; }
    .toggle-recovery { background:none; border:none; color:var(--primary); font-weight:600; font-size:.85rem; cursor:pointer; }
  </style>
</head>
<body>

<div class="login-card">
  <div class="login-header">
    <i class="fas fa-shield-alt fa-2x"></i>
    <h1>Verifikasi Dua Faktor</h1>
    <p>Masukkan kode dari aplikasi authenticator Anda</p>
  </div>

  <div class="login-body">
    @if ($errors->any())
      <div class="alert-error">
        <i class="fas fa-exclamation-circle mt-1"></i>
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    {{-- Form OTP --}}
    <form method="POST" action="{{ route('2fa.challenge.store') }}" id="otp-form">
      @csrf
      <div class="mb-3">
        <label for="code" class="form-label">Kode OTP (6 digit)</label>
        <input id="code" type="text" name="code" class="form-control text-center"
               style="letter-spacing:.5rem;font-size:1.25rem" placeholder="______"
               inputmode="numeric" autocomplete="one-time-code" autofocus>
      </div>
      <button type="submit" class="btn-login"><i class="fas fa-check me-2"></i>Verifikasi</button>
    </form>

    <div class="divider">atau</div>

    {{-- Form recovery code (tersembunyi default) --}}
    <div id="recovery-box" style="display:none">
      <form method="POST" action="{{ route('2fa.challenge.store') }}">
        @csrf
        <div class="mb-3">
          <label for="recovery_code" class="form-label">Recovery Code</label>
          <input id="recovery_code" type="text" name="recovery_code" class="form-control text-center"
                 placeholder="XXXXXX-XXXXXX" autocomplete="off">
        </div>
        <button type="submit" class="btn-login"><i class="fas fa-key me-2"></i>Gunakan Recovery Code</button>
      </form>
    </div>

    <div class="text-center mt-3">
      <button type="button" class="toggle-recovery" onclick="toggleRecovery()">
        <span id="toggle-label">Gunakan recovery code</span>
      </button>
    </div>

    <div class="text-center mt-3">
      <a href="{{ route('login') }}" style="color:#6b7280;font-size:.82rem;text-decoration:none">
        <i class="fas fa-arrow-left me-1"></i>Batal &amp; kembali ke login
      </a>
    </div>
  </div>
</div>

<script>
function toggleRecovery() {
  const box = document.getElementById('recovery-box');
  const otp = document.getElementById('otp-form');
  const label = document.getElementById('toggle-label');
  if (box.style.display === 'none') {
    box.style.display = 'block'; otp.style.display = 'none'; label.textContent = 'Gunakan kode OTP';
  } else {
    box.style.display = 'none'; otp.style.display = 'block'; label.textContent = 'Gunakan recovery code';
  }
}
</script>
</body>
</html>
