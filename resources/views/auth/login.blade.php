@extends('layouts.auth')
@section('title', 'Masuk')

@section('aside')
  <h2>Satu akun untuk <em>seluruh</em> kegiatan sekolah.</h2>
  <p>Jadwal, nilai, absensi, tugas, hingga ujian online. Semua dalam satu tempat.</p>
  <ul class="aside-list">
    <li><i class="fas fa-chalkboard-user"></i><div><b>Guru &amp; staf</b><span>Kelola kelas, nilai, dan absensi</span></div></li>
    <li><i class="fas fa-user-graduate"></i><div><b>Siswa</b><span>Lihat jadwal, materi, tugas, dan hasil belajar</span></div></li>
    <li><i class="fas fa-people-roof"></i><div><b>Orang tua &amp; pendaftar PPDB</b><span>Pantau perkembangan anak dan status pendaftaran</span></div></li>
  </ul>
@endsection

@section('content')
  <h1>Masuk</h1>
  <p class="lead-sm">Gunakan username dan password yang diberikan sekolah.</p>

  @if (session('status'))
    <div class="alert-x ok" role="status"><i class="fas fa-circle-check"></i><span>{{ session('status') }}</span></div>
  @endif

  @if ($errors->any())
    <div class="alert-x err" role="alert"><i class="fas fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
  @endif

  <form method="POST" action="{{ route('login') }}" data-busy="Memproses…">
    @csrf

    <div class="field">
      <label for="username">Username</label>
      <div class="ctl">
        <i class="fas fa-user"></i>
        <input id="username" type="text" name="username" class="form-control @error('username') is-invalid @enderror"
               value="{{ old('username') }}" placeholder="Masukkan username" required @if(! old('username')) autofocus @endif
               autocomplete="username" autocapitalize="none" spellcheck="false">
      </div>
    </div>

    <div class="field">
      <div class="field-row">
        <label for="password">Password</label>
        @if (Route::has('password.request'))
          <a href="{{ route('password.request') }}">Lupa password?</a>
        @endif
      </div>
      <div class="ctl">
        <i class="fas fa-lock"></i>
        <input id="password" type="password" name="password" class="form-control has-toggle"
               placeholder="Masukkan password" required @if(old('username')) autofocus @endif autocomplete="current-password">
        <button type="button" class="pw-toggle" data-pw-toggle="password" aria-label="Tampilkan password" aria-pressed="false">
          <i class="fas fa-eye"></i>
        </button>
      </div>
    </div>

    <label class="check mb-4" for="remember">
      <input type="checkbox" name="remember" id="remember" @checked(old('remember'))> Ingat saya di perangkat ini
    </label>

    <button type="submit" class="btn-main">Masuk <i class="fas fa-arrow-right"></i></button>
  </form>

  <div class="alt">
    <a class="alt-link" href="{{ route('ujian.login') }}">
      <span class="ic"><i class="fas fa-laptop-code"></i></span>
      <span><b>Siswa ikut ujian online?</b><span>Masuk dengan NIS</span></span>
      <i class="fas fa-arrow-right go"></i>
    </a>
  </div>
@endsection
