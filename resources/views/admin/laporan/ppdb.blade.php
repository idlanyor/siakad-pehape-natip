@extends('layouts.admin')
@section('title', 'Laporan PPDB')
@section('page_title', 'Laporan PPDB' . ($gelombang ? ' — ' . $gelombang->nama : ''))

@section('content')
<div class="row">
  <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Total</span><span class="info-box-number">{{ $summary['total'] }}</span></div></div></div>
  <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span><div class="info-box-content"><span class="info-box-text">Pending</span><span class="info-box-number">{{ $summary['pending'] }}</span></div></div></div>
  <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-check"></i></span><div class="info-box-content"><span class="info-box-text">Diterima</span><span class="info-box-number">{{ $summary['diterima'] }}</span></div></div></div>
  <div class="col-md-3 col-6"><div class="info-box"><span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span><div class="info-box-content"><span class="info-box-text">Ditolak</span><span class="info-box-number">{{ $summary['ditolak'] }}</span></div></div></div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Daftar Pendaftar</h3>
    <div class="float-right">
      <a href="{{ route('admin.laporan.ppdb', ['gelombang_id' => $gelombang?->id, 'export' => 'pdf']) }}" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
    </div>
  </div>
  <div class="card-body table-responsive p-0">
    <table class="table table-bordered table-hover">
      <thead><tr><th>#</th><th>No. Daftar</th><th>Nama</th><th>Asal Sekolah</th><th>Pilihan 1</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($rows as $i => $r)
        <tr>
          <td>{{ $i + 1 }}</td><td>{{ $r->no_pendaftaran }}</td><td>{{ $r->nama }}</td>
          <td>{{ $r->asal_sekolah }}</td><td>{{ $r->jurusanPilihan->nama_jurusan ?? '-' }}</td>
          <td><span class="badge badge-{{ $r->status_color }}">{{ $r->status_label }}</span></td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada pendaftar.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
