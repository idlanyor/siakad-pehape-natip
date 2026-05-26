@extends('layouts.public')
@section('title', setting('sekolah_nama', 'SMK Negeri 48'))
@section('page_title', 'Beranda')
@section('meta_desc', setting('sekolah_tagline'))

@push('styles')
<style>
  .counter-num { font-size: 2.8rem; font-weight: 800; }
  html { scroll-behavior: smooth; }
</style>
@endpush

@section('content')

{{-- ── HERO ─────────────────────────────────────────────────────────────────── --}}
<section class="hero-section" id="hero"
  @if(setting_url('hero_bg'))
    style="background: linear-gradient(135deg, rgba(26,58,107,.92) 0%, rgba(45,95,158,.88) 100%), url('{{ setting_url('hero_bg') }}') center/cover no-repeat;"
  @endif
>
  <div class="container position-relative" style="z-index:2; padding-top: 80px;">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <span class="hero-badge"><i class="fas fa-star me-1" style="color:var(--accent)"></i>Sekolah Unggulan Jakarta Timur</span>
        <h1 class="hero-title mb-3">{{ setting('sekolah_nama', 'SMK Negeri 48 Jakarta') }}</h1>
        <p class="hero-subtitle mb-4">{{ setting('sekolah_tagline', 'Mencetak generasi unggul, berkarakter, dan siap bersaing di era global.') }}</p>
        <div class="d-flex flex-wrap gap-3 mb-5">
          <a href="{{ route('ppdb.index') }}" class="btn btn-lg fw-bold px-4"
             style="background:var(--accent);color:#fff;border-radius:10px">
            <i class="fas fa-file-alt me-2"></i>Daftar PPDB
          </a>
          <a href="#program" class="btn btn-lg px-4"
             style="background:rgba(255,255,255,.15);color:#fff;border:2px solid rgba(255,255,255,.3);border-radius:10px">
            <i class="fas fa-graduation-cap me-2"></i>Program Kami
          </a>
        </div>

        {{-- Mini Stats --}}
        <div class="hero-stats d-flex flex-wrap gap-4">
          <div class="text-center">
            <div class="hero-stat-num">{{ $stats['siswa'] }}+</div>
            <div class="hero-stat-label">Siswa Aktif</div>
          </div>
          <div class="text-center">
            <div class="hero-stat-num">{{ $stats['guru'] }}+</div>
            <div class="hero-stat-label">Tenaga Pendidik</div>
          </div>
          <div class="text-center">
            <div class="hero-stat-num">{{ $stats['jurusan'] }}</div>
            <div class="hero-stat-label">Program Keahlian</div>
          </div>
          <div class="text-center">
            <div class="hero-stat-num">{{ date('Y') - (int)$stats['tahun_berdiri'] }}+</div>
            <div class="hero-stat-label">Tahun Berpengalaman</div>
          </div>
        </div>
      </div>

      <div class="col-lg-5 d-none d-lg-block text-center">
        @if(setting_url('logo'))
          <img src="{{ setting_url('logo') }}" class="img-fluid" style="max-height:340px;filter:drop-shadow(0 20px 40px rgba(0,0,0,.3))" alt="Logo Sekolah">
        @else
          <div style="width:280px;height:280px;border-radius:50%;background:rgba(255,255,255,.1);margin:auto;display:flex;align-items:center;justify-content:center">
            <i class="fas fa-graduation-cap" style="font-size:7rem;color:rgba(255,255,255,.4)"></i>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Scroll indicator --}}
  <div class="position-absolute bottom-0 start-50 translate-middle-x mb-4" style="z-index:2">
    <a href="#sambutan" style="color:rgba(255,255,255,.5);animation:bounce 2s infinite">
      <i class="fas fa-chevron-down fa-lg"></i>
    </a>
  </div>
</section>

@if(setting('ppdb_aktif') == '1')
{{-- ── PPDB ALERT BANNER ────────────────────────────────────────────────────── --}}
<div class="ppdb-cta py-4">
  <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="text-white">
      <span class="badge bg-white text-warning fw-bold me-2">PPDB {{ setting('ppdb_tahun_ajaran') }}</span>
      <strong class="fs-5">Pendaftaran Siswa Baru Sedang Dibuka!</strong>
      @if(setting('ppdb_info'))
        <p class="mb-0 mt-1 opacity-90 small">{{ setting('ppdb_info') }}</p>
      @endif
    </div>
    <a href="{{ route('ppdb.index') }}" class="btn btn-lg fw-bold px-4"
       style="background:#fff;color:var(--accent);border-radius:8px;white-space:nowrap">
      <i class="fas fa-paper-plane me-2"></i>Daftar Sekarang
    </a>
  </div>
