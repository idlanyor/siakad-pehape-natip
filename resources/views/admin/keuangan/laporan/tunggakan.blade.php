@extends('layouts.admin')
@section('title', 'Laporan Tunggakan SPP')
@section('page_title', 'Laporan Tunggakan SPP')

@section('content')

{{-- Filter --}}
<div class="card card-outline card-warning">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter Tunggakan</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('admin.keuangan.tunggakan') }}" class="form-inline flex-wrap gap-2">
      <div class="form-group mr-3 mb-2">
        <label class="mr-2">Bulan</label>
        <input type="month" name="bulan" class="form-control" value="{{ $bulanFilter }}">
      </div>
      <div class="form-group mr-3 mb-2">
        <label class="mr-2">Kelas</label>
        <select name="kelas_id" class="form-control">
          <option value="">Semua Kelas</option>
          @foreach($kelas as $k)
            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
              {{ $k->nama_kelas }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="form-group mr-3 mb-2">
        <label class="mr-2">Status</label>
        <select name="status" class="form-control">
          <option value="">Semua</option>
          @foreach($statusList as $key => $label)
            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-2">
        <button type="submit" class="btn btn-warning mr-2">
          <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('admin.keuangan.laporan', ['bulan' => $bulanFilter]) }}" class="btn btn-secondary">
          <i class="fas fa-chart-bar mr-1"></i> Kembali ke Laporan
        </a>
      </div>
    </form>
  </div>
</div>

{{-- Tabel Tunggakan --}}
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
      Daftar Tunggakan —
      <span class="text-muted">{{ \Carbon\Carbon::createFromFormat('Y-m', $bulanFilter)->translatedFormat('F Y') }}</span>
      <span class="badge badge-warning ml-2">{{ $tagihans->count() }} siswa</span>
    </h3>
  </div>
  <div class="card-body p-0">
    @if($tagihans->isEmpty())
      <div class="text-center py-4 text-success">
        <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
        Tidak ada tunggakan pada periode ini.
      </div>
    @else
      <table class="table table-sm table-hover table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th>#</th>
            <th>Siswa</th>
            <th>Kelas</th>
            <th class="text-right">Nominal</th>
            <th class="text-right">Sudah Dibayar</th>
            <th class="text-right">Sisa</th>
            <th class="text-center">Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($tagihans as $i => $t)
          <tr>
            <td class="text-muted">{{ $i + 1 }}</td>
            <td>
              <strong>{{ $t->siswa->nama }}</strong>
            </td>
            <td>{{ $t->siswa->kelas?->nama_kelas ?? '-' }}</td>
            <td class="text-right">Rp {{ number_format($t->nominal, 0, ',', '.') }}</td>
            <td class="text-right text-success">Rp {{ number_format($t->total_dibayar, 0, ',', '.') }}</td>
            <td class="text-right font-weight-bold text-danger">Rp {{ number_format($t->sisa_tagihan, 0, ',', '.') }}</td>
            <td class="text-center">
              <span class="badge badge-{{ $t->status_color }}">{{ $t->status_label }}</span>
            </td>
            <td>
              <a href="{{ route('admin.keuangan.tagihan.show', $t) }}" class="btn btn-xs btn-primary">
                <i class="fas fa-eye"></i>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="thead-light">
          <tr>
            <th colspan="3">Total</th>
            <th class="text-right">Rp {{ number_format($tagihans->sum('nominal'), 0, ',', '.') }}</th>
            <th class="text-right text-success">Rp {{ number_format($tagihans->sum(fn($t) => $t->total_dibayar), 0, ',', '.') }}</th>
            <th class="text-right text-danger">Rp {{ number_format($tagihans->sum(fn($t) => $t->sisa_tagihan), 0, ',', '.') }}</th>
            <th colspan="2"></th>
          </tr>
        </tfoot>
      </table>
    @endif
  </div>
</div>

@endsection
