@extends('layouts.public')
@section('title', $galeri->nama . ' — Galeri')
@section('page_title', 'Galeri')

@push('styles')
<style>
  .galeri-item { cursor: pointer; overflow: hidden; border-radius: 8px; }
  .galeri-item img { transition: transform .3s; width: 100%; height: 180px; object-fit: cover; display: block; }
  .galeri-item:hover img { transform: scale(1.05); }
  /* Lightbox */
  #lightbox { display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center }
  #lightbox.show { display:flex; }
  #lightbox img { max-width:90vw;max-height:90vh;border-radius:8px }
  #lightbox .lb-close { position:absolute;top:16px;right:20px;color:#fff;font-size:2rem;cursor:pointer;line-height:1 }
  #lightbox .lb-nav { position:absolute;top:50%;transform:translateY(-50%);color:#fff;font-size:2rem;cursor:pointer;padding:1rem;background:rgba(255,255,255,.1);border-radius:8px }
  #lightbox .lb-prev { left:12px; }
  #lightbox .lb-next { right:12px; }
</style>
@endpush

@section('content')
<div style="background:linear-gradient(135deg,var(--primary),var(--primary-light));padding:120px 0 60px">
  <div class="container text-white">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb" style="background:none;padding:0;margin-bottom:.5rem">
        <li class="breadcrumb-item"><a href="{{ route('landing.index') }}" style="color:rgba(255,255,255,.7)">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('galeri.index') }}" style="color:rgba(255,255,255,.7)">Galeri</a></li>
        <li class="breadcrumb-item active text-white">{{ $galeri->nama }}</li>
      </ol>
    </nav>
    <h1 class="fw-bold mb-2">{{ $galeri->nama }}</h1>
    <p class="opacity-75 mb-0">
      <i class="fas fa-calendar me-2"></i>{{ $galeri->tanggal->translatedFormat('d F Y') }}
      &nbsp;·&nbsp; {{ $galeri->items->count() }} foto
    </p>
  </div>
</div>

<section class="py-5">
  <div class="container">
    @if($galeri->deskripsi)
      <p class="text-muted mb-4">{{ $galeri->deskripsi }}</p>
    @endif

    <div class="row g-3" id="galeriGrid">
      @forelse($galeri->items as $i => $item)
      <div class="col-6 col-md-4 col-lg-3">
        <div class="galeri-item" data-index="{{ $i }}" onclick="openLightbox({{ $i }})">
          <img src="{{ $item->foto_url }}" alt="{{ $item->keterangan ?? '' }}" loading="lazy">
        </div>
        @if($item->keterangan)
          <div class="text-muted text-center mt-1" style="font-size:.78rem">{{ $item->keterangan }}</div>
        @endif
      </div>
      @empty
        <div class="col-12 text-center py-5 text-muted">Belum ada foto.</div>
      @endforelse
    </div>

    <div class="mt-4">
      <a href="{{ route('galeri.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left me-1"></i> Semua Galeri
      </a>
    </div>
  </div>
</section>

{{-- Lightbox --}}
<div id="lightbox">
  <span class="lb-close" onclick="closeLightbox()">&times;</span>
  <span class="lb-nav lb-prev" onclick="navLightbox(-1)">&#8249;</span>
  <img id="lbImg" src="" alt="">
  <span class="lb-nav lb-next" onclick="navLightbox(1)">&#8250;</span>
</div>
@endsection

@push('scripts')
<script>
const photos = @json($galeri->items->pluck('foto_url')->values());
let current = 0;

function openLightbox(idx) {
  current = idx;
  document.getElementById('lbImg').src = photos[current];
  document.getElementById('lightbox').classList.add('show');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('show');
  document.body.style.overflow = '';
}
function navLightbox(dir) {
  current = (current + dir + photos.length) % photos.length;
  document.getElementById('lbImg').src = photos[current];
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeLightbox();
  if (e.key === 'ArrowRight') navLightbox(1);
  if (e.key === 'ArrowLeft') navLightbox(-1);
});
</script>
@endpush
