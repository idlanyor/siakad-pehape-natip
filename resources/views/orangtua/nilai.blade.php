@extends('layouts.admin')
@section('title', 'Nilai Anak')
@section('page_title', 'Nilai Anak')

@section('content')
@include('orangtua.partials.anak-switcher')

@if(! $anak)
  <div class="alert alert-warning">Belum ada data anak.</div>
@else
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Nilai {{ $anak->nama }} — {{ $semester->nama ?? 'Semester Aktif' }}</h3>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-hover text-nowrap">
      <thead>
        <tr>
          <th>Mata Pelajaran</th>
          <th>Guru</th>
          <th class="text-center">Pengetahuan</th>
          <th class="text-center">Keterampilan</th>
          <th class="text-center">Nilai Akhir</th>
          <th class="text-center">Predikat</th>
        </tr>
      </thead>
      <tbody>
        @forelse($jadwals as $jadwal)
          @php $n = $nilais->get($jadwal->id); @endphp
          <tr>
            <td>{{ $jadwal->mapel->nama_mapel ?? '—' }}</td>
            <td>{{ $jadwal->guru->nama ?? '—' }}</td>
            <td class="text-center">{{ $n?->rata_pengetahuan ?? '—' }}</td>
            <td class="text-center">{{ $n?->rata_keterampilan ?? '—' }}</td>
            <td class="text-center font-weight-bold">{{ $n?->nilai_akhir ?? '—' }}</td>
            <td class="text-center">
              @if($n && $n->nilai_akhir !== null)
                <span class="badge badge-{{ $n->predikat_color }}">{{ $n->predikat }}</span>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-3">Belum ada jadwal/nilai.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
