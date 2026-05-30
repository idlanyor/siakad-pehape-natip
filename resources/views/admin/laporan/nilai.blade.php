@extends('layouts.admin')
@section('title', 'Laporan Nilai')
@section('page_title', 'Laporan Nilai — ' . $kelas->nama_kelas)

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Rekap Nilai Kelas {{ $kelas->nama_kelas }}</h3>
    <div class="float-right">
      <a href="{{ route('admin.laporan.nilai', ['kelas_id' => $kelas->id, 'export' => 'pdf']) }}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
    </div>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-bordered table-hover">
      <thead><tr><th>#</th><th>Nama</th><th>NISN</th><th class="text-center">Jml Mapel</th><th class="text-center">Rata-rata</th></tr></thead>
      <tbody>
        @forelse($rows as $i => $r)
        <tr>
          <td>{{ $i + 1 }}</td><td>{{ $r['nama'] }}</td><td>{{ $r['nisn'] }}</td>
          <td class="text-center">{{ $r['jml_mapel'] }}</td>
          <td class="text-center font-weight-bold">{{ $r['rata'] ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center text-muted py-3">Tidak ada siswa.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
