@extends('layouts.public')
@section('title', 'Cek Status Pendaftaran PPDB')
@section('page_title', 'Cek Status')

@include('ppdb.partials.gaya')

@push('styles')
<style>
  .tabs-cara { display: grid; grid-template-columns: 1fr 1fr; gap: .4rem; padding: .35rem; background: var(--bg-gray); border-radius: 14px; margin-bottom: 1.5rem; }
  .tabs-cara button { border: 0; background: transparent; border-radius: 10px; padding: .6rem .5rem; font-weight: 600; font-size: .9rem; color: var(--text-muted); }
  .tabs-cara button.active { background: #fff; color: var(--ink); box-shadow: var(--shadow-sm); }
  .no-input { text-align: center; letter-spacing: .12em; font-weight: 700; font-size: 1.1rem !important; text-transform: uppercase; }
  .result-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .result-head .no { font-size: .8rem; color: var(--text-muted); font-weight: 600; letter-spacing: .04em; }
  .result-head h2 { margin: .15rem 0 0; font-size: 1.3rem; }
  .pill-lg { font-size: .9rem; padding: .45rem 1rem; }
</style>
@endpush

@section('content')

@include('ppdb.partials.header', [
  'crumb' => 'Cek status',
  'judul' => 'Cek status pendaftaran',
  'sub'   => 'Gunakan nomor pendaftaran, atau NISN dan tanggal lahir yang kamu isi saat mendaftar.',
])

<section class="ppdb-body">
  <div class="container">
    <div class="row justify-content-center g-4">
      <div class="col-lg-7 col-xl-6">

        @if(session('error'))
          <div class="note note-danger mb-4" role="alert"><i class="fas fa-circle-xmark"></i><span>{{ session('error') }}</span></div>
        @endif

        @isset($pendaftar)
          @php
            $pesan = [
              'diterima'   => ['note-ok', 'fa-circle-check', 'Selamat, kamu diterima!', 'Masuk dengan username <strong>' . e($pendaftar->akun_username) . '</strong>. Password awal adalah NISN kamu; segera ganti setelah login pertama.'],
              'pending'    => ['note-info', 'fa-clock', 'Pendaftaran sedang dalam antrean', 'Pastikan berkas sudah diunggah di portal pendaftar, lalu tunggu informasi selanjutnya.'],
              'verifikasi' => ['note-info', 'fa-clipboard-check', 'Berkas sedang diverifikasi', 'Panitia sedang memeriksa berkasmu. Harap menunggu hasil seleksi.'],
              'ditolak'    => ['note-danger', 'fa-circle-xmark', 'Pendaftaran belum lolos seleksi', 'Terima kasih sudah mendaftar. Hubungi panitia bila ada pertanyaan.'],
            ][$pendaftar->status] ?? null;
          @endphp
          <div class="ppdb-card ppdb-card-pad mb-4">
            <div class="result-head">
              <div>
                <div class="no">{{ $pendaftar->no_pendaftaran }}</div>
                <h2>{{ $pendaftar->nama }}</h2>
              </div>
              <span class="pill pill-lg pill-{{ $pendaftar->status_color }}">{{ $pendaftar->status_label }}</span>
            </div>

            @if($pesan)
              <div class="note {{ $pesan[0] }} mb-3">
                <i class="fas {{ $pesan[1] }}"></i>
                <span><strong class="d-block">{{ $pesan[2] }}</strong>{!! $pesan[3] !!}</span>
              </div>
            @endif

            <dl class="kv">
              <dt>Asal sekolah</dt><dd>{{ $pendaftar->asal_sekolah }}</dd>
              <dt>Jurusan pilihan</dt><dd>{{ $pendaftar->jurusanPilihan?->nama_jurusan ?? '—' }}</dd>
              <dt>Tanggal daftar</dt><dd>{{ $pendaftar->created_at->locale('id')->translatedFormat('d F Y, H:i') }}</dd>
              @if($pendaftar->status === 'diterima')
                <dt>Kelas</dt><dd>{{ $pendaftar->kelasDiterima?->nama_kelas ?? '—' }}</dd>
                <dt>Username</dt><dd><code class="fs-6">{{ $pendaftar->akun_username ?? '—' }}</code></dd>
              @endif
              @if($pendaftar->status === 'ditolak' && $pendaftar->catatan_admin)
                <dt>Catatan panitia</dt><dd>{{ $pendaftar->catatan_admin }}</dd>
              @endif
            </dl>

            <div class="d-flex flex-wrap gap-2 mt-4">
              <a href="{{ route('login') }}" class="btn btn-primary"><i class="fas fa-arrow-right-to-bracket me-1"></i> Masuk ke portal</a>
              <a href="{{ route('ppdb.cek') }}" class="btn btn-outline-primary">Cek pendaftar lain</a>
            </div>
          </div>
        @endisset

        <form action="{{ route('ppdb.cek.status') }}" method="POST" class="ppdb-form ppdb-card ppdb-card-pad" id="formCek">
          @csrf
          <h2 class="mb-3">{{ isset($pendaftar) ? 'Cek pendaftaran lain' : 'Cari pendaftaran' }}</h2>

          @php $caraNisn = old('nisn') && ! old('no_pendaftaran'); @endphp
          <div class="tabs-cara" role="tablist">
            <button type="button" role="tab" data-cara="no" class="{{ $caraNisn ? '' : 'active' }}" aria-selected="{{ $caraNisn ? 'false' : 'true' }}">Nomor pendaftaran</button>
            <button type="button" role="tab" data-cara="nisn" class="{{ $caraNisn ? 'active' : '' }}" aria-selected="{{ $caraNisn ? 'true' : 'false' }}">NISN + tanggal lahir</button>
          </div>

          <div data-panel="no" @if($caraNisn) hidden @endif>
            <label class="form-label" for="no_pendaftaran">Nomor pendaftaran</label>
            <input type="text" id="no_pendaftaran" name="no_pendaftaran" class="form-control no-input" value="{{ old('no_pendaftaran') }}" placeholder="PPDB-XXXX-XXXXX" autocomplete="off">
            <div class="form-text">Tertera di bukti pendaftaran.</div>
          </div>

          <div data-panel="nisn" class="row g-3" @unless($caraNisn) hidden @endunless>
            <div class="col-sm-6">
              <label class="form-label" for="nisn">NISN</label>
              <input type="text" id="nisn" name="nisn" class="form-control" value="{{ old('nisn') }}" placeholder="10 digit" inputmode="numeric">
            </div>
            <div class="col-sm-6">
              <label class="form-label" for="tanggal_lahir">Tanggal lahir</label>
              <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}">
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 mt-4"><i class="fas fa-magnifying-glass me-1"></i> Cek status</button>
          <p class="muted small text-center mt-3 mb-0">Belum mendaftar? <a href="{{ route('ppdb.index') }}" class="fw-semibold">Isi formulir pendaftaran</a></p>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  // Dua cara pencarian. Panel yang disembunyikan dikosongkan agar server tidak menerima isian lama.
  (() => {
    const form = document.getElementById('formCek');
    const tabs = form.querySelectorAll('[data-cara]');
    tabs.forEach(t => t.addEventListener('click', () => {
      tabs.forEach(x => { x.classList.toggle('active', x === t); x.setAttribute('aria-selected', x === t); });
      form.querySelectorAll('[data-panel]').forEach(p => {
        const aktif = p.dataset.panel === t.dataset.cara;
        p.hidden = !aktif;
        if (!aktif) p.querySelectorAll('input').forEach(i => i.value = '');
      });
      form.querySelector(`[data-panel="${t.dataset.cara}"] input`).focus();
    }));
  })();
</script>
@endpush
