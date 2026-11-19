@extends('layouts.public')
@section('title', 'PPDB ' . setting('ppdb_tahun_ajaran') . ' — ' . setting('sekolah_nama'))
@section('page_title', 'Portal PPDB')
@section('meta_desc', 'Penerimaan Peserta Didik Baru ' . setting('sekolah_nama') . ' tahun ajaran ' . setting('ppdb_tahun_ajaran') . '. Daftar online, cek status, dan lihat persyaratan.')

@include('ppdb.partials.gaya')

@php
  $sisa = $gelombangAktif?->sisa_kuota;
  $persenKuota = ($gelombangAktif && $gelombangAktif->kuota > 0 && $sisa !== null)
      ? round(($gelombangAktif->kuota - $sisa) / $gelombangAktif->kuota * 100) : null;
  $berikutnya = $gelombangSemua->first(fn ($g) => $g->tanggal_buka->isFuture());
  $alur = [
    ['fas fa-file-pen', 'Isi formulir', 'Lengkapi data diri dan pilih jurusan secara online.'],
    ['fas fa-cloud-arrow-up', 'Unggah berkas', 'Masuk ke portal pendaftar, lalu unggah foto dan dokumen.'],
    ['fas fa-clipboard-check', 'Verifikasi', 'Panitia memeriksa kelengkapan dan keabsahan berkas.'],
    ['fas fa-bullhorn', 'Pengumuman', 'Pantau hasil seleksi lewat menu cek status.'],
    ['fas fa-school', 'Daftar ulang', 'Datang ke sekolah untuk daftar ulang setelah diterima.'],
  ];
@endphp

