@extends('layouts.admin')
@section('title', 'Raport Siswa')
@section('page_title', 'Raport Siswa')

@section('content')
<div class="row">
  {{-- Filter: Pilih Kelas + Siswa --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Pilih Siswa</h3>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('admin.raport') }}" class="form-inline">
          <div class="form-group mr-3">
            <label class="mr-2">Kelas:</label>
            <select name="kelas_id" class="form-control" id="select-kelas" onchange="this.form.submit()">
              <option value="">-- Pilih Kelas --</option>
              @foreach($kelasList as $k)
                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                  {{ $k->nama_kelas }} ({{ $k->jurusan?->nama_jurusan }})
                </option>
              @endforeach
            </select>
          </div>

          @if($siswas->isNotEmpty())
          <div class="form-group mr-3">
            <label class="mr-2">Siswa:</label>
            <select name="siswa_id" class="form-control" onchange="this.form.submit()">
              <option value="">-- Pilih Siswa --</option>
              @foreach($siswas as $s)
                <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>
                  {{ $s->nama }} ({{ $s->nisn }})
                </option>
              @endforeach
            </select>
          </div>
          @endif
        </form>
      </div>
    </div>
  </div>

  {{-- Raport --}}
  @if($siswa)
  <div class="col-12">
    <div class="card" id="raport-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Raport Semester</h3>
        <div>
          @if(request('siswa_id'))
          <a href="{{ route('admin.raport.pdf', request()->query()) }}" class="btn btn-danger btn-sm mr-1">
            <i class="fas fa-file-pdf mr-1"></i> Download PDF
          </a>
          @endif
          <button class="btn btn-secondary btn-sm" onclick="window.print()">
            <i class="fas fa-print mr-1"></i> Cetak
          </button>
        </div>
      </div>
      <div class="card-body">
        {{-- Identity --}}
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
  @endif
</div>
@endsection

@push('styles')
<style>
@@media print {
  .main-sidebar, .main-header, .main-footer, .card-header .btn, .content-header, form { display: none !important; }
  .content-wrapper { margin-left: 0 !important; }
  #raport-card { border: none !important; box-shadow: none !important; }
}
</style>
@endpush
