@extends('layouts.admin')
@section('title', 'PPDB — Daftar Pendaftar')
@section('page_title', 'PPDB — Daftar Pendaftar')

@section('content')

{{-- Stats --}}
<div class="row">
  <div class="col-sm-6 col-lg">
    <div class="small-box bg-info">
      <div class="inner"><h3>{{ $stats['total'] }}</h3><p>Total Pendaftar</p></div>
      <div class="icon"><i class="fas fa-users"></i></div>
    </div>
  </div>
  <div class="col-sm-6 col-lg">
    <div class="small-box bg-secondary">
      <div class="inner"><h3>{{ $stats['pending'] }}</h3><p>Menunggu</p></div>
      <div class="icon"><i class="fas fa-clock"></i></div>
    </div>
  </div>
  <div class="col-sm-6 col-lg">
    <div class="small-box bg-info">
      <div class="inner"><h3>{{ $stats['verifikasi'] }}</h3><p>Verifikasi</p></div>
      <div class="icon"><i class="fas fa-clipboard-check"></i></div>
    </div>
  </div>
  <div class="col-sm-6 col-lg">
    <div class="small-box bg-success">
      <div class="inner"><h3>{{ $stats['diterima'] }}</h3><p>Diterima</p></div>
      <div class="icon"><i class="fas fa-check-circle"></i></div>
    </div>
  </div>
  <div class="col-sm-6 col-lg">
    <div class="small-box bg-danger">
      <div class="inner"><h3>{{ $stats['ditolak'] }}</h3><p>Ditolak</p></div>
      <div class="icon"><i class="fas fa-times-circle"></i></div>
    </div>
  </div>
</div>

{{-- Filter --}}
<div class="card card-outline card-primary collapsed-card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter & Pencarian</h3>
    <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button></div>
  </div>
  <div class="card-body">
    <form method="GET" class="form-inline flex-wrap" style="gap:.5rem">
      <input type="text" name="q" class="form-control mr-2 mb-2" value="{{ request('q') }}" placeholder="Cari nama / no pendaftaran...">
      <select name="status" class="form-control mr-2 mb-2">
        <option value="">Semua Status</option>
        @foreach(\App\Models\PendaftarPpdb::STATUS_LABELS as $k => $v)
          <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
        @endforeach
      </select>
      <select name="jurusan_id" class="form-control mr-2 mb-2">
        <option value="">Semua Jurusan</option>
        @foreach($jurusans as $j)
          <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->nama_jurusan }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-search mr-1"></i>Filter</button>
      <a href="{{ route('admin.ppdb.index') }}" class="btn btn-secondary mb-2 ml-1">Reset</a>
    </form>
  </div>
</div>

{{-- Tabel --}}
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      Pendaftar
      <span class="badge badge-primary ml-1">{{ $pendaftars->total() }}</span>
    </h3>
    <div class="card-tools">
      <a href="{{ route('admin.ppdb.laporan') }}" class="btn btn-sm btn-info">
        <i class="fas fa-chart-bar mr-1"></i> Laporan
      </a>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover table-striped table-sm mb-0">
      <thead class="thead-light">
        <tr>
          <th>No. Pendaftaran</th>
          <th>Nama</th>
          <th>Asal Sekolah</th>
          <th>Jurusan Pilihan</th>
          <th class="text-center">Nilai</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($pendaftars as $p)
        <tr>
          <td class="font-weight-bold text-muted">{{ $p->no_pendaftaran }}</td>
          <td>
            {{ $p->nama }}
            <small class="d-block text-muted">{{ $p->jk_label }} · {{ $p->tanggal_lahir->format('d/m/Y') }}</small>
          </td>
          <td>{{ $p->asal_sekolah }}</td>
          <td>{{ $p->jurusanPilihan->nama_jurusan }}</td>
          <td class="text-center">{{ $p->nilai_rata_rata ? number_format($p->nilai_rata_rata, 2) : '—' }}</td>
          <td class="text-center">
            <span class="badge badge-{{ $p->status_color }}">{{ $p->status_label }}</span>
          </td>
          <td>
            <a href="{{ route('admin.ppdb.show', $p) }}" class="btn btn-xs btn-primary">
              <i class="fas fa-eye mr-1"></i>Review
            </a>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pendaftar.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($pendaftars->hasPages())
  <div class="card-footer">{{ $pendaftars->links() }}</div>
  @endif
</div>
@endsection
