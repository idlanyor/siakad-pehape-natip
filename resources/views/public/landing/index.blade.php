@extends('layouts.public')
@section('title', setting('sekolah_nama', 'SIAKAD SMK'))
@section('page_title', 'Beranda')
@section('meta_desc', setting('sekolah_tagline', ''))

@php
  $ppdbAktif = setting('ppdb_aktif') == '1';
  $heroBg = setting_url('hero_bg');
  $warnaTingkat = [
    'Internasional'  => ['#fde68a', '#78350f'],
    'Nasional'       => ['#fee2e2', '#991b1b'],
    'Provinsi'       => ['#dbeafe', '#1e3a8a'],
    'Kabupaten/Kota' => ['#dcfce7', '#14532d'],
  ];
@endphp

@push('styles')
<style>
  .hero h1, .prog-card h3, .panel h3, .ach h4, .news h3 { color: var(--ink); }
  .section { padding: 96px 0; }
  .section-tight { padding: 72px 0; }
  .bg-soft { background: var(--bg-light); }
  .section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 2.5rem; }
  .section-head p { max-width: 560px; margin: 0; }

  /* ── Hero ── */
  .hero {
    position: relative; overflow: hidden;
    padding: 72px 0 64px;
    background:
      radial-gradient(900px 420px at 85% -10%, rgba(27, 93, 143, .14), transparent 60%),
      radial-gradient(700px 380px at -10% 110%, rgba(245, 158, 11, .12), transparent 60%),
      var(--bg-light);
  }
  .hero::before {
    content: ''; position: absolute; inset: 0; pointer-events: none;
    background-image: radial-gradient(rgba(15, 23, 42, .07) 1px, transparent 1px);
    background-size: 22px 22px;
    mask-image: linear-gradient(to bottom, #000 30%, transparent 85%);
    -webkit-mask-image: linear-gradient(to bottom, #000 30%, transparent 85%);
  }
  .hero .container { position: relative; }
  .hero-chip {
    display: inline-flex; align-items: center; gap: .55rem;
    background: #fff; border: 1px solid var(--line); box-shadow: var(--shadow-sm);
    padding: .4rem .9rem .4rem .45rem; border-radius: 999px;
    font-size: .85rem; font-weight: 600; color: var(--text-dark); text-decoration: none;
  }
  .hero-chip .dot { background: var(--accent-soft); color: var(--accent-dark); border-radius: 999px; padding: .15rem .6rem; font-size: .75rem; font-weight: 700; }
  a.hero-chip:hover { border-color: var(--accent); color: var(--ink); }
  .hero h1 {
    font-size: clamp(2.3rem, 5.2vw, 4rem); font-weight: 800; line-height: 1.05; letter-spacing: -.04em;
    margin: 1.4rem 0 1.25rem;
  }
  .hero h1 .hl { color: var(--primary-light); }
  .hero-lead { font-size: 1.125rem; line-height: 1.75; color: var(--text-muted); max-width: 540px; margin-bottom: 2rem; }
  .hero .btn-lg { padding: .9rem 1.6rem; font-size: 1rem; }

  .hero-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0; margin-top: 3rem; max-width: 600px; }
  .hero-stats > div { padding-right: 1rem; }
  .hero-stats > div + div { padding-left: 1rem; border-left: 1px solid var(--line); }
  .stat-num { font-size: clamp(1.5rem, 2.6vw, 2rem); font-weight: 800; color: var(--ink); letter-spacing: -.03em; line-height: 1.1; font-variant-numeric: tabular-nums; }
  .stat-lbl { font-size: .82rem; color: var(--text-muted); font-weight: 500; margin-top: .2rem; }

  .hero-visual { position: relative; padding: 1.5rem 1.5rem 2.5rem 2.5rem; }
  .hero-panel {
    position: relative; aspect-ratio: 4 / 4.4; border-radius: 28px; overflow: hidden;
    background: linear-gradient(150deg, var(--primary) 0%, var(--primary-light) 100%);
    box-shadow: 0 40px 80px -30px rgba(11, 58, 94, .55);
    display: grid; place-items: center;
  }
  .hero-panel.has-img { background-size: cover; background-position: center; }
  .hero-panel.has-img::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, rgba(7, 31, 51, .55), transparent 55%); }
  .hero-panel .rings { position: absolute; inset: 0; }
  .hero-panel .rings span { position: absolute; border: 1px solid rgba(255, 255, 255, .14); border-radius: 50%; left: 50%; top: 50%; transform: translate(-50%, -50%); }
  .hero-panel .emblem {
    position: relative; width: 44%; aspect-ratio: 1; border-radius: 32px;
    background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .2);
    backdrop-filter: blur(6px); display: grid; place-items: center; color: #fff;
  }
  .hero-panel .emblem img { width: 72%; height: auto; }
  .hero-panel .emblem i { font-size: clamp(3rem, 7vw, 5rem); opacity: .95; }
  .float-card {
    position: absolute; z-index: 2; background: #fff; border-radius: 16px; padding: .85rem 1rem;
    box-shadow: 0 18px 40px -16px rgba(15, 23, 42, .35); display: flex; align-items: center; gap: .75rem;
    font-size: .85rem; line-height: 1.3; max-width: 240px;
  }
  .float-card .ic { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; flex-shrink: 0; }
  .float-card strong { display: block; color: var(--ink); font-size: .95rem; }
  .float-card span { color: var(--text-muted); }
  .float-a { left: 0; bottom: 18%; }
  .float-b { right: 0; top: 6%; }
  @media (prefers-reduced-motion: no-preference) {
    .float-a { animation: bob 6s ease-in-out infinite; }
    .float-b { animation: bob 7s ease-in-out -2s infinite; }
    @keyframes bob { 50% { transform: translateY(-8px); } }
  }

  /* ── Sambutan ── */
  .kepsek-photo { position: relative; border-radius: 24px; overflow: hidden; aspect-ratio: 4 / 5; background: var(--primary-soft); max-width: 420px; }
  .kepsek-photo img { width: 100%; height: 100%; object-fit: cover; }
  .kepsek-photo .initials { position: absolute; inset: 0; display: grid; place-items: center; font-size: 5rem; font-weight: 800; color: var(--primary); letter-spacing: -.04em; }
  .kepsek-name {
    position: absolute; left: 16px; right: 16px; bottom: 16px;
    background: rgba(255, 255, 255, .92); backdrop-filter: blur(8px);
    border-radius: 16px; padding: .9rem 1.1rem;
  }
  .kepsek-name strong { display: block; color: var(--ink); }
  .kepsek-name span { font-size: .85rem; color: var(--text-muted); }
  .quote { position: relative; font-size: clamp(1.1rem, 1.6vw, 1.3rem); line-height: 1.8; color: var(--text-dark); font-weight: 500; }
  .quote-mark { font-size: 4.5rem; line-height: .6; color: var(--accent); font-family: Georgia, serif; display: block; margin-bottom: 1rem; }

  /* ── Program ── */
  .prog-card {
    position: relative; height: 100%; display: flex; flex-direction: column;
    background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 1.75rem;
    text-decoration: none; color: inherit; transition: border-color .2s, box-shadow .2s, transform .2s;
  }
  .prog-card:hover { border-color: transparent; box-shadow: var(--shadow); transform: translateY(-4px); color: inherit; }
  .prog-card .num { position: absolute; top: 1.5rem; right: 1.75rem; font-weight: 800; color: var(--line); font-size: 1.6rem; letter-spacing: -.03em; transition: color .2s; }
  .prog-card:hover .num { color: var(--accent); }
  .prog-ic { width: 52px; height: 52px; border-radius: 14px; display: grid; place-items: center; background: var(--primary-soft); color: var(--primary); font-size: 1.3rem; margin-bottom: 1.25rem; transition: background .2s, color .2s; }
  .prog-card:hover .prog-ic { background: var(--primary); color: #fff; }
  .prog-card h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: .6rem; padding-right: 2.5rem; }
  .prog-card p { color: var(--text-muted); font-size: .95rem; line-height: 1.65; margin-bottom: 1.25rem; flex-grow: 1; }
  .prog-card .more { font-weight: 700; font-size: .9rem; color: var(--primary); }

  /* ── Prestasi & ekskul ── */
  .panel { background: #fff; border: 1px solid var(--line); border-radius: var(--radius); padding: 1.75rem; height: 100%; }
  .panel h3 { font-size: 1.3rem; font-weight: 800; }
  .ach { display: flex; gap: 1rem; padding: 1rem 0; border-top: 1px solid var(--line); }
  .ach:first-of-type { border-top: 0; padding-top: .25rem; }
  .ach-ic { width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0; display: grid; place-items: center; background: var(--accent-soft); color: var(--accent-dark); font-size: 1.1rem; }
  .ach h4 { font-size: 1rem; font-weight: 700; margin: 0 0 .3rem; }
  .ach .meta { font-size: .85rem; color: var(--text-muted); display: flex; flex-wrap: wrap; gap: .35rem .6rem; align-items: center; }
  .tingkat { font-size: .72rem; font-weight: 700; padding: .15rem .55rem; border-radius: 999px; }
  .chips { display: flex; flex-wrap: wrap; gap: .6rem; }
  .chip {
    display: inline-flex; align-items: center; gap: .6rem; padding: .55rem 1rem .55rem .55rem;
    background: var(--bg-light); border: 1px solid var(--line); border-radius: 999px; font-weight: 600; font-size: .92rem; color: var(--text-dark);
  }
  .chip .ic { width: 30px; height: 30px; border-radius: 50%; background: #fff; display: grid; place-items: center; color: var(--primary); font-size: .8rem; box-shadow: var(--shadow-sm); }
  .empty { color: var(--text-muted); background: var(--bg-light); border-radius: var(--radius-sm); padding: 1.5rem; text-align: center; font-size: .95rem; }

  /* ── Galeri ── */
  .gallery { display: grid; gap: 1rem; grid-template-columns: repeat(4, 1fr); grid-auto-rows: 200px; }
  .gallery a:first-child { grid-column: span 2; grid-row: span 2; }
  .g-item {
    position: relative; display: block; border-radius: var(--radius); overflow: hidden; text-decoration: none;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
  }
  .g-item:nth-child(3n+2) { background: linear-gradient(135deg, #1b5d8f, #3b82b8); }
  .g-item:nth-child(3n) { background: linear-gradient(135deg, #b45309, var(--accent)); }
  .g-item img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
  .g-item:hover img { transform: scale(1.05); }
  .g-item .ph { position: absolute; inset: 0; display: grid; place-items: center; color: rgba(255, 255, 255, .35); font-size: 2.5rem; }
  .g-item .cap { position: absolute; inset: auto 0 0 0; padding: 2.5rem 1.1rem 1rem; color: #fff; background: linear-gradient(to top, rgba(7, 31, 51, .8), transparent); }
  .g-item .cap strong { display: block; font-size: 1rem; line-height: 1.3; }
  .g-item .cap span { font-size: .8rem; opacity: .8; }

  /* ── Berita ── */
  .news { display: flex; flex-direction: column; height: 100%; text-decoration: none; color: inherit; }
  .news-img { position: relative; aspect-ratio: 16 / 10; border-radius: var(--radius); overflow: hidden; background: var(--primary-soft); margin-bottom: 1.1rem; }
  .news-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
  .news:hover .news-img img { transform: scale(1.04); }
  .news-img .ph { position: absolute; inset: 0; display: grid; place-items: center; color: var(--primary-light); opacity: .35; font-size: 3rem; }
  .news-img .cat { position: absolute; top: 14px; left: 14px; background: #fff; color: var(--ink); font-size: .75rem; font-weight: 700; padding: .3rem .7rem; border-radius: 999px; box-shadow: var(--shadow-sm); }
  .news time { font-size: .85rem; color: var(--text-muted); font-weight: 500; }
  .news h3 { font-size: 1.15rem; font-weight: 700; line-height: 1.4; margin: .4rem 0 .5rem; transition: color .15s; }
  .news:hover h3 { color: var(--primary-light); }
  .news p { color: var(--text-muted); font-size: .95rem; line-height: 1.65; margin: 0; }

  /* ── CTA ── */
  .cta {
    position: relative; overflow: hidden; border-radius: 28px; padding: clamp(2rem, 5vw, 3.5rem);
    background: linear-gradient(135deg, #071f33 0%, var(--primary) 60%, var(--primary-light) 100%); color: #fff;
  }
  .cta::after {
    content: ''; position: absolute; width: 420px; height: 420px; right: -120px; top: -160px; border-radius: 50%;
    background: radial-gradient(circle, rgba(245, 158, 11, .45), transparent 65%);
  }
  .cta > * { position: relative; z-index: 1; }
  .cta h2 { color: #fff; font-size: clamp(1.6rem, 3vw, 2.3rem); font-weight: 800; letter-spacing: -.03em; }
  .cta p { color: rgba(255, 255, 255, .75); font-size: 1.05rem; }

  @media (max-width: 991.98px) {
    .hero { padding: 48px 0 56px; }
    .section { padding: 72px 0; }
    .hero-visual { max-width: 460px; margin: 1rem auto 0; }
    .gallery { grid-template-columns: repeat(2, 1fr); grid-auto-rows: 160px; }
    .g-item:not(:first-child) .ph { display: none; }
  }
  @media (max-width: 575.98px) {
    .hero-stats { grid-template-columns: repeat(2, 1fr); row-gap: 1.25rem; }
    .hero-stats > div:nth-child(3) { padding-left: 0; border-left: 0; }
    .hero-visual { padding: 1rem 0 2rem 1rem; }
    .float-card { max-width: 200px; font-size: .78rem; padding: .7rem .8rem; }
    .float-b { right: -4px; }
    .gallery { grid-auto-rows: 130px; }
    .hero-actions .btn { width: 100%; }
  }
</style>
@endpush

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<section class="hero">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-6">
        @if($ppdbAktif)
          <a href="{{ route('ppdb.index') }}" class="hero-chip reveal">
            <span class="dot">PPDB {{ setting('ppdb_tahun_ajaran') }}</span> Pendaftaran sudah dibuka <i class="fas fa-arrow-right small"></i>
          </a>
        @else
          <span class="hero-chip reveal"><span class="dot">Sejak {{ $stats['tahun_berdiri'] }}</span> Website resmi sekolah</span>
        @endif

        <h1 class="reveal" style="--d:.05s">{{ setting('sekolah_nama', 'SIAKAD SMK') }}</h1>
        <p class="hero-lead reveal" style="--d:.1s">
          {{ setting('sekolah_tagline', 'Berkomitmen mencetak lulusan yang kompeten, berkarakter mulia, dan siap menghadapi tantangan dunia industri.') }}
        </p>

        <div class="hero-actions d-flex flex-wrap gap-2 reveal" style="--d:.15s">
          @if($ppdbAktif)
            <a href="{{ route('ppdb.index') }}" class="btn btn-accent btn-lg">Daftar PPDB <i class="fas fa-arrow-right ms-1"></i></a>
          @endif
          <a href="#program" class="btn {{ $ppdbAktif ? 'btn-outline-primary' : 'btn-primary' }} btn-lg">Lihat program keahlian</a>
        </div>

        <div class="hero-stats reveal" style="--d:.2s">
          <div><div class="stat-num" data-count="{{ $stats['siswa'] }}">{{ number_format($stats['siswa'], 0, ',', '.') }}</div><div class="stat-lbl">Siswa aktif</div></div>
          <div><div class="stat-num" data-count="{{ $stats['guru'] }}">{{ number_format($stats['guru'], 0, ',', '.') }}</div><div class="stat-lbl">Pendidik</div></div>
          <div><div class="stat-num" data-count="{{ $stats['jurusan'] }}">{{ number_format($stats['jurusan'], 0, ',', '.') }}</div><div class="stat-lbl">Program keahlian</div></div>
          <div><div class="stat-num" data-count="{{ $stats['prestasi'] }}">{{ number_format($stats['prestasi'], 0, ',', '.') }}</div><div class="stat-lbl">Prestasi</div></div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="hero-visual reveal" style="--d:.1s">
          <div class="hero-panel {{ $heroBg ? 'has-img' : '' }}" @if($heroBg) style="background-image:url('{{ $heroBg }}')" @endif>
            @unless($heroBg)
              <div class="rings" aria-hidden="true">
                <span style="width:70%;aspect-ratio:1"></span>
                <span style="width:100%;aspect-ratio:1"></span>
                <span style="width:132%;aspect-ratio:1"></span>
              </div>
              <div class="emblem">
                @if(setting_url('logo_putih') || setting_url('logo'))
                  <img src="{{ setting_url('logo_putih') ?: setting_url('logo') }}" alt="Logo {{ setting('sekolah_nama') }}">
                @else
                  <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                @endif
              </div>
            @endunless
          </div>

          <div class="float-card float-b">
            <div class="ic" style="background:var(--accent-soft);color:var(--accent-dark)"><i class="fas fa-trophy"></i></div>
            <div><strong>{{ number_format($stats['prestasi'], 0, ',', '.') }} prestasi</strong><span>diraih siswa kami</span></div>
          </div>
          <div class="float-card float-a">
            <div class="ic" style="background:var(--primary-soft);color:var(--primary)"><i class="fas fa-layer-group"></i></div>
            <div><strong>{{ $stats['jurusan'] }} program keahlian</strong><span>&amp; {{ $stats['ekskul'] }} ekstrakurikuler</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── Sambutan kepala sekolah ──────────────────────────────────────────── --}}
@if(setting('sambutan_kepsek') || setting('nama_kepsek'))
@php
  $namaKepsek = setting('nama_kepsek', 'Kepala Sekolah');
  $inisial = collect(preg_split('/\s+/', preg_replace('/^(drs?|dra|ir|prof|h|hj)\.?\s+/i', '', $namaKepsek)))
    ->filter(fn ($k) => preg_match('/^\p{L}/u', $k))->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
@endphp
<section class="section">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-5 reveal">
        <div class="kepsek-photo mx-auto mx-lg-0">
          @if(setting_url('foto_kepsek'))
            <img src="{{ setting_url('foto_kepsek') }}" alt="Foto {{ $namaKepsek }}">
          @else
            <div class="initials" aria-hidden="true">{{ $inisial ?: 'KS' }}</div>
          @endif
          <div class="kepsek-name">
            <strong>{{ $namaKepsek }}</strong>
            <span>Kepala Sekolah</span>
          </div>
        </div>
      </div>
      <div class="col-lg-7 reveal" style="--d:.1s">
        <span class="eyebrow">Sambutan kepala sekolah</span>
        <h2 class="section-title mt-2 mb-4">Membangun generasi yang siap berkarya</h2>
        @if(setting('sambutan_kepsek'))
          <blockquote class="quote mb-0">
            <span class="quote-mark" aria-hidden="true">&ldquo;</span>
            {{ Str::limit(html_entity_decode(strip_tags(setting('sambutan_kepsek'))), 480) }}
          </blockquote>
        @endif
        <a href="{{ route('landing.tentang') }}" class="link-arrow mt-4">Kenali sekolah kami <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>
  </div>
</section>
@endif

{{-- ── Program keahlian ─────────────────────────────────────────────────── --}}
<section class="section bg-soft" id="program">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow">Program keahlian</span>
        <h2 class="section-title mt-2">Pilih jalur keahlianmu</h2>
        <p class="section-subtitle">Kurikulum yang disusun bersama dunia industri, dengan praktik langsung sejak kelas X.</p>
      </div>
      <a href="{{ route('landing.program') }}" class="link-arrow">Semua program <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="row g-4">
      @forelse($jurusans as $j)
        <div class="col-md-6 col-lg-4 reveal" style="--d:{{ ($loop->index % 3) * .06 }}s">
          <a href="{{ route('landing.program') }}" class="prog-card">
            <span class="num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            <div class="prog-ic"><i class="{{ $j->icon ?: 'fas fa-laptop-code' }}"></i></div>
            <h3>{{ $j->nama_jurusan }}</h3>
            <p>{{ $j->deskripsi_publik ? Str::limit($j->deskripsi_publik, 130) : 'Program keahlian yang mempersiapkan siswa menjadi tenaga profesional di bidangnya.' }}</p>
            <span class="more">Pelajari <i class="fas fa-arrow-right ms-1 small"></i></span>
          </a>
        </div>
      @empty
        <div class="col-12"><div class="empty">Belum ada data program keahlian.</div></div>
      @endforelse
    </div>
  </div>
</section>

{{-- ── Prestasi & ekstrakurikuler ───────────────────────────────────────── --}}
<section class="section">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-7 reveal">
        <div class="panel">
          <span class="eyebrow">Pencapaian</span>
          <h3 class="mt-2 mb-3">Prestasi terbaru</h3>
          @forelse($prestasis->take(4) as $p)
            @php [$bg, $fg] = $warnaTingkat[$p->tingkat] ?? ['#e2e8f0', '#334155']; @endphp
            <div class="ach">
              <div class="ach-ic"><i class="fas fa-trophy"></i></div>
              <div>
                <h4>{{ $p->nama_prestasi }}</h4>
                <div class="meta">
                  <span class="tingkat" style="background:{{ $bg }};color:{{ $fg }}">{{ $p->tingkat }}</span>
                  <span>{{ $p->siswa->nama ?? 'Siswa' }}</span>
                  <span><i class="far fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($p->tanggal)->locale('id')->translatedFormat('F Y') }}</span>
                </div>
              </div>
            </div>
          @empty
            <div class="empty">Belum ada data prestasi.</div>
          @endforelse
        </div>
      </div>

      <div class="col-lg-5 reveal" style="--d:.08s">
        <div class="panel">
          <span class="eyebrow">Pengembangan diri</span>
          <h3 class="mt-2 mb-2">Ekstrakurikuler</h3>
          <p class="text-muted mb-4" style="font-size:.95rem">Wadah siswa mengasah minat, bakat, dan kepemimpinan di luar kelas.</p>
          @if($ekskuls->isNotEmpty())
            <div class="chips">
              @foreach($ekskuls as $e)
                <span class="chip" @if($e->pembina) title="Pembina: {{ $e->pembina->nama }}" @endif>
                  <span class="ic"><i class="fas fa-star"></i></span>{{ $e->nama }}
                </span>
              @endforeach
            </div>
          @else
            <div class="empty">Belum ada data ekstrakurikuler.</div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── Galeri ───────────────────────────────────────────────────────────── --}}
@if($galeriTerbaru->isNotEmpty())
<section class="section-tight bg-soft">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow">Galeri</span>
        <h2 class="section-title mt-2">Momen di sekolah</h2>
      </div>
      <a href="{{ route('galeri.index') }}" class="link-arrow">Semua album <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="gallery reveal">
      @foreach($galeriTerbaru as $g)
        <a href="{{ route('galeri.show', $g) }}" class="g-item">
          @if($g->cover_url)
            <img src="{{ $g->cover_url }}" alt="{{ $g->nama }}" loading="lazy">
          @else
            <span class="ph" aria-hidden="true"><i class="far fa-images"></i></span>
          @endif
          <span class="cap">
            <strong>{{ $g->nama }}</strong>
            <span>{{ $g->items_count }} foto @if($g->tanggal)· {{ $g->tanggal->locale('id')->translatedFormat('d M Y') }}@endif</span>
          </span>
        </a>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── Berita ───────────────────────────────────────────────────────────── --}}
@if($artikelTerbaru->isNotEmpty())
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <span class="eyebrow">Kabar terbaru</span>
        <h2 class="section-title mt-2">Berita &amp; pengumuman</h2>
      </div>
      <a href="{{ route('berita.index') }}" class="link-arrow">Semua berita <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="row g-4 g-lg-5">
      @foreach($artikelTerbaru as $a)
        <div class="col-md-6 col-lg-4 reveal" style="--d:{{ $loop->index * .06 }}s">
          <a href="{{ route('berita.show', $a) }}" class="news">
            <div class="news-img">
              @if($a->thumbnail_url)
                <img src="{{ $a->thumbnail_url }}" alt="" loading="lazy">
              @else
                <span class="ph" aria-hidden="true"><i class="far fa-newspaper"></i></span>
              @endif
              <span class="cat">{{ $a->kategori_label }}</span>
            </div>
            <time datetime="{{ \Carbon\Carbon::parse($a->published_at)->toDateString() }}">{{ \Carbon\Carbon::parse($a->published_at)->locale('id')->translatedFormat('d F Y') }}</time>
            <h3>{{ Str::limit($a->judul, 80) }}</h3>
            <p>{{ Str::limit($a->ringkasan_auto, 110) }}</p>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── CTA ──────────────────────────────────────────────────────────────── --}}
<section class="pb-5 {{ $artikelTerbaru->isEmpty() ? 'pt-5' : '' }}">
  <div class="container pb-4">
    <div class="cta reveal">
      <div class="row align-items-center g-4">
        <div class="col-lg-8">
          @if($ppdbAktif)
            <span class="badge rounded-pill mb-3" style="background:var(--accent);color:var(--ink)">PPDB {{ setting('ppdb_tahun_ajaran') }}</span>
            <h2 class="mb-2">Siap bergabung bersama kami?</h2>
            <p class="mb-0">Pendaftaran peserta didik baru tahun ajaran {{ setting('ppdb_tahun_ajaran') }} sudah dibuka. Daftar secara online dan pantau statusnya kapan saja.</p>
          @else
            <h2 class="mb-2">Ingin tahu lebih banyak?</h2>
            <p class="mb-0">Hubungi kami atau kunjungi sekolah untuk informasi program, kegiatan, dan penerimaan siswa baru.</p>
          @endif
        </div>
        <div class="col-lg-4 text-lg-end">
          @if($ppdbAktif)
            <a href="{{ route('ppdb.index') }}" class="btn btn-accent btn-lg px-4">Daftar sekarang <i class="fas fa-arrow-right ms-1"></i></a>
          @else
            <a href="{{ route('ppdb.landing') }}" class="btn btn-light btn-lg px-4">Info PPDB <i class="fas fa-arrow-right ms-1"></i></a>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
  // Angka statistik sudah benar dari server; animasi hitung hanya pemanis.
  (() => {
    if (!('IntersectionObserver' in window) || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const fmt = n => n.toLocaleString('id-ID');
    const io = new IntersectionObserver(entries => entries.forEach(e => {
      if (!e.isIntersecting) return;
      io.unobserve(e.target);
      const el = e.target, target = +el.dataset.count, t0 = performance.now(), dur = 900;
      if (!target) return;
      const tick = now => {
        const p = Math.min((now - t0) / dur, 1);
        el.textContent = fmt(Math.round(target * (1 - Math.pow(1 - p, 3))));
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    }), { threshold: .6 });
    document.querySelectorAll('[data-count]').forEach(el => io.observe(el));
  })();
</script>
@endpush
