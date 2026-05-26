@extends('layouts.admin')
@section('title', 'Laporan PPDB')
@section('page_title', 'Laporan PPDB')

@section('content')

{{-- Stats Global --}}
<div class="row">
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Pendaftar</span>
        <span class="info-box-number">{{ $statsGlobal['total'] }}</span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Diterima</span>
        <span class="info-box-number">{{ $statsGlobal['diterima'] }}</span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Ditolak</span>
        <span class="info-box-number">{{ $statsGlobal['ditolak'] }}</span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Menunggu Proses</span>
        <span class="info-box-number">{{ $statsGlobal['menunggu'] }}</span>
      </div>
    </div>
  </div>
</div>

{{-- Rekap per Jurusan --}}
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>Rekap per Jurusan</h3>
    <div class="card-tools">
      <a href="{{ route('admin.ppdb.index') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-list mr-1"></i> Data Pendaftar
      </a>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover table-striped mb-0">
      <thead class="thead-light">
        <tr>
          <th>Jurusan</th>
          <th class="text-center">Total</th>
          <th class="text-center">Menunggu</th>
          <th class="text-center">Verifikasi</th>
          <th class="text-center text-success">Diterima</th>
          <th class="text-center text-danger">Ditolak</th>
          <th class="text-center" style="min-width:100px">% Terisi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rekapJurusan as $r)
        <tr>
          <td class="font-weight-bold">{{ $r['jurusan']->nama_jurusan }}</td>
          <td class="text-center">{{ $r['total'] }}</td>
          <td class="text-center">{{ $r['pending'] }}</td>
          <td class="text-center">{{ $r['verifikasi'] }}</td>
          <td class="text-center font-weight-bold text-success">{{ $r['diterima'] }}</td>
          <td class="text-center text-danger">{{ $r['ditolak'] }}</td>
          <td class="text-center">
            @php $pct = $r['total'] > 0 ? round(($r['diterima'] / $r['total']) * 100) : 0; @endphp
            <div class="progress progress-sm">
              <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
            </div>
            <small>{{ $pct }}%</small>
          </td>
        </tr>
        @endforeach
      </tbody>
      <tfoot class="thead-light">
        <tr>
          <th>TOTAL</th>
          <th class="text-center">{{ $statsGlobal['total'] }}</th>
          <th class="text-center">{{ $rekapJurusan->sum('pending') }}</th>
          <th class="text-center">{{ $rekapJurusan->sum('verifikasi') }}</th>
          <th class="text-center text-success">{{ $statsGlobal['diterima'] }}</th>
          <th class="text-center text-danger">{{ $statsGlobal['ditolak'] }}</th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

{{-- Per Gelombang --}}
@if($gelombangs->isNotEmpty())
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Rekap per Gelombang</h3>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover table-sm mb-0">
      <thead class="thead-light">
        <tr>
          <th>Gelombang</th>
          <th class="text-center">Total Pendaftar</th>
          <th class="text-center text-success">Diterima</th>
        </tr>
      </thead>
      <tbody>
        @foreach($gelombangs as $g)
        <tr>
          <td class="font-weight-bold">Gelombang {{ $g->gelombang }}</td>
          <td class="text-center">{{ $g->total }}</td>
          <td class="text-center text-success font-weight-bold">{{ $g->diterima }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

@endsection
