@extends('layouts.admin')
@section('title', 'Manajemen Galeri')
@section('page_title', 'Galeri Foto')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-images mr-2"></i>Galeri Foto</h3>
    <a href="{{ route('admin.cms.galeri.create') }}" class="btn btn-primary btn-sm">
      <i class="fas fa-plus mr-1"></i> Buat Galeri
    </a>
  </div>
  <div class="card-body">
    @if($galeris->isEmpty())
      <div class="text-center py-5 text-muted">
        <i class="fas fa-images fa-3x mb-3 d-block"></i>Belum ada galeri.
      </div>
    @else
      <div class="row g-3">
        @foreach($galeris as $g)
        <div class="col-md-4 col-lg-3">
          <div class="card border h-100">
            @if($g->cover_url)
              <img src="{{ $g->cover_url }}" class="card-img-top" style="height:150px;object-fit:cover" alt="">
            @else
              <div style="height:150px;background:#e2e8f0;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-images fa-2x text-muted"></i>
              </div>
            @endif
            <div class="card-body p-3">
              <h6 class="font-weight-bold mb-1">{{ $g->nama }}</h6>
              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ $g->tanggal->format('d/m/Y') }}</small>
                <span class="badge badge-secondary">{{ $g->items_count }} foto</span>
              </div>
              <div class="mt-2">
                @if($g->is_published)
                  <span class="badge badge-success">Published</span>
                @else
                  <span class="badge badge-secondary">Draft</span>
                @endif
              </div>
            </div>
            <div class="card-footer bg-transparent p-2 d-flex justify-content-between">
              <a href="{{ route('admin.cms.galeri.show', $g) }}" class="btn btn-xs btn-primary">
                <i class="fas fa-folder-open mr-1"></i>Kelola
              </a>
              <div>
                <a href="{{ route('admin.cms.galeri.edit', $g) }}" class="btn btn-xs btn-warning">
                  <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('admin.cms.galeri.destroy', $g) }}" method="POST" class="d-inline">
                  @csrf @method('DELETE')
                  <button class="btn btn-xs btn-danger" onclick="return confirm('Hapus galeri ini beserta semua fotonya?')">
                    <i class="fas fa-trash"></i>
                  </button>
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
@endsection
