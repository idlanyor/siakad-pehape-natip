@extends('layouts.public')
@section('title', 'Galeri Foto')
@section('page_title', 'Galeri')

@section('content')
<div style="background:linear-gradient(135deg,var(--primary),var(--primary-light));padding:120px 0 60px">
  <div class="container text-white">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb" style="background:none;padding:0;margin-bottom:.5rem">
        <li class="breadcrumb-item"><a href="{{ route('landing.index') }}" style="color:rgba(255,255,255,.7)">Beranda</a></li>
        <li class="breadcrumb-item active text-white">Galeri</li>
      </ol>
    </nav>
    <h1 class="fw-bold mb-2">Galeri Foto</h1>
    <p class="opacity-75 mb-0">Momen kegiatan dan prestasi {{ setting('sekolah_nama') }}</p>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      @forelse($galeris as $g)
      <div class="col-md-6 col-lg-4">
        <a href="{{ route('galeri.show', $g) }}" class="text-decoration-none">
          <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100"
               style="transition:transform .25s,box-shadow .25s"
               onmouseenter="this.style.transform='translateY(-5px)';this.style.boxShadow='0 12px 30px rgba(0,0,0,.12)'"
               onmouseleave="this.style.transform='';this.style.boxShadow=''">
            @if($g->cover_url)
              <div style="position:relative;overflow:hidden;height:200px">
                <img src="{{ $g->cover_url }}" class="w-100 h-100" style="object-fit:cover" alt="{{ $g->nama }}">
                <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.6));padding:1rem">
                  <span class="text-white small fw-semibold">
                    <i class="fas fa-images me-1"></i>{{ $g->items_count }} foto
                  </span>
                </div>
              </div>
            @else
              <div style="height:200px;background:var(--bg-light);display:flex;align-items:center;justify-content:center">
                <i class="fas fa-images fa-3x" style="color:var(--primary);opacity:.3"></i>
              </div>
            @endif
            <div class="card-body p-3">
              <h6 class="fw-bold mb-1" style="color:var(--primary)">{{ $g->nama }}</h6>
              <div class="text-muted small">
                <i class="fas fa-calendar me-1"></i>{{ $g->tanggal->translatedFormat('d F Y') }}
              </div>
              @if($g->deskripsi)
                <p class="text-muted small mb-0 mt-1">{{ Str::limit($g->deskripsi, 80) }}</p>
              @endif
            </div>
          </div>
        </a>
      </div>
      @empty
        <div class="col-12 text-center py-5 text-muted">
          <i class="fas fa-images fa-3x mb-3 d-block opacity-25"></i>Belum ada galeri.
        </div>
      @endforelse
    </div>

    @if($galeris->hasPages())
      <div class="mt-4">{{ $galeris->links() }}</div>
    @endif
  </div>
</section>
@endsection
