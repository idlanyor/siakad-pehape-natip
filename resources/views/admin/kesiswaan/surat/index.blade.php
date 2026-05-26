@extends('layouts.admin')
@section('title', 'Surat Keterangan')
@section('page_title', 'Surat Keterangan Siswa')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-file-alt text-primary mr-2"></i>Log Surat Keterangan</h3>
        <a href="{{ route('admin.surat.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Buat Surat Baru
        </a>
      </div>
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
          </div>
        @endif

        <form method="GET" class="mb-3 form-inline">
          <label class="mr-2">Filter Jenis:</label>
          <select name="jenis_surat" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
            <option value="">-- Semua Jenis --</option>
            <option value="aktif"  {{ request('jenis_surat') == 'aktif'  ? 'selected' : '' }}>Surat Keterangan Aktif</option>
            <option value="pindah" {{ request('jenis_surat') == 'pindah' ? 'selected' : '' }}>Surat Keterangan Pindah</option>
            <option value="lulus"  {{ request('jenis_surat') == 'lulus'  ? 'selected' : '' }}>Surat Keterangan Lulus</option>
          </select>
          @if(request('jenis_surat'))
            <a href="{{ route('admin.surat.index') }}" class="btn btn-secondary btn-sm">Reset</a>
          @endif
        </form>

        <table id="tabel-surat" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>No. Surat</th>
              <th>Tanggal</th>
              <th>Nama Siswa</th>
              <th>Jenis Surat</th>
              <th>Dibuat Oleh</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($surats as $i => $s)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><code>{{ $s->nomor_surat }}</code></td>
              <td>{{ $s->tanggal_surat->format('d/m/Y') }}</td>
              <td><strong>{{ $s->siswa->nama }}</strong></td>
              <td>
                @php
                  $color = match($s->jenis_surat) { 'aktif' => 'success', 'lulus' => 'primary', 'pindah' => 'warning', default => 'secondary' };
                @endphp
                <span class="badge badge-{{ $color }}">{{ $s->jenis_label }}</span>
              </td>
              <td>{{ $s->pencatat?->name ?? '—' }}</td>
              <td>
                <a href="{{ route('admin.surat.print', $s) }}" class="btn btn-success btn-xs" target="_blank">
                  <i class="fas fa-print"></i> Cetak
                </a>
                <a href="{{ route('admin.surat.edit', $s) }}" class="btn btn-warning btn-xs">
                  <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.surat.destroy', $s) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus data surat ini?')">
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
  $(function () { $('#tabel-surat').DataTable({ responsive: true, autoWidth: false, order: [[2, 'desc']] }); });
</script>
@endpush
