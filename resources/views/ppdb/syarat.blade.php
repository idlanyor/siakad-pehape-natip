@extends('layouts.public')
@section('title', 'Persyaratan PPDB')
@section('page_title', 'Persyaratan')

@section('content')
<div style="background:linear-gradient(135deg,var(--primary),var(--primary-light));padding:120px 0 60px">
  <div class="container text-white">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb" style="background:none;padding:0;margin-bottom:.5rem">
        <li class="breadcrumb-item"><a href="{{ route('landing.index') }}" style="color:rgba(255,255,255,.7)">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ppdb.landing') }}" style="color:rgba(255,255,255,.7)">PPDB</a></li>
        <li class="breadcrumb-item active text-white">Persyaratan</li>
      </ol>
    </nav>
    <h1 class="fw-bold mb-0">Persyaratan Pendaftaran</h1>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        @if($persyaratanList->isEmpty())
          <div class="text-center py-5 text-muted">Persyaratan belum tersedia. Hubungi pihak sekolah.</div>
        @else
          <div class="card border-0 shadow-sm rounded-3 p-5">
            <h4 class="fw-bold mb-4" style="color:var(--primary)">Dokumen yang Diperlukan</h4>
            <div class="d-flex flex-column gap-3">
              @foreach($persyaratanList as $i => $p)
              <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background:var(--bg-light)">
                <div class="fw-bold text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:36px;height:36px;background:var(--accent)">{{ $i + 1 }}</div>
                <div>
                  <div class="fw-semibold" style="color:var(--primary)">{{ $p->judul }}</div>
                  @if($p->deskripsi)<div class="text-muted small mt-1 lh-lg">{{ $p->deskripsi }}</div>@endif
                </div>
              </div>
              @endforeach
            </div>
            <div class="mt-4 pt-3 border-top">
              <a href="{{ route('ppdb.index') }}" class="btn btn-primary fw-semibold me-2">
                <i class="fas fa-file-alt me-1"></i>Daftar Sekarang
              </a>
              <a href="{{ route('ppdb.landing') }}" class="btn btn-outline-secondary">Kembali ke PPDB</a>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
