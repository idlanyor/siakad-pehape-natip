@extends('layouts.admin')
@section('title', 'Review Pendaftar — ' . $pendaftar->nama)
@section('page_title', 'Review Pendaftar PPDB')

@section('content')
<div class="row">

  {{-- Kolom kiri: Data pendaftar --}}
  <div class="col-md-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
          <i class="fas fa-user-graduate mr-2"></i>{{ $pendaftar->nama }}
        </h3>
        <span class="badge badge-{{ $pendaftar->status_color }} px-3 py-2" style="font-size:.9rem">
          {{ strtoupper($pendaftar->status_label) }}
        </span>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tr><th class="pl-3" style="width:35%">No. Pendaftaran</th><td class="font-weight-bold">{{ $pendaftar->no_pendaftaran }}</td></tr>
          <tr><th class="pl-3">Jenis Kelamin</th><td>{{ $pendaftar->jk_label }}</td></tr>
          <tr><th class="pl-3">TTL</th><td>{{ $pendaftar->tempat_lahir }}, {{ $pendaftar->tanggal_lahir->format('d F Y') }}</td></tr>
          <tr><th class="pl-3">NISN</th><td>{{ $pendaftar->nisn ?? '—' }}</td></tr>
          <tr><th class="pl-3">Alamat</th><td>{{ $pendaftar->alamat }}</td></tr>
          <tr><th class="pl-3">No. HP</th><td>{{ $pendaftar->no_hp }}</td></tr>
          <tr><th class="pl-3">Email</th><td>{{ $pendaftar->email ?? '—' }}</td></tr>
          <tr><th class="pl-3">Asal Sekolah</th><td>{{ $pendaftar->asal_sekolah }}</td></tr>
          <tr><th class="pl-3 border-top" colspan="2"><strong>Orang Tua / Wali</strong></th></tr>
          <tr><th class="pl-3">Nama Ayah</th><td>{{ $pendaftar->nama_ayah ?? '—' }}</td></tr>
          <tr><th class="pl-3">Nama Ibu</th><td>{{ $pendaftar->nama_ibu ?? '—' }}</td></tr>
          <tr><th class="pl-3">No. HP Ortu</th><td>{{ $pendaftar->no_hp_ortu ?? '—' }}</td></tr>
          <tr><th class="pl-3">Pekerjaan Ortu</th><td>{{ $pendaftar->pekerjaan_ortu ?? '—' }}</td></tr>
          <tr><th class="pl-3 border-top" colspan="2"><strong>Pilihan Akademik</strong></th></tr>
          <tr><th class="pl-3">Jurusan Pilihan 1</th><td class="font-weight-bold">{{ $pendaftar->jurusanPilihan->nama_jurusan }}</td></tr>
          <tr><th class="pl-3">Jurusan Pilihan 2</th><td>{{ $pendaftar->jurusanPilihan2?->nama_jurusan ?? '—' }}</td></tr>
          <tr><th class="pl-3">Nilai Rata-rata</th><td>{{ $pendaftar->nilai_rata_rata ? number_format($pendaftar->nilai_rata_rata, 2) : '—' }}</td></tr>
          <tr><th class="pl-3">Gelombang</th><td>{{ $pendaftar->gelombang }}</td></tr>
          <tr><th class="pl-3">Tahun Ajaran</th><td>{{ $pendaftar->tahun_ajaran }}</td></tr>
          <tr><th class="pl-3">Tanggal Daftar</th><td>{{ $pendaftar->created_at->format('d/m/Y H:i') }}</td></tr>
          @if($pendaftar->diproses)
            <tr><th class="pl-3">Diproses Oleh</th><td>{{ $pendaftar->diproses->name }} — {{ $pendaftar->diproses_at?->format('d/m/Y H:i') }}</td></tr>
          @endif
          @if($pendaftar->catatan_admin)
            <tr><th class="pl-3">Catatan Admin</th><td class="text-danger">{{ $pendaftar->catatan_admin }}</td></tr>
          @endif
          @if($pendaftar->status === 'diterima' && $pendaftar->kelasDiterima)
            <tr class="table-success"><th class="pl-3">Kelas Diterima</th><td class="font-weight-bold">{{ $pendaftar->kelasDiterima->nama_kelas }}</td></tr>
          @endif
        </table>
      </div>
      <div class="card-footer">
        <a href="{{ route('admin.ppdb.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
      </div>
    </div>

    {{-- Dokumen --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Dokumen</h3>
      </div>
      <div class="card-body">
        <div class="row">
          @foreach(\App\Models\PendaftarPpdb::DOKUMEN_FIELDS as $field => $label)
          <div class="col-md-6 mb-3">
            <label class="font-weight-bold text-muted d-block">{{ $label }}</label>
            @if($pendaftar->$field)
              @php $url = asset('storage/' . $pendaftar->$field); $ext = pathinfo($pendaftar->$field, PATHINFO_EXTENSION); @endphp
              @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                <a href="{{ $url }}" target="_blank">
                  <img src="{{ $url }}" class="img-thumbnail" style="max-height:150px">
                </a>
              @else
                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-info">
                  <i class="fas fa-file-pdf mr-1"></i> Lihat Dokumen
                </a>
              @endif
            @else
              <span class="text-muted"><i class="fas fa-times-circle mr-1"></i> Belum diupload</span>
            @endif
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Kolom kanan: Aksi --}}
  <div class="col-md-5">

    @if($pendaftar->status === 'diterima')
      <div class="alert alert-success">
        <h5><i class="fas fa-check-circle mr-1"></i> Sudah Diterima</h5>
        <p class="mb-0">Pendaftar ini sudah diterima di kelas <strong>{{ $pendaftar->kelasDiterima?->nama_kelas }}</strong>.</p>
      </div>
    @elseif($pendaftar->status === 'ditolak')
      <div class="alert alert-danger">
        <h5><i class="fas fa-times-circle mr-1"></i> Ditolak</h5>
        <p class="mb-0">{{ $pendaftar->catatan_admin ?? 'Tidak ada catatan.' }}</p>
      </div>
    @else

      {{-- Verifikasi --}}
      @if($pendaftar->status === 'pending')
      <div class="card card-info">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-clipboard-check mr-2"></i>Verifikasi Berkas</h3>
        </div>
        <div class="card-body">
          <p>Tandai bahwa berkas pendaftar sudah diverifikasi dan lengkap.</p>
        </div>
        <div class="card-footer">
          <form action="{{ route('admin.ppdb.verifikasi', $pendaftar) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-info btn-block">
              <i class="fas fa-clipboard-check mr-1"></i> Set ke Verifikasi
            </button>
          </form>
        </div>
      </div>
      @endif

      {{-- Terima --}}
      <div class="card card-success">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Terima Pendaftar</h3>
        </div>
        <form action="{{ route('admin.ppdb.terima', $pendaftar) }}" method="POST">
          @csrf
          <div class="card-body">
            <p class="text-muted">Menerima akan otomatis membuat akun User + Siswa.</p>
            @if($errors->any())
              <div class="alert alert-danger py-2">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
              </div>
            @endif
            <div class="form-group">
              <label>Pilih Kelas Tujuan <span class="text-danger">*</span></label>
              <select name="kelas_id" class="form-control @error('kelas_id') is-invalid @enderror" required>
                <option value="">-- Pilih Kelas --</option>
                @foreach($kelasList as $k)
                  <option value="{{ $k->id }}">{{ $k->nama_kelas }} — {{ $k->jurusan?->nama }}</option>
                @endforeach
              </select>
              @error('kelas_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-success btn-block"
                    onclick="return confirm('Terima pendaftar ini? Akun siswa akan dibuat otomatis.')">
              <i class="fas fa-check mr-1"></i> Terima & Buat Akun
            </button>
          </div>
        </form>
      </div>

      {{-- Tolak --}}
      <div class="card card-danger">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-times mr-2"></i>Tolak Pendaftar</h3>
        </div>
        <form action="{{ route('admin.ppdb.tolak', $pendaftar) }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="form-group">
              <label>Alasan Penolakan</label>
              <textarea name="catatan_admin" rows="3" class="form-control"
                        placeholder="Opsional — alasan penolakan..."></textarea>
            </div>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-danger btn-block"
                    onclick="return confirm('Tolak pendaftar ini?')">
              <i class="fas fa-times mr-1"></i> Tolak Pendaftar
            </button>
          </div>
        </form>
      </div>

    @endif
  </div>

</div>
@endsection
