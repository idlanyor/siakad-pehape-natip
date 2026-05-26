@extends('layouts.admin')
@section('title', 'Tagihan SPP')
@section('page_title', 'Daftar Tagihan SPP')

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@endpush

@section('content')
{{-- Summary Cards --}}
<div class="row">
  <div class="col-md-3">
    <div class="small-box bg-info">
      <div class="inner"><h3>{{ $tagihans->count() }}</h3><p>Total Tagihan</p></div>
      <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="small-box bg-success">
      <div class="inner"><h3>{{ $summary['jumlah_lunas'] }}</h3><p>Lunas</p></div>
      <div class="icon"><i class="fas fa-check-circle"></i></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="small-box bg-warning">
      <div class="inner"><h3>{{ $summary['jumlah_cicil'] }}</h3><p>Cicilan</p></div>
      <div class="icon"><i class="fas fa-clock"></i></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="small-box bg-danger">
      <div class="inner"><h3>{{ $summary['jumlah_belum'] }}</h3><p>Belum Bayar</p></div>
      <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-file-invoice-dollar text-primary mr-2"></i>Tagihan SPP</h3>
    <div>
      <a href="{{ route('admin.keuangan.tagihan.export', request()->query()) }}" class="btn btn-success btn-sm mr-1">
        <i class="fas fa-file-excel mr-1"></i> Export Excel
      </a>
      <a href="{{ route('admin.keuangan.tagihan.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Generate Tagihan
      </a>
    </div>
  </div>
  <div class="card-body">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="mb-3">
      <div class="row">
        <div class="col-md-3">
          <div class="form-group mb-0">
            <label class="small">Bulan</label>
            <input type="month" name="bulan" class="form-control form-control-sm" value="{{ request('bulan') }}">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group mb-0">
            <label class="small">Kelas</label>
            <select name="kelas_id" class="form-control form-control-sm">
              <option value="">-- Semua Kelas --</option>
              @foreach($kelas as $k)
                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group mb-0">
            <label class="small">Status</label>
            <select name="status" class="form-control form-control-sm">
              <option value="">-- Semua Status --</option>
              @foreach($statusList as $key => $label)
                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary btn-sm mr-2">Filter</button>
          <a href="{{ route('admin.keuangan.tagihan.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </div>
    </form>

    <table id="tabel-tagihan" class="table table-bordered table-striped table-sm">
      <thead>
        <tr>
          <th>#</th>
          <th>Siswa</th>
          <th>Kelas</th>
          <th>Bulan</th>
          <th class="text-right">Nominal</th>
          <th class="text-center">Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($tagihans as $i => $t)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td><strong>{{ $t->siswa->nama }}</strong></td>
          <td>{{ $t->siswa->kelas?->nama_kelas ?? '-' }}</td>
          <td>{{ $t->bulan_label }}</td>
          <td class="text-right">{{ $t->nominal_rp }}</td>
          <td class="text-center">
            <span class="badge badge-{{ $t->status_color }}">{{ $t->status_label }}</span>
          </td>
          <td>
            <a href="{{ route('admin.keuangan.tagihan.show', $t) }}" class="btn btn-info btn-xs">
              <i class="fas fa-eye"></i> Detail
            </a>
            @if(!$t->pembayarans()->exists())
            <form action="{{ route('admin.keuangan.tagihan.destroy', $t) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Hapus tagihan ini?')">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
            </form>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>$(function () { $('#tabel-tagihan').DataTable({ responsive: true, autoWidth: false, pageLength: 25, order: [[3, 'desc']] }); });</script>
@endpush
