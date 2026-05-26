@extends('layouts.public')
@section('title', 'PPDB ' . setting('ppdb_tahun_ajaran') . ' — ' . setting('sekolah_nama'))
@section('page_title', 'Portal PPDB')

@push('styles')
<style>
  .ppdb-hero { background: linear-gradient(135deg, #1a3a6b 0%, #e8a020 100%); min-height: 100vh; display: flex; align-items: center; padding-top: 80px; }
  .countdown-card { background: rgba(255,255,255,.15); backdrop-filter: blur(10px); border-radius: 16px; padding: 2rem; }
  .countdown-item { text-align: center; min-width: 80px; }
  .countdown-num { font-size: 3rem; font-weight: 800; color: #fff; line-height: 1; display: block; }
  .countdown-label { font-size: .75rem; color: rgba(255,255,255,.75); font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }
  .countdown-sep { font-size: 2.5rem; font-weight: 800; color: rgba(255,255,255,.5); align-self: flex-start; padding-top: .15rem; }
  .step-number { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
  .syarat-item { border-left: 3px solid var(--accent); padding-left: 1rem; margin-bottom: 1rem; }
  .gelombang-card { border: 2px solid transparent; border-radius: 12px; padding: 1.5rem; transition: all .2s; }
  .gelombang-card.buka { border-color: #28a745; background: #f0fff4; }
  .gelombang-card.tutup { background: #f8f9fa; opacity: .7; }
</style>
@endpush

@section('content')

{{-- ── HERO ──────────────────────────────────────────────────────────────────── --}}
<section class="ppdb-hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <span class="badge mb-3 px-3 py-2" style="background:rgba(255,255,255,.2);color:#fff;font-size:.85rem">
          PPDB {{ setting('ppdb_tahun_ajaran') }}
        </span>
        <h1 class="fw-800 text-white mb-3" style="font-size:clamp(2rem,4vw,3rem);font-weight:800;line-height:1.2">
          Penerimaan Peserta<br>Didik Baru
          <span style="color:var(--accent)">{{ setting('sekolah_nama', 'SMK Negeri 48') }}</span>
        </h1>
        <p class="text-white opacity-85 mb-4" style="font-size:1.05rem">
          Bergabunglah dan wujudkan masa depanmu bersama kami.
          {{ setting('sekolah_tagline') }}
        </p>
        <div class="d-flex flex-wrap gap-3" style="gap:.75rem">
          @if($gelombangAktif)
            <a href="{{ route('ppdb.index') }}" class="btn btn-lg fw-bold px-5"
               style="background:var(--accent);color:#fff;border-radius:10px">
              <i class="fas fa-file-alt me-2"></i> Daftar Sekarang
            </a>
          @endif
          <a href="{{ route('ppdb.cek') }}" class="btn btn-lg px-4"
             style="background:rgba(255,255,255,.15);color:#fff;border:2px solid rgba(255,255,255,.4);border-radius:10px">
            <i class="fas fa-search me-2"></i> Cek Status
          </a>
        </div>
      </div>

      <div class="col-lg-6">
        @if($gelombangAktif)
        <div class="countdown-card">
          <p class="text-white fw-semibold mb-3 text-center opacity-85">
            <i class="fas fa-clock me-2"></i>{{ $gelombangAktif->nama }} menutup dalam
          </p>
          <div class="d-flex justify-content-center align-items-center gap-2" id="countdown" style="gap:.75rem">
            <div class="countdown-item"><span class="countdown-num" id="cd-days">--</span><span class="countdown-label">Hari</span></div>
            <span class="countdown-sep">:</span>
            <div class="countdown-item"><span class="countdown-num" id="cd-hours">--</span><span class="countdown-label">Jam</span></div>
            <span class="countdown-sep">:</span>
            <div class="countdown-item"><span class="countdown-num" id="cd-mins">--</span><span class="countdown-label">Menit</span></div>
            <span class="countdown-sep">:</span>
            <div class="countdown-item"><span class="countdown-num" id="cd-secs">--</span><span class="countdown-label">Detik</span></div>
          </div>
          <p class="text-center mt-3 mb-0 opacity-75" style="color:#fff;font-size:.85rem">
            Tutup: {{ $gelombangAktif->tanggal_tutup->translatedFormat('d F Y, H:i') }} WIB
          </p>
          @if($gelombangAktif->sisa_kuota !== null)
          <div class="mt-3 text-center">
            <span class="badge" style="background:rgba(255,255,255,.2);color:#fff;font-size:.9rem;padding:.5rem 1rem">
              Sisa Kuota: <strong>{{ $gelombangAktif->sisa_kuota }}</strong> / {{ $gelombangAktif->kuota }}
            </span>
          </div>
          @endif
        </div>
        @else
        <div class="countdown-card text-center">
          <i class="fas fa-calendar-times fa-3x mb-3 d-block" style="color:rgba(255,255,255,.5)"></i>
          <h5 class="text-white mb-2">Pendaftaran Sedang Ditutup</h5>
          <p class="opacity-75 mb-0" style="color:#fff">
            Pantau informasi pembukaan gelombang berikutnya di sini.
          </p>
          @if($gelombangSemua->isNotEmpty())
            <div class="mt-3">
              @foreach($gelombangSemua as $g)
                @if($g->tanggal_buka->isFuture())
                <span class="badge mt-1" style="background:rgba(255,255,255,.15);color:#fff;font-size:.82rem">
                  {{ $g->nama }}: Buka {{ $g->tanggal_buka->format('d/m/Y') }}
                </span>
                @endif
              @endforeach
            </div>
          @endif
        </div>
        @endif
      </div>
    </div>
  </div>
</section>

{{-- ── GELOMBANG PENDAFTARAN ─────────────────────────────────────────────────── --}}
@if($gelombangSemua->count() > 0)
<section class="py-5 bg-white">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Gelombang Pendaftaran</h2>
      <p class="section-subtitle">Jadwal pendaftaran peserta didik baru {{ setting('ppdb_tahun_ajaran') }}</p>
    </div>
    <div class="row g-4 justify-content-center">
      @foreach($gelombangSemua as $g)
      <div class="col-md-4">
        <div class="gelombang-card {{ $g->is_buka ? 'buka' : 'tutup' }}">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <h5 class="fw-bold mb-0" style="color:var(--primary)">{{ $g->nama }}</h5>
            <span class="badge badge-{{ $g->status_color }}">{{ $g->status_label }}</span>
          </div>
          <table class="table table-sm table-borderless mb-0" style="font-size:.87rem">
            <tr><td class="text-muted ps-0">Dibuka</td><td class="fw-semibold">{{ $g->tanggal_buka->translatedFormat('d F Y') }}</td></tr>
            <tr><td class="text-muted ps-0">Ditutup</td><td class="fw-semibold">{{ $g->tanggal_tutup->translatedFormat('d F Y') }}</td></tr>
            @if($g->kuota > 0)
            <tr><td class="text-muted ps-0">Kuota</td><td class="fw-semibold">{{ $g->kuota }} siswa</td></tr>
            @endif
          </table>
          @if($g->keterangan)
            <p class="text-muted small mb-0 mt-2">{{ $g->keterangan }}</p>
          @endif
          @if($g->is_buka)
            <div class="mt-3">
              <a href="{{ route('ppdb.index') }}" class="btn btn-success w-100 fw-semibold">
                <i class="fas fa-file-alt me-1"></i>Daftar di Gelombang Ini
              </a>
            </div>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── PROGRAM JURUSAN ─────────────────────────────────────────────────────── --}}
@if($jurusans->count() > 0)
<section class="py-5" style="background:var(--bg-light)">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Program Keahlian yang Dibuka</h2>
      <p class="section-subtitle">Pilih jurusan sesuai minat dan bakat kamu</p>
    </div>
    <div class="row g-3 justify-content-center">
      @foreach($jurusans as $j)
      <div class="col-md-4 col-lg-3">
        <div class="card border-0 shadow-sm h-100 text-center p-4 rounded-3" style="transition:transform .2s"
             onmouseenter="this.style.transform='translateY(-4px)'" onmouseleave="this.style.transform=''">
          <div class="mx-auto mb-3" style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center">
            <i class="{{ $j->icon ?? 'fas fa-graduation-cap' }}" style="color:#fff;font-size:1.3rem"></i>
          </div>
          <h6 class="fw-bold" style="color:var(--primary)">{{ $j->nama_jurusan }}</h6>
          @if($j->deskripsi_publik)
            <p class="text-muted small mb-0">{{ Str::limit($j->deskripsi_publik, 80) }}</p>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── TIMELINE ─────────────────────────────────────────────────────────────── --}}
<section class="py-5 bg-white">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Alur Pendaftaran</h2>
      <p class="section-subtitle">Ikuti langkah-langkah berikut untuk mendaftar</p>
    </div>
    <div class="row g-4 justify-content-center">
      @foreach([
        ['icon' => 'fas fa-file-alt', 'judul' => 'Isi Formulir Online', 'desc' => 'Lengkapi data diri dan pilih jurusan yang diminati melalui portal ini.'],
        ['icon' => 'fas fa-upload', 'judul' => 'Upload Dokumen', 'desc' => 'Upload foto, ijazah, rapor, KK, dan akta kelahiran sesuai ketentuan.'],
        ['icon' => 'fas fa-clipboard-check', 'judul' => 'Verifikasi Berkas', 'desc' => 'Panitia PPDB akan memverifikasi dokumen yang telah kamu upload.'],
        ['icon' => 'fas fa-bullhorn', 'judul' => 'Pengumuman', 'desc' => 'Pantau status pendaftaran menggunakan nomor pendaftaran yang diberikan.'],
        ['icon' => 'fas fa-school', 'judul' => 'Daftar Ulang', 'desc' => 'Lakukan daftar ulang ke sekolah setelah dinyatakan diterima.'],
      ] as $i => $step)
      <div class="col-md-4 col-lg-2 text-center">
        <div class="position-relative">
          <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
               style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light))">
            <i class="{{ $step['icon'] }}" style="color:#fff;font-size:1.4rem"></i>
          </div>
          <div class="fw-bold mb-1" style="color:var(--primary)">{{ $step['judul'] }}</div>
          <p class="text-muted small lh-lg">{{ $step['desc'] }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── PERSYARATAN ──────────────────────────────────────────────────────────── --}}
