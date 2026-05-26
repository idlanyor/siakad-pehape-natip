@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Admin')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total_siswa'] }}</h3>
                <p>Total Siswa</p>
            </div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['total_guru'] }}</h3>
                <p>Total Guru</p>
            </div>
            <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['total_kelas'] }}</h3>
                <p>Total Kelas</p>
            </div>
            <div class="icon"><i class="fas fa-door-open"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['total_jurusan'] }}</h3>
                <p>Jurusan</p>
            </div>
            <div class="icon"><i class="fas fa-graduation-cap"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_mapel'] }}</h3>
                <p>Mata Pelajaran</p>
            </div>
            <div class="icon"><i class="fas fa-book"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $stats['tahun_ajaran'] }}</h3>
                <p>Tahun Ajaran Aktif</p>
            </div>
            <div class="icon"><i class="fas fa-calendar"></i></div>
            <a href="#" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box" style="background:#6f42c1; color:#fff;">
            <div class="inner">
                <h3>Semester {{ $stats['semester_aktif'] }}</h3>
                <p>Semester Aktif</p>
            </div>
            <div class="icon"><i class="fas fa-clipboard-list"></i></div>
            <a href="#" class="small-box-footer" style="color:#ddd;">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

{{-- Alert PPDB & Tunggakan --}}
@if($stats['ppdb_pending'] > 0 || $stats['spp_tunggakan'] > 0)
<div class="row">
  @if($stats['ppdb_pending'] > 0)
  <div class="col-md-6">
    <div class="alert alert-warning alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <i class="fas fa-user-clock mr-1"></i>
      Ada <strong>{{ $stats['ppdb_pending'] }}</strong> pendaftar PPDB menunggu proses.
      <a href="{{ route('admin.ppdb.index', ['status' => 'pending']) }}" class="alert-link ml-2">Review sekarang →</a>
    </div>
  </div>
  @endif
  @if($stats['spp_tunggakan'] > 0)
  <div class="col-md-6">
    <div class="alert alert-info alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <i class="fas fa-exclamation-circle mr-1"></i>
      <strong>{{ $stats['spp_tunggakan'] }}</strong> tagihan SPP belum lunas.
      <a href="{{ route('admin.keuangan.tunggakan') }}" class="alert-link ml-2">Lihat tunggakan →</a>
    </div>
  </div>
  @endif
</div>
@endif

{{-- Charts Row --}}
<div class="row">
  {{-- Chart: Absensi 6 Bulan --}}
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Absensi 6 Bulan Terakhir</h3>
      </div>
      <div class="card-body">
        <canvas id="chartAbsensi" height="120"></canvas>
      </div>
    </div>
  </div>

  {{-- Chart: SPP Bulan Ini --}}
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>SPP {{ now()->translatedFormat('F Y') }}</h3>
      </div>
      <div class="card-body d-flex justify-content-center">
        <canvas id="chartSpp" height="200" style="max-width:220px"></canvas>
      </div>
      <div class="card-footer text-center text-muted small">
        Lunas: {{ $sppChart['lunas'] }} &nbsp;·&nbsp; Cicil: {{ $sppChart['cicil'] }} &nbsp;·&nbsp; Belum: {{ $sppChart['belum'] }}
      </div>
    </div>
  </div>
</div>

{{-- Chart: PPDB per Jurusan --}}
@if($ppdbChart->isNotEmpty() && $ppdbChart->sum('diterima') + $ppdbChart->sum('pending') > 0)
<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>PPDB per Jurusan</h3>
        <div class="card-tools">
          <a href="{{ route('admin.ppdb.laporan') }}" class="btn btn-xs btn-info">Laporan Lengkap</a>
        </div>
      </div>
      <div class="card-body">
        <canvas id="chartPpdb" height="160"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Panel Administrator</h3>
      </div>
      <div class="card-body">
        <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Anda login sebagai <span class="badge badge-danger">Administrator</span>.</p>
        <p class="text-muted">Gunakan menu di sidebar untuk mengelola data sekolah.</p>
        <div class="row mt-3">
          <div class="col-6">
            <a href="{{ route('admin.nilai.index') }}" class="btn btn-outline-primary btn-sm btn-block mb-2">
              <i class="fas fa-star-half-alt mr-1"></i> Input Nilai
            </a>
            <a href="{{ route('admin.absensi.index') }}" class="btn btn-outline-success btn-sm btn-block mb-2">
              <i class="fas fa-clipboard-list mr-1"></i> Absensi
            </a>
          </div>
          <div class="col-6">
            <a href="{{ route('admin.keuangan.tagihan.index') }}" class="btn btn-outline-warning btn-sm btn-block mb-2">
              <i class="fas fa-money-bill mr-1"></i> Kelola SPP
            </a>
            <a href="{{ route('admin.ppdb.index') }}" class="btn btn-outline-info btn-sm btn-block mb-2">
              <i class="fas fa-school mr-1"></i> Data PPDB
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@else
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Panel Administrator</h3>
      </div>
      <div class="card-body">
        <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Anda login sebagai <span class="badge badge-danger">Administrator</span>.</p>
        <p class="text-muted mb-0">Gunakan menu di sidebar untuk mengelola data sekolah.</p>
      </div>
    </div>
  </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Chart Absensi
const absensiData = @json($absensiChart);
new Chart(document.getElementById('chartAbsensi'), {
  type: 'bar',
  data: {
    labels: absensiData.map(d => d.label),
    datasets: [
      { label: 'Hadir',  data: absensiData.map(d => d.hadir), backgroundColor: '#28a745' },
      { label: 'Sakit',  data: absensiData.map(d => d.sakit), backgroundColor: '#ffc107' },
      { label: 'Izin',   data: absensiData.map(d => d.izin),  backgroundColor: '#17a2b8' },
      { label: 'Alpa',   data: absensiData.map(d => d.alpa),  backgroundColor: '#dc3545' },
    ]
  },
  options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { x: { stacked: false }, y: { beginAtZero: true } } }
});

// Chart SPP Donut
const spp = @json($sppChart);
new Chart(document.getElementById('chartSpp'), {
  type: 'doughnut',
  data: {
    labels: ['Lunas', 'Cicilan', 'Belum Bayar'],
    datasets: [{ data: [spp.lunas, spp.cicil, spp.belum], backgroundColor: ['#28a745', '#ffc107', '#dc3545'] }]
  },
  options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Chart PPDB Bar
@if($ppdbChart->isNotEmpty())
const ppdb = @json($ppdbChart);
new Chart(document.getElementById('chartPpdb'), {
  type: 'bar',
  data: {
    labels: ppdb.map(d => d.jurusan),
    datasets: [
      { label: 'Diterima', data: ppdb.map(d => d.diterima), backgroundColor: '#28a745' },
      { label: 'Proses',   data: ppdb.map(d => d.pending),  backgroundColor: '#ffc107' },
    ]
  },
  options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
});
@endif
</script>
@endpush
@endsection
