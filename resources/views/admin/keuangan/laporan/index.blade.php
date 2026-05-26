@extends('layouts.admin')
@section('title', 'Laporan Keuangan SPP')
@section('page_title', 'Laporan Keuangan SPP')

@section('content')

{{-- Filter --}}
<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter Laporan</h3>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('admin.keuangan.laporan') }}">
      <div class="row align-items-end">
        <div class="col-md-2">
          <div class="form-group mb-md-0">
            <label class="small">Mode</label>
            <select name="mode" id="filterMode" class="form-control form-control-sm">
              <option value="bulan" {{ ($mode ?? 'bulan') === 'bulan' ? 'selected' : '' }}>Per Bulan</option>
              <option value="range" {{ ($mode ?? '') === 'range' ? 'selected' : '' }}>Rentang Bulan</option>
              <option value="tahun" {{ ($mode ?? '') === 'tahun' ? 'selected' : '' }}>Per Tahun</option>
            </select>
          </div>
        </div>
        <div class="col-md-3 filter-field" id="f-bulan">
          <div class="form-group mb-md-0">
            <label class="small">Bulan</label>
            <input type="month" name="bulan" class="form-control form-control-sm" value="{{ $bulanFilter }}">
          </div>
        </div>
        <div class="col-md-3 filter-field d-none" id="f-bulan-mulai">
          <div class="form-group mb-md-0">
            <label class="small">Dari</label>
            <input type="month" name="bulan_mulai" class="form-control form-control-sm" value="{{ request('bulan_mulai', now()->subMonths(5)->format('Y-m')) }}">
          </div>
        </div>
        <div class="col-md-3 filter-field d-none" id="f-bulan-selesai">
          <div class="form-group mb-md-0">
            <label class="small">Sampai</label>
            <input type="month" name="bulan_selesai" class="form-control form-control-sm" value="{{ request('bulan_selesai', now()->format('Y-m')) }}">
          </div>
        </div>
        <div class="col-md-2 filter-field d-none" id="f-tahun">
          <div class="form-group mb-md-0">
            <label class="small">Tahun</label>
            <input type="number" name="tahun" class="form-control form-control-sm" value="{{ request('tahun', now()->year) }}" min="2020" max="2040">
          </div>
        </div>
        <div class="col-md-auto">
          <button type="submit" class="btn btn-primary btn-sm mr-2">
            <i class="fas fa-search mr-1"></i> Lihat
          </button>
          <a href="{{ route('admin.keuangan.tunggakan', ['bulan' => $bulanFilter]) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-exclamation-circle mr-1"></i> Tunggakan
          </a>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Summary --}}
<div class="row">
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-info"><i class="fas fa-file-invoice"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Tagihan</span>
        <span class="info-box-number">{{ $summary['total_tagihan'] }}</span>
        <span class="progress-description">Rp {{ number_format($summary['total_nominal'], 0, ',', '.') }}</span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Lunas</span>
        <span class="info-box-number">{{ $summary['jumlah_lunas'] }}</span>
        <span class="progress-description">
          Terkumpul Rp {{ number_format($summary['total_terkumpul'], 0, ',', '.') }}
        </span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Cicilan</span>
        <span class="info-box-number">{{ $summary['jumlah_cicil'] }}</span>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="info-box bg-light">
      <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Belum Bayar</span>
        <span class="info-box-number">{{ $summary['jumlah_belum'] }}</span>
        <span class="progress-description text-danger">
          Tunggakan Rp {{ number_format($summary['total_tunggakan'], 0, ',', '.') }}
        </span>
      </div>
    </div>
  </div>
</div>

{{-- Rekap per Kelas --}}
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-table mr-2"></i>Rekap per Kelas —
      <span class="text-muted">{{ $filterLabel ?? '' }}</span>
    </h3>
  </div>
  <div class="card-body p-0">
    @if($rekapKelas->isEmpty())
      <div class="text-center py-4 text-muted">
        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Tidak ada data tagihan pada bulan ini.
      </div>
    @else
      <table class="table table-sm table-hover table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th>Kelas</th>
            <th class="text-center">Total Siswa</th>
            <th class="text-center text-success">Lunas</th>
            <th class="text-center text-warning">Cicilan</th>
            <th class="text-center text-danger">Belum Bayar</th>
            <th class="text-center" style="min-width:120px">% Lunas</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rekapKelas as $r)
          <tr>
            <td>
              <strong>{{ $r['kelas']->nama_kelas }}</strong>
              <small class="text-muted d-block">{{ $r['kelas']->jurusan?->nama }}</small>
            </td>
            <td class="text-center">{{ $r['total'] }}</td>
            <td class="text-center font-weight-bold text-success">{{ $r['lunas'] }}</td>
            <td class="text-center font-weight-bold text-warning">{{ $r['cicil'] }}</td>
            <td class="text-center font-weight-bold text-danger">{{ $r['belum'] }}</td>
            <td class="text-center">
              <div class="progress progress-sm mb-1">
                <div class="progress-bar bg-success" style="width:{{ $r['persen_lunas'] }}%"></div>
              </div>
              <small>{{ $r['persen_lunas'] }}%</small>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="thead-light">
          <tr>
            <th>Total</th>
            <th class="text-center">{{ $summary['total_tagihan'] }}</th>
            <th class="text-center text-success">{{ $summary['jumlah_lunas'] }}</th>
            <th class="text-center text-warning">{{ $summary['jumlah_cicil'] }}</th>
            <th class="text-center text-danger">{{ $summary['jumlah_belum'] }}</th>
            <th class="text-center">
              @if($summary['total_tagihan'] > 0)
                {{ round(($summary['jumlah_lunas'] / $summary['total_tagihan']) * 100) }}%
              @else —
              @endif
            </th>
          </tr>
        </tfoot>
      </table>
    @endif
  </div>
</div>

@endsection

@push('scripts')
<script>
  const mode = document.getElementById('filterMode');
  function toggleFields() {
    document.querySelectorAll('.filter-field').forEach(el => el.classList.add('d-none'));
    const m = mode.value;
    if (m === 'bulan')  document.getElementById('f-bulan').classList.remove('d-none');
    if (m === 'range') {
      document.getElementById('f-bulan-mulai').classList.remove('d-none');
      document.getElementById('f-bulan-selesai').classList.remove('d-none');
    }
    if (m === 'tahun')  document.getElementById('f-tahun').classList.remove('d-none');
  }
  mode.addEventListener('change', toggleFields);
  toggleFields();
</script>
@endpush
