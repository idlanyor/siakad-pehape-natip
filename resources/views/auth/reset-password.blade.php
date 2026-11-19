@extends('layouts.auth')
@section('title', 'Atur Ulang Password')

@section('aside')
  <h2>Buat password <em>baru</em>.</h2>
  <p>Gunakan minimal 8 karakter, campurkan huruf dan angka, dan jangan pakai ulang password lama.</p>
@endsection

@section('content')
  <div class="auth-ic"><i class="fas fa-lock"></i></div>
  <h1>Atur ulang password</h1>
  <p class="lead-sm">Masukkan password baru untuk akunmu.</p>

  <form method="POST" action="{{ route('password.store') }}" data-busy="Menyimpan…">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="field">
      <label for="email">Email</label>
      <div class="ctl">
        <i class="fas fa-envelope"></i>
        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $request->email) }}" required autocomplete="username">
      </div>
      @error('email')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label for="password">Password baru</label>
      <div class="ctl">
        <i class="fas fa-lock"></i>
        <input id="password" type="password" name="password" class="form-control has-toggle @error('password') is-invalid @enderror"
               required autofocus autocomplete="new-password">
        <button type="button" class="pw-toggle" data-pw-toggle="password" aria-label="Tampilkan password" aria-pressed="false"><i class="fas fa-eye"></i></button>
      </div>
      @error('password')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <div class="field mb-4">
      <label for="password_confirmation">Ulangi password baru</label>
      <div class="ctl">
        <i class="fas fa-lock"></i>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror"
               required autocomplete="new-password">
      </div>
      @error('password_confirmation')<div class="field-error">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn-main">Simpan password baru</button>
  </form>
@endsection
