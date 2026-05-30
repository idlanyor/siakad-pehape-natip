@extends('layouts.admin')
@section('title', 'Kesiswaan Anak')
@section('page_title', 'Kesiswaan Anak')

@section('content')
@include('orangtua.partials.anak-switcher')

@if(! $anak)
  <div class="alert alert-warning">Belum ada data anak.</div>
@else
<div class="row">
  <div class="col-md-6">
    <div class="card card-warning">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> Pelanggaran</h3>
        <span class="float-right badge badge-light">Total Poin: {{ $totalPoin }}</span>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-sm">
          <thead><tr><th>Tanggal</th><th>Jenis</th><th class="text-center">Poin</th></tr></thead>
          <tbody>
            @forelse($pelanggarans as $p)
              <tr>
                <td>{{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') : '—' }}</td>
                <td>{{ $p->jenis }}<br><small class="text-muted">{{ $p->deskripsi }}</small></td>
                <td class="text-center"><span class="badge badge-danger">{{ $p->poin }}</span></td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada pelanggaran. 👍</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card card-success">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-trophy mr-1"></i> Prestasi</h3></div>
      <div class="card-body table-responsive p-0">
        <table class="table table-sm">
          <thead><tr><th>Tanggal</th><th>Prestasi</th><th>Tingkat</th></tr></thead>
          <tbody>
            @forelse($prestasis as $pr)
              <tr>
                <td>{{ $pr->tanggal ? \Carbon\Carbon::parse($pr->tanggal)->format('d/m/Y') : '—' }}</td>
                <td>{{ $pr->nama_prestasi }}<br><small class="text-muted">{{ $pr->keterangan }}</small></td>
                <td><span class="badge badge-info">{{ $pr->tingkat }}</span></td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted py-3">Belum ada prestasi.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endif
@endsection
