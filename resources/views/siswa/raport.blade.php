@extends('layouts.admin')
@section('title', 'Raport Saya')
@section('page_title', 'Raport Semester')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card" id="raport-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Raport Semester</h3>
        <button class="btn btn-secondary btn-sm" onclick="window.print()">
          <i class="fas fa-print"></i> Cetak
        </button>
      </div>
      <div class="card-body">
        {{-- Identitas --}}
        <div class="row mb-4">
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr><td style="width:140px"><strong>Nama Siswa</strong></td><td>: {{ $siswa->nama }}</td></tr>
              <tr><td><strong>NISN</strong></td><td>: {{ $siswa->nisn }}</td></tr>
              <tr><td><strong>Kelas</strong></td><td>: {{ $siswa->kelas?->nama_kelas }}</td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr><td style="width:140px"><strong>Jurusan</strong></td><td>: {{ $siswa->jurusan?->nama_jurusan }}</td></tr>
              <tr><td><strong>Semester</strong></td><td>: {{ $semester?->semester ?? '-' }} / {{ $semester?->tahunAjaran->nama ?? '-' }}</td></tr>
            </table>
          </div>
        </div>

        {{-- Tabel Nilai --}}
        <table class="table table-bordered table-sm">
          <thead class="thead-light">
            <tr>
              <th class="text-center" style="width:40px">#</th>
              <th>Mata Pelajaran</th>
              <th class="text-center">Pengetahuan</th>
              <th class="text-center">Keterampilan</th>
              <th class="text-center">Nilai Akhir</th>
              <th class="text-center">Predikat</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            @php $totalNilai = 0; $countNilai = 0; @endphp
            @foreach($jadwals as $i => $j)
            @php
              $n = $nilais[$j->id] ?? null;
              if ($n?->nilai_akhir) { $totalNilai += $n->nilai_akhir; $countNilai++; }
            @endphp
            <tr>
              <td class="text-center">{{ $i + 1 }}</td>
              <td>{{ $j->mapel->nama_mapel }}</td>
              <td class="text-center">{{ $n?->rata_pengetahuan ?? '-' }}</td>
              <td class="text-center">{{ $n?->rata_keterampilan ?? '-' }}</td>
              <td class="text-center"><strong>{{ $n?->nilai_akhir ?? '-' }}</strong></td>
              <td class="text-center">
                @if($n)
                  <span class="badge badge-{{ $n->predikat_color }}">{{ $n->predikat }}</span>
                @else - @endif
              </td>
              <td>{{ $n?->catatan ?? '' }}</td>
            </tr>
            @endforeach
          </tbody>
          @if($countNilai > 0)
          <tfoot>
            <tr class="font-weight-bold">
              <td colspan="4" class="text-right">Rata-rata</td>
              <td class="text-center">{{ round($totalNilai / $countNilai, 2) }}</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
@@media print {
  .main-sidebar, .main-header, .main-footer, .card-header .btn, .content-header { display: none !important; }
  .content-wrapper { margin-left: 0 !important; }
  #raport-card { border: none !important; box-shadow: none !important; }
}
</style>
@endpush
