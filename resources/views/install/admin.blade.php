@extends('install.layout')
@section('step', 'Akun Administrator')
@php $activeStep = 3; @endphp

@section('wizard')
<h5 class="mb-3">Identitas Sekolah & Akun Admin</h5>
<form method="POST" action="{{ route('install.finish') }}">
  @csrf
  <div class="row g-3">
    <div class="col-12">
      <label class="form-label">Nama Sekolah Lengkap</label>
      <input type="text" name="sekolah_nama" class="form-control" value="{{ old('sekolah_nama') }}" placeholder="SMK Negeri 1 ..." required>
    </div>

    <div class="col-12"><hr><strong>Akun Administrator</strong></div>

    <div class="col-md-6">
      <label class="form-label">Nama</label>
      <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Username</label>
      <input type="text" name="admin_username" class="form-control" value="{{ old('admin_username', 'admin') }}" required>
    </div>
    <div class="col-12">
      <label class="form-label">Email</label>
      <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Password</label>
      <input type="password" name="admin_password" class="form-control" required minlength="6">
    </div>
    <div class="col-md-6">
      <label class="form-label">Konfirmasi Password</label>
      <input type="password" name="admin_password_confirmation" class="form-control" required minlength="6">
    </div>
  </div>

  <div class="alert alert-warning mt-3 mb-3 small">
    <i class="fas fa-database me-1"></i> Menekan tombol di bawah akan menjalankan migrasi database & membuat data awal.
  </div>

  <div class="d-flex justify-content-between">
    <a href="{{ route('install.database') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    <button type="submit" class="btn btn-success"><i class="fas fa-rocket me-1"></i> Pasang Sekarang</button>
  </div>
</form>
@endsection
