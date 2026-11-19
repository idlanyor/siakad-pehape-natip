@extends('layouts.auth')
@section('title', 'Masuk Ujian')

@section('aside')
  <h2>Ujian online, <em>tenang</em> dan fokus.</h2>
  <p>Masuk dengan NIS untuk melihat ujian yang tersedia dan mulai mengerjakan.</p>
  <ul class="aside-list">
    <li><i class="fas fa-floppy-disk"></i><div><b>Jawaban tersimpan otomatis</b><span>Tetap aman meski koneksi sempat putus</span></div></li>
    <li><i class="fas fa-stopwatch"></i><div><b>Waktu mengikuti server</b><span>Sisa waktu tetap akurat walau halaman dimuat ulang</span></div></li>
    <li><i class="fas fa-mobile-screen"></i><div><b>Satu perangkat per sesi</b><span>Kerjakan dari satu perangkat sampai selesai</span></div></li>
  </ul>
@endsection

@section('content')
  <div class="auth-ic" style="background:#fef3c7;color:#d97706"><i class="fas fa-laptop-code"></i></div>
  <h1>Masuk ujian</h1>
  <p class="lead-sm">Khusus siswa. Masukkan NIS dan password akun SIAKAD kamu.</p>

  @if ($errors->any())
    <div class="alert-x err" role="alert"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
  @endif

  <form method="POST" action="{{ route('ujian.login.store') }}" data-busy="Memproses…">
    @csrf

    <div class="field">
      <label for="nis">NIS</label>
      <div class="ctl">
        <i class="fas fa-id-card"></i>
        <input id="nis" type="text" name="nis" class="form-control @error('nis') is-invalid @enderror"
               value="{{ old('nis') }}" placeholder="Masukkan NIS" required @if(! old('nis')) autofocus @endif
               autocomplete="username" inputmode="numeric" spellcheck="false">
      </div>
    </div>

    <div class="field mb-4">
      <label for="password">Password</label>
      <div class="ctl">
        <i class="fas fa-lock"></i>
        <input id="password" type="password" name="password" class="form-control has-toggle @error('password') is-invalid @enderror"
               placeholder="Masukkan password" required @if(old('nis')) autofocus @endif autocomplete="current-password">
        <button type="button" class="pw-toggle" data-pw-toggle="password" aria-label="Tampilkan password" aria-pressed="false">
          <i class="fas fa-eye"></i>
        </button>
      </div>
    </div>

    <button type="submit" class="btn-main">Masuk ujian <i class="fas fa-arrow-right"></i></button>
  </form>

  <div class="alt">
    <a class="alt-link" href="{{ route('login') }}">
      <span class="ic" style="background:var(--primary-soft);color:var(--primary)"><i class="fas fa-user"></i></span>
      <span><b>Bukan untuk ujian?</b><span>Masuk ke SIAKAD dengan username</span></span>
      <i class="fas fa-arrow-right go"></i>
    </a>
  </div>
@endsection