</div>
@endif

{{-- ── SAMBUTAN KEPSEK ──────────────────────────────────────────────────────── --}}
@if(setting('sambutan_kepsek') || setting('nama_kepsek'))
<section class="sambutan-section py-5" id="sambutan">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-md-3 text-center">
        @if(setting_url('foto_kepsek'))
          <img src="{{ setting_url('foto_kepsek') }}" class="kepsek-photo" alt="Kepala Sekolah">
        @else
          <div style="width:160px;height:160px;border-radius:50%;background:#e2e8f0;margin:auto;display:flex;align-items:center;justify-content:center">
            <i class="fas fa-user fa-3x text-muted"></i>
          </div>
        @endif
        @if(setting('nama_kepsek'))
          <div class="mt-3">
            <div class="fw-bold text-dark">{{ setting('nama_kepsek') }}</div>
            <div class="text-muted small">Kepala Sekolah</div>
            @if(setting('nip_kepsek'))
              <div class="text-muted small">NIP. {{ setting('nip_kepsek') }}</div>
            @endif
          </div>
        @endif
      </div>
      <div class="col-md-9">
        <div class="mb-2"><span class="badge" style="background:var(--accent);color:#fff;font-size:.75rem">Sambutan</span></div>
        <h2 class="section-title mb-3">Kata Kepala Sekolah</h2>
        <div class="text-muted lh-lg" style="font-size:.98rem">{!! setting('sambutan_kepsek') !!}</div>
      </div>
    </div>
  </div>
</section>
@endif

{{-- ── PROGRAM KEAHLIAN ─────────────────────────────────────────────────────── --}}
<section class="py-5 bg-white" id="program">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Program Keahlian</h2>
      <p class="section-subtitle">Pilih jurusan yang sesuai dengan minat dan bakatmu</p>
    </div>
    <div class="row g-4">
      @forelse($jurusans as $j)
      <div class="col-md-6 col-lg-4">
        <div class="jurusan-card card h-100 p-4">
          <div class="d-flex align-items-start gap-3">
            <div class="jurusan-icon flex-shrink-0">
              <i class="{{ $j->icon ?? 'fas fa-graduation-cap' }}"></i>
            </div>
            <div>
              <h5 class="fw-bold mb-1" style="color:var(--primary)">{{ $j->nama_jurusan }}</h5>
              @if($j->deskripsi_publik)
                <p class="text-muted small mb-0 lh-lg">{{ Str::limit($j->deskripsi_publik, 120) }}</p>
              @endif
            </div>
          </div>
          @if($j->gambar)
            <img src="{{ asset('storage/' . $j->gambar) }}" class="mt-3 rounded" style="width:100%;height:140px;object-fit:cover" alt="{{ $j->nama_jurusan }}">
          @endif
        </div>
      </div>
      @empty
        <div class="col-12 text-center text-muted py-4">Belum ada program keahlian.</div>
      @endforelse
    </div>
    <div class="text-center mt-4">
      <a href="{{ route('landing.program') }}" class="btn btn-outline-primary px-4 fw-semibold">
        Lihat Semua Program <i class="fas fa-arrow-right ms-2"></i>
      </a>
    </div>
  </div>
</section>

{{-- ── STATISTIK ────────────────────────────────────────────────────────────── --}}
<section class="py-5" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%)">
  <div class="container">
    <div class="row g-4 text-center text-white">
      <div class="col-6 col-lg-3">
        <div class="counter-num text-warning" data-target="{{ $stats['siswa'] }}">0</div>
        <div class="mt-1 opacity-75">Siswa Aktif</div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="counter-num text-warning" data-target="{{ $stats['guru'] }}">0</div>
        <div class="mt-1 opacity-75">Tenaga Pendidik</div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="counter-num text-warning" data-target="{{ $stats['ekskul'] }}">0</div>
        <div class="mt-1 opacity-75">Ekstrakurikuler</div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="counter-num text-warning" data-target="{{ $stats['prestasi'] }}">0</div>
        <div class="mt-1 opacity-75">Prestasi Diraih</div>
      </div>
    </div>
  </div>
</section>

