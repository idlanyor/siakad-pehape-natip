@extends('layouts.admin')
@section('title', 'Penempatan PKL')
@section('page_title', 'Penempatan Siswa PKL')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Penempatan PKL</h3>
        <a href="{{ route('admin.pkl-penempatan.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Penempatan
        </a>
      </div>
      <div class="card-body">
        <table id="tabel-penempatan" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Siswa</th>
              <th>Tempat PKL</th>
              <th>Guru Pembimbing</th>
              <th>Periode</th>
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
                <small class="text-muted">{{ $p->siswa->nisn }} | {{ $p->siswa->kelas?->nama_kelas }}</small>
              </td>
              <td>{{ $p->tempatPkl->nama }}</td>
              <td>{{ $p->guruPembimbing?->nama ?? '-' }}</td>
              <td>
                @if($p->periode_mulai)
                  {{ $p->periode_mulai->format('d/m/Y') }} –
                  {{ $p->periode_selesai?->format('d/m/Y') ?? '...' }}
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td>
                <span class="badge badge-{{ $p->status === 'aktif' ? 'success' : 'secondary' }}">
                  {{ ucfirst($p->status) }}
                </span>
              </td>
              <td>
                <a href="{{ route('admin.pkl.detail', $p) }}" class="btn btn-info btn-xs">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('admin.pkl-penempatan.edit', $p) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('admin.pkl-penempatan.destroy', $p) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus penempatan ini?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                </form>
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
<script>$(function(){ $('#tabel-penempatan').DataTable({ responsive:true, autoWidth:false }); });</script>
@endpush
