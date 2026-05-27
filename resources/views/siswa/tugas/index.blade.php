@extends('layouts.admin')
@section('title', 'Tugas Saya')
@section('page_title', 'Daftar Tugas')

@section('content')
@if($semester)
<div class="alert alert-info py-2 mb-3">
  <i class="fas fa-calendar-check mr-1"></i>
  Semester {{ $semester->semester }} — {{ $semester->tahunAjaran->nama }}
</div>
@endif

@php
  $belumKumpul = $tugas->filter(fn($t) => !isset($pengumpulans[$t->id]) && !$t->is_expired);
  $terlambat   = $tugas->filter(fn($t) => !isset($pengumpulans[$t->id]) && $t->is_expired);
  $sudahKumpul = $tugas->filter(fn($t) => isset($pengumpulans[$t->id]));
@endphp

@if($belumKumpul->isNotEmpty())
<div class="alert alert-warning py-2">
  <i class="fas fa-exclamation-triangle"></i>
  <strong>{{ $belumKumpul->count() }} tugas</strong> belum dikumpulkan.
</div>
@endif

<div class="row">
  @forelse($tugas as $t)
  @php $p = $pengumpulans[$t->id] ?? null; @endphp
  <div class="col-md-6">
    <div class="card mb-3">
      <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <div>
          <strong>{{ $t->judul }}</strong>
          <span class="badge badge-info ml-1">{{ $t->tipe_label }}</span>
        </div>
        @if($p)
          <span class="badge badge-{{ $p->status_color }}">{{ $p->status_label }}</span>
        @elseif($t->is_expired)
          <span class="badge badge-danger">Terlambat</span>
        @else
          <span class="badge badge-warning">Belum Kumpul</span>
        @endif
      </div>
      <div class="card-body py-2">
        <small class="text-muted d-block">
          <i class="fas fa-book mr-1"></i>{{ $t->jadwal->mapel->nama_mapel }}
        </small>
        <small class="text-{{ $t->deadline_color }}">
          <i class="fas fa-clock mr-1"></i>Deadline: {{ $t->deadline->format('d M Y H:i') }}
          — {{ $t->sisa_waktu }}
        </small>
        @if($p && $p->is_dinilai)
          <div class="mt-1">
            <span class="badge badge-success">
              Nilai: {{ $p->nilai }} / {{ $t->nilai_maksimal }}
            </span>
          </div>
        @endif
      </div>
      <div class="card-footer py-2">
        <a href="{{ route('siswa.tugas.show', $t) }}" class="btn btn-sm btn-primary btn-block">
          <i class="fas fa-eye"></i> Lihat Detail & Kumpul
        </a>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    <div class="alert alert-info">
      <i class="fas fa-info-circle"></i> Belum ada tugas untuk semester ini.
    </div>
  </div>
  @endforelse
</div>
@endsection
