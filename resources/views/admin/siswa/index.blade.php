@extends('layouts.admin')
@section('title', 'Data Siswa')
@section('page_title', 'Data Siswa')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Siswa</h3>
        <a href="{{ route('admin.siswa.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Siswa
        </a>
      </div>
      <div class="card-body">
        <table id="tabel-siswa" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama</th>
              <th>NISN</th>
              <th>Username</th>
              <th>Jurusan</th>
              <th>Kelas</th>
              <th>Tgl Lahir</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($siswas as $i => $s)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $s->nama }}</td>
              <td>{{ $s->nisn }}</td>
              <td><span class="badge badge-secondary">{{ $s->user->username }}</span></td>
              <td>{{ $s->jurusan->nama_jurusan }}</td>
              <td>{{ $s->kelas?->nama_kelas ?? '-' }}</td>
              <td>{{ \Carbon\Carbon::parse($s->tanggal_lahir)->format('d/m/Y') }}</td>
              <td>
                <a href="{{ route('admin.siswa.edit', $s) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.siswa.destroy', $s) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus siswa {{ $s->nama }}? Akun login-nya juga akan dihapus.')">
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
  $(function () { $('#tabel-siswa').DataTable({ responsive: true, autoWidth: false }); });
</script>
@endpush