@push('styles')
<style>
  .ppdb-hero {
    position: relative; overflow: hidden; color: #fff; padding: 72px 0 120px;
    background:
      radial-gradient(800px 420px at 100% 0%, rgba(245, 158, 11, .35), transparent 60%),
      radial-gradient(600px 400px at 0% 100%, rgba(59, 130, 184, .35), transparent 60%),
      linear-gradient(140deg, #061a2b 0%, var(--primary) 60%, #14507e 100%);
  }
  .ppdb-hero::before {
    content: ''; position: absolute; inset: 0; pointer-events: none;
    background-image: radial-gradient(rgba(255, 255, 255, .07) 1px, transparent 1px); background-size: 24px 24px;
  }
  .ppdb-hero .container { position: relative; }
  .ppdb-hero .tag { display: inline-flex; align-items: center; gap: .5rem; padding: .35rem .85rem; border-radius: 999px; background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .18); font-size: .85rem; font-weight: 600; }
  .ppdb-hero .tag .live { width: 8px; height: 8px; border-radius: 50%; background: #4ade80; box-shadow: 0 0 0 4px rgba(74, 222, 128, .25); }
  .ppdb-hero .tag .off { width: 8px; height: 8px; border-radius: 50%; background: #fbbf24; }
  .ppdb-hero h1 { color: #fff; font-weight: 800; font-size: clamp(2.2rem, 5vw, 3.6rem); line-height: 1.05; letter-spacing: -.04em; margin: 1.25rem 0 1rem; }
  .ppdb-hero h1 em { font-style: normal; color: var(--accent); }
  .ppdb-hero .lead { color: rgba(255, 255, 255, .75); font-size: 1.1rem; line-height: 1.7; max-width: 520px; margin-bottom: 2rem; }
  .btn-ghost { background: rgba(255, 255, 255, .1); color: #fff; border: 1px solid rgba(255, 255, 255, .28); }
  .btn-ghost:hover { background: rgba(255, 255, 255, .18); color: #fff; }

  .status-card { background: #fff; color: var(--text-dark); border-radius: 24px; padding: 1.75rem; box-shadow: 0 40px 80px -30px rgba(0, 0, 0, .55); }
  .status-card .head { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.25rem; }
  .status-card h2 { font-size: 1.15rem; font-weight: 800; margin: 0; color: var(--ink); }
  .cd { display: grid; grid-template-columns: repeat(4, 1fr); gap: .6rem; }
  .cd div { background: var(--bg-light); border: 1px solid var(--line); border-radius: 16px; padding: .9rem .25rem; text-align: center; }
  .cd b { display: block; font-size: clamp(1.6rem, 3.4vw, 2.2rem); font-weight: 800; color: var(--ink); line-height: 1; font-variant-numeric: tabular-nums; letter-spacing: -.03em; }
  .cd span { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); }
  .meter { height: 8px; border-radius: 999px; background: var(--bg-gray); overflow: hidden; }
  .meter > span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--accent), var(--accent-dark)); }
  .closed-ic { width: 56px; height: 56px; border-radius: 16px; background: var(--accent-soft); color: var(--accent-dark); display: grid; place-items: center; font-size: 1.4rem; }

  .quick { margin-top: -64px; position: relative; z-index: 2; }
  .quick a {
    display: flex; gap: 1rem; align-items: center; height: 100%; padding: 1.25rem 1.35rem; border-radius: 20px;
    background: #fff; border: 1px solid var(--line); box-shadow: var(--shadow); text-decoration: none; color: inherit; transition: transform .2s, border-color .2s;
  }
  .quick a:hover { transform: translateY(-3px); border-color: var(--primary-light); }
  .quick .ic { width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center; flex-shrink: 0; font-size: 1.15rem; }
  .quick strong { display: block; color: var(--ink); font-size: 1rem; }
  .quick span { color: var(--text-muted); font-size: .87rem; }
  .quick .go { margin-left: auto; color: var(--text-muted); transition: transform .2s; }
  .quick a:hover .go { transform: translateX(3px); color: var(--primary); }

  .sec { padding: 88px 0; }
  .sec-head { max-width: 640px; margin-bottom: 2.5rem; }

  .wave-card { height: 100%; border: 1px solid var(--line); border-radius: 20px; padding: 1.5rem; background: #fff; display: flex; flex-direction: column; }
  .wave-card.open { border-color: #86efac; box-shadow: 0 0 0 4px rgba(134, 239, 172, .25); }
  .wave-card.past { background: var(--bg-light); }
  .wave-card.past h3, .wave-card.past .dates { opacity: .6; }
  .wave-card h3 { font-size: 1.1rem; font-weight: 800; margin: 0; color: var(--ink); }
  .dates { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: .5rem; margin: 1.25rem 0; }
  .dates small { display: block; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); font-weight: 600; }
  .dates strong { color: var(--ink); font-size: .95rem; }
  .dates .to { color: var(--text-muted); }

  .steps { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.25rem; position: relative; counter-reset: s; }
  .steps::before { content: ''; position: absolute; top: 27px; left: 10%; right: 10%; height: 2px; background: repeating-linear-gradient(90deg, var(--line) 0 8px, transparent 8px 14px); }
  .step { position: relative; text-align: center; }
  .step .ic { width: 56px; height: 56px; border-radius: 18px; margin: 0 auto 1rem; display: grid; place-items: center; background: #fff; border: 1px solid var(--line); color: var(--primary); font-size: 1.2rem; box-shadow: var(--shadow-sm); position: relative; }
  .step .ic::after { counter-increment: s; content: counter(s); position: absolute; top: -8px; right: -8px; width: 22px; height: 22px; border-radius: 50%; background: var(--accent); color: var(--ink); font-size: .72rem; font-weight: 800; display: grid; place-items: center; }
  .step h3 { font-size: 1rem; font-weight: 700; margin-bottom: .35rem; color: var(--ink); }
  .step p { font-size: .88rem; color: var(--text-muted); line-height: 1.6; margin: 0; }

  .jur { display: flex; gap: .9rem; align-items: center; padding: 1rem 1.1rem; border: 1px solid var(--line); border-radius: 16px; background: #fff; height: 100%; }
  .jur .ic { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; background: var(--primary-soft); color: var(--primary); flex-shrink: 0; }
  .jur strong { display: block; color: var(--ink); font-size: .97rem; line-height: 1.3; }
  .jur span { color: var(--text-muted); font-size: .84rem; }

  .check { display: flex; gap: .85rem; padding: 1rem 0; border-top: 1px solid var(--line); }
  .check:first-child { border-top: 0; padding-top: 0; }
  .check i { color: #16a34a; margin-top: .25rem; }
  .check strong { color: var(--ink); display: block; }
  .check span { color: var(--text-muted); font-size: .9rem; line-height: 1.6; }

  .faq .accordion-item { border: 1px solid var(--line); border-radius: 16px !important; overflow: hidden; margin-bottom: .75rem; }
  .faq .accordion-button { font-weight: 700; color: var(--ink); padding: 1.1rem 1.25rem; background: #fff; box-shadow: none; }
  .faq .accordion-button:not(.collapsed) { color: var(--primary); background: #fff; }
  .faq .accordion-button:focus { box-shadow: none; }
  .faq .accordion-body { color: var(--text-muted); line-height: 1.75; padding: 0 1.25rem 1.25rem; }

  @media (max-width: 991.98px) {
    .steps { grid-template-columns: 1fr; gap: 0; }
    .steps::before { top: 28px; bottom: 28px; left: 27px; right: auto; width: 2px; height: auto; background: repeating-linear-gradient(180deg, var(--line) 0 8px, transparent 8px 14px); }
    .step { display: grid; grid-template-columns: 56px 1fr; gap: 1rem; text-align: left; padding-bottom: 1.5rem; }
    .step .ic { margin: 0; }
  }
  @media (max-width: 575.98px) {
    .ppdb-hero { padding: 48px 0 104px; }
    .ppdb-hero .btn { width: 100%; }
    .sec { padding: 64px 0; }
    .status-card { padding: 1.25rem; }
  }
</style>
@endpush

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
<section class="ppdb-hero">
  <div class="container">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-6">
        <span class="tag reveal">
          @if($gelombangAktif)<span class="live"></span> {{ $gelombangAktif->nama }} sedang dibuka
          @else<span class="off"></span> PPDB {{ setting('ppdb_tahun_ajaran') }}@endif
        </span>
        <h1 class="reveal" style="--d:.05s">Penerimaan Peserta Didik Baru <em>{{ setting('ppdb_tahun_ajaran') }}</em></h1>
        <p class="lead reveal" style="--d:.1s">
          Mulai perjalananmu di {{ setting('sekolah_nama', 'SIAKAD SMK') }}. Daftar online, unggah berkas dari rumah, dan pantau hasil seleksi kapan saja.
        </p>
        <div class="d-flex flex-wrap gap-2 reveal" style="--d:.15s">
          @if($gelombangAktif)
            <a href="{{ route('ppdb.index') }}" class="btn btn-accent btn-lg px-4">Daftar sekarang <i class="fas fa-arrow-right ms-1"></i></a>
          @endif
          <a href="{{ route('ppdb.cek') }}" class="btn btn-ghost btn-lg px-4"><i class="fas fa-magnifying-glass me-1"></i> Cek status</a>
        </div>
      </div>

      <div class="col-lg-5 offset-lg-1">
        <div class="status-card reveal" style="--d:.1s">
          @if($gelombangAktif)
            <div class="head">
              <h2>Pendaftaran ditutup dalam</h2>
              <span class="pill pill-success">Dibuka</span>
            </div>
            <div class="cd" id="countdown" data-deadline="{{ $gelombangAktif->tanggal_tutup->toIso8601String() }}" aria-live="off">
              <div><b data-u="d">--</b><span>Hari</span></div>
              <div><b data-u="h">--</b><span>Jam</span></div>
              <div><b data-u="m">--</b><span>Menit</span></div>
              <div><b data-u="s">--</b><span>Detik</span></div>
            </div>
            <p class="muted small mt-3 mb-0"><i class="far fa-calendar me-1"></i> Ditutup {{ $gelombangAktif->tanggal_tutup->locale('id')->translatedFormat('l, d F Y · H:i') }} WIB</p>
            @if($persenKuota !== null)
              <div class="mt-4">
                <div class="d-flex justify-content-between small mb-2">
                  <span class="fw-semibold">Kuota terisi</span>
                  <span class="muted">Sisa <strong class="text-dark">{{ $sisa }}</strong> dari {{ $gelombangAktif->kuota }}</span>
                </div>
                <div class="meter" role="progressbar" aria-valuenow="{{ $persenKuota }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ max($persenKuota, 2) }}%"></span></div>
              </div>
            @endif
          @else
            <div class="d-flex gap-3 align-items-start">
              <div class="closed-ic"><i class="far fa-calendar-xmark"></i></div>
              <div>
                <h2 class="mb-1">Pendaftaran belum dibuka</h2>
                @if($berikutnya)
                  <p class="muted mb-0">{{ $berikutnya->nama }} dibuka <strong class="text-dark">{{ $berikutnya->tanggal_buka->locale('id')->translatedFormat('d F Y') }}</strong>.</p>
                @else
                  <p class="muted mb-0">Jadwal gelombang berikutnya akan diumumkan di halaman ini.</p>
                @endif
              </div>
            </div>
            <div class="note note-info mt-4 mb-0">
              <i class="fas fa-circle-info"></i>
              <span>Sudah pernah mendaftar? Gunakan <a href="{{ route('ppdb.cek') }}" class="fw-semibold">cek status</a> untuk melihat hasil seleksi.</span>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── Akses cepat ──────────────────────────────────────────────────────── --}}
<div class="container quick">
  <div class="row g-3">
    <div class="col-md-4 reveal">
      <a href="{{ route('ppdb.index') }}">
        <span class="ic" style="background:var(--accent-soft);color:var(--accent-dark)"><i class="fas fa-file-pen"></i></span>
        <span><strong>Formulir pendaftaran</strong><span>Isi data diri &amp; pilihan jurusan</span></span>
        <i class="fas fa-arrow-right go"></i>
      </a>
    </div>
    <div class="col-md-4 reveal" style="--d:.05s">
      <a href="{{ route('ppdb.cek') }}">
        <span class="ic" style="background:var(--primary-soft);color:var(--primary)"><i class="fas fa-magnifying-glass"></i></span>
        <span><strong>Cek status</strong><span>Dengan nomor pendaftaran atau NISN</span></span>
        <i class="fas fa-arrow-right go"></i>
      </a>
    </div>
    <div class="col-md-4 reveal" style="--d:.1s">
      <a href="{{ route('ppdb.syarat') }}">
        <span class="ic" style="background:#dcfce7;color:#166534"><i class="fas fa-list-check"></i></span>
        <span><strong>Persyaratan</strong><span>Dokumen yang perlu disiapkan</span></span>
        <i class="fas fa-arrow-right go"></i>
      </a>
    </div>
  </div>
</div>

{{-- ── Gelombang ────────────────────────────────────────────────────────── --}}
@if($gelombangSemua->isNotEmpty())
<section class="sec">
  <div class="container">
    <div class="sec-head reveal">
      <span class="eyebrow">Jadwal</span>
      <h2 class="section-title mt-2">Gelombang pendaftaran</h2>
      <p class="section-subtitle mb-0">Pendaftaran dibuka dalam beberapa gelombang. Daftar lebih awal agar kuota tidak terlewat.</p>
    </div>
    <div class="row g-4">
      @foreach($gelombangSemua as $g)
        <div class="col-md-6 col-lg-4 reveal" style="--d:{{ ($loop->index % 3) * .06 }}s">
          <div class="wave-card {{ $g->is_buka ? 'open' : ($g->tanggal_tutup->isPast() ? 'past' : '') }}">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <h3>{{ $g->nama }}</h3>
              <span class="pill pill-{{ $g->status_color }}">{{ $g->status_label }}</span>
            </div>
            <div class="dates">
              <div><small>Dibuka</small><strong>{{ $g->tanggal_buka->locale('id')->translatedFormat('d M Y') }}</strong></div>
              <i class="fas fa-arrow-right to"></i>
              <div><small>Ditutup</small><strong>{{ $g->tanggal_tutup->locale('id')->translatedFormat('d M Y') }}</strong></div>
            </div>
            @if($g->kuota > 0)
              <p class="muted small mb-2"><i class="fas fa-users me-1"></i> Kuota {{ $g->kuota }} siswa</p>
            @endif
            @if($g->keterangan)
              <p class="muted small mb-0">{{ $g->keterangan }}</p>
            @endif
            @if($g->is_buka)
              <a href="{{ route('ppdb.index') }}" class="btn btn-primary w-100 mt-auto pt-2" style="margin-top:1.25rem !important">Daftar di gelombang ini</a>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── Alur ─────────────────────────────────────────────────────────────── --}}
<section class="sec bg-soft" style="background:var(--bg-light)">
  <div class="container">
    <div class="sec-head reveal">
      <span class="eyebrow">Alur</span>
      <h2 class="section-title mt-2">Lima langkah menuju bangku sekolah</h2>
    </div>
    <div class="steps">
      @foreach($alur as [$ic, $judul, $desc])
        <div class="step reveal" style="--d:{{ $loop->index * .06 }}s">
          <div class="ic"><i class="{{ $ic }}"></i></div>
          <div>
            <h3>{{ $judul }}</h3>
            <p>{{ $desc }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── Jurusan & persyaratan ────────────────────────────────────────────── --}}
@if($jurusans->isNotEmpty() || $persyaratanList->isNotEmpty())
<section class="sec">
  <div class="container">
    <div class="row g-5">
      @if($jurusans->isNotEmpty())
      <div class="{{ $persyaratanList->isNotEmpty() ? 'col-lg-7' : 'col-12' }}">
        <div class="reveal">
          <span class="eyebrow">Program keahlian</span>
          <h2 class="section-title mt-2 mb-4">Jurusan yang dibuka</h2>
        </div>
        <div class="row g-3">
          @foreach($jurusans as $j)
            <div class="col-sm-6 reveal" style="--d:{{ ($loop->index % 2) * .05 }}s">
              <div class="jur">
                <span class="ic"><i class="{{ $j->icon ?: 'fas fa-graduation-cap' }}"></i></span>
                <span>
                  <strong>{{ $j->nama_jurusan }}</strong>
                  @if($j->deskripsi_publik)<span>{{ Str::limit($j->deskripsi_publik, 70) }}</span>@endif
                </span>
              </div>
            </div>
          @endforeach
        </div>
      </div>
      @endif

      @if($persyaratanList->isNotEmpty())
      <div class="{{ $jurusans->isNotEmpty() ? 'col-lg-5' : 'col-12' }} reveal">
        <div class="ppdb-card ppdb-card-pad h-100">
          <span class="eyebrow">Persiapan</span>
          <h2 class="section-title mt-2 mb-4" style="font-size:1.6rem">Dokumen yang dibutuhkan</h2>
          @foreach($persyaratanList->take(6) as $p)
            <div class="check">
              <i class="fas fa-circle-check"></i>
              <div><strong>{{ $p->judul }}</strong>@if($p->deskripsi)<span>{{ $p->deskripsi }}</span>@endif</div>
            </div>
          @endforeach
          <a href="{{ route('ppdb.syarat') }}" class="link-arrow mt-3">Lihat persyaratan lengkap <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      @endif
    </div>
  </div>
</section>
@endif

{{-- ── FAQ ──────────────────────────────────────────────────────────────── --}}
@if($faqList->isNotEmpty())
<section class="sec" style="background:var(--bg-light)">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-4 reveal">
        <span class="eyebrow">FAQ</span>
        <h2 class="section-title mt-2">Pertanyaan yang sering diajukan</h2>
        <p class="section-subtitle">Belum menemukan jawabannya? Hubungi panitia PPDB
          @if(setting('sekolah_no_telp')) di <a href="tel:{{ preg_replace('/[^0-9+]/', '', setting('sekolah_no_telp')) }}">{{ setting('sekolah_no_telp') }}</a>@endif.</p>
      </div>
      <div class="col-lg-8">
        <div class="accordion faq reveal" id="faqAccordion">
          @foreach($faqList as $i => $faq)
            <div class="accordion-item">
              <h3 class="accordion-header" id="faq-h{{ $i }}">
                <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse"
                        data-bs-target="#faq-c{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="faq-c{{ $i }}">
                  {{ $faq->pertanyaan }}
                </button>
              </h3>
              <div id="faq-c{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" aria-labelledby="faq-h{{ $i }}" data-bs-parent="#faqAccordion">
                <div class="accordion-body">{{ $faq->jawaban }}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
@endif

{{-- ── CTA ──────────────────────────────────────────────────────────────── --}}
@if($gelombangAktif)
<section class="py-5">
  <div class="container py-3">
    <div class="ppdb-card ppdb-card-pad d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 reveal" style="border-radius:24px">
      <div>
        <h2 class="mb-1" style="font-size:1.5rem">Siap mendaftar?</h2>
        <p class="muted mb-0">Formulir hanya butuh sekitar 5 menit. Berkas bisa diunggah setelahnya.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('ppdb.cek') }}" class="btn btn-outline-primary btn-lg px-4">Cek status</a>
        <a href="{{ route('ppdb.index') }}" class="btn btn-accent btn-lg px-4">Daftar sekarang <i class="fas fa-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
</section>
@endif

@endsection

@push('scripts')
<script>
  (() => {
    const el = document.getElementById('countdown');
    if (!el) return;
    const deadline = new Date(el.dataset.deadline).getTime();
    const set = (u, v) => { el.querySelector(`[data-u="${u}"]`).textContent = String(v).padStart(2, '0'); };
    const tick = () => {
      const diff = deadline - Date.now();
      if (diff <= 0) { el.outerHTML = '<p class="fw-semibold mb-0">Pendaftaran gelombang ini sudah ditutup.</p>'; clearInterval(t); return; }
      set('d', Math.floor(diff / 864e5)); set('h', Math.floor(diff / 36e5) % 24);
      set('m', Math.floor(diff / 6e4) % 60); set('s', Math.floor(diff / 1e3) % 60);
    };
    const t = setInterval(tick, 1000); tick();
  })();
</script>
@endpush
