@extends('ppdb.layout')
@section('title', 'Cek Status Pendaftaran')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="ppdb-card bg-white">
      <div class="ppdb-header">
        <h1><i class="fas fa-search mr-2"></i>Cek Status Pendaftaran</h1>
        <p>Masukkan nomor pendaftaran <strong>atau</strong> NISN + tanggal lahir</p>
      </div>
      <div class="card-body p-4">

        @if(session('error'))
          <div class="alert alert-danger"><i class="fas fa-times-circle mr-1"></i> {{ session('error') }}</div>
        @endif

        <form action="{{ route('ppdb.cek.status') }}" method="POST">
          @csrf

          {{-- Cara 1: Nomor Pendaftaran --}}
          <div class="card mb-3">
            <div class="card-header py-2 bg-light"><small class="font-weight-bold text-muted">CARA 1 — Via Nomor Pendaftaran</small></div>
            <div class="card-body py-3">
              <div class="form-group mb-0">
                <label class="small">Nomor Pendaftaran</label>
                <input type="text" name="no_pendaftaran" class="form-control text-center"
                       value="{{ old('no_pendaftaran') }}"
                       placeholder="PPDB-2026-XXXXX"
                       style="letter-spacing:2px;font-weight:600">
              </div>
            </div>
          </div>

          {{-- Cara 2: NISN + Tanggal Lahir --}}
          <div class="text-center text-muted mb-3"><small>— atau —</small></div>
          <div class="card mb-4">
            <div class="card-header py-2 bg-light"><small class="font-weight-bold text-muted">CARA 2 — Via NISN + Tanggal Lahir</small></div>
            <div class="card-body py-3">
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group mb-sm-0">
                    <label class="small">NISN</label>
                    <input type="text" name="nisn" class="form-control" value="{{ old('nisn') }}" placeholder="10 digit NISN">
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group mb-0">
                    <label class="small">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg">
            <i class="fas fa-search mr-1"></i> Cek Status
          </button>
        </form>

        @if(isset($pendaftar))
          <hr class="my-4">
          <div class="text-center mb-3">
            <span class="badge badge-{{ $pendaftar->status_color }}" style="font-size:1.1rem;padding:.6rem 1.4rem">
              {{ strtoupper($pendaftar->status_label) }}
            </span>
          </div>

          <table class="table table-sm table-bordered mt-3">
            <tr><th class="bg-light" style="width:40%">No. Pendaftaran</th><td class="font-weight-bold">{{ $pendaftar->no_pendaftaran }}</td></tr>
            <tr><th class="bg-light">Nama</th><td>{{ $pendaftar->nama }}</td></tr>
            <tr><th class="bg-light">Asal Sekolah</th><td>{{ $pendaftar->asal_sekolah }}</td></tr>
            <tr><th class="bg-light">Jurusan Pilihan</th><td>{{ $pendaftar->jurusanPilihan?->nama_jurusan ?? '-' }}</td></tr>
            <tr><th class="bg-light">Tanggal Daftar</th><td>{{ $pendaftar->created_at->format('d F Y, H:i') }}</td></tr>
            @if($pendaftar->status === 'diterima')
              <tr class="table-success">
                <th class="bg-light">Kelas Diterima</th>
                <td class="font-weight-bold">{{ $pendaftar->kelasDiterima?->nama_kelas ?? '—' }}</td>
              </tr>
              <tr class="table-success">
                <th class="bg-light">Username Login</th>
                <td class="font-weight-bold">{{ $pendaftar->akun_username ?? '—' }}</td>
              </tr>
            @endif
            @if($pendaftar->status === 'ditolak' && $pendaftar->catatan_admin)
              <tr class="table-danger">
                <th class="bg-light">Catatan</th>
                <td>{{ $pendaftar->catatan_admin }}</td>
              </tr>
            @endif
          </table>

          @if($pendaftar->status === 'diterima')
            <div class="alert alert-success mt-3">
              <i class="fas fa-check-circle mr-1"></i>
              <strong>Selamat!</strong> Anda diterima. Gunakan username <strong>{{ $pendaftar->akun_username }}</strong> untuk login.
              Password awal menggunakan NISN Anda. Segera ganti password setelah login pertama.
            </div>
          @elseif($pendaftar->status === 'pending')
            <div class="alert alert-secondary mt-3">
              <i class="fas fa-clock mr-1"></i> Pendaftaran sedang dalam antrian. Mohon tunggu informasi selanjutnya.
            </div>
          @elseif($pendaftar->status === 'verifikasi')
            <div class="alert alert-info mt-3">
              <i class="fas fa-clipboard-check mr-1"></i> Berkas sedang diverifikasi. Harap menunggu hasil seleksi.
            </div>
          @elseif($pendaftar->status === 'ditolak')
            <div class="alert alert-danger mt-3">
              <i class="fas fa-times-circle mr-1"></i> Maaf, pendaftaran Anda tidak lolos seleksi.
            </div>
          @endif
        @endif

      </div>
      <div class="card-footer bg-light text-center py-3" style="border-radius:0 0 12px 12px">
        <a href="{{ route('ppdb.index') }}" class="btn btn-outline-primary">
          <i class="fas fa-edit mr-1"></i> Daftar Baru
        </a>
        <a href="{{ route('login') }}" class="btn btn-outline-secondary ml-2">
          <i class="fas fa-sign-in-alt mr-1"></i> Login
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
