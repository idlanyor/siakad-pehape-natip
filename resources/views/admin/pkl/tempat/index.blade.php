@extends('layouts.admin')
@section('title', 'Tempat PKL')
@section('page_title', 'Data Tempat PKL')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Tempat PKL</h3>
        <a href="{{ route('admin.pkl-tempat.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Tempat PKL
        </a>
      </div>
      <div class="card-body">
        <table id="tabel-tempat" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama Tempat / Instansi</th>
              <th>Bidang Usaha</th>
              <th>Kontak</th>
              <th>Kuota</th>
              <th>Siswa Aktif</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($tempats as $i => $t)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                <strong>{{ $t->nama }}</strong><br>
                <small class="text-muted">{{ $t->alamat }}</small>
              </td>
              <td>{{ $t->bidang_usaha ?? '-' }}</td>
              <td>
                {{ $t->kontak_person ?? '-' }}<br>
                <small>{{ $t->no_hp ?? '' }}</small>
              </td>
              <td class="text-center">{{ $t->kuota }}</td>
              <td class="text-center">
                <span class="badge badge-{{ $t->penempatans_count > 0 ? 'info' : 'secondary' }}">
                  {{ $t->penempatans_count }}
                </span>
              </td>
              <td>
                <a href="{{ route('admin.pkl-tempat.edit', $t) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('admin.pkl-tempat.destroy', $t) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus tempat PKL ini?')">
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
<script>$(function(){ $('#tabel-tempat').DataTable({ responsive:true, autoWidth:false }); });</script>
@endpush
