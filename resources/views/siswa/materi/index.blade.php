@extends('layouts.admin')
@section('title', 'Materi Pelajaran')
@section('page_title', 'Materi Pelajaran')

@section('content')
@if($semester)
<div class="alert alert-info py-2 mb-3">
  <i class="fas fa-calendar-check mr-1"></i>
  Semester {{ $semester->semester }} — {{ $semester->tahunAjaran->nama }}
  &nbsp;|&nbsp; Kelas: <strong>{{ $siswa->kelas?->nama_kelas ?? '-' }}</strong>
</div>
@endif

<div class="row">
  @forelse($jadwals as $jadwal)
  <div class="col-md-6 col-lg-4">
    <div class="card mb-3">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <strong>{{ $jadwal->mapel->nama_mapel }}</strong>
        <span class="badge badge-primary">{{ $jadwal->materis_count }} materi</span>
      </div>
      <div class="card-body py-2">
        <small class="text-muted d-block">
          <i class="fas fa-chalkboard-teacher mr-1"></i>{{ $jadwal->guru?->nama ?? '-' }}
        </small>
        <small class="text-muted">
          <i class="fas fa-clock mr-1"></i>{{ $jadwal->hari }}, {{ $jadwal->jam_mulai }}–{{ $jadwal->jam_selesai }}
        </small>
      </div>
      <div class="card-footer py-2">
        <a href="{{ route('siswa.materi.jadwal', $jadwal) }}" class="btn btn-primary btn-sm btn-block">
          <i class="fas fa-book-open"></i> Lihat Materi
        </a>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="alert alert-warning">
      <i class="fas fa-exclamation-triangle"></i> Belum ada jadwal atau kelas belum ditentukan.
    </div>
  </div>
  @endforelse
</div>
@endsection
