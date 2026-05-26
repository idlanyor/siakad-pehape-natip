@extends('layouts.admin')
@section('title', 'Laporan Keuangan SPP')
@section('page_title', 'Laporan Keuangan SPP')

@section('content')

{{-- Filter Bulan --}}
<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter Laporan</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('admin.keuangan.laporan') }}" class="form-inline">
      <div class="form-group mr-3">
        <label class="mr-2">Bulan</label>
        <input type="month" name="bulan" class="form-control" value="{{ $bulanFilter }}">
      </div>
      <button type="submit" class="btn btn-primary mr-2">
        <i class="fas fa-search mr-1"></i> Lihat
      </button>
      <a href="{{ route('admin.keuangan.tunggakan', ['bulan' => $bulanFilter]) }}"
         class="btn btn-warning">
        <i class="fas fa-exclamation-circle mr-1"></i> Lihat Tunggakan
      </a>
    </form>
  </div>
</div>

{{-- Summary --}}
<div class="row">
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-info"><i class="fas fa-file-invoice"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Tagihan</span>
        <span class="info-box-number">{{ $summary['total_tagihan'] }}</span>
        <span class="progress-description">Rp {{ number_format($summary['total_nominal'], 0, ',', '.') }}</span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Lunas</span>
        <span class="info-box-number">{{ $summary['jumlah_lunas'] }}</span>
        <span class="progress-description">
          Terkumpul Rp {{ number_format($summary['total_terkumpul'], 0, ',', '.') }}
        </span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Cicilan</span>
        <span class="info-box-number">{{ $summary['jumlah_cicil'] }}</span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Belum Bayar</span>
        <span class="info-box-number">{{ $summary['jumlah_belum'] }}</span>
        <span class="progress-description text-danger">
          Tunggakan Rp {{ number_format($summary['total_tunggakan'], 0, ',', '.') }}
        </span>
      </div>
    </div>
  </div>
</div>

{{-- Rekap per Kelas --}}
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-table mr-2"></i>Rekap per Kelas —
      <span class="text-muted">{{ \Carbon\Carbon::createFromFormat('Y-m', $bulanFilter)->translatedFormat('F Y') }}</span>
    </h3>
  </div>
  <div class="card-body p-0">
    @if($rekapKelas->isEmpty())
      <div class="text-center py-4 text-muted">
        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Tidak ada data tagihan pada bulan ini.
      </div>
    @else
      <table class="table table-sm table-hover table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th>Kelas</th>
            <th class="text-center">Total Siswa</th>
            <th class="text-center text-success">Lunas</th>
            <th class="text-center text-warning">Cicilan</th>
            <th class="text-center text-danger">Belum Bayar</th>
            <th class="text-center" style="min-width:120px">% Lunas</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rekapKelas as $r)
          <tr>
            <td>
              <strong>{{ $r['kelas']->nama_kelas }}</strong>
              <small class="text-muted d-block">{{ $r['kelas']->jurusan?->nama }}</small>
            </td>
            <td class="text-center">{{ $r['total'] }}</td>
            <td class="text-center font-weight-bold text-success">{{ $r['lunas'] }}</td>
            <td class="text-center font-weight-bold text-warning">{{ $r['cicil'] }}</td>
            <td class="text-center font-weight-bold text-danger">{{ $r['belum'] }}</td>
            <td class="text-center">
              <div class="progress progress-sm mb-1">
                <div class="progress-bar bg-success" style="width:{{ $r['persen_lunas'] }}%"></div>
              </div>
              <small>{{ $r['persen_lunas'] }}%</small>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="thead-light">
          <tr>
            <th>Total</th>
            <th class="text-center">{{ $summary['total_tagihan'] }}</th>
            <th class="text-center text-success">{{ $summary['jumlah_lunas'] }}</th>
            <th class="text-center text-warning">{{ $summary['jumlah_cicil'] }}</th>
            <th class="text-center text-danger">{{ $summary['jumlah_belum'] }}</th>
            <th class="text-center">
              @if($summary['total_tagihan'] > 0)
                {{ round(($summary['jumlah_lunas'] / $summary['total_tagihan']) * 100) }}%
              @else —
              @endif
            </th>
          </tr>
        </tfoot>
      </table>
    @endif
  </div>
</div>

@endsection
