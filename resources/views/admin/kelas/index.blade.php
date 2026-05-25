@extends('layouts.admin')
@section('title', 'Data Kelas')
@section('page_title', 'Data Kelas')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Kelas</h3>
        <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Kelas
        </a>
      </div>
      <div class="card-body">
        <table id="tabel-kelas" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama Kelas</th>
              <th>Tingkat</th>
              <th>Jurusan</th>
              <th>Wali Kelas</th>
              <th>Jumlah Siswa</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($kelas as $i => $k)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $k->nama_kelas }}</td>
              <td><span class="badge badge-secondary">{{ $k->tingkat }}</span></td>
              <td>{{ $k->jurusan->nama_jurusan }}</td>
              <td>{{ $k->waliKelas?->nama ?? '<span class="text-muted">-</span>' }}</td>
              <td><span class="badge badge-info">{{ $k->siswas_count }}</span></td>
              <td>
                <a href="{{ route('admin.kelas.edit', $k) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.kelas.destroy', $k) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus kelas {{ $k->nama_kelas }}?')">
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
  $(function () { $('#tabel-kelas').DataTable({ responsive: true, autoWidth: false }); });
</script>
@endpush
