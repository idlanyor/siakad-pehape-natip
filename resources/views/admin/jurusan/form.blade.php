@extends('layouts.admin')
@section('title', $jurusan ? 'Edit Jurusan' : 'Tambah Jurusan')
@section('page_title', $jurusan ? 'Edit Jurusan' : 'Tambah Jurusan')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $jurusan ? 'Edit' : 'Tambah' }} Jurusan</h3>
      </div>
      <form action="{{ $jurusan ? route('admin.jurusan.update', $jurusan) : route('admin.jurusan.store') }}" method="POST">
        @csrf
        @if($jurusan) @method('PUT') @endif
        <div class="card-body">
          <div class="form-group">
            <label>Nama Jurusan <span class="text-danger">*</span></label>
            <input type="text" name="nama_jurusan" class="form-control @error('nama_jurusan') is-invalid @enderror"
                   value="{{ old('nama_jurusan', $jurusan?->nama_jurusan) }}" placeholder="Contoh: Teknik Komputer dan Jaringan">
            @error('nama_jurusan') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.jurusan.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
