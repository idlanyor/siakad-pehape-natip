@extends('layouts.admin')
@section('title', $penempatan ? 'Edit Penempatan PKL' : 'Tambah Penempatan PKL')
@section('page_title', $penempatan ? 'Edit Penempatan PKL' : 'Tambah Penempatan PKL')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-9">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $penempatan ? 'Edit' : 'Tambah' }} Penempatan PKL</h3>
      </div>
      <form action="{{ $penempatan ? route('admin.pkl-penempatan.update', $penempatan) : route('admin.pkl-penempatan.store') }}" method="POST">
        @csrf
        @if($penempatan) @method('PUT') @endif
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Siswa <span class="text-danger">*</span></label>
                <select name="siswa_id" class="form-control @error('siswa_id') is-invalid @enderror">
                  <option value="">-- Pilih Siswa --</option>
                  @foreach($siswas as $s)
                    <option value="{{ $s->id }}" {{ old('siswa_id', $penempatan?->siswa_id) == $s->id ? 'selected' : '' }}>
                      {{ $s->nama }} ({{ $s->nisn }})
                    </option>
                  @endforeach
                </select>
                @error('siswa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Tempat PKL <span class="text-danger">*</span></label>
                <select name="tempat_pkl_id" class="form-control @error('tempat_pkl_id') is-invalid @enderror">
                  <option value="">-- Pilih Tempat --</option>
                  @foreach($tempats as $t)
                    <option value="{{ $t->id }}" {{ old('tempat_pkl_id', $penempatan?->tempat_pkl_id) == $t->id ? 'selected' : '' }}>
                      {{ $t->nama }}
                    </option>
                  @endforeach
                </select>
                @error('tempat_pkl_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Guru Pembimbing</label>
                <select name="guru_pembimbing_id" class="form-control @error('guru_pembimbing_id') is-invalid @enderror">
                  <option value="">-- Tidak ada --</option>
                  @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ old('guru_pembimbing_id', $penempatan?->guru_pembimbing_id) == $g->id ? 'selected' : '' }}>
                      {{ $g->nama }}
                    </option>
                  @endforeach
                </select>
                @error('guru_pembimbing_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Semester <span class="text-danger">*</span></label>
                <select name="semester_id" class="form-control @error('semester_id') is-invalid @enderror">
                  <option value="">-- Pilih Semester --</option>
                  @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ old('semester_id', $penempatan?->semester_id) == $sem->id ? 'selected' : '' }}>
                      Sem {{ $sem->semester }} / {{ $sem->tahunAjaran->nama }}
                      {{ $sem->aktif ? '(Aktif)' : '' }}
                    </option>
                  @endforeach
                </select>
                @error('semester_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-5">
              <div class="form-group">
                <label>Periode Mulai</label>
                <input type="date" name="periode_mulai" class="form-control @error('periode_mulai') is-invalid @enderror"
                       value="{{ old('periode_mulai', $penempatan?->periode_mulai?->format('Y-m-d')) }}">
                @error('periode_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-group">
                <label>Periode Selesai</label>
                <input type="date" name="periode_selesai" class="form-control @error('periode_selesai') is-invalid @enderror"
                       value="{{ old('periode_selesai', $penempatan?->periode_selesai?->format('Y-m-d')) }}">
                @error('periode_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>Status <span class="text-danger">*</span></label>
                <select name="status" class="form-control @error('status') is-invalid @enderror">
                  <option value="aktif" {{ old('status', $penempatan?->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                  <option value="selesai" {{ old('status', $penempatan?->status) === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.pkl-penempatan.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
