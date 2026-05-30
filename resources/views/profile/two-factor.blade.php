@extends('layouts.admin')
@section('title', 'Autentikasi Dua Faktor')
@section('page_title', 'Keamanan — Autentikasi Dua Faktor (2FA)')

@section('content')

<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Status 2FA</h3>
        <span class="float-right badge badge-{{ $user->hasTwoFactorEnabled() ? 'success' : 'secondary' }}">
          {{ $user->hasTwoFactorEnabled() ? 'AKTIF' : 'NONAKTIF' }}
        </span>
      </div>
      <div class="card-body">

        @if($user->hasTwoFactorEnabled())
          <p class="text-success"><i class="fas fa-check-circle mr-1"></i> Akun Anda dilindungi dengan 2FA sejak {{ $user->two_factor_confirmed_at->format('d/m/Y H:i') }}.</p>
          <hr>
          <h5>Nonaktifkan 2FA</h5>
          <form action="{{ route('profile.2fa.disable') }}" method="POST" class="form-inline">
            @csrf @method('DELETE')
            <input type="password" name="password" class="form-control mr-2" placeholder="Konfirmasi password" required>
            <button class="btn btn-danger" onclick="return confirm('Yakin nonaktifkan 2FA?')">Nonaktifkan</button>
          </form>

        @elseif($qrSvg)
          <p>1. Scan QR code ini dengan aplikasi <strong>Google Authenticator</strong> / Authy:</p>
          <div class="mb-3" style="max-width:200px">{!! $qrSvg !!}</div>
          <p>2. Masukkan 6 digit kode dari aplikasi:</p>
          <form action="{{ route('profile.2fa.confirm') }}" method="POST" class="form-inline mb-3">
            @csrf
            <input type="text" name="code" class="form-control mr-2" placeholder="123456" autocomplete="off" required autofocus>
            <button class="btn btn-success">Aktifkan</button>
          </form>

          @if($recoveryCodes)
          <div class="alert alert-warning">
            <h6><i class="fas fa-key mr-1"></i> Recovery Codes</h6>
            <p class="mb-1"><small>Simpan kode ini di tempat aman. Bisa dipakai login jika kehilangan akses ke aplikasi authenticator.</small></p>
            <div class="row">
              @foreach($recoveryCodes as $rc)
                <div class="col-6"><code>{{ $rc }}</code></div>
              @endforeach
            </div>
          </div>
          @endif

        @else
          <p>Tambahkan lapisan keamanan ekstra. Setelah aktif, Anda perlu memasukkan kode dari aplikasi authenticator setiap login.</p>
          <form action="{{ route('profile.2fa.enable') }}" method="POST">
            @csrf
            <button class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Aktifkan 2FA</button>
          </form>
        @endif

      </div>
    </div>
  </div>
</div>
@endsection
