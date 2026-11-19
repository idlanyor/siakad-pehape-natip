@extends('layouts.public')
@section('title', 'Formulir Pendaftaran PPDB')
@section('page_title', 'Daftar PPDB')

@include('ppdb.partials.gaya')

@push('styles')
<style>
  .side { position: sticky; top: calc(var(--nav-h) + 20px); }
  .side-steps { list-style: none; padding: 0; margin: 0; counter-reset: s; }
  .side-steps li { position: relative; padding: 0 0 1.1rem 2.5rem; color: var(--text-muted); font-size: .92rem; line-height: 1.5; }
  .side-steps li::before { counter-increment: s; content: counter(s); position: absolute; left: 0; top: -.1rem; width: 26px; height: 26px; border-radius: 8px; display: grid; place-items: center; background: var(--bg-gray); color: var(--text-dark); font-weight: 700; font-size: .78rem; }
  .side-steps li.now::before { background: var(--primary); color: #fff; }
  .side-steps li.now { color: var(--ink); font-weight: 600; }
  .side-steps li:last-child { padding-bottom: 0; }
  .submit-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
  @media (max-width: 991.98px) { .side { position: static; } }
  @media (max-width: 575.98px) { .submit-bar .btn { width: 100%; } }
</style>
@endpush

@section('content')

@include('ppdb.partials.header', [
  'crumb' => 'Formulir',
  'judul' => 'Formulir pendaftaran',
  'sub'   => 'Penerimaan Peserta Didik Baru tahun ajaran ' . setting('ppdb_tahun_ajaran', date('Y') . '/' . (date('Y') + 1)) . '. Kolom bertanda * wajib diisi.',
])

<section class="ppdb-body">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-8">

        @if(!$gelombangBuka)
          <div class="note note-warn mb-4">
            <i class="fas fa-triangle-exclamation"></i>
            <span><strong>Belum ada gelombang yang dibuka.</strong> Formulir tetap bisa diisi, tetapi pastikan kamu memantau jadwal gelombang terbaru.
              <a href="{{ route('ppdb.landing') }}" class="fw-semibold">Lihat jadwal</a></span>
          </div>
        @endif

        @if($errors->any())
          <div class="note note-danger mb-4" role="alert">
            <i class="fas fa-circle-exclamation"></i>
            <div>
              <strong>Periksa kembali isian berikut:</strong>
              <ul class="mb-0 mt-1 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          </div>
        @endif

        <form action="{{ route('ppdb.store') }}" method="POST" class="ppdb-form">
          @csrf
          <div class="ppdb-card ppdb-card-pad">

            {{-- ── 1. Data pribadi ── --}}
            <div class="form-step">
              <span class="n">1</span>
              <div><h2>Data pribadi</h2><p>Isi sesuai ijazah atau akta kelahiran.</p></div>
            </div>
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label" for="nama">Nama lengkap<span class="req">*</span></label>
                <input type="text" id="nama" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" placeholder="Sesuai ijazah" autocomplete="name" required>
                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="jenis_kelamin">Jenis kelamin<span class="req">*</span></label>
                <select id="jenis_kelamin" name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                  <option value="">Pilih</option>
                  <option value="L" @selected(old('jenis_kelamin') == 'L')>Laki-laki</option>
                  <option value="P" @selected(old('jenis_kelamin') == 'P')>Perempuan</option>
                </select>
                @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-5">
                <label class="form-label" for="tempat_lahir">Tempat lahir<span class="req">*</span></label>
                <input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" value="{{ old('tempat_lahir') }}" required>
                @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="tanggal_lahir">Tanggal lahir<span class="req">*</span></label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir') }}" max="{{ now()->subDay()->toDateString() }}" required>
                @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-3">
                <label class="form-label" for="nisn">NISN<span class="opt">opsional</span></label>
                <input type="text" id="nisn" name="nisn" class="form-control @error('nisn') is-invalid @enderror" value="{{ old('nisn') }}" placeholder="10 digit" inputmode="numeric">
                @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label" for="alamat">Alamat lengkap<span class="req">*</span></label>
                <textarea id="alamat" name="alamat" rows="2" class="form-control @error('alamat') is-invalid @enderror" placeholder="Jalan, RT/RW, kelurahan, kecamatan, kota" autocomplete="street-address" required>{{ old('alamat') }}</textarea>
                @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="no_hp">No. HP / WhatsApp<span class="req">*</span></label>
                <input type="tel" id="no_hp" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
                @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="email">Email<span class="opt">opsional</span></label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@email.com" autocomplete="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-4">
                <label class="form-label" for="asal_sekolah">Asal sekolah<span class="req">*</span></label>
                <input type="text" id="asal_sekolah" name="asal_sekolah" class="form-control @error('asal_sekolah') is-invalid @enderror" value="{{ old('asal_sekolah') }}" placeholder="SMP/MTs" required>
                @error('asal_sekolah')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="divider-x"></div>

            {{-- ── 2. Orang tua ── --}}
            <div class="form-step">
              <span class="n">2</span>
              <div><h2>Orang tua / wali</h2><p>Opsional, tetapi membantu panitia menghubungi keluarga.</p></div>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="nama_ayah">Nama ayah</label>
                <input type="text" id="nama_ayah" name="nama_ayah" class="form-control @error('nama_ayah') is-invalid @enderror" value="{{ old('nama_ayah') }}">
                @error('nama_ayah')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="nama_ibu">Nama ibu</label>
                <input type="text" id="nama_ibu" name="nama_ibu" class="form-control @error('nama_ibu') is-invalid @enderror" value="{{ old('nama_ibu') }}">
                @error('nama_ibu')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="no_hp_ortu">No. HP orang tua</label>
                <input type="tel" id="no_hp_ortu" name="no_hp_ortu" class="form-control @error('no_hp_ortu') is-invalid @enderror" value="{{ old('no_hp_ortu') }}">
                @error('no_hp_ortu')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="pekerjaan_ortu">Pekerjaan orang tua</label>
                <input type="text" id="pekerjaan_ortu" name="pekerjaan_ortu" class="form-control @error('pekerjaan_ortu') is-invalid @enderror" value="{{ old('pekerjaan_ortu') }}">
                @error('pekerjaan_ortu')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="divider-x"></div>

            {{-- ── 3. Jurusan ── --}}
            <div class="form-step">
              <span class="n">3</span>
              <div><h2>Pilihan jurusan</h2><p>Pilihan kedua dipakai bila kuota pilihan pertama sudah penuh.</p></div>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="jurusan_pilihan_id">Pilihan pertama<span class="req">*</span></label>
                <select id="jurusan_pilihan_id" name="jurusan_pilihan_id" class="form-select @error('jurusan_pilihan_id') is-invalid @enderror" required>
                  <option value="">Pilih jurusan</option>
                  @foreach($jurusans as $j)
                    <option value="{{ $j->id }}" @selected(old('jurusan_pilihan_id') == $j->id)>{{ $j->nama_jurusan }}</option>
                  @endforeach
                </select>
                @error('jurusan_pilihan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="jurusan_pilihan_2_id">Pilihan kedua<span class="opt">opsional</span></label>
                <select id="jurusan_pilihan_2_id" name="jurusan_pilihan_2_id" class="form-select @error('jurusan_pilihan_2_id') is-invalid @enderror">
                  <option value="">Tidak ada</option>
                  @foreach($jurusans as $j)
                    <option value="{{ $j->id }}" @selected(old('jurusan_pilihan_2_id') == $j->id)>{{ $j->nama_jurusan }}</option>
                  @endforeach
                </select>
                @error('jurusan_pilihan_2_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="nilai_rata_rata">Rata-rata nilai rapor SMP<span class="opt">opsional</span></label>
                <input type="number" id="nilai_rata_rata" name="nilai_rata_rata" class="form-control @error('nilai_rata_rata') is-invalid @enderror" value="{{ old('nilai_rata_rata') }}" min="0" max="100" step="0.01" placeholder="0–100">
                @error('nilai_rata_rata')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="note note-info mt-4">
              <i class="fas fa-circle-info"></i>
              <span>Berkas (foto, ijazah, rapor, KK, akta) <strong>tidak diunggah di sini</strong>. Setelah mendaftar kamu mendapat akun portal pendaftar untuk mengunggahnya.</span>
            </div>

            <div class="divider-x"></div>

            <div class="submit-bar">
              <a href="{{ route('ppdb.landing') }}" class="btn btn-link text-decoration-none px-0"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
              <button type="submit" class="btn btn-primary btn-lg px-4">Kirim pendaftaran <i class="fas fa-paper-plane ms-1"></i></button>
            </div>
          </div>
        </form>
      </div>

      <div class="col-lg-4">
        <aside class="side d-flex flex-column gap-3">
          <div class="ppdb-card ppdb-card-pad">
            @if($gelombangBuka)
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="mb-0" style="font-size:1rem">{{ $gelombangBuka->nama }}</h2>
                <span class="pill pill-success">Dibuka</span>
              </div>
              <p class="muted small mb-0">Ditutup {{ $gelombangBuka->tanggal_tutup->locale('id')->translatedFormat('d F Y, H:i') }} WIB</p>
            @else
              <h2 class="mb-1" style="font-size:1rem">Belum ada gelombang dibuka</h2>
              <p class="muted small mb-0"><a href="{{ route('ppdb.landing') }}">Lihat jadwal gelombang</a></p>
            @endif
          </div>
          <div class="ppdb-card ppdb-card-pad">
            <h2 class="mb-3" style="font-size:1rem">Setelah formulir ini</h2>
            <ol class="side-steps">
              <li class="now">Kirim formulir pendaftaran</li>
              <li>Catat nomor pendaftaran &amp; akun portal</li>
              <li>Unggah berkas di portal pendaftar</li>
              <li>Pantau hasil lewat cek status</li>
            </ol>
          </div>
          <div class="ppdb-card ppdb-card-pad">
            <h2 class="mb-2" style="font-size:1rem">Sudah pernah mendaftar?</h2>
            <p class="muted small mb-3">Jangan mendaftar dua kali. Cek status pendaftaranmu dulu.</p>
            <a href="{{ route('ppdb.cek') }}" class="btn btn-outline-primary w-100">Cek status</a>
          </div>
        </aside>
      </div>
    </div>
  </div>
</section>
@endsection
