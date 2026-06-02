@extends('layouts.admin')
@section('title', 'Dashboard Siswa')
@section('page_title', 'Dashboard')

@section('content')

{{-- ── Profil + Stats ────────────────────────────────────────── --}}
<div class="row">

  {{-- Kartu Profil --}}
  <div class="col-md-4 col-lg-3">
    <div class="card card-widget widget-user-2 shadow-sm">
      <div class="widget-user-header bg-gradient-info">
        <div class="widget-user-image">
          <img class="img-circle bg-white elevation-2"
               src="{{ asset('dist/img/user7-128x128.png') }}"
               alt="Avatar" style="width:65px;height:65px;object-fit:cover">
        </div>
        <h3 class="widget-user-username text-truncate" style="max-width:170px">{{ $siswa->nama }}</h3>
        <h5 class="widget-user-desc">NISN: {{ $siswa->nisn ?? '-' }}</h5>
      </div>
      <div class="card-footer p-0">
        <ul class="nav flex-column">
          <li class="nav-item">
            <span class="nav-link d-flex justify-content-between align-items-center">
              <span><i class="fas fa-chalkboard text-primary mr-2"></i>Kelas</span>
              <span class="badge bg-primary">{{ $siswa->kelas?->nama_kelas ?? '-' }}</span>
            </span>
          </li>
          <li class="nav-item">
            <span class="nav-link d-flex justify-content-between align-items-center">
              <span><i class="fas fa-graduation-cap text-info mr-2"></i>Jurusan</span>
              <span class="badge bg-info" style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ $siswa->jurusan?->nama_jurusan ?? '-' }}
              </span>
            </span>
          </li>
          <li class="nav-item">
            <span class="nav-link d-flex justify-content-between align-items-center">
              <span><i class="fas fa-birthday-cake text-warning mr-2"></i>Lahir</span>
              <span class="text-muted small">
                {{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->isoFormat('D MMM YYYY') : '-' }}
              </span>
            </span>
          </li>
          <li class="nav-item">
            <span class="nav-link d-flex justify-content-between align-items-center">
              <span><i class="fas fa-book text-success mr-2"></i>Mata Pelajaran</span>
              <span class="badge bg-success">{{ $jumlahMapel }}</span>
            </span>
          </li>
          @if($semester)
          <li class="nav-item">
            <span class="nav-link d-flex justify-content-between align-items-center">
              <span><i class="fas fa-calendar text-secondary mr-2"></i>Semester</span>
              <span class="text-muted small">{{ $semester->tahunAjaran?->nama ?? '-' }} / {{ $semester->semester }}</span>
            </span>
          </li>
          @endif
        </ul>
      </div>
    </div>
  </div>

  {{-- Kolom Kanan --}}
  <div class="col-md-8 col-lg-9">

    {{-- ── Stat Cards ──────────────────────────────────────── --}}
    @php
      $hadir  = $absensiRekap['Hadir'] ?? 0;
      $izin   = $absensiRekap['Izin']  ?? 0;
      $sakit  = $absensiRekap['Sakit'] ?? 0;
      $alpa   = $absensiRekap['Alpa']  ?? 0;
      $totalAbs = $hadir + $izin + $sakit + $alpa;
      $pctHadir = $totalAbs > 0 ? round($hadir / $totalAbs * 100) : 0;
      $tunggakan = $tagihanBelumLunas->count();
    @endphp

    <div class="row mb-3">
      <div class="col-6 col-lg-3">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ $pctHadir }}<sup style="font-size:18px">%</sup></h3>
            <p>Kehadiran</p>
          </div>
          <div class="icon"><i class="fas fa-check-circle"></i></div>
          <a href="{{ route('siswa.absensi') }}" class="small-box-footer">
            Detail <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{ $alpa }}</h3>
            <p>Total Alpa</p>
          </div>
          <div class="icon"><i class="fas fa-times-circle"></i></div>
          <a href="{{ route('siswa.absensi') }}" class="small-box-footer">
            Detail <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ $jumlahMapel }}</h3>
            <p>Mata Pelajaran</p>
          </div>
          <div class="icon"><i class="fas fa-book-open"></i></div>
          <a href="{{ route('siswa.nilai') }}" class="small-box-footer">
            Lihat Nilai <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="small-box {{ $tunggakan > 0 ? 'bg-danger' : 'bg-secondary' }}">
          <div class="inner">
            <h3>{{ $tunggakan }}</h3>
            <p>Tunggakan SPP</p>
          </div>
          <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
          <a href="{{ route('siswa.spp') }}" class="small-box-footer">
            Lihat SPP <i class="fas fa-arrow-circle-right"></i>
          </a>
        </div>
      </div>
    </div>

    {{-- ── Widget Ujian Tersedia ──────────────────────────────── --}}
    @if($ujianTersedia > 0)
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
      <h5><i class="icon fas fa-exclamation-triangle"></i> Ujian Tersedia!</h5>
      Kamu memiliki <strong>{{ $ujianTersedia }} ujian</strong> yang sedang berlangsung dan belum dikerjakan.
      <a href="{{ rtrim(config('services.ujian.url', ''), '/') }}/siswa/login" class="alert-link">Klik di sini untuk mengerjakan</a>.
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    @endif

    {{-- ── Jadwal Hari Ini ─────────────────────────────────── --}}
    <div class="card shadow-sm">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-calendar-day mr-2 text-primary"></i>Jadwal Hari Ini
          <span class="badge badge-primary ml-1">{{ $hariIni }}</span>
        </h3>
      </div>
      <div class="card-body p-0">
        @if($jadwalHariIni->isEmpty())
          <div class="text-center text-muted py-4">
            <i class="fas fa-coffee fa-2x mb-2 d-block"></i>
            Tidak ada jadwal hari ini.
          </div>
        @else
        <table class="table table-sm table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th width="75">Kode</th>
              <th>Mata Pelajaran</th>
              <th>Waktu</th>
              <th>Ruangan / Guru</th>
            </tr>
          </thead>
          <tbody>
            @foreach($jadwalHariIni as $j)
            <tr>
              <td><span class="badge badge-secondary">{{ $j->mapel?->kode ?? '-' }}</span></td>
              <td class="font-weight-bold">{{ $j->mapel?->nama_mapel ?? '-' }}</td>
              <td class="text-nowrap">
                <i class="far fa-clock text-muted mr-1"></i>
                {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}–{{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
              </td>
              <td>
                @if($j->ruangan)
                  <span class="badge badge-light border mr-1">{{ $j->ruangan }}</span>
                @endif
                @if($j->guru)
                  <small class="text-muted">{{ $j->guru->nama }}</small>
                @endif
                @if($j->link_zoom)
                  <a href="{{ $j->link_zoom }}" target="_blank" class="badge badge-info ml-1">
                    <i class="fas fa-video"></i> Zoom
                  </a>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
    </div>

  </div>
</div>

{{-- ── Rekap Absensi + Pengumuman ──────────────────────────────── --}}
<div class="row">

  {{-- Rekap Absensi --}}
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clipboard-list mr-2 text-success"></i>Rekap Absensi</h3>
        <div class="card-tools">
          <a href="{{ route('siswa.absensi') }}" class="btn btn-xs btn-outline-success">Detail</a>
        </div>
      </div>
      <div class="card-body">
        @if($totalAbs === 0)
          <p class="text-muted text-center mb-0">Belum ada data absensi.</p>
        @else
          @foreach([['Hadir',$hadir,'success'],['Izin',$izin,'warning'],['Sakit',$sakit,'info'],['Alpa',$alpa,'danger']] as [$label,$val,$color])
          <div class="d-flex justify-content-between mb-1">
            <span><i class="fas fa-circle text-{{ $color }} mr-1"></i>{{ $label }}</span>
            <strong>{{ $val }}x</strong>
          </div>
          <div class="progress progress-xs mb-3">
            <div class="progress-bar bg-{{ $color }}" style="width:{{ $totalAbs > 0 ? round($val/$totalAbs*100) : 0 }}%"></div>
          </div>
          @endforeach
          <hr class="mt-0 mb-2">
          <div class="d-flex justify-content-between text-muted small">
            <span>Total pertemuan</span><strong>{{ $totalAbs }}</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted small">Persentase kehadiran</span>
            <strong class="{{ $pctHadir >= 75 ? 'text-success' : 'text-danger' }}">{{ $pctHadir }}%</strong>
          </div>
        @endif
      </div>
    </div>

    {{-- Tagihan SPP --}}
    @if($tagihanBelumLunas->count() > 0)
    <div class="card card-outline card-danger shadow-sm">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exclamation-circle text-danger mr-1"></i>Tagihan Belum Lunas</h3>
        <div class="card-tools">
          <a href="{{ route('siswa.spp') }}" class="btn btn-xs btn-outline-danger">Semua</a>
        </div>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          @foreach($tagihanBelumLunas->take(4) as $tagihan)
          <li class="list-group-item py-2 d-flex justify-content-between align-items-center">
            <span class="small">{{ \Carbon\Carbon::parse($tagihan->bulan)->isoFormat('MMMM YYYY') }}</span>
            <span class="badge badge-{{ $tagihan->status === 'cicil' ? 'warning' : 'danger' }}">
              {{ $tagihan->status === 'cicil' ? 'Cicil' : 'Belum' }}
            </span>
          </li>
          @endforeach
        </ul>
        @if($tagihanBelumLunas->count() > 4)
        <div class="text-center py-2 text-muted small">
          + {{ $tagihanBelumLunas->count() - 4 }} tagihan lainnya
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>

  {{-- Pengumuman --}}
  <div class="col-md-8">
    <div class="card shadow-sm">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bullhorn mr-2 text-warning"></i>Pengumuman Terbaru</h3>
        <div class="card-tools">
          <a href="{{ route('pengumuman.index') }}" class="btn btn-xs btn-outline-warning">Semua</a>
        </div>
      </div>
      <div class="card-body p-0">
        @forelse($pengumumans as $p)
        <div class="border-bottom px-3 py-3">
          <div class="font-weight-bold mb-1">{{ $p->judul }}</div>
          <p class="text-muted small mb-1" style="line-height:1.5">
            {{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 140) }}
          </p>
          <small class="text-muted">
            <i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($p->published_at)->diffForHumans() }}
            @if($p->target_role === 2)
              <span class="badge badge-light border ml-1">Siswa</span>
            @endif
          </small>
        </div>
        @empty
        <div class="text-center text-muted py-5">
          <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
          Tidak ada pengumuman saat ini.
        </div>
        @endforelse
      </div>
    </div>
  </div>

</div>
@endsection
