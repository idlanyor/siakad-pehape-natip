@extends('layouts.admin')
@section('title', 'Notifikasi')
@section('page_title', 'Notifikasi')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-bell mr-2"></i>Semua Notifikasi</h3>
    <form action="{{ route('notifikasi.readAll') }}" method="POST">
      @csrf
      <button type="submit" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
      </button>
    </form>
  </div>
  <div class="card-body p-0">
    @if($notifikasis->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="fas fa-bell-slash fa-3x mb-3 d-block"></i>Tidak ada notifikasi.
      </div>
    @else
      <ul class="list-group list-group-flush">
        @foreach($notifikasis as $n)
        <li class="list-group-item {{ !$n->is_read ? 'bg-light' : '' }}">
          <div class="d-flex align-items-start">
            <div class="mr-3 mt-1">
              @if(!$n->is_read)
                <span class="badge badge-primary badge-pill">&nbsp;</span>
              @else
                <span class="badge badge-secondary badge-pill">&nbsp;</span>
              @endif
            </div>
            <div class="flex-grow-1">
              <div class="font-weight-bold {{ !$n->is_read ? 'text-dark' : 'text-muted' }}">
                {{ $n->judul }}
              </div>
              <div class="text-muted small mt-1">{{ $n->pesan }}</div>
              <div class="text-muted small mt-1">
                <i class="fas fa-clock mr-1"></i>{{ $n->created_at->diffForHumans() }}
              </div>
            </div>
            @if($n->url)
            <div class="ml-3">
              <a href="{{ $n->url }}" class="btn btn-xs btn-outline-primary">
                <i class="fas fa-external-link-alt"></i>
              </a>
            </div>
            @endif
          </div>
        </li>
        @endforeach
      </ul>
    @endif
  </div>
  @if($notifikasis->hasPages())
  <div class="card-footer">
    {{ $notifikasis->links() }}
  </div>
  @endif
</div>
@endsection
