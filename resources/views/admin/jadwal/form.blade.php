@extends('layouts.admin')
@section('title', $jadwal ? 'Edit Jadwal' : 'Tambah Jadwal')
@section('page_title', $jadwal ? 'Edit Jadwal' : 'Tambah Jadwal')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">{{ $jadwal ? 'Edit' : 'Tambah' }} Jadwal Pelajaran</h3>
      </div>
      <form action="{{ $jadwal ? route('admin.jadwal.update', $jadwal) : route('admin.jadwal.store') }}" method="POST">
        @csrf
        @if($jadwal) @method('PUT') @endif
        <div class="card-body">

          {{-- Conflict warning --}}
          @if(session('error'))
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
          </div>
          @endif

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Semester <span class="text-danger">*</span></label>
                <select name="semester_id" class="form-control @error('semester_id') is-invalid @enderror">
                  <option value="">-- Pilih Semester --</option>
                  @foreach($semesters as $s)
                    <option value="{{ $s->id }}" {{ old('semester_id', $jadwal?->semester_id) == $s->id ? 'selected' : '' }}>
                      Semester {{ $s->semester }} — {{ $s->tahunAjaran->nama }}
                      @if($s->aktif) (Aktif) @endif
                    </option>
                  @endforeach
                </select>
                @error('semester_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Mata Pelajaran <span class="text-danger">*</span></label>
                <select name="mapel_id" class="form-control @error('mapel_id') is-invalid @enderror">
                  <option value="">-- Pilih Mapel --</option>
                  @foreach($mapels as $m)
                    <option value="{{ $m->id }}" {{ old('mapel_id', $jadwal?->mapel_id) == $m->id ? 'selected' : '' }}>
                      {{ $m->nama_mapel }} ({{ $m->kode }})
                    </option>
                  @endforeach
                </select>
                @error('mapel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Kelas <span class="text-danger">*</span></label>
                <select name="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror">
                  <option value="">-- Pilih Kelas --</option>
                  @foreach($kelas as $k)
                    <option value="{{ $k->id }}" {{ old('kelas_id', $jadwal?->kelas_id) == $k->id ? 'selected' : '' }}>
                      {{ $k->nama_kelas }} — {{ $k->jurusan->nama_jurusan }}
                    </option>
                  @endforeach
                </select>
                @error('kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Guru Pengampu <span class="text-danger">*</span></label>
                <select name="guru_id" class="form-control @error('guru_id') is-invalid @enderror">
                  <option value="">-- Pilih Guru --</option>
                  @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ old('guru_id', $jadwal?->guru_id) == $g->id ? 'selected' : '' }}>
                      {{ $g->nama }}
                    </option>
                  @endforeach
                </select>
                @error('guru_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label>Hari <span class="text-danger">*</span></label>
                <select name="hari" class="form-control @error('hari') is-invalid @enderror">
                  <option value="">-- Pilih Hari --</option>
                  @foreach($hariList as $h)
                    <option value="{{ $h }}" {{ old('hari', $jadwal?->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                  @endforeach
                </select>
                @error('hari') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Jam Mulai <span class="text-danger">*</span></label>
                <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror"
                       value="{{ old('jam_mulai', $jadwal?->jam_mulai) }}">
                @error('jam_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label>Jam Selesai <span class="text-danger">*</span></label>
                <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror"
                       value="{{ old('jam_selesai', $jadwal?->jam_selesai) }}">
                @error('jam_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Ruangan</label>
                <input type="text" name="ruangan" class="form-control"
                       value="{{ old('ruangan', $jadwal?->ruangan) }}" placeholder="Contoh: Lab Komputer 1">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Link Zoom</label>
                <input type="url" name="link_zoom" class="form-control @error('link_zoom') is-invalid @enderror"
                       value="{{ old('link_zoom', $jadwal?->link_zoom) }}" placeholder="https://zoom.us/j/...">
                @error('link_zoom') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          {{-- Conflict info box --}}
          <div class="alert alert-light border mt-2">
            <small><i class="fas fa-info-circle text-info mr-1"></i>
              Sistem akan otomatis menolak jadwal jika terdapat <strong>bentrok</strong> pada kelas, guru, atau ruangan di hari dan jam yang sama.</small>
          </div>

        </div>
        <div class="card-footer d-flex justify-content-between">
          <a href="{{ route('admin.jadwal.index') }}" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
