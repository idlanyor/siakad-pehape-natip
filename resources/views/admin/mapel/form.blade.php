@extends('layouts.admin')
@section('title', $matkul ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran')
@section('page_title', $matkul ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $matkul ? 'Edit' : 'Tambah' }} Mata Pelajaran</h3>
      </div>
      <form action="{{ $matkul ? route('admin.mapel.update', $matkul) : route('admin.mapel.store') }}" method="POST">
        @csrf
        @if($matkul) @method('PUT') @endif
        <div class="card-body">
          <div class="form-group">
            <label>Kode Mapel <span class="text-danger">*</span></label>
            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                   value="{{ old('kode', $matkul?->kode) }}" placeholder="Contoh: TKJ-01">
            @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Nama Mata Pelajaran <span class="text-danger">*</span></label>
            <input type="text" name="nama_matkul" class="form-control @error('nama_matkul') is-invalid @enderror"
                   value="{{ old('nama_matkul', $matkul?->nama_matkul) }}" placeholder="Contoh: Basis Data">
            @error('nama_matkul') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Guru Pengampu <span class="text-danger">*</span></label>
            <input type="text" name="dosen_pengampu" class="form-control @error('dosen_pengampu') is-invalid @enderror"
                   value="{{ old('dosen_pengampu', $matkul?->dosen_pengampu) }}" placeholder="Nama lengkap guru">
            @error('dosen_pengampu') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.mapel.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