@if($persyaratanList->count() > 0)
<section class="py-5" style="background:var(--bg-light)">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Persyaratan Pendaftaran</h2>
      <p class="section-subtitle">Dokumen yang perlu disiapkan</p>
    </div>
    <div class="row g-3 justify-content-center">
      @foreach($persyaratanList as $i => $p)
      <div class="col-md-6 col-lg-4">
        <div class="d-flex align-items-start gap-3 p-3 bg-white rounded-3 shadow-sm h-100">
          <div class="flex-shrink-0 fw-bold text-white rounded-circle d-flex align-items-center justify-content-center"
               style="width:36px;height:36px;background:var(--accent);font-size:.9rem">
            {{ $i + 1 }}
          </div>
          <div>
            <div class="fw-semibold" style="color:var(--primary)">{{ $p->judul }}</div>
            @if($p->deskripsi)
              <div class="text-muted small mt-1 lh-lg">{{ $p->deskripsi }}</div>
            @endif
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ── FAQ ──────────────────────────────────────────────────────────────────── --}}
@if($faqList->count() > 0)
<section class="py-5 bg-white">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-divider mx-auto"></div>
      <h2 class="section-title">Pertanyaan Umum (FAQ)</h2>
      <p class="section-subtitle">Jawaban atas pertanyaan yang sering ditanyakan</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="accordion" id="faqAccordion">
          @foreach($faqList as $i => $faq)
          <div class="accordion-item border mb-2 rounded-3 overflow-hidden">
            <h2 class="accordion-header" id="faq-h{{ $i }}">
              <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }} fw-semibold"
                      type="button" data-bs-toggle="collapse"
                      data-bs-target="#faq-c{{ $i }}"
                      style="color:var(--primary);background:#fff">
                {{ $faq->pertanyaan }}
              </button>
            </h2>
            <div id="faq-c{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
              <div class="accordion-body text-muted lh-lg">{{ $faq->jawaban }}</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>
