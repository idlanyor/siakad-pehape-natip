@extends('ppdb.layout')
@section('title', 'Pendaftaran Berhasil')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="ppdb-card bg-white text-center">
      <div class="ppdb-header" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
        <div style="font-size:3rem"><i class="fas fa-check-circle"></i></div>
        <h1 class="mt-2">Pendaftaran Berhasil!</h1>
        <p>Data pendaftaran Anda telah diterima oleh sistem</p>
      </div>
      <div class="card-body p-4">
        <div class="alert alert-info text-left">
          <h5 class="font-weight-bold"><i class="fas fa-id-card mr-2"></i>Nomor Pendaftaran Anda</h5>
          <div class="text-center my-3">
            <span style="font-size:2rem; font-weight:700; letter-spacing:3px; color:#1e3a5f">
              {{ $pendaftar->no_pendaftaran }}
            </span>
          </div>
          <p class="mb-0 text-danger small">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            <strong>SIMPAN nomor ini!</strong> Anda membutuhkannya untuk mengecek status pendaftaran.
          </p>
        </div>

        <table class="table table-sm table-borderless text-left mt-3">
          <tr><th class="text-muted" style="width:40%">Nama</th><td>{{ $pendaftar->nama }}</td></tr>
          <tr><th class="text-muted">Asal Sekolah</th><td>{{ $pendaftar->asal_sekolah }}</td></tr>
          <tr><th class="text-muted">Jurusan Pilihan</th><td>{{ $pendaftar->jurusanPilihan->nama_jurusan }}</td></tr>
          <tr><th class="text-muted">Status</th>
            <td><span class="badge badge-{{ $pendaftar->status_color }}">{{ $pendaftar->status_label }}</span></td>
          </tr>
        </table>
      </div>
      <div class="card-footer bg-light py-3" style="border-radius: 0 0 12px 12px">
        <a href="{{ route('ppdb.cek') }}" class="btn btn-info">
          <i class="fas fa-search mr-1"></i> Cek Status Pendaftaran
        </a>
        <a href="{{ route('ppdb.index') }}" class="btn btn-secondary ml-2">
          <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
