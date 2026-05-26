@extends('layouts.admin')
@section('title', 'Nilai Saya')
@section('page_title', 'Nilai Saya')

@section('content')
@if($semester)
<div class="alert alert-info py-2 mb-3">
  <i class="fas fa-calendar-check mr-1"></i>
  Semester {{ $semester->semester }} — {{ $semester->tahunAjaran->nama }}
  &nbsp;|&nbsp; Kelas: <strong>{{ $siswa->kelas?->nama_kelas ?? '-' }}</strong>
</div>
@endif

<div class="row">
  @forelse($jadwals as $jadwal)
  @php $n = $nilais[$jadwal->id] ?? null; @endphp
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center py-2">
        <div>
          <strong>{{ $jadwal->mapel->nama_mapel }}</strong>
          <span class="badge badge-secondary ml-1">{{ $jadwal->hari }}</span>
        </div>
        @if($n)
          <span class="badge badge-{{ $n->predikat_color }} badge-lg">{{ $n->predikat }}</span>
        @endif
      </div>
      <div class="card-body pb-2">
        @if($n)
          {{-- Nilai Akhir --}}
          <div class="mb-2">
            <div class="d-flex justify-content-between mb-1">
              <small>Nilai Akhir</small>
              <small><strong>{{ $n->nilai_akhir ?? '-' }}</strong></small>
            </div>
            @if($n->nilai_akhir)
            <div class="progress" style="height:10px">
              @php $color = $n->nilai_akhir >= 80 ? 'success' : ($n->nilai_akhir >= 70 ? 'warning' : 'danger'); @endphp
              <div class="progress-bar bg-{{ $color }}" style="width:{{ $n->nilai_akhir }}%"></div>
            </div>
            @endif
          </div>

          {{-- Pengetahuan --}}
          <div class="mb-2">
            <small class="text-muted d-block mb-1"><strong>Pengetahuan</strong> (Rata-rata: {{ $n->rata_pengetahuan ?? '-' }})</small>
            <div class="row text-center">
              <div class="col-4">
                <span class="badge badge-outline d-block py-1 border">{{ $n->tugas ?? '-' }}</span>
                <small>Tugas</small>
              </div>
              <div class="col-4">
                <span class="badge badge-outline d-block py-1 border">{{ $n->pts ?? '-' }}</span>
                <small>PTS</small>
              </div>
              <div class="col-4">
                <span class="badge badge-outline d-block py-1 border">{{ $n->pas ?? '-' }}</span>
                <small>PAS</small>
              </div>
            </div>
          </div>

          {{-- Keterampilan --}}
          <div class="mb-1">
            <small class="text-muted d-block mb-1"><strong>Keterampilan</strong> (Rata-rata: {{ $n->rata_keterampilan ?? '-' }})</small>
            <div class="row text-center">
              <div class="col-4">
                <span class="badge badge-outline d-block py-1 border">{{ $n->praktik ?? '-' }}</span>
                <small>Praktik</small>
              </div>
              <div class="col-4">
                <span class="badge badge-outline d-block py-1 border">{{ $n->proyek ?? '-' }}</span>
                <small>Proyek</small>
              </div>
              <div class="col-4">
                <span class="badge badge-outline d-block py-1 border">{{ $n->portofolio ?? '-' }}</span>
                <small>Portofolio</small>
              </div>
            </div>
          </div>

          @if($n->catatan)
          <div class="mt-2">
            <small class="text-muted"><i class="fas fa-comment"></i> {{ $n->catatan }}</small>
          </div>
          @endif
        @else
          <p class="text-muted small mb-0">Belum ada nilai.</p>
        @endif
      </div>
      <div class="card-footer py-1 text-muted small">
        Guru: {{ $jadwal->guru?->nama ?? '-' }}
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="alert alert-warning">Belum ada jadwal atau kelas belum ditentukan.</div>
  </div>
  @endforelse
</div>
@endsection
