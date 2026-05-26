@extends('layouts.admin')
@section('title', 'Kotak Masuk Kontak')
@section('page_title', 'Kotak Masuk Kontak')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title">
      <i class="fas fa-envelope mr-2"></i>Pesan Masuk
      @if($unreadCount > 0)
        <span class="badge badge-danger ml-1">{{ $unreadCount }} belum dibaca</span>
      @endif
    </h3>
    <div class="d-flex align-items-center" style="gap:.5rem">
      <a href="{{ route('admin.kontak.index') }}" class="btn btn-sm {{ !request('filter') ? 'btn-primary' : 'btn-outline-secondary' }}">Semua</a>
      <a href="{{ route('admin.kontak.index', ['filter' => 'unread']) }}" class="btn btn-sm {{ request('filter') == 'unread' ? 'btn-primary' : 'btn-outline-secondary' }}">Belum Dibaca</a>
      @if($unreadCount > 0)
        <form action="{{ route('admin.kontak.readAll') }}" method="POST" class="mb-0">
          @csrf
          <button class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-check-double mr-1"></i>Tandai Semua Dibaca
          </button>
        </form>
      @endif
    </div>
  </div>
  <div class="card-body p-0">
    @if($pesanList->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>Tidak ada pesan.
      </div>
    @else
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width:3%"></th>
            <th>Pengirim</th>
            <th>Subjek</th>
            <th>Waktu</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($pesanList as $p)
          <tr class="{{ !$p->is_read ? 'table-light font-weight-bold' : '' }}">
            <td class="text-center">
              @if(!$p->is_read)
                <i class="fas fa-circle text-primary" style="font-size:.5rem;vertical-align:middle"></i>
              @endif
            </td>
            <td>
              <div>{{ $p->nama }}</div>
              <small class="text-muted">{{ $p->email }}</small>
            </td>
            <td>
              <a href="{{ route('admin.kontak.show', $p) }}" class="{{ !$p->is_read ? 'text-dark' : 'text-muted' }}">
                {{ $p->subjek }}
              </a>
              <div class="text-muted small">{{ Str::limit($p->pesan, 60) }}</div>
            </td>
            <td class="text-muted small text-nowrap">{{ $p->created_at->diffForHumans() }}</td>
            <td class="text-right">
              <a href="{{ route('admin.kontak.show', $p) }}" class="btn btn-xs btn-primary">
                <i class="fas fa-eye mr-1"></i>Baca
              </a>
              <form action="{{ route('admin.kontak.destroy', $p) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-xs btn-danger" onclick="return confirm('Hapus pesan ini?')">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
  @if($pesanList->hasPages())
    <div class="card-footer">{{ $pesanList->links() }}</div>
  @endif
</div>
@endsection
