@extends('layouts.admin')
@section('title', 'Pelanggaran Siswa')
@section('page_title', 'Pelanggaran Siswa')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Data Pelanggaran Siswa</h3>
        <a href="{{ route('admin.pelanggaran.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Pelanggaran
        </a>
      </div>
      <div class="card-body">

        {{-- Filter Siswa --}}
        <form method="GET" class="mb-3 form-inline">
          <label class="mr-2">Filter Siswa:</label>
          <select name="siswa_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
            <option value="">-- Semua Siswa --</option>
            @foreach($siswas as $s)
              <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>
                {{ $s->nama }}
              </option>
            @endforeach
          </select>
          @if(request('siswa_id'))
            <a href="{{ route('admin.pelanggaran.index') }}" class="btn btn-secondary btn-sm">Reset</a>
          @endif
        </form>

        <table id="tabel-pelanggaran" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Tanggal</th>
              <th>Nama Siswa</th>
              <th>Kelas</th>
              <th>Jenis Pelanggaran</th>
              <th>Deskripsi</th>
              <th class="text-center">Poin</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pelanggarans as $i => $p)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
              <td><strong>{{ $p->siswa->nama }}</strong></td>
              <td>{{ $p->siswa->kelas?->nama_kelas ?? '-' }}</td>
              <td>{{ $p->jenis }}</td>
              <td>{{ $p->deskripsi ? \Str::limit($p->deskripsi, 60) : '-' }}</td>
              <td class="text-center">
                <span class="badge badge-{{ $p->poin >= 50 ? 'danger' : ($p->poin >= 25 ? 'warning' : 'info') }}">
                  {{ $p->poin }}
                </span>
              </td>
              <td>
                <a href="{{ route('admin.pelanggaran.edit', $p) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.pelanggaran.destroy', $p) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus pelanggaran ini?')">
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
  $(function () { $('#tabel-pelanggaran').DataTable({ responsive: true, autoWidth: false, order: [[1, 'desc']] }); });
</script>
@endpush
