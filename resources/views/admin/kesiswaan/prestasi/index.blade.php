@extends('layouts.admin')
@section('title', 'Prestasi Siswa')
@section('page_title', 'Prestasi Siswa')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-trophy text-warning mr-2"></i>Data Prestasi Siswa</h3>
        <a href="{{ route('admin.prestasi.create') }}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i> Tambah Prestasi
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
          <label class="mr-2">Filter Siswa:</label>
          <select name="siswa_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
            <option value="">-- Semua Siswa --</option>
            @foreach($siswas as $s)
              <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
            @endforeach
          </select>
          @if(request('siswa_id'))
            <a href="{{ route('admin.prestasi.index') }}" class="btn btn-secondary btn-sm">Reset</a>
          @endif
        </form>

        <table id="tabel-prestasi" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Tanggal</th>
              <th>Nama Siswa</th>
              <th>Prestasi</th>
              <th>Tingkat</th>
              <th>Bukti</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($prestasis as $i => $p)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
              <td><strong>{{ $p->siswa->nama }}</strong></td>
              <td>{{ $p->nama_prestasi }}</td>
              <td>
                @php
                  $badgeColor = match($p->tingkat) {
                    'Internasional' => 'danger',
                    'Nasional'      => 'primary',
                    'Provinsi'      => 'info',
                    'Kabupaten/Kota'=> 'success',
                    'Kecamatan'     => 'warning',
                    default         => 'secondary',
                  };
                @endphp
                <span class="badge badge-{{ $badgeColor }}">{{ $p->tingkat }}</span>
              </td>
              <td class="text-center">
                @if($p->foto_url)
                  <a href="{{ $p->foto_url }}" target="_blank">
                    <img src="{{ $p->foto_url }}" alt="Bukti" style="height:40px;width:60px;object-fit:cover;border-radius:4px;">
                  </a>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>
                <a href="{{ route('admin.prestasi.edit', $p) }}" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i> Edit</a>
                <form action="{{ route('admin.prestasi.destroy', $p) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus prestasi ini?')">
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
  $(function () { $('#tabel-prestasi').DataTable({ responsive: true, autoWidth: false, order: [[1, 'desc']] }); });
</script>
@endpush
