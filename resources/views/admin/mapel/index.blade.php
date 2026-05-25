@extends('layouts.admin')
@section('title', 'Data Mata Pelajaran')
@section('page_title', 'Data Mata Pelajaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Mata Pelajaran</h3>
        <a href="{{ route('admin.mapel.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Mapel
        </a>
      </div>
      <div class="card-body">
        <table id="tabel-mapel" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Kode</th>
              <th>Nama Mata Pelajaran</th>
              <th>Guru Pengampu</th>
              <th>Jadwal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($matkuls as $i => $m)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><span class="badge badge-secondary">{{ $m->kode }}</span></td>
              <td>{{ $m->nama_matkul }}</td>
              <td>{{ $m->dosen_pengampu }}</td>
              <td>
                @foreach($m->jadwals as $j)
                  <span class="badge badge-info">{{ $j->hari }}, {{ $j->waktu }}</span>
                @endforeach
              </td>
              <td>
                <a href="{{ route('admin.mapel.edit', $m) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.mapel.destroy', $m) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus mata pelajaran ini?')">
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
  $(function () { $('#tabel-mapel').DataTable({ responsive: true, autoWidth: false }); });
</script>
@endpush
