@extends('layouts.admin')
@section('title', 'Rekap Absensi Ekskul — ' . $ekskul->nama)
@section('page_title', 'Rekap Absensi: ' . $ekskul->nama)

@push('styles')
<style>
  .rekap-table { font-size: 12px; }
  .rekap-table th, .rekap-table td { white-space: nowrap; vertical-align: middle; }
  .rekap-table .sticky-col { position: sticky; left: 0; background: #fff; z-index: 1; min-width: 150px; }
  .rekap-table thead .sticky-col { background: #f4f6f9; }
  .badge-H { background-color: #28a745; color: #fff; }
  .badge-I { background-color: #17a2b8; color: #fff; }
  .badge-S { background-color: #ffc107; color: #000; }
  .badge-A { background-color: #dc3545; color: #fff; }
</style>
@endpush

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Rekap Kehadiran Ekskul</h3>
    <a href="{{ route('guru.ekskul.absensi', $ekskul) }}" class="btn btn-success btn-sm">
      <i class="fas fa-plus"></i> Input Absensi Baru
    </a>
  </div>
  <div class="card-body">
    @if($tanggals->isEmpty())
      <div class="text-center text-muted py-4">
        <i class="fas fa-calendar-times fa-2x mb-2"></i>
        <p>Belum ada data absensi untuk ekskul ini. Mulai dengan menginput absensi pertemuan pertama.</p>
        <a href="{{ route('guru.ekskul.absensi', $ekskul) }}" class="btn btn-success btn-sm">Input Absensi</a>
      </div>
    @else
      <p class="text-muted">
        <i class="fas fa-info-circle mr-1"></i>
        <strong>{{ $tanggals->count() }}</strong> pertemuan tercatat.
        Keterangan: <span class="badge badge-H">H</span>=Hadir
        <span class="badge badge-I">I</span>=Izin
        <span class="badge badge-S">S</span>=Sakit
        <span class="badge badge-A">A</span>=Alpa
      </p>
      <div class="table-responsive">
        <table class="table table-bordered table-sm rekap-table">
          <thead class="thead-light">
            <tr>
              <th class="sticky-col">#</th>
              <th class="sticky-col" style="left:40px;min-width:180px;">Nama Siswa</th>
              @foreach($tanggals as $tgl)
                <th class="text-center">{{ \Carbon\Carbon::parse($tgl)->format('d/m') }}</th>
              @endforeach
              <th class="text-center">Hadir</th>
              <th class="text-center">%</th>
            </tr>
          </thead>
          <tbody>
            @foreach($grid as $i => $row)
            <tr>
              <td class="sticky-col">{{ $loop->iteration }}</td>
              <td class="sticky-col" style="left:40px;"><strong>{{ $row['siswa']->nama }}</strong></td>
              @foreach($tanggals as $tgl)
                @php $abs = $row['absensi']->get($tgl); @endphp
                <td class="text-center">
                  @if($abs)
                    <span class="badge badge-{{ strtoupper($abs->status[0]) }}">{{ $abs->status[0] }}</span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
              @endforeach
              <td class="text-center font-weight-bold">{{ $row['jumlah_hadir'] }}/{{ $row['total'] }}</td>
              <td class="text-center">
                @php $persen = $row['persen_hadir']; @endphp
                <span class="badge badge-{{ $persen >= 75 ? 'success' : ($persen >= 50 ? 'warning' : 'danger') }}">
                  {{ $persen }}%
                </span>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
  <div class="card-footer">
    <a href="{{ route('guru.ekskul.index') }}" class="btn btn-secondary">← Kembali</a>
  </div>
</div>
@endsection
