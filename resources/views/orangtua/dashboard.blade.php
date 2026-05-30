@extends('layouts.admin')
@section('title', 'Dashboard Orang Tua')
@section('page_title', 'Dashboard Orang Tua')

@section('content')


@include('orangtua.partials.anak-switcher')

@if(! $anak)
  <div class="alert alert-warning">
    <i class="fas fa-info-circle mr-1"></i> Belum ada data anak yang terhubung ke akun Anda. Hubungi admin sekolah.
  </div>
@else

<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner"><h3>{{ $rekapAbsensi['Hadir'] }}</h3><p>Kehadiran</p></div>
      <div class="icon"><i class="fas fa-clipboard-check"></i></div>
      <a href="{{ route('orangtua.absensi') }}" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner"><h3>{{ $rekapAbsensi['Alpa'] }}</h3><p>Alpa</p></div>
      <div class="icon"><i class="fas fa-user-times"></i></div>
      <a href="{{ route('orangtua.absensi') }}" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner"><h3>{{ $totalPoinPelanggaran }}</h3><p>Poin Pelanggaran</p></div>
      <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
      <a href="{{ route('orangtua.kesiswaan') }}" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner"><h3>{{ $jumlahPrestasi }}</h3><p>Prestasi</p></div>
      <div class="icon"><i class="fas fa-trophy"></i></div>
      <a href="{{ route('orangtua.kesiswaan') }}" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-user-graduate mr-1"></i> Profil Anak</h3></div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tr><th class="pl-3" style="width:40%">Nama</th><td>{{ $anak->nama }}</td></tr>
          <tr><th class="pl-3">NISN</th><td>{{ $anak->nisn ?? '—' }}</td></tr>
          <tr><th class="pl-3">Kelas</th><td>{{ $anak->kelas->nama_kelas ?? '—' }}</td></tr>
          <tr><th class="pl-3">Jurusan</th><td>{{ $anak->jurusan->nama_jurusan ?? '—' }}</td></tr>
          <tr><th class="pl-3">Jumlah Mapel</th><td>{{ $jumlahMapel }}</td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card {{ $totalTunggakan > 0 ? 'card-warning' : 'card-success' }}">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-money-bill-wave mr-1"></i> Tagihan SPP</h3></div>
      <div class="card-body">
        @if($tagihanBelum->isEmpty())
          <p class="text-success mb-0"><i class="fas fa-check-circle mr-1"></i> Semua tagihan lunas.</p>
        @else
          <p>Total tunggakan: <strong class="text-danger">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</strong></p>
          <ul class="mb-2">
            @foreach($tagihanBelum->take(3) as $t)
              <li>{{ $t->bulan_label }} — <span class="badge badge-{{ $t->status_color }}">{{ $t->status_label }}</span></li>
            @endforeach
          </ul>
          <a href="{{ route('orangtua.spp') }}" class="btn btn-sm btn-primary">Bayar Sekarang</a>
        @endif
      </div>
    </div>
  </div>
</div>

@endif
@endsection
