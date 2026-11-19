@extends('layouts.public')
@section('title', setting('sekolah_nama', 'SMK Negeri 48'))
@section('page_title', 'Beranda')
@section('meta_desc', setting('sekolah_tagline', ''))

@push('styles')
<style>
  html { scroll-behavior: smooth; }

  /* Modern Hero Section */
  .hero-section {
    position: relative;
    padding: 120px 0 80px;
    background: linear-gradient(135deg, rgba(15,60,92,0.95) 0%, rgba(24,90,138,0.9) 100%);
    overflow: hidden;
  }

  .hero-section::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url('{{ setting_url("hero_bg") }}') center/cover no-repeat;
    opacity: 0.3;
    z-index: 0;
  }

  .hero-content {
    position: relative;
    z-index: 2;
  }

  .hero-badge {
    background: rgba(243, 156, 18, 0.2);
    color: var(--accent);
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid rgba(243, 156, 18, 0.3);
  }

  .hero-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    text-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  .hero-subtitle {
    font-size: 1.15rem;
    color: rgba(255,255,255,0.9);
    line-height: 1.8;
    margin-bottom: 2.5rem;
    max-width: 90%;
  }

  /* Modern Stats Bar */
  .stats-bar {
    background: #fff;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    margin-top: -50px;
    position: relative;
    z-index: 10;
  }

  .stat-item {
    text-align: center;
    position: relative;
  }

  .stat-item:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 20%;
    height: 60%;
    width: 1px;
    background: var(--bg-gray);
  }

  .stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--primary);
    line-height: 1;
    margin-bottom: 0.5rem;
    font-family: 'Poppins', sans-serif;
  }

  .stat-label {
    color: var(--text-muted);
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Section Styles */
  .section-padding { padding: 80px 0; }
  .bg-light-gray { background-color: var(--bg-light); }

  /* Cards */
  .hover-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(0,0,0,0.05);
  }
  .hover-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
  }

  /* Feature Icons */
  .feature-icon-wrapper {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    background: rgba(15, 60, 92, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    color: var(--primary);
    font-size: 1.8rem;
    transition: all 0.3s;
  }

  .hover-card:hover .feature-icon-wrapper {
    background: var(--primary);
    color: #fff;
  }

  /* Sambutan Kepsek */
  .kepsek-wrapper {
    position: relative;
    padding: 2rem;
  }
  .kepsek-image {
    width: 100%;
    max-width: 320px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    position: relative;
    z-index: 2;
  }
  .kepsek-bg-shape {
    position: absolute;
    top: 0; right: 0;
    width: 80%; height: 80%;
    background: var(--accent);
    border-radius: 20px;
    z-index: 1;
    transform: translate(20px, -20px);
  }

  /* CTA Banner */
  .cta-banner {
    background: linear-gradient(135deg, var(--accent) 0%, #d35400 100%);
    border-radius: 20px;
    padding: 3rem;
    color: #fff;
    box-shadow: 0 15px 30px rgba(243, 156, 18, 0.2);
  }
</style>
@endpush

@section('content')

{{-- ── HERO SECTION ────────────────────────────────────────────────────────── --}}
<section class="hero-section" id="hero">
  <div class="container hero-content">
    <div class="row align-items-center">
      <div class="col-lg-7 py-5">
        <div class="hero-badge" data-aos="fade-up">
          <i class="fas fa-certificate"></i> Sekolah Unggulan & Berprestasi
        </div>
        <h1 class="hero-title" data-aos="fade-up" data-aos-delay="100">
          Selamat Datang di<br>
          <span style="color: var(--accent)">{{ setting('sekolah_nama', 'SMK Negeri 48') }}</span>
        </h1>
        <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="200">
          {{ setting('sekolah_tagline', 'Berkomitmen mencetak lulusan yang kompeten, berkarakter mulia, dan siap menghadapi tantangan dunia industri global.') }}
        </p>
        <div class="d-flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="300">
          @if(setting('ppdb_aktif') == '1')
            <a href="{{ route('ppdb.index') }}" class="btn btn-warning btn-lg px-4 py-3 fw-bold rounded-pill text-dark shadow-sm">
              <i class="fas fa-user-plus me-2"></i> Daftar PPDB Sekarang
            </a>
          @endif
          <a href="#program" class="btn btn-outline-light btn-lg px-4 py-3 fw-semibold rounded-pill">
            <i class="fas fa-compass me-2"></i> Jelajahi Program Kami
          </a>
        </div>
      </div>

      <div class="col-lg-5 d-none d-lg-block text-center" data-aos="zoom-in" data-aos-delay="200">
        @if(setting_url('logo'))
          <img src="{{ setting_url('logo') }}" class="img-fluid" style="max-height:400px; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.3));" alt="Logo {{ setting('sekolah_nama') }}">
        @endif
      </div>
    </div>
  </div>
</section>

{{-- ── STATS BAR ───────────────────────────────────────────────────────────── --}}
<div class="container">
  <div class="stats-bar">
    <div class="row g-4">
      <div class="col-6 col-md-3 stat-item">
        <div class="stat-number counter-num" data-target="{{ $stats['siswa'] }}">0</div>
        <div class="stat-label">Siswa Aktif</div>
      </div>
      <div class="col-6 col-md-3 stat-item">
        <div class="stat-number counter-num" data-target="{{ $stats['guru'] }}">0</div>
        <div class="stat-label">Tenaga Pendidik</div>
      </div>
      <div class="col-6 col-md-3 stat-item">
        <div class="stat-number counter-num" data-target="{{ $stats['jurusan'] }}">0</div>
        <div class="stat-label">Program Keahlian</div>
      </div>
      <div class="col-6 col-md-3 stat-item" style="border:none;">
        <div class="stat-number counter-num" data-target="{{ $stats['prestasi'] }}">0</div>
        <div class="stat-label">Prestasi Diraih</div>
      </div>
    </div>
  </div>
</div>

{{-- ── SAMBUTAN KEPSEK ─────────────────────────────────────────────────────── --}}
@if(setting('sambutan_kepsek') || setting('nama_kepsek'))
<section class="section-padding" id="sambutan">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5">
        <div class="kepsek-wrapper">
          <div class="kepsek-bg-shape d-none d-md-block"></div>
          @if(setting_url('foto_kepsek'))
            <img src="{{ setting_url('foto_kepsek') }}" class="kepsek-image" alt="Foto Kepala Sekolah">
          @else
            <div class="kepsek-image bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
              <i class="fas fa-user-tie fa-5x text-muted"></i>
            </div>
          @endif
        </div>
      </div>
      <div class="col-lg-7">
        <h6 class="text-warning fw-bold text-uppercase tracking-wider mb-2">Sambutan Kepala Sekolah</h6>
        <h2 class="section-title mb-4">Membangun Generasi Emas Masa Depan</h2>

        <div class="lead text-muted mb-4" style="font-size: 1.1rem; line-height: 1.8;">
          {!! Str::limit(strip_tags(setting('sambutan_kepsek')), 400) !!}
        </div>

        <div class="d-flex align-items-center mt-4">
          <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; color: white;">
            <i class="fas fa-quote-right"></i>
          </div>
          <div>
            <h5 class="fw-bold text-dark mb-0">{{ setting('nama_kepsek', 'Nama Kepala Sekolah') }}</h5>
            <span class="text-muted small">Kepala {{ setting('sekolah_nama') }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endif

{{-- ── PROGRAM KEAHLIAN ────────────────────────────────────────────────────── --}}
<section class="section-padding bg-light-gray" id="program">
  <div class="container">
    <div class="text-center mb-5">
      <h6 class="text-warning fw-bold text-uppercase mb-2">Program Studi</h6>
      <h2 class="section-title">Program Keahlian Kami</h2>
      <div class="section-divider"></div>
      <p class="section-subtitle mx-auto" style="max-width: 600px;">Pilihan program keahlian yang disesuaikan dengan kebutuhan industri masa kini dan masa depan.</p>
    </div>

    <div class="row g-4 justify-content-center">
      @forelse($jurusans as $j)
      <div class="col-lg-4 col-md-6">
        <div class="card h-100 border-0 rounded-4 hover-card bg-white p-4">
          <div class="feature-icon-wrapper">
            <i class="{{ $j->icon ?? 'fas fa-laptop-code' }}"></i>
          </div>
          <h4 class="fw-bold mb-3" style="color: var(--primary)">{{ $j->nama_jurusan }}</h4>
          <p class="text-muted mb-4 flex-grow-1" style="line-height: 1.7;">
            {{ $j->deskripsi_publik ? Str::limit($j->deskripsi_publik, 120) : 'Program keahlian unggulan yang mempersiapkan siswa menjadi tenaga profesional di bidangnya.' }}
          </p>
          <a href="{{ route('landing.program') }}" class="text-primary fw-semibold text-decoration-none">
            Pelajari Lebih Lanjut <i class="fas fa-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
      @empty
        <div class="col-12 text-center text-muted">Belum ada data program keahlian.</div>
      @endforelse
    </div>
  </div>
</section>

{{-- ── CTA PPDB ───────────────────────────────────────────────────────────── --}}
@if(setting('ppdb_aktif') == '1')
<section class="py-5">
  <div class="container">
    <div class="cta-banner position-relative overflow-hidden">
      <!-- Background elements -->
      <i class="fas fa-graduation-cap position-absolute" style="font-size: 15rem; color: rgba(255,255,255,0.1); right: -50px; top: -50px;"></i>

      <div class="row align-items-center position-relative z-index-1">
        <div class="col-lg-8 mb-4 mb-lg-0">
          <span class="badge bg-white text-warning fw-bold px-3 py-2 rounded-pill mb-3">PPDB {{ setting('ppdb_tahun_ajaran') }} BUKA</span>
          <h2 class="fw-bold mb-3" style="font-family: 'Poppins', sans-serif;">Mari Bergabung Bersama Kami!</h2>
          <p class="lead mb-0 opacity-90">Pendaftaran Peserta Didik Baru (PPDB) Tahun Ajaran {{ setting('ppdb_tahun_ajaran') }} telah resmi dibuka. Daftar sekarang juga.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
          <a href="{{ route('ppdb.index') }}" class="btn btn-light btn-lg px-5 py-3 fw-bold rounded-pill text-primary shadow">
            Daftar Sekarang <i class="fas fa-arrow-right ms-2"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
@endif

{{-- ── PRESTASI & EKSKUL (Combined Section) ────────────────────────────────── --}}
<section class="section-padding">
  <div class="container">
    <div class="row g-5">
      <!-- Prestasi -->
      <div class="col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <div>
            <h6 class="text-warning fw-bold text-uppercase mb-1">Pencapaian</h6>
            <h3 class="fw-bold" style="color: var(--primary);">Prestasi Siswa</h3>
          </div>
          <a href="#" class="btn btn-outline-primary btn-sm rounded-pill px-3">Lihat Semua</a>
        </div>

        <div class="d-flex flex-column gap-3">
          @forelse($prestasis->take(4) as $p)
          <div class="card border-0 shadow-sm rounded-3 hover-card">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 60px; height: 60px;">
                <i class="fas fa-trophy fa-2x"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-1">{{ $p->nama_prestasi }}</h6>
                <div class="text-muted small">
                  <span class="badge bg-light text-dark border me-1">{{ $p->tingkat }}</span>
                  {{ $p->siswa->nama ?? 'Siswa' }} • {{ \Carbon\Carbon::parse($p->tanggal)->format('M Y') }}
                </div>
              </div>
            </div>
          </div>
          @empty
            <div class="text-muted text-center p-4 bg-light rounded">Belum ada data prestasi.</div>
          @endforelse
        </div>
      </div>

      <!-- Ekskul -->
      <div class="col-lg-6">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <div>
            <h6 class="text-warning fw-bold text-uppercase mb-1">Pengembangan Diri</h6>
            <h3 class="fw-bold" style="color: var(--primary);">Ekstrakurikuler</h3>
          </div>
        </div>

        <div class="row g-3">
          @forelse($ekskuls->take(6) as $e)
          <div class="col-sm-6">
            <div class="card border-0 bg-light-gray rounded-3 h-100 hover-card">
              <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-primary shadow-sm flex-shrink-0" style="width: 45px; height: 45px;">
                  <i class="fas fa-users"></i>
                </div>
                <div>
                  <h6 class="fw-bold mb-0 text-dark">{{ $e->nama }}</h6>
                  @if($e->pembina)<small class="text-muted">{{ $e->pembina->nama }}</small>@endif
                </div>
              </div>
            </div>
          </div>
          @empty
            <div class="col-12 text-muted text-center p-4 bg-light rounded">Belum ada data ekstrakurikuler.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── BERITA TERBARU ───────────────────────────────────────────────────────── --}}
@if($artikelTerbaru->count() > 0)
<section class="section-padding bg-light-gray">
  <div class="container">
    <div class="text-center mb-5">
      <h6 class="text-warning fw-bold text-uppercase mb-2">Informasi Terkini</h6>
      <h2 class="section-title">Berita & Pengumuman</h2>
      <div class="section-divider"></div>
    </div>

    <div class="row g-4">
      @foreach($artikelTerbaru as $a)
      <div class="col-lg-4 col-md-6">
        <div class="card border-0 rounded-4 shadow-sm h-100 hover-card overflow-hidden">
          <div class="position-relative">
            @if($a->thumbnail_url)
              <img src="{{ $a->thumbnail_url }}" class="card-img-top" style="height:220px; object-fit:cover;" alt="{{ $a->judul }}">
            @else
              <div class="bg-secondary d-flex align-items-center justify-content-center" style="height:220px;">
                <i class="fas fa-newspaper fa-4x text-white opacity-50"></i>
              </div>
            @endif
            <div class="position-absolute top-0 end-0 m-3">
              <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold shadow-sm">{{ $a->kategori_label }}</span>
            </div>
          </div>
          <div class="card-body p-4 d-flex flex-column">
            <div class="text-muted small mb-2 d-flex align-items-center gap-3">
              <span><i class="far fa-calendar-alt me-1 text-primary"></i> {{ \Carbon\Carbon::parse($a->published_at)->format('d M Y') }}</span>
            </div>
            <h5 class="fw-bold mb-3">
              <a href="{{ route('berita.show', $a) }}" class="text-dark text-decoration-none hover-primary">{{ Str::limit($a->judul, 60) }}</a>
            </h5>
            <p class="text-muted mb-4 flex-grow-1" style="font-size: 0.95rem;">{{ Str::limit($a->ringkasan_auto, 100) }}</p>
            <a href="{{ route('berita.show', $a) }}" class="text-primary fw-bold text-decoration-none mt-auto">
              Baca Selengkapnya <i class="fas fa-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    <div class="text-center mt-5">
      <a href="{{ route('berita.index') }}" class="btn btn-primary btn-lg rounded-pill px-5 fw-semibold shadow-sm">
        Lihat Semua Berita
      </a>
    </div>
  </div>
</section>
@endif

@endsection

@push('scripts')
<!-- Include AOS for scroll animations if not already in layout -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  // Initialize AOS
  AOS.init({
    duration: 800,
    once: true,
    offset: 50
  });

  // Counter animation
  const counters = document.querySelectorAll('.counter-num[data-target]');
  const animateCounters = () => {
    counters.forEach(el => {
      const target = +el.dataset.target;
      const step = Math.ceil(target / 50); // Speed control
      let current = 0;
      const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current.toLocaleString('id');
        if (current >= target) clearInterval(timer);
      }, 30);
    });
  };

  // Trigger counters when stats bar comes into view
  const statsBar = document.querySelector('.stats-bar');
  if (statsBar) {
    const observer = new IntersectionObserver(entries => {
      if (entries[0].isIntersecting) {
        animateCounters();
        observer.disconnect();
      }
    }, { threshold: 0.5 });
    observer.observe(statsBar);
  }
</script>
@endpush
