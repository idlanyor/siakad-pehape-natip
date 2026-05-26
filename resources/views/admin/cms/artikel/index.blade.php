@extends('layouts.admin')
@section('title', 'Manajemen Artikel / Berita')
@section('page_title', 'Artikel & Berita')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-newspaper mr-2"></i>Artikel & Berita</h3>
    <a href="{{ route('admin.cms.artikel.create') }}" class="btn btn-primary btn-sm">
      <i class="fas fa-plus mr-1"></i> Tulis Artikel
    </a>
  </div>

  {{-- Filter --}}
  <div class="card-body border-bottom pb-3">
    <form method="GET" class="form-inline flex-wrap" style="gap:.5rem">
      <select name="kategori" class="form-control form-control-sm mr-2">
        <option value="">Semua Kategori</option>
        @foreach($kategoriList as $k => $v)
          <option value="{{ $k }}" {{ request('kategori') == $k ? 'selected' : '' }}>{{ $v }}</option>
        @endforeach
      </select>
      <select name="status" class="form-control form-control-sm mr-2">
        <option value="">Semua Status</option>
        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
      <a href="{{ route('admin.cms.artikel.index') }}" class="btn btn-light btn-sm">Reset</a>
    </form>
  </div>

  <div class="card-body p-0">
    @if($artikels->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="fas fa-newspaper fa-3x mb-3 d-block"></i>Belum ada artikel.
      </div>
    @else
      <table class="table table-hover table-striped mb-0">
        <thead class="thead-light">
          <tr>
            <th style="width:60px">Cover</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Penulis</th>
            <th class="text-center">Status</th>
            <th>Tanggal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($artikels as $a)
          <tr>
            <td>
              @if($a->thumbnail_url)
                <img src="{{ $a->thumbnail_url }}" style="width:50px;height:40px;object-fit:cover;border-radius:4px" alt="">
              @else
                <div style="width:50px;height:40px;background:#e2e8f0;border-radius:4px;display:flex;align-items:center;justify-content:center">
                  <i class="fas fa-image text-muted small"></i>
                </div>
              @endif
            </td>
            <td>
              <a href="{{ route('admin.cms.artikel.edit', $a) }}" class="font-weight-bold text-dark">
                {{ $a->judul }}
              </a>
              <div class="text-muted small">{{ Str::limit($a->ringkasan_auto, 70) }}</div>
            </td>
            <td>
              <span class="badge badge-{{ $a->kategori_color }}">{{ $a->kategori_label }}</span>
            </td>
            <td class="text-muted small">{{ $a->penulis?->name ?? '—' }}</td>
            <td class="text-center">
              @if($a->is_published)
                <span class="badge badge-success">Published</span>
              @else
                <span class="badge badge-secondary">Draft</span>
              @endif
            </td>
            <td class="text-muted small">
              {{ $a->published_at ? $a->published_at->format('d/m/Y') : $a->created_at->format('d/m/Y') }}
            </td>
            <td class="text-right" style="white-space:nowrap">
              <a href="{{ route('berita.show', $a) }}" target="_blank" class="btn btn-xs btn-info" title="Lihat publik">
                <i class="fas fa-external-link-alt"></i>
              </a>
              <a href="{{ route('admin.cms.artikel.edit', $a) }}" class="btn btn-xs btn-warning">
                <i class="fas fa-edit"></i>
              </a>
              <form action="{{ route('admin.cms.artikel.destroy', $a) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-xs btn-danger" onclick="return confirm('Hapus artikel ini?')">
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
  @if($artikels->hasPages())
    <div class="card-footer">{{ $artikels->links() }}</div>
  @endif
</div>
@endsection
