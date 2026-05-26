@extends('layouts.admin')
@section('title', 'Detail Nilai')
@section('page_title', 'Detail Nilai — ' . $jadwal->mapel->nama_mapel)

@push('styles')
<style>
  .table-rekap th, .table-rekap td { white-space: nowrap; text-align: center; vertical-align: middle; }
  .table-rekap th:nth-child(1), .table-rekap td:nth-child(1),
  .table-rekap th:nth-child(2), .table-rekap td:nth-child(2) {
    position: sticky; background: #fff; z-index: 1; text-align: left;
  }
  .table-rekap th:nth-child(1), .table-rekap td:nth-child(1) { left: 0; }
  .table-rekap th:nth-child(2), .table-rekap td:nth-child(2) { left: 30px; }
  .thead-light th { background: #f4f6f9 !important; }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card card-info card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">{{ $jadwal->mapel->nama_mapel }} — {{ $jadwal->kelas?->nama_kelas }}</h5>
          <small>Guru: {{ $jadwal->guru?->nama }} | Sem {{ $jadwal->semester?->semester }} / {{ $jadwal->semester?->tahunAjaran->nama }}</small>
        </div>
        <div>
          <a href="{{ route('admin.nilai.export', $jadwal) }}" class="btn btn-success btn-sm mr-1">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
          </a>
          <a href="{{ route('admin.nilai.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-sm table-rekap">
          <thead class="thead-light">
            <tr>
              <th rowspan="2" style="width:30px">#</th>
              <th rowspan="2">Nama Siswa</th>
              <th colspan="3">Pengetahuan</th>
              <th>Rata-rata P</th>
              <th colspan="3">Keterampilan</th>
              <th>Rata-rata K</th>
              <th>Nilai Akhir</th>
              <th>Predikat</th>
            </tr>
            <tr>
              <th>Tugas</th><th>PTS</th><th>PAS</th>
              <th></th>
              <th>Praktik</th><th>Proyek</th><th>Portofolio</th>
              <th></th><th></th><th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($siswas as $i => $s)
            @php $n = $nilais[$s->id] ?? null; @endphp
            <tr>
              <td>{{ $i + 1 }}</td>
              <td style="text-align:left">{{ $s->nama }}</td>
              <td>{{ $n?->tugas ?? '-' }}</td>
              <td>{{ $n?->pts ?? '-' }}</td>
              <td>{{ $n?->pas ?? '-' }}</td>
              <td><strong>{{ $n?->rata_pengetahuan ?? '-' }}</strong></td>
              <td>{{ $n?->praktik ?? '-' }}</td>
              <td>{{ $n?->proyek ?? '-' }}</td>
              <td>{{ $n?->portofolio ?? '-' }}</td>
              <td><strong>{{ $n?->rata_keterampilan ?? '-' }}</strong></td>
              <td>
                @if($n?->nilai_akhir)
                  <strong>{{ $n->nilai_akhir }}</strong>
                @else - @endif
              </td>
              <td>
                @if($n)
                  <span class="badge badge-{{ $n->predikat_color }}">{{ $n->predikat }}</span>
                @else - @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        <small class="text-muted">
          <strong>Predikat:</strong>
          <span class="badge badge-success">A</span> &ge; 90 &nbsp;
          <span class="badge badge-primary">B</span> &ge; 80 &nbsp;
          <span class="badge badge-warning">C</span> &ge; 70 &nbsp;
          <span class="badge badge-danger">D</span> &lt; 70
        </small>
      </div>
    </div>
  </div>
</div>
@endsection
