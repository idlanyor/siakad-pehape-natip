@extends('layouts.public')
@section('title', 'Berita & Pengumuman')
@section('page_title', 'Berita')

@section('content')
<div style="background:linear-gradient(135deg,var(--primary),var(--primary-light));padding:120px 0 60px">
  <div class="container text-white">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb" style="background:none;padding:0;margin-bottom:.5rem">
        <li class="breadcrumb-item"><a href="{{ route('landing.index') }}" style="color:rgba(255,255,255,.7)">Beranda</a></li>
        <li class="breadcrumb-item active text-white">Berita</li>
      </ol>
    </nav>
    <h1 class="fw-bold mb-2">Berita & Pengumuman</h1>
    <p class="opacity-75 mb-0">Informasi terkini dari {{ setting('sekolah_nama') }}</p>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      {{-- Main content --}}
      <div class="col-lg-8">
        {{-- Filter kategori --}}
        <div class="d-flex flex-wrap gap-2 mb-4" style="gap:.5rem">
          <a href="{{ route('berita.index') }}"
             class="btn btn-sm {{ !request('kategori') ? 'btn-primary' : 'btn-outline-secondary' }}">
            Semua
          </a>
          @foreach(\App\Models\Artikel::KATEGORI as $k => $v)
            <a href="{{ route('berita.index', ['kategori' => $k]) }}"
               class="btn btn-sm {{ request('kategori') == $k ? 'btn-primary' : 'btn-outline-secondary' }}">
              {{ $v }}
            </a>
          @endforeach
        </div>

        {{-- Form search --}}
        <form method="GET" action="{{ route('berita.index') }}" class="mb-4">
          @if(request('kategori'))
            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
          @endif
          <div class="input-group">
            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari berita...">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
          </div>
        </form>

        @forelse($artikels as $a)
        <article class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
          <div class="row g-0">
            @if($a->thumbnail_url)
            <div class="col-md-4">
              <img src="{{ $a->thumbnail_url }}" class="img-fluid h-100" style="object-fit:cover;min-height:160px" alt="">
            </div>
            <div class="col-md-8">
            @else
            <div class="col-12">
            @endif
              <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <span class="badge bg-{{ $a->kategori_color }} text-white">{{ $a->kategori_label }}</span>
                  <span class="text-muted small">
                    <i class="fas fa-clock me-1"></i>
                    {{ $a->published_at?->diffForHumans() ?? $a->created_at->diffForHumans() }}
                  </span>
                </div>
                <h5 class="fw-bold mb-2">
                  <a href="{{ route('berita.show', $a) }}" style="color:var(--primary);text-decoration:none">
                    {{ $a->judul }}
                  </a>
                </h5>
                <p class="text-muted small mb-3 lh-lg">{{ $a->ringkasan_auto }}</p>
                <a href="{{ route('berita.show', $a) }}" class="btn btn-sm btn-outline-primary">
                  Baca Selengkapnya <i class="fas fa-arrow-right ms-1"></i>
                </a>
              </div>
            </div>
          </div>
        </article>
        @empty
          <div class="text-center py-5 text-muted">
            <i class="fas fa-newspaper fa-3x mb-3 d-block opacity-25"></i>
            Belum ada artikel.
          </div>
        @endforelse

        {{ $artikels->links() }}
      </div>

      {{-- Sidebar --}}
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
          <h6 class="fw-bold mb-3" style="color:var(--primary)">Berita Terbaru</h6>
          @foreach($terbaru as $t)
            <div class="d-flex gap-3 mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
              @if($t->thumbnail_url)
                <img src="{{ $t->thumbnail_url }}" style="width:60px;height:50px;object-fit:cover;border-radius:6px;flex-shrink:0" alt="">
              @endif
              <div>
                <a href="{{ route('berita.show', $t) }}" style="color:var(--text);text-decoration:none;font-size:.87rem;font-weight:600;line-height:1.3" class="d-block">
                  {{ Str::limit($t->judul, 55) }}
                </a>
                <span class="text-muted" style="font-size:.75rem">{{ $t->published_at?->format('d M Y') }}</span>
              </div>
            </div>
          @endforeach
        </div>
      </div>

    </div>
  </div>
</section>
@endsection
