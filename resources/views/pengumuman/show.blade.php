@extends('layouts.admin')
@section('title', $pengumuman->judul)
@section('page_title', 'Pengumuman')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-9">
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title font-weight-bold">{{ $pengumuman->judul }}</h3>
        <div class="card-tools text-muted small">
          <i class="fas fa-clock mr-1"></i>
          {{ $pengumuman->published_at?->format('d F Y, H:i') ?? '—' }}
          &nbsp;·&nbsp;
          <i class="fas fa-user mr-1"></i>{{ $pengumuman->pembuat?->name ?? '—' }}
        </div>
      </div>
      <div class="card-body" style="white-space: pre-line; line-height: 1.8">{{ $pengumuman->isi }}</div>
      <div class="card-footer">
        <a href="{{ route('pengumuman.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
