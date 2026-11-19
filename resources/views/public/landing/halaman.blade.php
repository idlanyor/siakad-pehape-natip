@extends('layouts.public')
@section('title', $halaman->judul)
@section('page_title', $halaman->judul)
@section('meta_desc', $halaman->meta_desc ?? '')

@section('content')
<div style="background:linear-gradient(135deg,var(--primary),var(--primary-light));padding:120px 0 60px">
  <div class="container text-white">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb" style="background:none;padding:0;margin-bottom:.5rem">
        <li class="breadcrumb-item"><a href="{{ route('landing.index') }}" style="color:rgba(255,255,255,.7)">Beranda</a></li>
        <li class="breadcrumb-item active text-white">{{ $halaman->judul }}</li>
      </ol>
    </nav>
    <h1 class="fw-bold mb-0">{{ $halaman->judul }}</h1>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-3 p-5">
          <div class="lh-lg" style="color:var(--text)">{!! $halaman->konten !!}</div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
