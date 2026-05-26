@extends('layouts.public')
@section('title', 'Program Keahlian')
@section('page_title', 'Program Keahlian')

@section('content')
<div style="background:linear-gradient(135deg,var(--primary),var(--primary-light));padding:120px 0 60px">
  <div class="container text-white">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb" style="background:none;padding:0;margin-bottom:.5rem">
        <li class="breadcrumb-item"><a href="{{ route('landing.index') }}" style="color:rgba(255,255,255,.7)">Beranda</a></li>
        <li class="breadcrumb-item active text-white">Program Keahlian</li>
      </ol>
    </nav>
    <h1 class="fw-bold mb-2">Program Keahlian</h1>
    <p class="opacity-75 mb-0">{{ $jurusans->count() }} program keahlian tersedia</p>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <div class="row g-4">
      @forelse($jurusans as $j)
      <div class="col-md-6 col-lg-4">
        <div class="jurusan-card card h-100">
          @if($j->gambar)
            <img src="{{ asset('storage/' . $j->gambar) }}" class="card-img-top" style="height:180px;object-fit:cover" alt="{{ $j->nama_jurusan }}">
          @else
            <div style="height:140px;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center">
              <i class="{{ $j->icon ?? 'fas fa-graduation-cap' }}" style="font-size:3rem;color:rgba(255,255,255,.5)"></i>
            </div>
          @endif
          <div class="card-body p-4">
            <div class="jurusan-icon mb-3" style="width:46px;height:46px">
              <i class="{{ $j->icon ?? 'fas fa-graduation-cap' }}" style="font-size:1.1rem"></i>
            </div>
            <h5 class="fw-bold" style="color:var(--primary)">{{ $j->nama_jurusan }}</h5>
            @if($j->deskripsi_publik)
              <p class="text-muted small lh-lg">{{ $j->deskripsi_publik }}</p>
            @else
              <p class="text-muted small">Program keahlian ini mempersiapkan siswa dengan kompetensi di bidang {{ strtolower($j->nama_jurusan) }}.</p>
            @endif
          </div>
          <div class="card-footer bg-transparent border-top px-4 pb-4 pt-0">
            @if($j->is_aktif_ppdb && setting('ppdb_aktif') == '1')
              <a href="{{ route('ppdb.index') }}?jurusan={{ $j->id }}"
                 class="btn btn-sm fw-semibold" style="background:var(--accent);color:#fff;border-radius:6px">
                <i class="fas fa-file-alt me-1"></i> Daftar Jurusan Ini
              </a>
            @endif
          </div>
        </div>
      </div>
      @empty
        <div class="col-12 text-center py-5 text-muted">
          <i class="fas fa-graduation-cap fa-3x mb-3 d-block opacity-25"></i>
          Program keahlian belum tersedia.
        </div>
      @endforelse
    </div>
  </div>
</section>
@endsection
