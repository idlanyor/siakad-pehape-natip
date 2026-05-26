@extends('layouts.admin')
@section('title', 'Tarif SPP')
@section('page_title', 'Konfigurasi Tarif SPP')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-money-bill-wave text-success mr-2"></i>Tarif SPP per Kelas</h3>
        <a href="{{ route('admin.keuangan.tarif.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Tarif
        </a>
      </div>
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
        @endif

        {{-- Filter TA --}}
        <form method="GET" class="mb-3 form-inline">
          <label class="mr-2">Tahun Ajaran:</label>
          <select name="tahun_ajaran_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
            <option value="">-- Semua --</option>
            @foreach($tahunAjarans as $ta)
              <option value="{{ $ta->id }}" {{ request('tahun_ajaran_id') == $ta->id ? 'selected' : '' }}>{{ $ta->nama }}</option>
            @endforeach
          </select>
          @if(request('tahun_ajaran_id'))
            <a href="{{ route('admin.keuangan.tarif.index') }}" class="btn btn-secondary btn-sm">Reset</a>
          @endif
        </form>

        <table id="tabel-tarif" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Kelas</th>
              <th>Jurusan</th>
              <th>Tahun Ajaran</th>
              <th class="text-right">Nominal SPP</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($tarifs as $i => $t)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><strong>{{ $t->kelas->nama_kelas }}</strong></td>
              <td>{{ $t->kelas->jurusan?->nama_jurusan ?? '-' }}</td>
              <td>{{ $t->tahunAjaran->nama }}</td>
              <td class="text-right">
                <span class="font-weight-bold text-success">{{ $t->nominal_rp }}</span>
              </td>
              <td>
                <a href="{{ route('admin.keuangan.tarif.edit', $t) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.keuangan.tarif.destroy', $t) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus tarif SPP untuk kelas {{ $t->kelas->nama_kelas }}?')">
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
<script>$(function () { $('#tabel-tarif').DataTable({ responsive: true, autoWidth: false }); });</script>
@endpush