{{-- ── EKSTRAKURIKULER ──────────────────────────────────────────────────────── --}}
@if($ekskuls->count() > 0)
<section class="py-5" style="background:var(--bg-light)">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Ekstrakurikuler</h2>
      <p class="section-subtitle">Kembangkan bakat dan minat melalui kegiatan ekstrakurikuler kami</p>
    </div>
    <div class="row g-3">
      @foreach($ekskuls as $e)
      <div class="col-6 col-md-4 col-lg-3">
        <div class="ekskul-card d-flex align-items-center gap-2">
          <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-users" style="color:#fff;font-size:.8rem"></i>
          </div>
          <div>
            <div class="fw-semibold small" style="color:var(--primary)">{{ $e->nama }}</div>
            @if($e->pembina)
              <div class="text-muted" style="font-size:.72rem">{{ $e->pembina->nama }}</div>
            @endif
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── PRESTASI ─────────────────────────────────────────────────────────────── --}}
@if($prestasis->count() > 0)
<section class="py-5 bg-white">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Prestasi Terkini</h2>
      <p class="section-subtitle">Capaian membanggakan siswa-siswi kami</p>
    </div>
    <div class="row g-4">
      @foreach($prestasis as $p)
      <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
          <div class="d-flex align-items-start gap-3">
            @if($p->foto)
              <img src="{{ asset('storage/' . $p->foto) }}" style="width:56px;height:56px;object-fit:cover;border-radius:8px;flex-shrink:0" alt="">
            @else
              <div style="width:56px;height:56px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-trophy text-warning fa-lg"></i>
              </div>
            @endif
            <div>
              <span class="prestasi-badge mb-1 d-inline-block
                {{ in_array($p->tingkat, ['Nasional','Internasional']) ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
                {{ $p->tingkat }}
              </span>
              <div class="fw-semibold small lh-sm">{{ $p->nama_prestasi }}</div>
              <div class="text-muted" style="font-size:.78rem">{{ $p->siswa->nama ?? '-' }} · {{ \Carbon\Carbon::parse($p->tanggal)->format('M Y') }}</div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── BERITA TERBARU ───────────────────────────────────────────────────────── --}}
@if($artikelTerbaru->count() > 0)
<section class="py-5 bg-white">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Berita Terbaru</h2>
      <p class="section-subtitle">Informasi dan kabar terkini dari sekolah kami</p>
    </div>
    <div class="row g-4">
      @foreach($artikelTerbaru as $a)
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100 overflow-hidden" style="transition:transform .25s,box-shadow .25s"
             onmouseenter="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 28px rgba(0,0,0,.1)'"
             onmouseleave="this.style.transform='';this.style.boxShadow=''">
          @if($a->thumbnail_url)
            <img src="{{ $a->thumbnail_url }}" style="height:180px;object-fit:cover;width:100%" alt="{{ $a->judul }}">
          @else
            <div style="height:180px;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center">
              <i class="fas fa-newspaper fa-3x" style="color:rgba(255,255,255,.3)"></i>
            </div>
          @endif
          <div class="card-body p-4">
            <span class="badge mb-2" style="background:var(--accent);color:#fff;font-size:.72rem">{{ $a->kategori_label }}</span>
            <h6 class="fw-bold lh-sm mb-2" style="color:var(--primary)">{{ Str::limit($a->judul, 65) }}</h6>
            <p class="text-muted small lh-lg mb-3">{{ Str::limit($a->ringkasan_auto, 90) }}</p>
            <a href="{{ route('berita.show', $a) }}" class="btn btn-sm btn-outline-primary">
              Baca <i class="fas fa-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    <div class="text-center mt-4">
      <a href="{{ route('berita.index') }}" class="btn btn-outline-primary px-4 fw-semibold">
        Semua Berita <i class="fas fa-arrow-right ms-2"></i>
      </a>
    </div>
  </div>
</section>
@endif

