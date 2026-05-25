@extends('layouts.admin')
@section('title', 'Data Jurusan')
@section('page_title', 'Data Jurusan')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Jurusan</h3>
        <a href="{{ route('admin.jurusan.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Jurusan
        </a>
      </div>
      <div class="card-body">
        <table id="tabel-jurusan" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama Jurusan</th>
              <th>Jumlah Siswa</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($jurusans as $i => $jurusan)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $jurusan->nama_jurusan }}</td>
              <td><span class="badge badge-info">{{ $jurusan->siswas_count }} siswa</span></td>
              <td>
                <a href="{{ route('admin.jurusan.edit', $jurusan) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.jurusan.destroy', $jurusan) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus jurusan {{ $jurusan->nama_jurusan }}?')">
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
  $(function () { $('#tabel-jurusan').DataTable({ responsive: true, autoWidth: false }); });
</script>
@endpush
