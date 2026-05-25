@extends('layouts.admin')
@section('title', $kelas ? 'Edit Kelas' : 'Tambah Kelas')
@section('page_title', $kelas ? 'Edit Kelas' : 'Tambah Kelas')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $kelas ? 'Edit' : 'Tambah' }} Kelas</h3>
      </div>
      <form action="{{ $kelas ? route('admin.kelas.update', $kelas) : route('admin.kelas.store') }}" method="POST">
        @csrf
        @if($kelas) @method('PUT') @endif
        <div class="card-body">
          <div class="form-group">
            <label>Nama Kelas <span class="text-danger">*</span></label>
            <input type="text" name="nama_kelas" class="form-control @error('nama_kelas') is-invalid @enderror"
                   value="{{ old('nama_kelas', $kelas?->nama_kelas) }}" placeholder="Contoh: X TKJ 1">
            @error('nama_kelas') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Tingkat <span class="text-danger">*</span></label>
            <select name="tingkat" class="form-control @error('tingkat') is-invalid @enderror">
              <option value="">-- Pilih Tingkat --</option>
              @foreach(['X', 'XI', 'XII'] as $t)
                <option value="{{ $t }}" {{ old('tingkat', $kelas?->tingkat) == $t ? 'selected' : '' }}>{{ $t }}</option>
              @endforeach
            </select>
            @error('tingkat') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Jurusan <span class="text-danger">*</span></label>
            <select name="jurusan_id" class="form-control @error('jurusan_id') is-invalid @enderror">
              <option value="">-- Pilih Jurusan --</option>
              @foreach($jurusans as $j)
                <option value="{{ $j->id }}" {{ old('jurusan_id', $kelas?->jurusan_id) == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
              @endforeach
            </select>
            @error('jurusan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Wali Kelas</label>
            <select name="wali_kelas_id" class="form-control">
              <option value="">-- Tidak Ada --</option>
              @foreach($gurus as $g)
                <option value="{{ $g->id }}" {{ old('wali_kelas_id', $kelas?->wali_kelas_id) == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.kelas.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