{{-- ── GALERI TERBARU ───────────────────────────────────────────────────────── --}}
@if($galeriTerbaru->count() > 0)
<section class="py-5" style="background:var(--bg-light)">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Galeri Kegiatan</h2>
      <p class="section-subtitle">Momen berharga kegiatan sekolah kami</p>
    </div>
    <div class="row g-3">
      @foreach($galeriTerbaru as $g)
      <div class="col-6 col-md-3">
        <a href="{{ route('galeri.show', $g) }}" class="d-block text-decoration-none" style="overflow:hidden;border-radius:10px;position:relative">
          @if($g->cover_url)
            <img src="{{ $g->cover_url }}" style="width:100%;height:180px;object-fit:cover;transition:transform .3s;display:block"
                 onmouseenter="this.style.transform='scale(1.05)'" onmouseleave="this.style.transform=''"
                 alt="{{ $g->nama }}">
          @else
            <div style="height:180px;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center">
              <i class="fas fa-images fa-2x" style="color:rgba(255,255,255,.4)"></i>
            </div>
          @endif
          <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.7));padding:.75rem">
            <div class="text-white fw-semibold" style="font-size:.82rem">{{ Str::limit($g->nama, 35) }}</div>
            <div class="text-white opacity-75" style="font-size:.72rem">{{ $g->items_count }} foto</div>
          </div>
        </a>
      </div>
      @endforeach
    </div>
    <div class="text-center mt-4">
      <a href="{{ route('galeri.index') }}" class="btn btn-outline-primary px-4 fw-semibold">
        Lihat Semua Galeri <i class="fas fa-arrow-right ms-2"></i>
      </a>
    </div>
  </div>
</section>
@endif

{{-- ── KONTAK ───────────────────────────────────────────────────────────────── --}}
<section class="py-5" style="background:var(--bg-light)" id="kontak">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Hubungi Kami</h2>
      <p class="section-subtitle">Kami siap membantu pertanyaan Anda</p>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100">
          <div class="mb-3" style="font-size:2rem;color:var(--primary)"><i class="fas fa-map-marker-alt"></i></div>
          <h6 class="fw-bold">Alamat</h6>
          <p class="text-muted small mb-0">{{ setting('sekolah_alamat') }}</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100">
          <div class="mb-3" style="font-size:2rem;color:var(--primary)"><i class="fas fa-phone-alt"></i></div>
          <h6 class="fw-bold">Telepon & Email</h6>
          <p class="text-muted small mb-0">
            {{ setting('sekolah_no_telp') }}<br>
            {{ setting('sekolah_email') }}
          </p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100">
          <div class="mb-3" style="font-size:2rem;color:var(--primary)"><i class="fas fa-clock"></i></div>
          <h6 class="fw-bold">Jam Operasional</h6>
          <p class="text-muted small mb-0">Senin – Jumat<br>07.00 – 16.00 WIB</p>
        </div>
      </div>
    </div>

    @if(setting('maps_embed'))
    <div class="mt-4 rounded-3 overflow-hidden shadow-sm" style="height:280px">
      <iframe src="{{ setting('maps_embed') }}" width="100%" height="280" style="border:0;display:block" allowfullscreen loading="lazy"></iframe>
    </div>
    @endif

    {{-- Form Kontak --}}
    <div class="row justify-content-center mt-5">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 p-4 p-md-5">
          <h4 class="fw-bold mb-1" style="color:var(--primary)">Kirim Pesan</h4>
          <p class="text-muted small mb-4">Isi formulir di bawah ini, kami akan membalas secepatnya.</p>

          @if(session('kontak_success'))
            <div class="alert alert-success alert-dismissible">
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              <i class="fas fa-check-circle me-2"></i>{{ session('kontak_success') }}
            </div>
          @endif

          <form action="{{ route('kontak.store') }}" method="POST">
            @csrf
            @if($errors->has('nama') || $errors->has('email') || $errors->has('pesan'))
              <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
              </div>
            @endif
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" placeholder="Nama Anda" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="email@contoh.com" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">No. HP / WhatsApp</label>
                <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Subjek <span class="text-danger">*</span></label>
                <input type="text" name="subjek" class="form-control" value="{{ old('subjek') }}" placeholder="Topik pesan" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Pesan <span class="text-danger">*</span></label>
                <textarea name="pesan" rows="4" class="form-control" placeholder="Tulis pesan Anda di sini..." required minlength="10">{{ old('pesan') }}</textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary fw-semibold px-5">
                  <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
// Counter animation
const counters = document.querySelectorAll('.counter-num[data-target]');
const animateCounters = () => {
  counters.forEach(el => {
    const target = +el.dataset.target;
    const step = Math.ceil(target / 60);
    let current = 0;
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current.toLocaleString('id');
      if (current >= target) clearInterval(timer);
    }, 25);
  });
};

// Trigger on scroll into view
const statsSection = document.querySelector('[data-target]')?.closest('section');
if (statsSection) {
  const observer = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) { animateCounters(); observer.disconnect(); }
  }, { threshold: .3 });
  observer.observe(statsSection);
}
</script>
@endpush
