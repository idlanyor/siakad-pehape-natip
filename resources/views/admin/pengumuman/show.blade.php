@extends('layouts.admin')
@section('title', $pengumuman->judul)
@section('page_title', 'Detail Pengumuman')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-9">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
          <i class="fas fa-bullhorn text-primary mr-2"></i>{{ $pengumuman->judul }}
        </h3>
        <span class="badge badge-{{ $pengumuman->is_published ? 'success' : 'warning' }} ml-2">
          {{ $pengumuman->is_published ? 'Published' : 'Draft' }}
        </span>
      </div>
      <div class="card-body">
        <div class="mb-3 d-flex flex-wrap gap-2" style="gap:.5rem">
          <span class="badge badge-secondary">
            <i class="fas fa-users mr-1"></i>{{ $pengumuman->target_label }}
          </span>
          @if($pengumuman->published_at)
            <span class="badge badge-info">
              <i class="fas fa-clock mr-1"></i>{{ $pengumuman->published_at->format('d/m/Y H:i') }}
            </span>
          @endif
          <span class="badge badge-light text-dark">
            <i class="fas fa-user mr-1"></i>{{ $pengumuman->pembuat?->name ?? '—' }}
          </span>
        </div>
        <hr>
        <div style="white-space: pre-line; line-height: 1.7">{{ $pengumuman->isi }}</div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <div>
          @if(!$pengumuman->is_published)
            <form action="{{ route('admin.pengumuman.publish', $pengumuman) }}" method="POST" class="d-inline">
              @csrf
              <button class="btn btn-success mr-2">
                <i class="fas fa-paper-plane mr-1"></i> Publish Sekarang
              </button>
            </form>
          @endif
          <a href="{{ route('admin.pengumuman.edit', $pengumuman) }}" class="btn btn-warning">
            <i class="fas fa-edit mr-1"></i> Edit
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