@endif

{{-- ── CTA Daftar ─────────────────────────────────────────────────────────── --}}
@if($gelombangAktif)
<section class="py-5" style="background:linear-gradient(135deg,var(--primary),var(--primary-light))">
  <div class="container text-center text-white">
    <h2 class="fw-bold mb-3">Siap Bergabung?</h2>
    <p class="opacity-80 mb-4 fs-5">Jangan lewatkan kesempatan mendaftar di {{ setting('sekolah_nama') }}!</p>
    <a href="{{ route('ppdb.index') }}" class="btn btn-lg fw-bold px-5 me-3"
       style="background:var(--accent);color:#fff;border-radius:10px">
      <i class="fas fa-file-alt me-2"></i>Daftar Sekarang
    </a>
    <a href="{{ route('ppdb.cek') }}" class="btn btn-lg px-4"
       style="background:rgba(255,255,255,.15);color:#fff;border:2px solid rgba(255,255,255,.3);border-radius:10px">
      <i class="fas fa-search me-2"></i>Cek Status
    </a>
  </div>
</section>
@endif

@endsection

@push('scripts')
<script>
@if($gelombangAktif)
// Countdown Timer
const deadline = new Date("{{ $gelombangAktif->tanggal_tutup->toIso8601String() }}").getTime();

function updateCountdown() {
  const now = Date.now();
  const diff = deadline - now;

  if (diff <= 0) {
    document.getElementById('countdown').innerHTML = '<span class="text-white opacity-75">Pendaftaran Ditutup</span>';
    return;
  }

  const days  = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const mins  = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const secs  = Math.floor((diff % (1000 * 60)) / 1000);

  document.getElementById('cd-days').textContent  = String(days).padStart(2, '0');
  document.getElementById('cd-hours').textContent = String(hours).padStart(2, '0');
  document.getElementById('cd-mins').textContent  = String(mins).padStart(2, '0');
  document.getElementById('cd-secs').textContent  = String(secs).padStart(2, '0');
}

updateCountdown();
setInterval(updateCountdown, 1000);
@endif
</script>
@endpush
