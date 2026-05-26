@extends('layouts.admin')
@section('title', $prestasi ? 'Edit Prestasi' : 'Tambah Prestasi')
@section('page_title', $prestasi ? 'Edit Prestasi' : 'Tambah Prestasi')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-trophy text-warning mr-2"></i>{{ $prestasi ? 'Edit' : 'Tambah' }} Prestasi Siswa</h3>
      </div>
      <form action="{{ $prestasi ? route('admin.prestasi.update', $prestasi) : route('admin.prestasi.store') }}"
            method="POST" enctype="multipart/form-data">
        @csrf
        @if($prestasi) @method('PUT') @endif
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif

          <div class="row">
            <div class="col-md-7">
              <div class="form-group">
                <label>Siswa <span class="text-danger">*</span></label>
                <select name="siswa_id" class="form-control @error('siswa_id') is-invalid @enderror">
                  <option value="">-- Pilih Siswa --</option>
                  @foreach($siswas as $s)
                    <option value="{{ $s->id }}" {{ old('siswa_id', $prestasi?->siswa_id) == $s->id ? 'selected' : '' }}>
                      {{ $s->nama }} — {{ $s->kelas?->nama_kelas ?? '-' }}
                    </option>
                  @endforeach
                </select>
                @error('siswa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group">
                <label>Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                       value="{{ old('tanggal', $prestasi?->tanggal ?? today()->toDateString()) }}">
                @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Nama Prestasi <span class="text-danger">*</span></label>
                <input type="text" name="nama_prestasi" class="form-control @error('nama_prestasi') is-invalid @enderror"
                       value="{{ old('nama_prestasi', $prestasi?->nama_prestasi) }}"
                       placeholder="e.g. Juara 1 Lomba Robotik">
                @error('nama_prestasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Tingkat <span class="text-danger">*</span></label>
                <select name="tingkat" class="form-control @error('tingkat') is-invalid @enderror">
                  <option value="">-- Pilih Tingkat --</option>
                  @foreach($tingkats as $t)
                    <option value="{{ $t }}" {{ old('tingkat', $prestasi?->tingkat) == $t ? 'selected' : '' }}>{{ $t }}</option>
                  @endforeach
                </select>
                @error('tingkat') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan" rows="2" class="form-control" placeholder="Keterangan tambahan...">{{ old('keterangan', $prestasi?->keterangan) }}</textarea>
          </div>

          <div class="form-group">
            <label>Foto Bukti Prestasi</label>
            @if($prestasi?->foto_url)
              <div class="mb-2">
                <img src="{{ $prestasi->foto_url }}" alt="Foto Prestasi" class="img-thumbnail" style="max-height:150px;">
                <small class="d-block text-muted">Foto saat ini. Upload foto baru untuk menggantikan.</small>
              </div>
            @endif
            <input type="file" name="foto" class="form-control-file @error('foto') is-invalid @enderror" accept="image/*">
            @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.prestasi.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
