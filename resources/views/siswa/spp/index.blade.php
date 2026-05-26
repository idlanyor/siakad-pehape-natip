@extends('layouts.admin')
@section('title', 'SPP Saya')
@section('page_title', 'Tagihan SPP Saya')

@section('content')

{{-- Ringkasan --}}
<div class="row">
  <div class="col-sm-6 col-lg-3">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $summary['total_tagihan'] }}</h3>
        <p>Total Tagihan</p>
      </div>
      <div class="icon"><i class="fas fa-file-invoice"></i></div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ $summary['jumlah_lunas'] }}</h3>
        <p>Sudah Lunas</p>
      </div>
      <div class="icon"><i class="fas fa-check-circle"></i></div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3>{{ $summary['jumlah_belum'] }}</h3>
        <p>Belum / Cicilan</p>
      </div>
      <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>Rp {{ number_format($summary['total_tunggakan'], 0, ',', '.') }}</h3>
        <p>Total Tunggakan</p>
      </div>
      <div class="icon"><i class="fas fa-wallet"></i></div>
    </div>
  </div>
</div>

{{-- Tabel Tagihan --}}
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-list mr-2"></i>Riwayat Tagihan SPP
    </h3>
  </div>
  <div class="card-body p-0">
    @if($tagihans->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
        Belum ada tagihan SPP.
      </div>
    @else
      <table class="table table-hover table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th>Bulan</th>
            <th class="text-right">Nominal</th>
            <th class="text-right">Dibayar</th>
            <th class="text-right">Sisa</th>
            <th class="text-center">Status</th>
            <th class="text-center">Detail</th>
          </tr>
        </thead>
        <tbody>
          @foreach($tagihans as $t)
          <tr>
            <td class="font-weight-bold">{{ $t->bulan_label }}</td>
            <td class="text-right">{{ $t->nominal_rp }}</td>
            <td class="text-right text-success">Rp {{ number_format($t->total_dibayar, 0, ',', '.') }}</td>
            <td class="text-right {{ $t->sisa_tagihan > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
              Rp {{ number_format($t->sisa_tagihan, 0, ',', '.') }}
            </td>
            <td class="text-center">
              <span class="badge badge-{{ $t->status_color }}">{{ $t->status_label }}</span>
            </td>
            <td class="text-center">
              <a href="{{ route('siswa.spp.show', $t) }}" class="btn btn-xs btn-primary">
                <i class="fas fa-eye mr-1"></i>Detail
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>

@endsection
