@extends('ppdb.layout')
@section('title', 'Cek Status Pendaftaran')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="ppdb-card bg-white">
      <div class="ppdb-header">
        <h1><i class="fas fa-search mr-2"></i>Cek Status Pendaftaran</h1>
        <p>Masukkan nomor pendaftaran untuk melihat status Anda</p>
      </div>
      <div class="card-body p-4">

        @if(session('error'))
          <div class="alert alert-danger">
            <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
          </div>
        @endif

        <form action="{{ route('ppdb.cek.status') }}" method="POST">
          @csrf
          <div class="form-group">
            <label>Nomor Pendaftaran</label>
            <input type="text" name="no_pendaftaran"
                   class="form-control form-control-lg text-center"
                   value="{{ old('no_pendaftaran', request('no_pendaftaran')) }}"
                   placeholder="PPDB-2026-XXXXX"
                   style="letter-spacing:2px; font-weight:600"
                   required autofocus>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-lg">
            <i class="fas fa-search mr-1"></i> Cek Status
          </button>
        </form>

        @if(isset($pendaftar))
          <hr class="my-4">
          <div class="text-center mb-3">
            <span class="badge badge-{{ $pendaftar->status_color }}" style="font-size:1.2rem; padding:.7rem 1.5rem">
              {{ strtoupper($pendaftar->status_label) }}
            </span>
          </div>

          <table class="table table-sm table-bordered mt-3">
            <tr><th class="bg-light" style="width:40%">No. Pendaftaran</th><td class="font-weight-bold">{{ $pendaftar->no_pendaftaran }}</td></tr>
            <tr><th class="bg-light">Nama</th><td>{{ $pendaftar->nama }}</td></tr>
            <tr><th class="bg-light">Asal Sekolah</th><td>{{ $pendaftar->asal_sekolah }}</td></tr>
            <tr><th class="bg-light">Jurusan Pilihan</th><td>{{ $pendaftar->jurusanPilihan->nama_jurusan }}</td></tr>
            <tr><th class="bg-light">Tanggal Daftar</th><td>{{ $pendaftar->created_at->format('d F Y, H:i') }}</td></tr>

            @if($pendaftar->status === 'diterima')
              <tr class="table-success">
                <th class="bg-light">Kelas Diterima</th>
                <td class="font-weight-bold">{{ $pendaftar->kelasDiterima?->nama_kelas ?? '—' }}</td>
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
              <strong>Selamat!</strong> Anda diterima di SMK Negeri 48. Silakan datang ke sekolah untuk proses daftar ulang dan mendapatkan akun login.
            </div>
          @elseif($pendaftar->status === 'pending')
            <div class="alert alert-secondary mt-3">
              <i class="fas fa-clock mr-1"></i>
              Pendaftaran Anda sedang dalam antrian. Mohon tunggu informasi selanjutnya.
            </div>
          @elseif($pendaftar->status === 'verifikasi')
            <div class="alert alert-info mt-3">
              <i class="fas fa-clipboard-check mr-1"></i>
              Berkas Anda sedang diverifikasi oleh panitia. Harap menunggu hasil seleksi.
            </div>
          @endif
        @endif

      </div>
      <div class="card-footer bg-light text-center py-3" style="border-radius: 0 0 12px 12px">
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
