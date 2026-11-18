@extends('layouts.admin')
@section('title', 'Rekap Absensi')
@section('page_title', 'Rekap Absensi — ' . $jadwal->mapel->nama_mapel)

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
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="mb-0">{{ $jadwal->mapel->nama_mapel }} — {{ $jadwal->kelas?->nama_kelas }}</h5>
    <small class="text-muted">{{ $jadwal->hari }}, {{ $jadwal->jam_mulai }}-{{ $jadwal->jam_selesai }}</small>
  </div>
  <div>
    <a href="{{ route('guru.absensi.form', $jadwal) }}?tanggal={{ today()->toDateString() }}" class="btn btn-primary btn-sm">
      <i class="fas fa-plus"></i> Input Absensi
    </a>
    <a href="{{ route('guru.absensi.index') }}" class="btn btn-secondary btn-sm ml-1">
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
            <th class="text-center" title="{{ \Carbon\Carbon::parse($tgl)->isoFormat('dddd, D MMM') }}">
              {{ \Carbon\Carbon::parse($tgl)->format('d/m') }}
            </th>
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
            $h = $siswaAbsensi->filter(fn($v) => $v === 'Hadir')->count();
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
            @php  $st = $siswaAbsensi[$tgl] ?? '-'; @endphp
            <td class="{{ match($st) { 'Hadir'=>'status-H','Izin'=>'status-I','Sakit'=>'status-S','Alpa'=>'status-A', default=>'' } }}">
              {{ $st === '-' ? '—' : substr($st, 0, 1) }}
            </td>
            @endforeach
            <td class="text-center text-success font-weight-bold">{{ $h }}</td>
            <td class="text-center text-warning font-weight-bold">{{ $iz }}</td>
            <td class="text-center text-info font-weight-bold">{{ $sk }}</td>
            <td class="text-center font-weight-bold {{ $al >= \App\Models\Absensi::THRESHOLD_ALPA ? 'table-danger' : '' }}">
              {{ $al }}
              @if($al >= \App\Models\Absensi::THRESHOLD_ALPA)
              <i class="fas fa-exclamation-triangle text-danger" title="Melebihi threshold!"></i>
              @endif
            </td>
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
    <strong>Keterangan:</strong>
    H = Hadir &nbsp;|&nbsp; I = Izin &nbsp;|&nbsp; S = Sakit &nbsp;|&nbsp; A = Alpa &nbsp;|&nbsp;
    % = Persentase kehadiran (total {{ $pertemuan->count() }} pertemuan).
    <span class="text-danger">Warna merah pada kolom A = Alpa ≥ {{ \App\Models\Absensi::THRESHOLD_ALPA }}x</span>
  </div>
</div>
@endif
@endsection
