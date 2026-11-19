{{-- Header halaman PPDB. Parameter: $judul, $sub (opsional), $crumb (label halaman ini). --}}
<section class="ppdb-head">
  <div class="container">
    <ol class="crumbs" aria-label="Breadcrumb">
      <li><a href="{{ route('landing.index') }}">Beranda</a></li>
      <li><a href="{{ route('ppdb.landing') }}">PPDB {{ setting('ppdb_tahun_ajaran') }}</a></li>
      <li aria-current="page">{{ $crumb }}</li>
    </ol>
    <h1>{{ $judul }}</h1>
    @isset($sub)<p>{{ $sub }}</p>@endisset
  </div>
</section>
