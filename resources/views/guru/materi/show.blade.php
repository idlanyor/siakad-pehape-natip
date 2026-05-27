@extends('layouts.admin')
@section('title', 'Materi — ' . $jadwal->mapel->nama_mapel)
@section('page_title', 'Materi Pelajaran')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card card-primary card-outline">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
          <span class="badge badge-primary">{{ $jadwal->mapel->nama_mapel }}</span>
          &mdash; {{ $jadwal->kelas?->nama_kelas }}
          | Sem {{ $jadwal->semester?->semester }} / {{ $jadwal->semester?->tahunAjaran->nama }}
        </h3>
        <div>
          <a href="{{ route('guru.materi.create', $jadwal) }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus"></i> Tambah Materi
          </a>
          <a href="{{ route('guru.materi.index') }}" class="btn btn-secondary btn-sm ml-1">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        @if($materis->isEmpty())
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Belum ada materi untuk jadwal ini.
            <a href="{{ route('guru.materi.create', $jadwal) }}" class="alert-link">Tambah materi pertama.</a>
          </div>
        @else
          <div class="row">
            @foreach($materis as $m)
            <div class="col-md-6 col-lg-4">
              <div class="card mb-3 {{ $m->is_published ? '' : 'border-warning' }}">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                  <span class="badge badge-{{ $m->tipe_color }}">
                    <i class="{{ $m->tipe_icon }}"></i> {{ $m->tipe_label }}
                  </span>
                  @if(! $m->is_published)
                    <span class="badge badge-warning">Draft</span>
                  @endif
                  <small class="text-muted">{{ $m->created_at->format('d M Y') }}</small>
                </div>
                <div class="card-body py-2">
                  <h6 class="card-title mb-1">{{ $m->judul }}</h6>
                  @if($m->deskripsi)
                    <p class="card-text text-muted small mb-0">{{ Str::limit($m->deskripsi, 100) }}</p>
                  @endif
                </div>
                <div class="card-footer py-2 d-flex justify-content-between">
                  <div>
                    @if($m->tipe !== 'teks')
                      <a href="{{ $m->download_url }}" target="_blank" class="btn btn-xs btn-outline-primary">
                        <i class="fas fa-external-link-alt"></i>
                        {{ $m->tipe === 'file' ? 'Unduh' : 'Buka' }}
                      </a>
                    @endif
                  </div>
                  <div>
                    <a href="{{ route('guru.materi.edit', $m) }}" class="btn btn-xs btn-warning">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('guru.materi.destroy', $m) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Hapus materi ini?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
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
