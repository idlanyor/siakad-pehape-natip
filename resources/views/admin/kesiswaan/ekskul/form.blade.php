@extends('layouts.admin')
@section('title', $ekskul ? 'Edit Ekskul' : 'Tambah Ekskul')
@section('page_title', $ekskul ? 'Edit Ekskul' : 'Tambah Ekskul')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users text-info mr-2"></i>{{ $ekskul ? 'Edit' : 'Tambah' }} Ekstrakurikuler</h3>
      </div>
      <form action="{{ $ekskul ? route('admin.ekskul.update', $ekskul) : route('admin.ekskul.store') }}" method="POST">
        @csrf
        @if($ekskul) @method('PUT') @endif
        <div class="card-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif

          <div class="form-group">
            <label>Nama Ekstrakurikuler <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                   value="{{ old('nama', $ekskul?->nama) }}"
                   placeholder="e.g. Paskibra, OSIS, Robotik, Futsal">
            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="form-group">
            <label>Guru Pembina</label>
            <select name="pembina_id" class="form-control">
              <option value="">-- Belum Ditunjuk --</option>
              @foreach($gurus as $g)
                <option value="{{ $g->id }}" {{ old('pembina_id', $ekskul?->pembina_id) == $g->id ? 'selected' : '' }}>
                  {{ $g->nama }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="3" class="form-control"
                      placeholder="Deskripsi singkat kegiatan ekskul...">{{ old('deskripsi', $ekskul?->deskripsi) }}</textarea>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.ekskul.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
