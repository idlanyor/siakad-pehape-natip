@extends('layouts.admin')
@section('title', 'Ekstrakurikuler')
@section('page_title', 'Ekstrakurikuler')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-users text-info mr-2"></i>Daftar Ekstrakurikuler</h3>
        <a href="{{ route('admin.ekskul.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Ekskul
        </a>
      </div>
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
          </div>
        @endif

        <table id="tabel-ekskul" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama Ekskul</th>
              <th>Guru Pembina</th>
              <th>Deskripsi</th>
              <th class="text-center">Jumlah Anggota</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($ekskuls as $i => $e)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><strong>{{ $e->nama }}</strong></td>
              <td>{{ $e->pembina?->nama ?? '<em class="text-muted">Belum ditunjuk</em>' }}</td>
              <td>{{ $e->deskripsi ? \Str::limit($e->deskripsi, 70) : '—' }}</td>
              <td class="text-center">
                <span class="badge badge-info">{{ $e->anggotas->count() }} siswa</span>
              </td>
              <td>
                <a href="{{ route('admin.ekskul.anggota', $e) }}" class="btn btn-info btn-xs">
                  <i class="fas fa-users"></i> Anggota
                </a>
                <a href="{{ route('admin.ekskul.edit', $e) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.ekskul.destroy', $e) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus ekskul {{ $e->nama }}?')">
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
  $(function () { $('#tabel-ekskul').DataTable({ responsive: true, autoWidth: false }); });
</script>
@endpush
