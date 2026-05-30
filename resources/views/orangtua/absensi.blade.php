@extends('layouts.admin')
@section('title', 'Absensi Anak')
@section('page_title', 'Absensi Anak')

@section('content')
@include('orangtua.partials.anak-switcher')

@if(! $anak)
  <div class="alert alert-warning">Belum ada data anak.</div>
@else
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Rekap Absensi {{ $anak->nama }} — {{ $semester->nama ?? 'Semester Aktif' }}</h3>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-hover text-nowrap">
      <thead>
        <tr>
          <th>Mata Pelajaran</th>
          <th class="text-center">Hadir</th>
          <th class="text-center">Izin</th>
          <th class="text-center">Sakit</th>
          <th class="text-center">Alpa</th>
          <th class="text-center">% Hadir</th>
        </tr>
      </thead>
      <tbody>
        @forelse($jadwals as $jadwal)
          @php $r = $rekap[$jadwal->id]; @endphp
          <tr>
            <td>{{ $jadwal->mapel->nama_mapel ?? '—' }}</td>
            <td class="text-center text-success">{{ $r['hadir'] }}</td>
            <td class="text-center">{{ $r['izin'] }}</td>
            <td class="text-center">{{ $r['sakit'] }}</td>
            <td class="text-center text-danger font-weight-bold">{{ $r['alpa'] }}</td>
            <td class="text-center">
              <span class="badge badge-{{ $r['persen'] >= 75 ? 'success' : ($r['persen'] >= 50 ? 'warning' : 'danger') }}">
                {{ $r['persen'] }}%
              </span>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-3">Belum ada data absensi.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
