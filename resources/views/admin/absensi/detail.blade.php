@extends('layouts.admin')
@section('title', 'Detail Absensi')
@section('page_title', 'Detail Absensi — ' . $jadwal->mapel->nama_mapel)

@push('styles')
<style>
  .status-H { background:#d4edda; color:#155724; font-weight:600; text-align:center; }
  .status-I { background:#fff3cd; color:#856404; font-weight:600; text-align:center; }
  .status-S { background:#d1ecf1; color:#0c5460; font-weight:600; text-align:center; }
  .status-A { background:#f8d7da; color:#721c24; font-weight:600; text-align:center; }
  .table-rekap th, .table-rekap td { font-size:.8rem; }
  .sticky-col { position:sticky; left:0; background:#fff; z-index:1; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between mb-3">
  <div>
    <h5>{{ $jadwal->mapel->nama_mapel }} — {{ $jadwal->kelas?->nama_kelas }}</h5>
    <small class="text-muted">Guru: {{ $jadwal->guru?->nama }} | {{ $jadwal->hari }}, {{ $jadwal->jam_mulai }}-{{ $jadwal->jam_selesai }}</small>
  </div>
  <div>
    <a href="{{ route('admin.absensi.export', $jadwal) }}" class="btn btn-success btn-sm mr-1">
      <i class="fas fa-file-excel mr-1"></i> Export Excel
    </a>
    <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Kembali
    </a>
  </div>
</div>

@if($pertemuan->isEmpty())
<div class="alert alert-info">Belum ada data absensi untuk jadwal ini.</div>
@else
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered table-rekap mb-0">
        <thead class="thead-dark">
          <tr>
            <th class="sticky-col">#</th>
            <th class="sticky-col" style="left:30px">Nama Siswa</th>
            @foreach($pertemuan as $tgl)
            <th class="text-center">{{ \Carbon\Carbon::parse($tgl)->format('d/m') }}</th>
            @endforeach
            <th class="text-center">H</th>
            <th class="text-center">I</th>
            <th class="text-center">S</th>
            <th class="text-center bg-danger text-white">A</th>
            <th class="text-center">%</th>
          </tr>
        </thead>
        <tbody>
          @foreach($siswas as $i => $s)
          @php
            $siswaAbsensi = $pivot[$s->id] ?? collect();
            $h  = $siswaAbsensi->filter(fn($v) => $v === 'Hadir')->count();
            $iz = $siswaAbsensi->filter(fn($v) => $v === 'Izin')->count();
            $sk = $siswaAbsensi->filter(fn($v) => $v === 'Sakit')->count();
            $al = $siswaAbsensi->filter(fn($v) => $v === 'Alpa')->count();
            $tot = $pertemuan->count();
            $pct = $tot > 0 ? round(($h / $tot) * 100) : 0;
          @endphp
          <tr>
            <td class="sticky-col">{{ $i + 1 }}</td>
            <td class="sticky-col font-weight-bold" style="left:30px; white-space:nowrap">{{ $s->nama }}</td>
            @foreach($pertemuan as $tgl)
            @php $st = $siswaAbsensi[$tgl] ?? '-'; @endphp
            <td class="{{ match($st) { 'Hadir'=>'status-H','Izin'=>'status-I','Sakit'=>'status-S','Alpa'=>'status-A',default=>'' } }}">
              {{ $st === '-' ? '—' : substr($st, 0, 1) }}
            </td>
            @endforeach
            <td class="text-center text-success font-weight-bold">{{ $h }}</td>
            <td class="text-center text-warning font-weight-bold">{{ $iz }}</td>
            <td class="text-center text-info font-weight-bold">{{ $sk }}</td>
            <td class="text-center font-weight-bold {{ $al >= \App\Models\Absensi::THRESHOLD_ALPA ? 'table-danger' : '' }}">{{ $al }}</td>
            <td class="text-center">
              <span class="badge {{ $pct >= 75 ? 'badge-success' : ($pct >= 50 ? 'badge-warning' : 'badge-danger') }}">
                {{ $pct }}%
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer text-muted small">
    H=Hadir | I=Izin | S=Sakit | A=Alpa | %=Persentase kehadiran ({{ $pertemuan->count() }} pertemuan)
  </div>
</div>
@endif
@endsection
