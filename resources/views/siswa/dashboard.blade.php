@extends('layouts.admin')

@section('title', 'Dashboard Siswa')
@section('page_title', 'Dashboard')

@section('content')
<div class="row pt-3">
  <div class="col-md-4">
    <!-- Widget: user widget style 2 -->
    <div class="card card-widget widget-user-2">
      <!-- Add the bg color to the header using any of the bg-* classes -->
      <div class="widget-user-header bg-info">
        <div class="widget-user-image">
          <img class="img-circle bg-white img-thumbnail elevation-2" src="{{ asset('dist/img/user7-128x128.png') }}" alt="User Avatar">
        </div>
        <!-- /.widget-user-image -->
        <h3 class="widget-user-username">{{ $siswa->nama }}</h3>
        <h5 class="widget-user-desc">{{ $siswa->nisn }}</h5>
      </div>
      <div class="card-footer p-0">
        <ul class="nav flex-column">
          <li class="nav-item">
            <a href="#" class="nav-link">
              Kelas <span class="float-right badge bg-primary">{{ $siswa->kelas?->nama_kelas ?? '-' }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              Jurusan <span class="float-right badge bg-info">{{ $siswa->jurusan->nama_jurusan }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              Tanggal Lahir <span class="float-right badge bg-success">{{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('d M Y') }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              Jumlah Mata Pelajaran <span class="float-right badge bg-danger">10</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <!-- /.widget-user -->
  </div>
  <!-- /.col -->
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title text-bold">Jadwal hari ini</h3>
      </div>
      <!-- /.card-header -->
      <div class="card-body p-0">
        <table class="table table-striped">
          <thead>
            <tr>
              <th style="width: 10px">Kode</th>
              <th>Mata Pelajaran</th>
              <th>Waktu</th>
              <th>#</th>
            </tr>
          </thead>
          <tbody>
            @forelse($jadwalHariIni as $j)
            <tr>
              <td>{{ $j->mapel->kode ?? '-' }}</td>
              <td>{{ $j->mapel->nama_mapel }}</td>
              <td>{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</td>
              <td>
                @if($j->ruangan)<span class="badge bg-secondary">{{ $j->ruangan }}</span>@endif
                @if($j->link_zoom)<a href="{{ $j->link_zoom }}" target="_blank" class="badge bg-info ml-1"><i class="fas fa-video"></i> Zoom</a>@endif
              </td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted">Tidak ada jadwal hari ini.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <!-- /.card-body -->
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-12 mt-3">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h2 class="card-title text-bold">Pengumuman</h2>
      </div>
      <div class="card-body">
        <div class="list-group">
          <div class="list-group-item list-group-item-action">
            Selamat menempuh Ujian Akhir Semester Genap 2022/2023...
          </div>
          {{-- Rest of announcements --}}
        </div>
      </div>
    </div><!-- /.card -->
  </div>
</div>
@endsection
