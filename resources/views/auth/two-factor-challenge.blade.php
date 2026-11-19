@extends('layouts.auth')
@section('title', 'Verifikasi Dua Langkah')

@section('aside')
  <h2>Satu langkah lagi untuk <em>mengamankan</em> akunmu.</h2>
  <p>Akun ini dilindungi verifikasi dua langkah. Kode dari aplikasi authenticator berganti setiap 30 detik.</p>
@endsection

@section('content')
  <div class="auth-ic"><i class="fas fa-shield-halved"></i></div>
  <h1>Verifikasi dua langkah</h1>
  <p class="lead-sm" id="tfa-lead">Masukkan 6 digit kode dari aplikasi authenticator kamu.</p>

  @if ($errors->any())
    <div class="alert-x err" role="alert"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
  @endif

  <form method="POST" action="{{ route('2fa.challenge.store') }}" id="otp-form" data-busy="Memverifikasi…">
    @csrf
    <div class="field mb-4">
      <label for="code">Kode OTP</label>
      <div class="ctl">
        <input id="code" type="text" name="code" class="form-control no-ic text-center fw-bold"
               style="letter-spacing:.6em;font-size:1.35rem" placeholder="••••••" maxlength="6"
               inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" autofocus>
      </div>
    </div>
    <button type="submit" class="btn-main"><i class="fas fa-check"></i> Verifikasi</button>
  </form>

  <form method="POST" action="{{ route('2fa.challenge.store') }}" id="recovery-form" data-busy="Memverifikasi…" hidden>
    @csrf
    <div class="field mb-4">
      <label for="recovery_code">Recovery code</label>
      <div class="ctl">
        <i class="fas fa-key"></i>
        <input id="recovery_code" type="text" name="recovery_code" class="form-control" placeholder="XXXXXX-XXXXXX" autocomplete="off" spellcheck="false">
      </div>
    </div>
    <button type="submit" class="btn-main"><i class="fas fa-key"></i> Gunakan recovery code</button>
  </form>

  <div class="alt d-flex flex-wrap justify-content-between gap-2">
    <button type="button" class="link-btn" id="toggle-recovery">Pakai recovery code</button>
    <a href="{{ route('login') }}" class="link-btn text-decoration-none" style="color:var(--muted)">Batal</a>
  </div>
@endsection

@push('scripts')
<script>
  (() => {
    const otp = document.getElementById('otp-form'), rec = document.getElementById('recovery-form');
    const btn = document.getElementById('toggle-recovery'), lead = document.getElementById('tfa-lead');
    btn.addEventListener('click', () => {
      const keRecovery = !otp.hidden;
      otp.hidden = keRecovery; rec.hidden = !keRecovery;
      btn.textContent = keRecovery ? 'Pakai kode OTP' : 'Pakai recovery code';
      lead.textContent = keRecovery ? 'Masukkan salah satu recovery code yang kamu simpan saat mengaktifkan 2FA.' : 'Masukkan 6 digit kode dari aplikasi authenticator kamu.';
      (keRecovery ? rec : otp).querySelector('input:not([type=hidden])').focus();
    });
  })();
</script>
@endpush
