@extends('layouts.public')
@section('title', 'Tentang Kami')
@section('page_title', 'Tentang Kami')

@section('content')
{{-- Page Header --}}
<div style="background:linear-gradient(135deg,var(--primary),var(--primary-light));padding:120px 0 60px">
  <div class="container text-white">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb" style="background:none;padding:0;margin-bottom:.5rem">
        <li class="breadcrumb-item"><a href="{{ route('landing.index') }}" style="color:rgba(255,255,255,.7)">Beranda</a></li>
        <li class="breadcrumb-item active text-white">Tentang Kami</li>
      </ol>
    </nav>
    <h1 class="fw-bold mb-0">Tentang {{ setting('sekolah_nama', 'Kami') }}</h1>
  </div>
</div>

<section class="py-5">
  <div class="container">
    @if($halaman && $halaman->konten)
      <div class="row justify-content-center">
        <div class="col-lg-9">
          <div class="card border-0 shadow-sm rounded-3 p-5">
            <h2 class="section-title mb-4">{{ $halaman->judul }}</h2>
            <div class="lh-lg" style="color:var(--text)">{!! $halaman->konten !!}</div>
          </div>
        </div>
      </div>
    @else
      {{-- Default content jika belum ada di CMS --}}
      <div class="row g-5 align-items-center">
        <div class="col-lg-6">
          @if(setting_url('foto_kepsek'))
            <img src="{{ setting_url('foto_kepsek') }}" class="img-fluid rounded-3 shadow" alt="Kepala Sekolah">
          @else
            <div style="height:350px;background:var(--bg-light);border-radius:14px;display:flex;align-items:center;justify-content:center">
              <i class="fas fa-school fa-5x text-muted opacity-25"></i>
            </div>
          @endif
        </div>
        <div class="col-lg-6">
          <div class="section-divider"></div>
          <h2 class="section-title mb-3">{{ setting('sekolah_nama') }}</h2>
          <p class="text-muted lh-lg mb-3">{{ setting('sekolah_tagline') }}</p>
          <p class="text-muted lh-lg">Berdiri sejak tahun {{ setting('sekolah_tahun_berdiri', '1975') }}, kami berkomitmen menghasilkan lulusan yang kompeten, berkarakter, dan siap memasuki dunia kerja maupun melanjutkan pendidikan ke jenjang yang lebih tinggi.</p>
          <ul class="list-unstyled mt-4">
            <li class="mb-2"><i class="fas fa-check-circle me-2" style="color:var(--accent)"></i>Akreditasi A</li>
            <li class="mb-2"><i class="fas fa-check-circle me-2" style="color:var(--accent)"></i>NPSN: {{ setting('sekolah_npsn', '-') }}</li>
            <li class="mb-2"><i class="fas fa-check-circle me-2" style="color:var(--accent)"></i>Fasilitas lengkap & modern</li>
          </ul>
        </div>
      </div>

      {{-- Sambutan Kepsek --}}
      @if(setting('sambutan_kepsek'))
      <div class="row justify-content-center mt-5">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-3 p-5" style="background:var(--bg-light)">
            <h4 class="fw-bold mb-4" style="color:var(--primary)">Sambutan Kepala Sekolah</h4>
            <div class="lh-lg text-muted">{!! setting('sambutan_kepsek') !!}</div>
            @if(setting('nama_kepsek'))
            <div class="d-flex align-items-center gap-3 mt-4 pt-4 border-top">
              @if(setting_url('foto_kepsek'))
                <img src="{{ setting_url('foto_kepsek') }}" style="width:52px;height:52px;border-radius:50%;object-fit:cover" alt="">
              @endif
              <div>
                <div class="fw-bold">{{ setting('nama_kepsek') }}</div>
                <div class="text-muted small">Kepala {{ setting('sekolah_nama') }}</div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
      @endif
    @endif
  </div>
</section>
@endsection
