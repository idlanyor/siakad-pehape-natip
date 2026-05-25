@extends('layouts.admin')
@section('title', $guru ? 'Edit Guru' : 'Tambah Guru')
@section('page_title', $guru ? 'Edit Guru' : 'Tambah Guru')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $guru ? 'Edit' : 'Tambah' }} Data Guru</h3>
      </div>
      <form action="{{ $guru ? route('admin.guru.update', $guru) : route('admin.guru.store') }}" method="POST">
        @csrf
        @if($guru) @method('PUT') @endif
        <div class="card-body">
          <h6 class="text-muted mb-3"><i class="fas fa-user mr-1"></i> Informasi Akun</h6>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $guru?->user->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username', $guru?->user->username) }}">
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $guru?->user->email) }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Password {{ $guru ? '(kosongkan jika tidak diubah)' : '' }} <span class="text-danger">{{ $guru ? '' : '*' }}</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ $guru ? '••••••' : 'Minimal 6 karakter' }}">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <hr>
          <h6 class="text-muted mb-3"><i class="fas fa-id-card mr-1"></i> Informasi Guru</h6>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>NIP</label>
                <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror"
                       value="{{ old('nip', $guru?->nip) }}" placeholder="Nomor Induk Pegawai">
                @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="no_hp" class="form-control"
                       value="{{ old('no_hp', $guru?->no_hp) }}" placeholder="08xxxxxxxxxx">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Bidang Studi</label>
            <input type="text" name="bidang_studi" class="form-control"
                   value="{{ old('bidang_studi', $guru?->bidang_studi) }}" placeholder="Contoh: Basis Data, Jaringan Komputer">
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.guru.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
