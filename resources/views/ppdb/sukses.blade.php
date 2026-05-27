@extends('ppdb.layout')
@section('title', 'Pendaftaran Berhasil')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="ppdb-card bg-white text-center">
      <div class="ppdb-header" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
        <div style="font-size:3rem"><i class="fas fa-check-circle"></i></div>
        <h1 class="mt-2">Pendaftaran Berhasil!</h1>
        <p>Data pendaftaran Anda telah diterima oleh sistem</p>
      </div>

      <div class="card-body p-4">

        {{-- Nomor Pendaftaran --}}
        <div class="alert alert-info text-left">
          <h6 class="font-weight-bold mb-1"><i class="fas fa-id-card mr-2"></i>Nomor Pendaftaran</h6>
          <div class="text-center my-2">
            <span style="font-size:1.8rem; font-weight:700; letter-spacing:3px; color:#1e3a5f">
              {{ $pendaftar->no_pendaftaran }}
            </span>
          </div>
        </div>

        {{-- Info Akun Login --}}
        @if($akunUsername)
        <div class="alert alert-warning text-left">
          <h6 class="font-weight-bold mb-2">
            <i class="fas fa-key mr-2"></i>Akun Portal Pendaftar
          </h6>
          <table class="table table-sm table-borderless mb-1">
            <tr>
              <th style="width:35%" class="text-muted py-1">Username</th>
              <td class="py-1">
                <code class="text-dark font-weight-bold" style="font-size:1rem">{{ $akunUsername }}</code>
              </td>
            </tr>
            <tr>
              <th class="text-muted py-1">Password</th>
              <td class="py-1">
                <code class="text-dark font-weight-bold" style="font-size:1rem">{{ $akunPassword }}</code>
              </td>
            </tr>
          </table>
          <p class="mb-0 text-danger small font-weight-bold">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            CATAT username &amp; password ini! Password tidak dapat ditampilkan lagi setelah halaman ini ditutup.
          </p>
        </div>
        @endif

        {{-- Ringkasan --}}
        <table class="table table-sm table-borderless text-left mt-2">
          <tr><th class="text-muted" style="width:40%">Nama</th><td>{{ $pendaftar->nama }}</td></tr>
          <tr><th class="text-muted">Asal Sekolah</th><td>{{ $pendaftar->asal_sekolah }}</td></tr>
          <tr><th class="text-muted">Jurusan Pilihan</th><td>{{ $pendaftar->jurusanPilihan->nama_jurusan }}</td></tr>
          <tr>
            <th class="text-muted">Status</th>
            <td><span class="badge badge-{{ $pendaftar->status_color }}">{{ $pendaftar->status_label }}</span></td>
          </tr>
        </table>

        {{-- Langkah Selanjutnya --}}
        <div class="alert alert-light border text-left mt-3">
          <h6 class="font-weight-bold mb-2"><i class="fas fa-list-ol mr-2 text-primary"></i>Langkah Selanjutnya</h6>
          <ol class="mb-0 pl-3 small">
            <li>Simpan nomor pendaftaran, username, dan password di atas.</li>
            <li>Login ke portal pendaftar untuk upload berkas persyaratan.</li>
            <li>Pantau status pendaftaran secara berkala.</li>
          </ol>
        </div>

      </div>

      <div class="card-footer bg-light py-3 d-flex flex-wrap justify-content-center gap-2" style="border-radius: 0 0 12px 12px; gap:.5rem">
        <a href="{{ route('ppdb.cetak', $pendaftar) }}" target="_blank" class="btn btn-outline-secondary">
          <i class="fas fa-print mr-1"></i> Cetak Bukti
        </a>
        <a href="{{ route('login') }}" class="btn btn-success">
          <i class="fas fa-sign-in-alt mr-1"></i> Login Portal Pendaftar
        </a>
        <a href="{{ route('ppdb.cek') }}" class="btn btn-info">
          <i class="fas fa-search mr-1"></i> Cek Status
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
