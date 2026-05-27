@extends('ppdb.layout')
@section('title', 'Pendaftaran Peserta Didik Baru')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-9">
    <div class="ppdb-card bg-white">
      <div class="ppdb-header">
        <h1><i class="fas fa-graduation-cap mr-2"></i>PPDB SMK Negeri 48</h1>
        <p>Pendaftaran Peserta Didik Baru Tahun Ajaran {{ date('Y') }}/{{ date('Y') + 1 }}</p>
        <div class="mt-3">
          <a href="{{ route('ppdb.cek') }}" class="btn btn-outline-light btn-sm">
            <i class="fas fa-search mr-1"></i> Cek Status Pendaftaran
          </a>
          <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm ml-2">
            <i class="fas fa-sign-in-alt mr-1"></i> Login
          </a>
        </div>
      </div>

      {{-- Banner jika pendaftaran tutup --}}
      @if(!$gelombangBuka)
      <div class="alert alert-warning m-3 rounded">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Perhatian:</strong> Saat ini tidak ada gelombang pendaftaran yang sedang dibuka.
        Kamu tetap bisa mengisi formulir, namun pastikan cek informasi gelombang terbaru.
        <a href="{{ route('ppdb.landing') }}" class="alert-link ml-2">Info Gelombang &rarr;</a>
      </div>
      @endif

      <form action="{{ route('ppdb.store') }}" method="POST">
        @csrf
        <div class="card-body px-4 py-4">

          @if($errors->any())
            <div class="alert alert-danger">
              <strong>Perbaiki kesalahan berikut:</strong>
              <ul class="mb-0 mt-2">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
          @endif

          {{-- ── Data Pribadi ── --}}
          <h5 class="section-title"><i class="fas fa-user mr-2"></i>Data Pribadi</h5>
          <div class="row">
            <div class="col-md-8 form-group">
              <label>Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                     value="{{ old('nama') }}" placeholder="Sesuai Ijazah" required>
              @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4 form-group">
              <label>Jenis Kelamin <span class="text-danger">*</span></label>
              <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" required>
                <option value="">-- Pilih --</option>
                <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
              </select>
              @error('jenis_kelamin') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>
          <div class="row">
            <div class="col-md-5 form-group">
              <label>Tempat Lahir <span class="text-danger">*</span></label>
              <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror"
                     value="{{ old('tempat_lahir') }}" required>
              @error('tempat_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4 form-group">
              <label>Tanggal Lahir <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
                     value="{{ old('tanggal_lahir') }}" required>
              @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3 form-group">
              <label>NISN</label>
              <input type="text" name="nisn" class="form-control" value="{{ old('nisn') }}" placeholder="10 digit">
            </div>
          </div>
          <div class="form-group">
            <label>Alamat Lengkap <span class="text-danger">*</span></label>
            <textarea name="alamat" rows="2" class="form-control @error('alamat') is-invalid @enderror" required>{{ old('alamat') }}</textarea>
            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="row">
            <div class="col-md-4 form-group">
              <label>No. HP / WhatsApp <span class="text-danger">*</span></label>
              <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror"
                     value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" required>
              @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4 form-group">
              <label>Email</label>
              <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Opsional">
            </div>
            <div class="col-md-4 form-group">
              <label>Asal Sekolah (SMP/MTs) <span class="text-danger">*</span></label>
              <input type="text" name="asal_sekolah" class="form-control @error('asal_sekolah') is-invalid @enderror"
                     value="{{ old('asal_sekolah') }}" required>
              @error('asal_sekolah') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          {{-- ── Data Orang Tua ── --}}
          <h5 class="section-title mt-4"><i class="fas fa-users mr-2"></i>Data Orang Tua / Wali</h5>
          <div class="row">
            <div class="col-md-6 form-group">
              <label>Nama Ayah</label>
              <input type="text" name="nama_ayah" class="form-control" value="{{ old('nama_ayah') }}">
            </div>
            <div class="col-md-6 form-group">
              <label>Nama Ibu</label>
              <input type="text" name="nama_ibu" class="form-control" value="{{ old('nama_ibu') }}">
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 form-group">
              <label>No. HP Orang Tua</label>
              <input type="text" name="no_hp_ortu" class="form-control" value="{{ old('no_hp_ortu') }}">
            </div>
            <div class="col-md-6 form-group">
              <label>Pekerjaan Orang Tua</label>
              <input type="text" name="pekerjaan_ortu" class="form-control" value="{{ old('pekerjaan_ortu') }}">
            </div>
          </div>

          {{-- ── Pilihan Jurusan ── --}}
          <h5 class="section-title mt-4"><i class="fas fa-graduation-cap mr-2"></i>Pilihan Jurusan</h5>
          <div class="row">
            <div class="col-md-4 form-group">
              <label>Jurusan Pilihan 1 <span class="text-danger">*</span></label>
              <select name="jurusan_pilihan_id" class="form-control @error('jurusan_pilihan_id') is-invalid @enderror" required>
                <option value="">-- Pilih Jurusan --</option>
                @foreach($jurusans as $j)
                  <option value="{{ $j->id }}" {{ old('jurusan_pilihan_id') == $j->id ? 'selected' : '' }}>
                    {{ $j->nama_jurusan }}
                  </option>
                @endforeach
              </select>
              @error('jurusan_pilihan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4 form-group">
              <label>Jurusan Pilihan 2 <small class="text-muted">(opsional)</small></label>
              <select name="jurusan_pilihan_2_id" class="form-control">
                <option value="">-- Tidak Ada --</option>
                @foreach($jurusans as $j)
                  <option value="{{ $j->id }}" {{ old('jurusan_pilihan_2_id') == $j->id ? 'selected' : '' }}>
                    {{ $j->nama_jurusan }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4 form-group">
              <label>Rata-rata Nilai Rapor SMP</label>
              <input type="number" name="nilai_rata_rata" class="form-control"
                     value="{{ old('nilai_rata_rata') }}" min="0" max="100" step="0.01" placeholder="0.00 - 100.00">
            </div>
          </div>

          {{-- ── Info Berkas ── --}}
          <div class="alert alert-info mt-4 mb-0">
            <h6 class="font-weight-bold mb-1"><i class="fas fa-info-circle mr-2"></i>Upload Berkas Dilakukan Setelah Pendaftaran</h6>
            <p class="mb-0 small">
              Setelah pendaftaran berhasil, kamu akan mendapatkan akun untuk login ke <strong>Portal Pendaftar</strong>.
              Upload foto dan dokumen persyaratan (ijazah, rapor, KK, akta) dilakukan melalui portal tersebut.
            </p>
          </div>

        </div>

        <div class="card-footer bg-light px-4 py-3 d-flex justify-content-between" style="border-radius: 0 0 12px 12px">
          <a href="{{ url('/') }}" class="btn btn-secondary">
            <i class="fas fa-home mr-1"></i> Beranda
          </a>
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-paper-plane mr-1"></i> Kirim Pendaftaran
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
