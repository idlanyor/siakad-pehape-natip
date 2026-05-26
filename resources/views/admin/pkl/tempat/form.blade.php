@extends('layouts.admin')
@section('title', $tempat ? 'Edit Tempat PKL' : 'Tambah Tempat PKL')
@section('page_title', $tempat ? 'Edit Tempat PKL' : 'Tambah Tempat PKL')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $tempat ? 'Edit' : 'Tambah' }} Tempat PKL</h3>
      </div>
      <form action="{{ $tempat ? route('admin.pkl-tempat.update', $tempat) : route('admin.pkl-tempat.store') }}" method="POST">
        @csrf
        @if($tempat) @method('PUT') @endif
        <div class="card-body">
          <div class="form-group">
            <label>Nama Tempat / Instansi <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                   value="{{ old('nama', $tempat?->nama) }}" placeholder="PT. / CV. / Dinas ...">
            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Bidang Usaha</label>
            <input type="text" name="bidang_usaha" class="form-control @error('bidang_usaha') is-invalid @enderror"
                   value="{{ old('bidang_usaha', $tempat?->bidang_usaha) }}" placeholder="Teknologi Informasi, Perhotelan, dll.">
            @error('bidang_usaha') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3"
                      placeholder="Alamat lengkap">{{ old('alamat', $tempat?->alamat) }}</textarea>
            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Kontak Person</label>
                <input type="text" name="kontak_person" class="form-control @error('kontak_person') is-invalid @enderror"
                       value="{{ old('kontak_person', $tempat?->kontak_person) }}" placeholder="Nama PIC">
                @error('kontak_person') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>No. HP</label>
                <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror"
                       value="{{ old('no_hp', $tempat?->no_hp) }}" placeholder="08xx...">
                @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Kuota Siswa <span class="text-danger">*</span></label>
            <input type="number" name="kuota" class="form-control @error('kuota') is-invalid @enderror"
                   value="{{ old('kuota', $tempat?->kuota ?? 1) }}" min="1" max="999" style="width:120px">
            @error('kuota') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.pkl-tempat.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
