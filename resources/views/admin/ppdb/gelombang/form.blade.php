@extends('layouts.admin')
@section('title', $gelombang ? 'Edit Gelombang' : 'Tambah Gelombang PPDB')
@section('page_title', $gelombang ? 'Edit Gelombang' : 'Tambah Gelombang PPDB')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-calendar-alt text-primary mr-2"></i>
          {{ $gelombang ? 'Edit' : 'Tambah' }} Gelombang PPDB
        </h3>
      </div>
      <form action="{{ $gelombang ? route('admin.ppdb.gelombang.update', $gelombang) : route('admin.ppdb.gelombang.store') }}" method="POST">
        @csrf
        @if($gelombang) @method('PUT') @endif
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
          @endif

          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label>Nama Gelombang <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                       value="{{ old('nama', $gelombang?->nama) }}"
                       placeholder="e.g. Gelombang 1, Gelombang Reguler" required>
                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Tahun Ajaran <span class="text-danger">*</span></label>
                <input type="text" name="tahun_ajaran" class="form-control"
                       value="{{ old('tahun_ajaran', $gelombang?->tahun_ajaran ?? setting('ppdb_tahun_ajaran', '2026/2027')) }}"
                       placeholder="2026/2027" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Tanggal & Jam Buka <span class="text-danger">*</span></label>
                <input type="datetime-local" name="tanggal_buka"
                       class="form-control @error('tanggal_buka') is-invalid @enderror"
                       value="{{ old('tanggal_buka', $gelombang?->tanggal_buka?->format('Y-m-d\TH:i')) }}" required>
                @error('tanggal_buka') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tanggal & Jam Tutup <span class="text-danger">*</span></label>
                <input type="datetime-local" name="tanggal_tutup"
                       class="form-control @error('tanggal_tutup') is-invalid @enderror"
                       value="{{ old('tanggal_tutup', $gelombang?->tanggal_tutup?->format('Y-m-d\TH:i')) }}" required>
                @error('tanggal_tutup') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Kuota <small class="text-muted">(0 = tidak terbatas)</small></label>
                <input type="number" name="kuota" class="form-control" min="0"
                       value="{{ old('kuota', $gelombang?->kuota ?? 0) }}">
              </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" id="isAktif"
                         name="is_aktif" value="1"
                         {{ old('is_aktif', $gelombang?->is_aktif ?? true) ? 'checked' : '' }}>
                  <label class="custom-control-label" for="isAktif">Gelombang Aktif</label>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan" rows="2" class="form-control"
                      placeholder="Catatan atau info tambahan...">{{ old('keterangan', $gelombang?->keterangan) }}</textarea>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.ppdb.gelombang.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
