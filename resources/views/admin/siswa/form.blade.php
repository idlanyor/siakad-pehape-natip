@extends('layouts.admin')
@section('title', $siswa ? 'Edit Siswa' : 'Tambah Siswa')
@section('page_title', $siswa ? 'Edit Siswa' : 'Tambah Siswa')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-9">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $siswa ? 'Edit' : 'Tambah' }} Data Siswa</h3>
      </div>
      <form action="{{ $siswa ? route('admin.siswa.update', $siswa) : route('admin.siswa.store') }}" method="POST">
        @csrf
        @if($siswa) @method('PUT') @endif
        <div class="card-body">
          <h6 class="text-muted mb-3"><i class="fas fa-user mr-1"></i> Informasi Akun</h6>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $siswa?->user->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username', $siswa?->user->username) }}">
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $siswa?->user->email) }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Password {{ $siswa ? '(kosongkan jika tidak diubah)' : '' }} <span class="text-danger">{{ $siswa ? '' : '*' }}</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ $siswa ? '••••••' : 'Minimal 6 karakter' }}">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <hr>
          <h6 class="text-muted mb-3"><i class="fas fa-id-card mr-1"></i> Informasi Akademik</h6>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>NISN <span class="text-danger">*</span></label>
                <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror"
                       value="{{ old('nisn', $siswa?->nisn) }}" placeholder="Nomor Induk Siswa Nasional">
                @error('nisn') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tanggal Lahir <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                       value="{{ old('tanggal_lahir', $siswa?->tanggal_lahir) }}">
                @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Jurusan <span class="text-danger">*</span></label>
                <select name="jurusan_id" class="form-control @error('jurusan_id') is-invalid @enderror">
                  <option value="">-- Pilih Jurusan --</option>
                  @foreach($jurusans as $j)
                    <option value="{{ $j->id }}" {{ old('jurusan_id', $siswa?->jurusan_id) == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
                  @endforeach
                </select>
                @error('jurusan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Kelas</label>
                <select name="kelas_id" class="form-control">
                  <option value="">-- Pilih Kelas --</option>
                  @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ old('kelas_id', $siswa?->kelas_id) == $k->id ? 'selected' : '' }}>
                      {{ $k->nama_kelas }} ({{ $k->jurusan->nama_jurusan }})
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.siswa.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
