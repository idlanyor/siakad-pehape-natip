@extends('layouts.admin')
@section('title', 'Data Guru')
@section('page_title', 'Data Guru')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Guru</h3>
        <a href="{{ route('admin.guru.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Guru
        </a>
      </div>
      <div class="card-body">
        <table id="tabel-guru" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama</th>
              <th>NIP</th>
              <th>Username</th>
              <th>Bidang Studi</th>
              <th>No. HP</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($gurus as $i => $g)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $g->nama }}</td>
              <td>{{ $g->nip ?? '-' }}</td>
              <td><span class="badge badge-secondary">{{ $g->user->username }}</span></td>
              <td>{{ $g->bidang_studi ?? '-' }}</td>
              <td>{{ $g->no_hp ?? '-' }}</td>
              <td>
                <a href="{{ route('admin.guru.edit', $g) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.guru.destroy', $g) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus guru {{ $g->nama }}? Akun login-nya juga akan dihapus.')">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i> Hapus</button>
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
<script>
  $(function () { $('#tabel-guru').DataTable({ responsive: true, autoWidth: false }); });
</script>
@endpush
