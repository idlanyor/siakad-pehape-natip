@extends('layouts.admin')
@section('title', 'Laporan')
@section('page_title', 'Pusat Laporan')

@section('content')
<div class="row">
  {{-- Kehadiran --}}
  <div class="col-md-6">
    <div class="card card-primary card-outline">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-check mr-1"></i> Laporan Kehadiran</h3></div>
      <form action="{{ route('admin.laporan.kehadiran') }}" method="GET" target="_blank">
        <div class="card-body">
          <div class="form-group">
            <label>Kelas</label>
            <select name="kelas_id" class="form-control" required>
              <option value="">-- Pilih Kelas --</option>
              @foreach($kelasList as $k)<option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>@endforeach
            </select>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary"><i class="fas fa-eye mr-1"></i> Tampilkan</button>
          <button type="submit" name="export" value="pdf" class="btn btn-danger"><i class="fas fa-file-pdf mr-1"></i> PDF</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Nilai --}}
  <div class="col-md-6">
    <div class="card card-success card-outline">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-star-half-alt mr-1"></i> Laporan Nilai</h3></div>
      <form action="{{ route('admin.laporan.nilai') }}" method="GET" target="_blank">
        <div class="card-body">
          <div class="form-group">
            <label>Kelas</label>
            <select name="kelas_id" class="form-control" required>
              <option value="">-- Pilih Kelas --</option>
              @foreach($kelasList as $k)<option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>@endforeach
            </select>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary"><i class="fas fa-eye mr-1"></i> Tampilkan</button>
          <button type="submit" name="export" value="pdf" class="btn btn-danger"><i class="fas fa-file-pdf mr-1"></i> PDF</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Keuangan --}}
  <div class="col-md-6">
    <div class="card card-warning card-outline">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-money-bill-wave mr-1"></i> Laporan Keuangan SPP</h3></div>
      <form action="{{ route('admin.laporan.keuangan') }}" method="GET" target="_blank">
        <div class="card-body">
          <div class="form-group">
            <label>Bulan</label>
            <input type="month" name="bulan" value="{{ now()->format('Y-m') }}" class="form-control" required>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary"><i class="fas fa-eye mr-1"></i> Tampilkan</button>
          <button type="submit" name="export" value="pdf" class="btn btn-danger"><i class="fas fa-file-pdf mr-1"></i> PDF</button>
        </div>
      </form>
    </div>
  </div>

  {{-- PPDB --}}
  <div class="col-md-6">
    <div class="card card-info card-outline">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> Laporan PPDB</h3></div>
      <form action="{{ route('admin.laporan.ppdb') }}" method="GET" target="_blank">
        <div class="card-body">
          <div class="form-group">
            <label>Gelombang</label>
            <select name="gelombang_id" class="form-control">
              <option value="">Semua Gelombang</option>
              @foreach($gelombangList as $g)<option value="{{ $g->id }}">{{ $g->nama }}</option>@endforeach
            </select>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary"><i class="fas fa-eye mr-1"></i> Tampilkan</button>
          <button type="submit" name="export" value="pdf" class="btn btn-danger"><i class="fas fa-file-pdf mr-1"></i> PDF</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
