@extends('layouts.admin')
@section('title', 'Jadwal Mengajar')
@section('page_title', 'Jadwal Mengajar Saya')

@push('styles')
<style>
  .jadwal-card { background:#fff; border-left:3px solid #28a745; border-radius:4px; padding:8px 10px; margin-bottom:6px; box-shadow:0 1px 3px rgba(0,0,0,.1); }
  .jadwal-card strong { display:block; font-size:.85rem; }
  .day-col { min-width:150px; vertical-align:top; }
</style>
@endpush

@section('content')
@if($semester)
<div class="alert alert-success py-2 mb-3">
  <i class="fas fa-calendar-check mr-1"></i>
  Jadwal mengajar Semester {{ $semester->semester }} — {{ $semester->tahunAjaran->nama }}
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
            <th class="day-col text-center" style="background:#343a40;color:#fff;">{{ $hari }}</th>
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
                {{ $j->matkul->nama_matkul }}<br>
                <small class="text-muted"><i class="fas fa-users fa-xs"></i> {{ $j->kelas?->nama_kelas ?? '-' }}</small><br>
                @if($j->ruangan)<small class="text-muted"><i class="fas fa-door-open fa-xs"></i> {{ $j->ruangan }}</small>@endif
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

<div class="card mt-3">
  <div class="card-header"><h3 class="card-title">Daftar Jadwal Mengajar</h3></div>
  <div class="card-body p-0">
    <table class="table table-striped mb-0">
      <thead class="thead-light">
        <tr><th>Hari</th><th>Jam</th><th>Mata Pelajaran</th><th>Kelas</th><th>Ruangan</th></tr>
      </thead>
      <tbody>
        @forelse($jadwals as $j)
        <tr>
          <td><span class="badge badge-secondary">{{ $j->hari }}</span></td>
          <td>{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</td>
          <td>{{ $j->matkul->nama_matkul }}</td>
          <td>{{ $j->kelas?->nama_kelas ?? '-' }}</td>
          <td>{{ $j->ruangan ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center text-muted">Belum ada jadwal mengajar.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
