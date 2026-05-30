@extends('layouts.admin')
@section('title', 'Pembayaran SPP Anak')
@section('page_title', 'Pembayaran SPP Anak')

@section('content')
@include('orangtua.partials.anak-switcher')

@if(! $anak)
  <div class="alert alert-warning">Belum ada data anak.</div>
@else
<div class="row">
  <div class="col-md-3 col-6">
    <div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-file-invoice"></i></span>
      <div class="info-box-content"><span class="info-box-text">Total Tagihan</span><span class="info-box-number">{{ $summary['total_tagihan'] }}</span></div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
      <div class="info-box-content"><span class="info-box-text">Lunas</span><span class="info-box-number">{{ $summary['jumlah_lunas'] }}</span></div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
      <div class="info-box-content"><span class="info-box-text">Belum/Cicil</span><span class="info-box-number">{{ $summary['jumlah_belum'] }}</span></div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-money-bill-wave"></i></span>
      <div class="info-box-content"><span class="info-box-text">Tunggakan</span><span class="info-box-number" style="font-size:1rem">Rp {{ number_format($summary['total_tunggakan'], 0, ',', '.') }}</span></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title">Daftar Tagihan {{ $anak->nama }}</h3></div>
  <div class="card-body table-responsive p-0">
    <table class="table table-hover">
      <thead><tr><th>Bulan</th><th>Nominal</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
      <tbody>
        @forelse($tagihans as $t)
          <tr>
            <td>{{ $t->bulan_label }}</td>
            <td>Rp {{ number_format($t->nominal, 0, ',', '.') }}</td>
            <td><span class="badge badge-{{ $t->status_color }}">{{ $t->status_label }}</span></td>
            <td class="text-right">
              <a href="{{ route('orangtua.spp.show', $t) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-eye"></i> Detail
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted py-3">Belum ada tagihan.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
