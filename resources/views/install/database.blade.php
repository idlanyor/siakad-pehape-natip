@extends('install.layout')
@section('step', 'Konfigurasi Database')
@php $activeStep = 2; @endphp

@section('wizard')
<h5 class="mb-3">Konfigurasi Aplikasi & Database</h5>
<form method="POST" action="{{ route('install.database.save') }}">
  @csrf
  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label">Nama Aplikasi / Sekolah</label>
      <input type="text" name="app_name" class="form-control" value="{{ old('app_name', 'SIAKAD SMK') }}" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">URL Aplikasi</label>
      <input type="url" name="app_url" class="form-control" value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" required>
    </div>

    <div class="col-12"><hr><strong>Database (MySQL)</strong></div>

    <div class="col-md-8">
      <label class="form-label">Host</label>
      <input type="text" name="db_host" class="form-control" value="{{ old('db_host', '127.0.0.1') }}" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Port</label>
      <input type="number" name="db_port" class="form-control" value="{{ old('db_port', '3306') }}" required>
    </div>
    <div class="col-md-12">
      <label class="form-label">Nama Database</label>
      <input type="text" name="db_database" class="form-control" value="{{ old('db_database') }}" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Username</label>
      <input type="text" name="db_username" class="form-control" value="{{ old('db_username') }}" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Password</label>
      <input type="password" name="db_password" class="form-control" value="{{ old('db_password') }}">
    </div>
  </div>

  <div class="alert alert-info mt-3 mb-3 small">
    <i class="fas fa-info-circle me-1"></i> Database harus sudah dibuat. Koneksi akan diuji sebelum disimpan.
  </div>

  <div class="d-flex justify-content-between">
    <a href="{{ route('install.welcome') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    <button type="submit" class="btn btn-primary">Uji & Lanjut <i class="fas fa-arrow-right ms-1"></i></button>
  </div>
</form>
@endsection
