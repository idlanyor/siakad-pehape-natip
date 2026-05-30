@extends('layouts.admin')
@section('title', $mapel ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran')
@section('page_title', $mapel ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $mapel ? 'Edit' : 'Tambah' }} Mata Pelajaran</h3>
      </div>
      <form action="{{ $mapel ? route('admin.mapel.update', $mapel) : route('admin.mapel.store') }}" method="POST">
        @csrf
        @if($mapel) @method('PUT') @endif
        <div class="card-body">
          <div class="form-group">
            <label>Kode Mapel <span class="text-danger">*</span></label>
            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                   value="{{ old('kode', $mapel?->kode) }}" placeholder="Contoh: TKJ-01">
            @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Nama Mata Pelajaran <span class="text-danger">*</span></label>
            <input type="text" name="nama_mapel" class="form-control @error('nama_mapel') is-invalid @enderror"
                   value="{{ old('nama_mapel', $mapel?->nama_mapel) }}" placeholder="Contoh: Basis Data">
            @error('nama_mapel') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Jurusan</label>
            <select name="jurusan_id" class="form-control @error('jurusan_id') is-invalid @enderror">
              <option value="">— Umum (Semua Jurusan) —</option>
              @foreach($jurusans as $j)
                <option value="{{ $j->id }}" @selected(old('jurusan_id', $mapel?->jurusan_id) == $j->id)>{{ $j->nama_jurusan }}</option>
              @endforeach
            </select>
            @error('jurusan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small class="form-text text-muted">Kosongkan jika mapel berlaku untuk semua jurusan.</small>
          </div>
          <div class="form-group">
            <label>Guru Pengampu</label>
            <select name="guru_ids[]" class="form-control select2 @error('guru_ids') is-invalid @enderror" multiple>
              @php $selectedGurus = old('guru_ids', $mapel?->gurus->pluck('id')->toArray() ?? []); @endphp
              @foreach($gurus as $g)
                <option value="{{ $g->id }}" @selected(in_array($g->id, $selectedGurus))>{{ $g->nama }} ({{ $g->nip }})</option>
              @endforeach
            </select>
            @error('guru_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <small class="form-text text-muted">Bisa pilih lebih dari satu guru.</small>
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

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
  $(function () { $('.select2').select2({ theme: 'bootstrap4', placeholder: 'Pilih guru...' }); });
</script>
@endpush
