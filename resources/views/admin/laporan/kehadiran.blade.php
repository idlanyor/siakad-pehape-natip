@extends('layouts.admin')
@section('title', 'Laporan Kehadiran')
@section('page_title', 'Laporan Kehadiran — ' . $kelas->nama_kelas)

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Kehadiran Kelas {{ $kelas->nama_kelas }} ({{ $kelas->jurusan->nama_jurusan ?? '-' }})</h3>
    <div class="float-right">
      <a href="{{ route('admin.laporan.kehadiran', ['kelas_id' => $kelas->id, 'export' => 'pdf']) }}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
    </div>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-bordered table-hover">
      <thead><tr><th>#</th><th>Nama</th><th>NISN</th><th class="text-center">Hadir</th><th class="text-center">Izin</th><th class="text-center">Sakit</th><th class="text-center">Alpa</th></tr></thead>
      <tbody>
        @forelse($rows as $i => $r)
        <tr>
          <td>{{ $i + 1 }}</td><td>{{ $r['nama'] }}</td><td>{{ $r['nisn'] }}</td>
          <td class="text-center text-success">{{ $r['hadir'] }}</td>
          <td class="text-center">{{ $r['izin'] }}</td>
          <td class="text-center">{{ $r['sakit'] }}</td>
          <td class="text-center text-danger">{{ $r['alpa'] }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada siswa.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
