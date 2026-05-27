@extends('layouts.admin')
@section('title', $materi->judul)
@section('page_title', $materi->judul)

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <span class="badge badge-{{ $materi->tipe_color }} mr-2">
            <i class="{{ $materi->tipe_icon }}"></i> {{ $materi->tipe_label }}
          </span>
          <span class="badge badge-secondary">{{ $materi->jadwal->mapel->nama_mapel }}</span>
        </div>
        <a href="{{ route('siswa.materi.jadwal', $materi->jadwal) }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
      <div class="card-body">
        <h4>{{ $materi->judul }}</h4>
        @if($materi->deskripsi)
          <p class="text-muted">{{ $materi->deskripsi }}</p>
        @endif
        <hr>
        <div class="materi-konten">
          {!! nl2br(e($materi->konten)) !!}
        </div>
      </div>
      <div class="card-footer text-muted small">
        Oleh: {{ $materi->guru?->nama }} &nbsp;|&nbsp; {{ $materi->created_at->format('d M Y H:i') }}
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .materi-konten { line-height: 1.8; font-size: 1rem; }
</style>
@endpush
