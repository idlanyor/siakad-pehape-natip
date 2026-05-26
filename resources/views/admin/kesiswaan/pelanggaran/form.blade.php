@extends('layouts.admin')
@section('title', $pelanggaran ? 'Edit Pelanggaran' : 'Tambah Pelanggaran')
@section('page_title', $pelanggaran ? 'Edit Pelanggaran' : 'Tambah Pelanggaran')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
          {{ $pelanggaran ? 'Edit' : 'Tambah' }} Pelanggaran Siswa
        </h3>
      </div>
      <form action="{{ $pelanggaran ? route('admin.pelanggaran.update', $pelanggaran) : route('admin.pelanggaran.store') }}" method="POST">
        @csrf
        @if($pelanggaran) @method('PUT') @endif
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
                    <option value="{{ $s->id }}" {{ old('siswa_id', $pelanggaran?->siswa_id) == $s->id ? 'selected' : '' }}>
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
                       value="{{ old('tanggal', $pelanggaran?->tanggal ?? today()->toDateString()) }}">
                @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Jenis Pelanggaran <span class="text-danger">*</span></label>
                <input type="text" name="jenis" class="form-control @error('jenis') is-invalid @enderror"
                       value="{{ old('jenis', $pelanggaran?->jenis) }}"
                       placeholder="e.g. Tidak membawa buku, Terlambat, Seragam tidak lengkap">
                @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Poin Pelanggaran <span class="text-danger">*</span></label>
                <input type="number" name="poin" min="0" max="100" class="form-control @error('poin') is-invalid @enderror"
                       value="{{ old('poin', $pelanggaran?->poin ?? 10) }}"
                       placeholder="0 - 100">
                @error('poin') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Keterangan / Deskripsi</label>
            <textarea name="deskripsi" rows="3" class="form-control" placeholder="Deskripsi singkat pelanggaran...">{{ old('deskripsi', $pelanggaran?->deskripsi) }}</textarea>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.pelanggaran.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
