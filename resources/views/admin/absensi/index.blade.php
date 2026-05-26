@extends('layouts.admin')
@section('title', 'Overview Absensi')
@section('page_title', 'Overview Absensi')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
{{-- Statistik global --}}
<div class="row mb-3">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner"><h3>{{ $stats['total_hadir'] }}</h3><p>Total Hadir</p></div>
      <div class="icon"><i class="fas fa-check"></i></div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner"><h3>{{ $stats['total_izin'] }}</h3><p>Total Izin</p></div>
      <div class="icon"><i class="fas fa-envelope"></i></div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner"><h3>{{ $stats['total_sakit'] }}</h3><p>Total Sakit</p></div>
      <div class="icon"><i class="fas fa-heartbeat"></i></div>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner"><h3>{{ $stats['total_alpa'] }}</h3><p>Total Alpa</p></div>
      <div class="icon"><i class="fas fa-times"></i></div>
    </div>
  </div>
</div>

{{-- Warning siswa alpa --}}
@if($siswaAlpa->isNotEmpty())
<div class="card card-danger card-outline mb-3">
  <div class="card-header">
    <h3 class="card-title text-danger">
      <i class="fas fa-exclamation-triangle mr-1"></i>
      Siswa dengan Alpa ≥ {{ $threshold }}x ({{ $siswaAlpa->count() }} siswa)
    </h3>
  </div>
  <div class="card-body p-0">
    <table class="table table-sm table-bordered mb-0">
      <thead class="thead-light">
        <tr><th>#</th><th>Nama Siswa</th><th>Kelas</th><th>Jurusan</th><th>Jumlah Alpa</th></tr>
      </thead>
      <tbody>
        @foreach($siswaAlpa as $i => $s)
        <tr class="table-danger">
          <td>{{ $i+1 }}</td>
          <td class="font-weight-bold">{{ $s->nama }}</td>
          <td>{{ $s->kelas?->nama_kelas ?? '-' }}</td>
          <td>{{ $s->jurusan->nama_jurusan }}</td>
          <td><span class="badge badge-danger">{{ $s->jumlah_alpa }}x</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- Detail per jadwal --}}
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Rekap per Mata Pelajaran</h3>
  </div>
  <div class="card-body p-0">
    <table id="tabel-absensi" class="table table-bordered table-striped mb-0">
      <thead class="thead-light">
        <tr>
          <th>#</th><th>Mata Pelajaran</th><th>Kelas</th><th>Guru</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @php
          $jadwals = \App\Models\Jadwal::with(['mapel','kelas','guru'])
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get();
        @endphp
        @foreach($jadwals as $i => $j)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $j->mapel->nama_mapel }}</td>
          <td>{{ $j->kelas?->nama_kelas ?? '-' }}</td>
          <td>{{ $j->guru?->nama ?? '-' }}</td>
          <td>
            <a href="{{ route('admin.absensi.detail', $j) }}" class="btn btn-info btn-xs">
              <i class="fas fa-table"></i> Lihat Rekap
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>$(function(){ $('#tabel-absensi').DataTable({ responsive:true, autoWidth:false }); });</script>
@endpush
