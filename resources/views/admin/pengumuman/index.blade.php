@extends('layouts.admin')
@section('title', 'Manajemen Pengumuman')
@section('page_title', 'Manajemen Pengumuman')

@section('content')
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-bullhorn mr-2"></i>Daftar Pengumuman</h3>
    <div class="card-tools">
      <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Buat Pengumuman
      </a>
    </div>
  </div>
  <div class="card-body p-0">
    @if($pengumumans->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="fas fa-bullhorn fa-3x mb-3 d-block"></i>Belum ada pengumuman.
      </div>
    @else
      <table class="table table-hover table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th>Judul</th>
            <th>Target</th>
            <th class="text-center">Status</th>
            <th>Dibuat</th>
            <th>Dipublish</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($pengumumans as $p)
          <tr>
            <td>
              <a href="{{ route('admin.pengumuman.show', $p) }}" class="font-weight-bold">
                {{ $p->judul }}
              </a>
              <small class="d-block text-muted">{{ Str::limit(strip_tags($p->isi), 60) }}</small>
            </td>
            <td>
              <span class="badge badge-secondary">{{ $p->target_label }}</span>
            </td>
            <td class="text-center">
              @if($p->is_published)
                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Publish</span>
              @else
                <span class="badge badge-warning">Draft</span>
              @endif
            </td>
            <td class="text-muted small">{{ $p->created_at->format('d/m/Y') }}</td>
            <td class="text-muted small">
              {{ $p->published_at ? $p->published_at->format('d/m/Y H:i') : '—' }}
            </td>
            <td class="text-right" style="white-space:nowrap">
              @if(!$p->is_published)
                <form action="{{ route('admin.pengumuman.publish', $p) }}" method="POST" class="d-inline">
                  @csrf
                  <button class="btn btn-xs btn-success" title="Publish sekarang">
                    <i class="fas fa-paper-plane"></i>
                  </button>
                </form>
              @endif
              <a href="{{ route('admin.pengumuman.edit', $p) }}" class="btn btn-xs btn-warning">
                <i class="fas fa-edit"></i>
              </a>
              <form action="{{ route('admin.pengumuman.destroy', $p) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-xs btn-danger" onclick="return confirm('Hapus pengumuman ini?')">
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
</div>
@endsection
