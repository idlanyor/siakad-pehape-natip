@extends('layouts.admin')
@section('title', 'Pengumuman')
@section('page_title', 'Pengumuman')

@section('content')
@if($pengumumans->isEmpty())
  <div class="card">
    <div class="card-body text-center py-5 text-muted">
      <i class="fas fa-bullhorn fa-3x mb-3 d-block"></i>Belum ada pengumuman.
    </div>
  </div>
@else
  @foreach($pengumumans as $p)
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title font-weight-bold">
        <a href="{{ route('pengumuman.show', $p) }}" class="text-dark">{{ $p->judul }}</a>
      </h3>
      <div class="card-tools text-muted small">
        <i class="fas fa-clock mr-1"></i>{{ $p->published_at->diffForHumans() }}
        &nbsp;·&nbsp;
        <i class="fas fa-user mr-1"></i>{{ $p->pembuat?->name ?? '—' }}
      </div>
    </div>
    <div class="card-body" style="white-space: pre-line">{{ Str::limit($p->isi, 300) }}</div>
    @if(strlen($p->isi) > 300)
    <div class="card-footer">
      <a href="{{ route('pengumuman.show', $p) }}" class="btn btn-sm btn-outline-primary">
        Baca Selengkapnya <i class="fas fa-arrow-right ml-1"></i>
      </a>
    </div>
    @endif
  </div>
  @endforeach

  {{ $pengumumans->links() }}
@endif
@endsection
