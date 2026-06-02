@extends('layouts.admin')

@section('title', 'Statistik Ujian')
@section('page_title', 'Statistik Ujian')

@section('content')
{{-- Info Ujian --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informasi Ujian</h3>
        <div class="card-tools">
            <a href="{{ route('admin.ujian.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="30%">Judul</th>
                        <td>{{ $ujian->judul }}</td>
                    </tr>
                    <tr>
                        <th>Guru</th>
                        <td>{{ $ujian->guru->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kelas</th>
                        <td>{{ $ujian->jadwal->kelas->nama_kelas ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Mata Pelajaran</th>
                        <td>{{ $ujian->jadwal->mapel->nama_mapel ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="30%">Tipe</th>
                        <td><span class="badge badge-{{ $ujian->tipe_color }}">{{ $ujian->tipe_label }}</span></td>
                    </tr>
                    <tr>
                        <th>Waktu</th>
                        <td>{{ $ujian->waktu_mulai->format('d/m/Y H:i') }} - {{ $ujian->waktu_selesai->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Durasi</th>
                        <td>{{ $ujian->durasi_menit }} menit</td>
                    </tr>
                    <tr>
                        <th>Passing Grade</th>
                        <td><strong>{{ $ujian->passing_grade }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Statistik Cards --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_peserta'] }}</h3>
                <p>Total Peserta</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['sudah_mengerjakan'] }} <small>({{ $stats['persentase_mengerjakan'] }}%)</small></h3>
                <p>Sudah Mengerjakan</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($stats['rata_rata'], 2) }}</h3>
                <p>Rata-rata Nilai</p>
            </div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['passing_rate'] }}%</h3>
                <p>Passing Rate</p>
            </div>
            <div class="icon"><i class="fas fa-trophy"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Nilai Tertinggi</span>
                <span class="info-box-number">{{ number_format($stats['tertinggi'], 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Nilai Terendah</span>
                <span class="info-box-number">{{ number_format($stats['terendah'], 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Lulus</span>
                <span class="info-box-number">{{ $stats['lulus'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Tidak Lulus</span>
                <span class="info-box-number">{{ $stats['tidak_lulus'] }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row">
    {{-- Distribusi Nilai --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Distribusi Nilai</h3>
            </div>
            <div class="card-body">
                <canvas id="chartDistribusi" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Passing Rate --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Passing Rate</h3>
            </div>
            <div class="card-body d-flex justify-content-center">
                <canvas id="chartPassingRate" height="200" style="max-width:250px"></canvas>
            </div>
            <div class="card-footer text-center text-muted small">
                Lulus: {{ $stats['lulus'] }} &nbsp;·&nbsp; Tidak Lulus: {{ $stats['tidak_lulus'] }}
            </div>
        </div>
    </div>
</div>

{{-- Ranking Siswa --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-medal mr-2"></i>Ranking Siswa (Top 10)</h3>
    </div>
    <div class="card-body p-0">
        @if($ranking->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>Belum ada siswa yang mengerjakan</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="8%">Rank</th>
                            <th>Nama Siswa</th>
                            <th>NIS</th>
                            <th>Waktu Selesai</th>
                            <th>Durasi</th>
                            <th>Nilai Akhir</th>
                            <th>Predikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ranking as $sesi)
                        <tr>
                            <td class="text-center">
                                @if($loop->iteration == 1)
                                    <i class="fas fa-trophy text-warning fa-lg"></i>
                                @elseif($loop->iteration == 2)
                                    <i class="fas fa-medal text-secondary fa-lg"></i>
                                @elseif($loop->iteration == 3)
                                    <i class="fas fa-medal text-danger fa-lg"></i>
                                @else
                                    <strong>{{ $loop->iteration }}</strong>
                                @endif
                            </td>
                            <td>{{ $sesi->siswa->nama ?? '-' }}</td>
                            <td>{{ $sesi->siswa->nis ?? '-' }}</td>
                            <td>{{ $sesi->waktu_selesai?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $sesi->durasi_detik ? round($sesi->durasi_detik / 60, 1) . ' menit' : '-' }}</td>
                            <td><strong>{{ number_format($sesi->nilai_akhir, 2) }}</strong></td>
                            <td>
                                <span class="badge badge-{{ $sesi->predikat_color }}">{{ $sesi->predikat }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Chart Distribusi Nilai (Histogram)
const distribusiData = @json($distribusi);
new Chart(document.getElementById('chartDistribusi'), {
    type: 'bar',
    data: {
        labels: Object.keys(distribusiData),
        datasets: [{
            label: 'Jumlah Siswa',
            data: Object.values(distribusiData),
            backgroundColor: '#007bff',
            borderColor: '#0056b3',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: {
                display: true,
                text: 'Distribusi Nilai Siswa'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});

// Chart Passing Rate (Pie)
const passingData = @json($stats);
new Chart(document.getElementById('chartPassingRate'), {
    type: 'doughnut',
    data: {
        labels: ['Lulus', 'Tidak Lulus'],
        datasets: [{
            data: [passingData.lulus, passingData.tidak_lulus],
            backgroundColor: ['#28a745', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
@endpush
