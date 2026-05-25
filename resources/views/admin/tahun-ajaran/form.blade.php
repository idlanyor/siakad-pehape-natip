@extends('layouts.admin')
@section('title', $tahunAjaran ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran')
@section('page_title', $tahunAjaran ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $tahunAjaran ? 'Edit' : 'Tambah' }} Tahun Ajaran</h3>
      </div>
      <form action="{{ $tahunAjaran ? route('admin.tahun-ajaran.update', $tahunAjaran) : route('admin.tahun-ajaran.store') }}" method="POST">
        @csrf
        @if($tahunAjaran) @method('PUT') @endif
        <div class="card-body">
          <div class="form-group">
            <label>Tahun Ajaran <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                   value="{{ old('nama', $tahunAjaran?->nama) }}" placeholder="Contoh: 2024/2025">
            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small class="form-text text-muted">Format: YYYY/YYYY (contoh: 2024/2025)</small>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.tahun-ajaran.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
