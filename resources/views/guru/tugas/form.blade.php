@extends('layouts.admin')
@section('title', isset($tugas) ? 'Edit Tugas' : 'Buat Tugas')
@section('page_title', isset($tugas) ? 'Edit Tugas' : 'Buat Tugas Baru')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card card-warning card-outline">
      <div class="card-header">
        <h3 class="card-title">
          {{ isset($tugas) ? 'Edit' : 'Buat' }} Tugas &mdash;
          <span class="badge badge-warning">{{ $jadwal->mapel->nama_mapel }}</span>
          {{ $jadwal->kelas?->nama_kelas }}
        </h3>
      </div>
      <form action="{{ isset($tugas) ? route('guru.tugas.update', $tugas) : route('guru.tugas.store', $jadwal) }}"
            method="POST">
        @csrf
        @isset($tugas) @method('PUT') @endisset
        <div class="card-body">
          <div class="form-group">
            <label>Judul Tugas <span class="text-danger">*</span></label>
            <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                   value="{{ old('judul', $tugas->judul ?? '') }}" required>
            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="form-group">
            <label>Instruksi / Deskripsi Tugas <span class="text-danger">*</span></label>
            <textarea name="instruksi" rows="5"
                      class="form-control @error('instruksi') is-invalid @enderror"
                      placeholder="Jelaskan apa yang harus dikerjakan siswa..." required>{{ old('instruksi', $tugas->instruksi ?? '') }}</textarea>
            @error('instruksi')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Deadline <span class="text-danger">*</span></label>
                <input type="datetime-local" name="deadline"
                       class="form-control @error('deadline') is-invalid @enderror"
                       value="{{ old('deadline', isset($tugas) ? $tugas->deadline->format('Y-m-d\TH:i') : '') }}" required>
                @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Nilai Maksimal <span class="text-danger">*</span></label>
                <input type="number" name="nilai_maksimal" min="1" max="100"
                       class="form-control @error('nilai_maksimal') is-invalid @enderror"
                       value="{{ old('nilai_maksimal', $tugas->nilai_maksimal ?? 100) }}" required>
                @error('nilai_maksimal')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Tipe Pengumpulan <span class="text-danger">*</span></label>
            <select name="tipe_pengumpulan" class="form-control @error('tipe_pengumpulan') is-invalid @enderror">
              @foreach(\App\Models\Tugas::TIPE_LABEL as $val => $label)
                <option value="{{ $val }}"
                  {{ old('tipe_pengumpulan', $tugas->tipe_pengumpulan ?? 'file') === $val ? 'selected' : '' }}>
                  {{ $label }}
                </option>
              @endforeach
            </select>
            @error('tipe_pengumpulan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('guru.tugas.show', $jadwal) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
          <button type="submit" class="btn btn-warning">
            <i class="fas fa-save"></i> {{ isset($tugas) ? 'Perbarui' : 'Simpan' }} Tugas
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
