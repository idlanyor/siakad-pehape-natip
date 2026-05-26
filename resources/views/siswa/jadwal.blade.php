@extends('layouts.admin')
@section('title', 'Jadwal Pelajaran')
@section('page_title', 'Jadwal Pelajaran Saya')

@push('styles')
<style>
  .jadwal-card { background:#fff; border-left:3px solid #3c8dbc; border-radius:4px; padding:8px 10px; margin-bottom:6px; box-shadow:0 1px 3px rgba(0,0,0,.1); }
  .jadwal-card strong { display:block; font-size:.85rem; }
  .day-col { min-width:150px; vertical-align:top; }
  .table-grid th { background:#343a40; color:#fff; text-align:center; }
</style>
@endpush

@section('content')
@if($semester)
<div class="alert alert-info py-2 mb-3">
  <i class="fas fa-calendar-check mr-1"></i>
  Semester {{ $semester->semester }} — {{ $semester->tahunAjaran->nama }}
  &nbsp;|&nbsp; Kelas: <strong>{{ $siswa->kelas?->nama_kelas ?? '-' }}</strong>
</div>
@endif

<div class="card">
  <div class="card-header"><h3 class="card-title">Grid Jadwal Mingguan</h3></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered mb-0">
        <thead>
          <tr>
            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
            <th class="day-col text-center">{{ $hari }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          <tr>
            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
            <td class="day-col">
              @forelse($grouped->get($hari, collect()) as $j)
              <div class="jadwal-card">
                <strong>{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</strong>
                {{ $j->mapel->nama_mapel }}<br>
                <small class="text-muted"><i class="fas fa-chalkboard-teacher fa-xs"></i> {{ $j->guru?->nama ?? '-' }}</small><br>
                @if($j->ruangan)<small class="text-muted"><i class="fas fa-door-open fa-xs"></i> {{ $j->ruangan }}</small>@endif
                @if($j->link_zoom)
                <br><a href="{{ $j->link_zoom }}" target="_blank" class="btn btn-xs btn-info mt-1"><i class="fas fa-video"></i> Zoom</a>
                @endif
              </div>
              @empty
              <span class="text-muted small">—</span>
              @endforelse
            </td>
            @endforeach
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Tabel list --}}
<div class="card mt-3">
  <div class="card-header"><h3 class="card-title">Daftar Jadwal</h3></div>
  <div class="card-body p-0">
    <table class="table table-striped mb-0">
      <thead class="thead-light">
        <tr><th>Hari</th><th>Jam</th><th>Mata Pelajaran</th><th>Guru</th><th>Ruangan</th><th>Zoom</th></tr>
      </thead>
      <tbody>
        @forelse($jadwals as $j)
        <tr>
          <td><span class="badge badge-secondary">{{ $j->hari }}</span></td>
          <td>{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</td>
          <td>{{ $j->mapel->nama_mapel }}</td>
          <td>{{ $j->guru?->nama ?? '-' }}</td>
          <td>{{ $j->ruangan ?? '-' }}</td>
          <td>
            @if($j->link_zoom)
            <a href="{{ $j->link_zoom }}" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-video"></i> Join</a>
            @else <span class="text-muted">-</span> @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center text-muted">Belum ada jadwal untuk kelas ini.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
