@extends('layouts.auth')
@section('title', 'Lupa Password')

@section('aside')
  <h2>Lupa password? <em>Tenang</em>, bisa diatur ulang.</h2>
  <p>Kami kirim tautan untuk membuat password baru ke email yang terdaftar di akunmu.</p>
@endsection

@section('content')
  <div class="auth-ic"><i class="fas fa-key"></i></div>
  <h1>Lupa password</h1>
  <p class="lead-sm">Masukkan email yang terdaftar. Bila akunmu belum punya email, minta admin sekolah untuk mengatur ulang password.</p>

  @if (session('status'))
    <div class="alert-x ok" role="status"><i class="fas fa-circle-check"></i><span>{{ session('status') }}</span></div>
  @endif

  <form method="POST" action="{{ route('password.email') }}" data-busy="Mengirim…">
    @csrf
    <div class="field mb-4">
      <label for="email">Email</label>
      <div class="ctl">
        <i class="fas fa-envelope"></i>
        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" placeholder="nama@email.com" required autofocus autocomplete="email">
      </div>
      @error('email')<div class="field-error">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="btn-main">Kirim tautan reset</button>
  </form>

  <div class="alt text-center">
    <a href="{{ route('login') }}" class="link-btn text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Kembali ke halaman masuk</a>
  </div>
@endsection
