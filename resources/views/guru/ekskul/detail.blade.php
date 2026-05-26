@extends('layouts.admin')
@section('title', 'Detail Ekskul — ' . $ekskul->nama)
@section('page_title', 'Detail Ekskul: ' . $ekskul->nama)

@section('content')
<div class="row">
  <div class="col-md-4">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Info Ekskul</h3>
      </div>
      <div class="card-body">
        <p><strong>Nama:</strong> {{ $ekskul->nama }}</p>
        <p><strong>Tahun Ajaran:</strong> {{ $tahunAjaran?->nama ?? '—' }}</p>
        <p><strong>Deskripsi:</strong><br>{{ $ekskul->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
      </div>
      <div class="card-footer">
        <a href="{{ route('guru.ekskul.absensi', $ekskul) }}" class="btn btn-success btn-block">
          <i class="fas fa-clipboard-check mr-1"></i> Input Absensi
        </a>
        <a href="{{ route('guru.ekskul.rekap', $ekskul) }}" class="btn btn-secondary btn-block mt-1">
          <i class="fas fa-chart-bar mr-1"></i> Lihat Rekap
        </a>
        <a href="{{ route('guru.ekskul.index') }}" class="btn btn-link btn-block mt-1">← Kembali</a>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-users mr-2"></i>Daftar Anggota
          <span class="badge badge-info ml-2">{{ $anggotas->count() }} siswa</span>
        </h3>
      </div>
      <div class="card-body p-0">
        @if($anggotas->isEmpty())
          <div class="p-4 text-center text-muted">Belum ada anggota untuk tahun ajaran ini.</div>
        @else
          <table class="table table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>#</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jurusan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($anggotas as $i => $a)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $a->siswa->nama }}</td>
                <td>{{ $a->siswa->kelas?->nama_kelas ?? '-' }}</td>
                <td>{{ $a->siswa->jurusan?->nama_jurusan ?? '-' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
