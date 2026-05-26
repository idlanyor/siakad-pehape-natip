@extends('layouts.admin')
@section('title', 'Rekap Absensi Saya')
@section('page_title', 'Rekap Absensi')

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
  @php $r = $rekap[$jadwal->id]; @endphp
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center py-2">
        <div>
          <strong>{{ $jadwal->mapel->nama_mapel }}</strong>
          <span class="badge badge-secondary ml-1">{{ $jadwal->hari }}</span>
        </div>
        <small class="text-muted">{{ $jadwal->jam_mulai }}-{{ $jadwal->jam_selesai }}</small>
      </div>
      <div class="card-body pb-2">
        {{-- Progress bar kehadiran --}}
        <div class="mb-2">
          <div class="d-flex justify-content-between mb-1">
            <small>Kehadiran</small>
            <small><strong>{{ $r['persen'] }}%</strong> ({{ $r['hadir'] }}/{{ $r['total'] }})</small>
          </div>
          <div class="progress" style="height:10px">
            <div class="progress-bar {{ $r['persen'] >= 75 ? 'bg-success' : ($r['persen'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                 style="width:{{ $r['persen'] }}%"></div>
          </div>
        </div>

        {{-- Breakdown --}}
        <div class="row text-center mt-2">
          <div class="col-3">
            <span class="badge badge-success d-block py-1">{{ $r['hadir'] }}</span>
            <small>Hadir</small>
          </div>
          <div class="col-3">
            <span class="badge badge-warning d-block py-1">{{ $r['izin'] }}</span>
            <small>Izin</small>
          </div>
          <div class="col-3">
            <span class="badge badge-info d-block py-1">{{ $r['sakit'] }}</span>
            <small>Sakit</small>
          </div>
          <div class="col-3">
            <span class="badge badge-danger d-block py-1">{{ $r['alpa'] }}</span>
            <small>Alpa</small>
          </div>
        </div>

        {{-- Warning alpa --}}
        @if($r['alpa'] >= \App\Models\Absensi::THRESHOLD_ALPA)
        <div class="alert alert-danger py-1 mt-2 mb-0 small">
          <i class="fas fa-exclamation-triangle"></i>
          Perhatian! Alpa <strong>{{ $r['alpa'] }}x</strong> — segera konfirmasi ke guru/wali kelas.
        </div>
        @endif

        {{-- Belum ada data --}}
        @if($r['total'] === 0)
        <p class="text-muted small mt-2 mb-0">Belum ada data absensi.</p>
        @endif
      </div>
      <div class="card-footer py-1 text-muted small">
        Guru: {{ $jadwal->guru?->nama ?? '-' }}
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="alert alert-warning">Belum ada jadwal atau kelas belum ditentukan.</div>
  </div>
  @endforelse
</div>
@endsection
