@extends('layouts.admin')
@section('title', 'Overview PKL')
@section('page_title', 'PKL — Overview')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
{{-- Stats --}}
<div class="row">
  <div class="col-md-4">
    <div class="info-box bg-info">
      <span class="info-box-icon"><i class="fas fa-users"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Penempatan</span>
        <span class="info-box-number">{{ $stats['total'] }}</span>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="info-box bg-success">
      <span class="info-box-icon"><i class="fas fa-briefcase"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Sedang PKL</span>
        <span class="info-box-number">{{ $stats['aktif'] }}</span>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="info-box bg-secondary">
      <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Selesai PKL</span>
        <span class="info-box-number">{{ $stats['selesai'] }}</span>
      </div>
    </div>
  </div>
</div>

{{-- Tabel Penempatan --}}
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          Daftar Penempatan
          @if($semester)
            <small class="text-muted">— Sem {{ $semester->semester }} / {{ $semester->tahunAjaran->nama }}</small>
          @endif
        </h3>
        <a href="{{ route('admin.pkl-penempatan.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah
        </a>
      </div>
      <div class="card-body">
        <table id="tabel-pkl" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Siswa</th>
              <th>Tempat PKL</th>
              <th>Guru Pembimbing</th>
              <th>Jurnal</th>
              <th>Absensi</th>
              <th>Nilai</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($penempatans as $i => $p)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                <strong>{{ $p->siswa->nama }}</strong><br>
                <small class="text-muted">{{ $p->siswa->kelas?->nama_kelas }}</small>
              </td>
              <td>{{ $p->tempatPkl->nama }}</td>
              <td>{{ $p->guruPembimbing?->nama ?? '-' }}</td>
              <td class="text-center">
                <span class="badge badge-info">{{ $p->jurnals_count ?? 0 }}</span>
              </td>
              <td class="text-center">
                <span class="badge badge-info">{{ $p->absensis_count ?? 0 }}</span>
              </td>
              <td class="text-center">
                @if($p->nilai)
                  <span class="badge badge-{{ $p->nilai->predikat_color }}">{{ $p->nilai->predikat }}</span>
                @else
                  <span class="badge badge-secondary">-</span>
                @endif
              </td>
              <td>
                <span class="badge badge-{{ $p->status === 'aktif' ? 'success' : 'secondary' }}">
                  {{ ucfirst($p->status) }}
                </span>
              </td>
              <td>
                <a href="{{ route('admin.pkl.detail', $p) }}" class="btn btn-info btn-sm">
                  <i class="fas fa-eye"></i> Detail
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>$(function(){ $('#tabel-pkl').DataTable({ responsive:true, autoWidth:false }); });</script>
@endpush
