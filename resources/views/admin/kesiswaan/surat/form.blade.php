@extends('layouts.admin')
@section('title', $surat ? 'Edit Surat Keterangan' : 'Buat Surat Keterangan')
@section('page_title', $surat ? 'Edit Surat Keterangan' : 'Buat Surat Keterangan')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-alt text-primary mr-2"></i>{{ $surat ? 'Edit' : 'Buat' }} Surat Keterangan</h3>
      </div>
      <form action="{{ $surat ? route('admin.surat.update', $surat) : route('admin.surat.store') }}" method="POST">
        @csrf
        @if($surat) @method('PUT') @endif
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
                    <option value="{{ $s->id }}" {{ old('siswa_id', $surat?->siswa_id) == $s->id ? 'selected' : '' }}>
                      {{ $s->nama }} — {{ $s->kelas?->nama_kelas ?? '-' }}
                    </option>
                  @endforeach
                </select>
                @error('siswa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group">
                <label>Jenis Surat <span class="text-danger">*</span></label>
                <select name="jenis_surat" class="form-control @error('jenis_surat') is-invalid @enderror">
                  <option value="">-- Pilih Jenis --</option>
                  @foreach($jenisList as $key => $label)
                    <option value="{{ $key }}" {{ old('jenis_surat', $surat?->jenis_surat) == $key ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
                @error('jenis_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-7">
              <div class="form-group">
                <label>Nomor Surat <span class="text-danger">*</span></label>
                <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror"
                       value="{{ old('nomor_surat', $surat?->nomor_surat) }}"
                       placeholder="e.g. 421.7/SMK48/V/2026/001">
                @error('nomor_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group">
                <label>Tanggal Surat <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror"
                       value="{{ old('tanggal_surat', $surat?->tanggal_surat?->toDateString() ?? today()->toDateString()) }}">
                @error('tanggal_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Keterangan Tambahan</label>
            <textarea name="keterangan" rows="3" class="form-control"
                      placeholder="Keterangan/catatan tambahan yang akan tercetak pada surat...">{{ old('keterangan', $surat?->keterangan) }}</textarea>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.surat.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-file-alt mr-1"></i> {{ $surat ? 'Simpan Perubahan' : 'Buat Surat' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
