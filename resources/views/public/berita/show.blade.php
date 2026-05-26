@extends('layouts.public')
@section('title', $artikel->judul)
@section('page_title', $artikel->judul)
@section('meta_desc', $artikel->ringkasan_auto)

@section('content')
<div style="background:linear-gradient(135deg,var(--primary),var(--primary-light));padding:120px 0 60px">
  <div class="container text-white">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb" style="background:none;padding:0;margin-bottom:.5rem">
        <li class="breadcrumb-item"><a href="{{ route('landing.index') }}" style="color:rgba(255,255,255,.7)">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('berita.index') }}" style="color:rgba(255,255,255,.7)">Berita</a></li>
        <li class="breadcrumb-item active text-white">{{ Str::limit($artikel->judul, 40) }}</li>
      </ol>
    </nav>
    <span class="badge mb-2" style="background:var(--accent)">{{ $artikel->kategori_label }}</span>
    <h1 class="fw-bold lh-sm">{{ $artikel->judul }}</h1>
    <div class="mt-3 d-flex flex-wrap gap-3 opacity-80" style="font-size:.88rem;gap:.75rem">
      <span><i class="fas fa-user me-1"></i>{{ $artikel->penulis?->name ?? 'Redaksi' }}</span>
      <span><i class="fas fa-calendar me-1"></i>{{ $artikel->published_at?->translatedFormat('d F Y') }}</span>
    </div>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-8">
        @if($artikel->thumbnail_url)
          <img src="{{ $artikel->thumbnail_url }}" class="img-fluid rounded-3 mb-4 w-100"
               style="max-height:400px;object-fit:cover" alt="{{ $artikel->judul }}">
        @endif

        <div class="card border-0 shadow-sm rounded-3 p-4 p-md-5">
          @if($artikel->ringkasan)
            <p class="lead text-muted border-start border-4 ps-3 mb-4" style="border-color:var(--accent)!important">
              {{ $artikel->ringkasan }}
            </p>
          @endif
          <div class="lh-lg article-content" style="color:var(--text)">
            {!! $artikel->konten !!}
          </div>
        </div>

        {{-- Share --}}
        <div class="mt-4 d-flex align-items-center gap-3">
          <span class="text-muted small">Bagikan:</span>
          <a href="https://wa.me/?text={{ urlencode($artikel->judul . ' ' . url()->current()) }}" target="_blank"
             class="btn btn-sm btn-success"><i class="fab fa-whatsapp me-1"></i>WhatsApp</a>
          <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"
             class="btn btn-sm btn-primary"><i class="fab fa-facebook-f me-1"></i>Facebook</a>
        </div>
      </div>

      <div class="col-lg-4">
        @if($terkait->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-4">
          <h6 class="fw-bold mb-3" style="color:var(--primary)">Berita Terkait</h6>
          @foreach($terkait as $t)
          <div class="d-flex gap-3 mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
            @if($t->thumbnail_url)
              <img src="{{ $t->thumbnail_url }}" style="width:60px;height:50px;object-fit:cover;border-radius:6px;flex-shrink:0" alt="">
            @endif
            <div>
              <a href="{{ route('berita.show', $t) }}" style="color:var(--text);font-size:.87rem;font-weight:600;text-decoration:none">
                {{ Str::limit($t->judul, 55) }}
              </a>
              <div class="text-muted" style="font-size:.75rem">{{ $t->published_at?->format('d M Y') }}</div>
            </div>
          </div>
          @endforeach
        </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3 p-4 mt-3">
          <a href="{{ route('berita.index') }}" class="btn btn-outline-primary w-100">
            <i class="fas fa-arrow-left me-1"></i> Semua Berita
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
