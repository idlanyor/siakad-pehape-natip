@extends('layouts.public')
@section('title', 'Pendaftaran Berhasil')
@section('page_title', 'PPDB')

@include('ppdb.partials.gaya')

@push('styles')
<style>
  .done-ic { width: 72px; height: 72px; border-radius: 22px; margin: 0 auto 1.25rem; display: grid; place-items: center; background: #dcfce7; color: #16a34a; font-size: 2rem; }
  .regno { background: var(--bg-light); border: 1px dashed #cbd5e1; border-radius: 18px; padding: 1.25rem; text-align: center; }
  .regno small { display: block; font-size: .75rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--text-muted); }
  .regno strong { display: block; font-size: clamp(1.4rem, 4vw, 2rem); letter-spacing: .08em; color: var(--ink); margin-top: .35rem; font-variant-numeric: tabular-nums; }
  .cred { border-radius: 18px; border: 1px solid #fde68a; background: #fffbeb; padding: 1.25rem; }
  .cred-row { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .6rem .8rem; background: #fff; border: 1px solid #fde68a; border-radius: 12px; }
  .cred-row + .cred-row { margin-top: .5rem; }
  .cred-row span { font-size: .8rem; color: #92400e; font-weight: 600; }
  .cred-row code { font-size: 1.05rem; color: var(--ink); font-weight: 700; word-break: break-all; }
  .copy { border: 0; background: transparent; color: #92400e; padding: .25rem .4rem; border-radius: 8px; }
  .copy:hover { background: #fef3c7; }
  .next { list-style: none; padding: 0; margin: 0; counter-reset: s; }
  .next li { position: relative; padding: 0 0 .9rem 2.4rem; color: var(--text-dark); font-size: .95rem; line-height: 1.55; }
  .next li::before { counter-increment: s; content: counter(s); position: absolute; left: 0; top: -.05rem; width: 26px; height: 26px; border-radius: 8px; display: grid; place-items: center; background: var(--primary-soft); color: var(--primary); font-weight: 700; font-size: .78rem; }
  .next li:last-child { padding-bottom: 0; }
</style>
@endpush

@section('content')
<section class="ppdb-body" style="padding-top:56px">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7 col-xl-6">
        <div class="ppdb-card ppdb-card-pad">
          <div class="text-center mb-4">
            <div class="done-ic"><i class="fas fa-check"></i></div>
            <h1 class="fw-bold mb-2" style="font-size:clamp(1.6rem,3vw,2rem);color:var(--ink)">Pendaftaran berhasil</h1>
            <p class="muted mb-0">Terima kasih, {{ $pendaftar->nama }}. Data pendaftaranmu sudah kami terima.</p>
          </div>

          <div class="regno mb-3">
            <small>Nomor pendaftaran</small>
            <strong>{{ $pendaftar->no_pendaftaran }}</strong>
          </div>

          @if($akunUsername)
            <div class="cred mb-4">
              <div class="d-flex gap-2 align-items-start mb-3" style="color:#78350f">
                <i class="fas fa-key mt-1"></i>
                <div><strong class="d-block">Akun portal pendaftar</strong><span class="small">
                  @if($akunPassword)
                    Catat sekarang. Password hanya ditampilkan sekali di halaman ini, dan juga tercantum di bukti pendaftaran.
                  @else
                    Password sudah ditampilkan saat mendaftar dan tercantum di bukti pendaftaran yang kamu cetak.
                  @endif
                </span></div>
              </div>
              <div class="cred-row">
                <div><span class="d-block">Username</span><code>{{ $akunUsername }}</code></div>
                <button type="button" class="copy" data-copy="{{ $akunUsername }}" aria-label="Salin username"><i class="far fa-copy"></i></button>
              </div>
              @if($akunPassword)
              <div class="cred-row">
                <div><span class="d-block">Password</span><code>{{ $akunPassword }}</code></div>
                <button type="button" class="copy" data-copy="{{ $akunPassword }}" aria-label="Salin password"><i class="far fa-copy"></i></button>
              </div>
              @endif
            </div>
          @endif

          <dl class="kv mb-4">
            <dt>Asal sekolah</dt><dd>{{ $pendaftar->asal_sekolah }}</dd>
            <dt>Jurusan pilihan</dt><dd>{{ $pendaftar->jurusanPilihan?->nama_jurusan ?? '—' }}</dd>
            <dt>Status</dt><dd><span class="pill pill-{{ $pendaftar->status_color }}">{{ $pendaftar->status_label }}</span></dd>
          </dl>

          <div class="mb-4">
            <h2 class="mb-3" style="font-size:1rem">Langkah selanjutnya</h2>
            <ol class="next">
              <li>Simpan nomor pendaftaran, username, dan password di atas.</li>
              <li>Masuk ke portal pendaftar untuk mengunggah berkas persyaratan.</li>
              <li>Pantau hasil seleksi lewat <a href="{{ route('ppdb.cek') }}">cek status</a>.</li>
            </ol>
          </div>

          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('login') }}" class="btn btn-primary flex-grow-1"><i class="fas fa-arrow-right-to-bracket me-1"></i> Masuk ke portal</a>
            <a href="{{ route('ppdb.cetak', $pendaftar) }}" target="_blank" rel="noopener" class="btn btn-outline-primary flex-grow-1"><i class="fas fa-print me-1"></i> Cetak bukti</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('[data-copy]').forEach(b => b.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(b.dataset.copy);
      const i = b.querySelector('i'); i.className = 'fas fa-check';
      setTimeout(() => { i.className = 'far fa-copy'; }, 1500);
    } catch (e) { /* clipboard tidak tersedia: pengguna tetap bisa menyalin manual */ }
  }));
</script>
@endpush
