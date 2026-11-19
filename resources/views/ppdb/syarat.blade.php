@extends('layouts.public')
@section('title', 'Persyaratan PPDB')
@section('page_title', 'Persyaratan')

@include('ppdb.partials.gaya')

@push('styles')
<style>
  .req-item { display: flex; gap: 1rem; padding: 1.1rem 0; border-top: 1px solid var(--line); }
  .req-item:first-child { border-top: 0; padding-top: 0; }
  .req-item .n { width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0; display: grid; place-items: center; background: var(--accent-soft); color: var(--accent-dark); font-weight: 800; font-size: .9rem; }
  .req-item strong { display: block; color: var(--ink); font-size: 1rem; }
  .req-item span { color: var(--text-muted); font-size: .92rem; line-height: 1.65; }
</style>
@endpush

@section('content')

@include('ppdb.partials.header', [
  'crumb' => 'Persyaratan',
  'judul' => 'Persyaratan pendaftaran',
  'sub'   => 'Siapkan dokumen berikut. Semuanya diunggah lewat portal pendaftar setelah formulir dikirim.',
])

<section class="ppdb-body">
  <div class="container">
    <div class="row justify-content-center g-4">
      <div class="col-lg-8">
        <div class="ppdb-card ppdb-card-pad">
          @if($persyaratanList->isEmpty())
            <div class="text-center py-4">
              <i class="far fa-folder-open fa-2x muted mb-3 d-block"></i>
              <p class="muted mb-0">Persyaratan belum tersedia. Silakan hubungi pihak sekolah.</p>
            </div>
          @else
            <h2 class="mb-4">Dokumen yang diperlukan</h2>
            @foreach($persyaratanList as $p)
              <div class="req-item">
                <span class="n">{{ $loop->iteration }}</span>
                <div><strong>{{ $p->judul }}</strong>@if($p->deskripsi)<span>{{ $p->deskripsi }}</span>@endif</div>
              </div>
            @endforeach
          @endif

          <div class="divider-x"></div>
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('ppdb.index') }}" class="btn btn-primary">Isi formulir pendaftaran <i class="fas fa-arrow-right ms-1"></i></a>
            <a href="{{ route('ppdb.landing') }}" class="btn btn-outline-primary">Kembali ke PPDB</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
