@extends('layouts.admin')
@section('title', 'Jurnal PKL')
@section('page_title', 'Jurnal Harian PKL')

@section('content')
<div class="row">
  {{-- Form tambah jurnal --}}
  <div class="col-md-5">
    <div class="card card-warning card-outline">
      <div class="card-header"><h3 class="card-title">Tambah Jurnal Hari Ini</h3></div>
      <form action="{{ route('siswa.pkl.jurnal.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
          <div class="form-group">
            <label>Tanggal <span class="text-danger">*</span></label>
            <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                   value="{{ old('tanggal', today()->toDateString()) }}"
                   max="{{ today()->toDateString() }}">
            @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Kegiatan <span class="text-danger">*</span></label>
            <textarea name="kegiatan" class="form-control @error('kegiatan') is-invalid @enderror" rows="5"
                      placeholder="Tuliskan kegiatan yang dilakukan hari ini...">{{ old('kegiatan') }}</textarea>
            @error('kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Keterangan Tambahan</label>
            <input type="text" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                   value="{{ old('keterangan') }}" placeholder="Opsional">
            @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Foto Kegiatan <small class="text-muted">(maks. 2MB)</small></label>
            <input type="file" name="foto" class="form-control-file @error('foto') is-invalid @enderror" accept="image/*">
            @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-warning btn-block">
            <i class="fas fa-plus"></i> Tambah Jurnal
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Daftar jurnal --}}
  <div class="col-md-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Riwayat Jurnal ({{ $jurnals->count() }} entri)</h3>
        <a href="{{ route('siswa.pkl') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
      <div class="card-body p-0">
        @forelse($jurnals as $j)
        <div class="border-bottom p-3">
          <div class="d-flex justify-content-between">
            <strong>{{ $j->tanggal->isoFormat('dddd, D MMMM Y') }}</strong>
            @if($j->foto)
              <a href="{{ asset('storage/' . $j->foto) }}" target="_blank">
                <img src="{{ asset('storage/' . $j->foto) }}" height="50" class="rounded">
              </a>
            @endif
          </div>
          <p class="mb-0 mt-1">{{ $j->kegiatan }}</p>
          @if($j->keterangan)<small class="text-muted">{{ $j->keterangan }}</small>@endif
        </div>
        @empty
        <div class="text-center text-muted py-4">Belum ada jurnal.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
