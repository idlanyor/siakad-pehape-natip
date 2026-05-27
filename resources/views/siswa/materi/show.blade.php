@extends('layouts.admin')
@section('title', 'Materi — ' . $jadwal->mapel->nama_mapel)
@section('page_title', 'Materi Pelajaran')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          <span class="badge badge-primary">{{ $jadwal->mapel->nama_mapel }}</span>
          &mdash; <small>{{ $jadwal->guru?->nama }}</small>
        </h3>
        <a href="{{ route('siswa.materi.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
      <div class="card-body">
        @if($materis->isEmpty())
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Belum ada materi yang dipublikasikan.
          </div>
        @else
          <div class="row">
            @foreach($materis as $m)
            <div class="col-md-6">
              <div class="card mb-3 border-left border-{{ $m->tipe_color }}" style="border-left-width:4px !important">
                <div class="card-body py-3">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 mr-2">
                      <h6 class="mb-1">
                        <i class="{{ $m->tipe_icon }} text-{{ $m->tipe_color }} mr-1"></i>
                        {{ $m->judul }}
                      </h6>
                      @if($m->deskripsi)
                        <p class="text-muted small mb-2">{{ $m->deskripsi }}</p>
                      @endif
                      <small class="text-muted">{{ $m->created_at->format('d M Y') }}</small>
                    </div>
                    <div>
                      @if($m->tipe === 'teks')
                        <a href="{{ route('siswa.materi.detail', $m) }}" class="btn btn-sm btn-success">
                          <i class="fas fa-eye"></i> Baca
                        </a>
                      @else
                        <a href="{{ $m->download_url }}" target="_blank" class="btn btn-sm btn-{{ $m->tipe_color }}">
                          <i class="{{ $m->tipe === 'file' ? 'fas fa-download' : 'fas fa-external-link-alt' }}"></i>
                          {{ $m->tipe === 'file' ? 'Unduh' : 'Buka' }}
                        </a>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
