@extends('layouts.admin')
@section('title', 'Laporan Keuangan')
@section('page_title', 'Laporan Keuangan SPP — ' . $bulan)

@section('content')
<div class="row">
  <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-file-invoice"></i></span><div class="info-box-content"><span class="info-box-text">Total Tagihan</span><span class="info-box-number">{{ $summary['total'] }}</span></div></div></div>
  <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-check"></i></span><div class="info-box-content"><span class="info-box-text">Lunas</span><span class="info-box-number">{{ $summary['lunas'] }}</span></div></div></div>
  <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-coins"></i></span><div class="info-box-content"><span class="info-box-text">Terkumpul</span><span class="info-box-number" style="font-size:.9rem">Rp {{ number_format($summary['terkumpul'], 0, ',', '.') }}</span></div></div></div>
  <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-money-bill"></i></span><div class="info-box-content"><span class="info-box-text">Total Nominal</span><span class="info-box-number" style="font-size:.9rem">Rp {{ number_format($summary['nominal'], 0, ',', '.') }}</span></div></div></div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Detail Tagihan {{ $bulan }}</h3>
    <div class="float-right">
      <a href="{{ route('admin.laporan.keuangan', ['bulan' => $bulan, 'export' => 'pdf']) }}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
    </div>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-bordered table-hover">
      <thead><tr><th>#</th><th>Siswa</th><th>Kelas</th><th class="text-right">Nominal</th><th class="text-right">Dibayar</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($tagihans as $i => $t)
        <tr>
          <td>{{ $i + 1 }}</td><td>{{ $t->siswa->nama ?? '-' }}</td><td>{{ $t->siswa->kelas->nama_kelas ?? '-' }}</td>
          <td class="text-right">Rp {{ number_format($t->nominal, 0, ',', '.') }}</td>
          <td class="text-right">Rp {{ number_format($t->total_dibayar, 0, ',', '.') }}</td>
          <td><span class="badge badge-{{ $t->status_color }}">{{ $t->status_label }}</span></td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada tagihan pada bulan ini.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
