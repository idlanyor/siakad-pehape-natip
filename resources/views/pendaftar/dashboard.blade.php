@extends('layouts.admin')
@section('title', 'Dashboard Pendaftar')
@section('page_title', 'Portal Pendaftar')

@section('content')


{{-- ── Header Profil & Status ────────────────────────────────── --}}
<div class="row">
  <div class="col-12">
    <div class="card card-primary card-outline">
      <div class="card-body d-flex flex-wrap align-items-center" style="gap:1rem">
        <div style="flex:1;min-width:220px">
          <h5 class="mb-0 font-weight-bold">{{ $pendaftar->nama }}</h5>
          <div class="text-muted small mt-1">
            <i class="fas fa-id-card mr-1"></i>{{ $pendaftar->no_pendaftaran }}
            &nbsp;&bull;&nbsp;
            <i class="fas fa-school mr-1"></i>{{ $pendaftar->asal_sekolah }}
          </div>
          <div class="mt-2">
            <span class="badge badge-{{ $pendaftar->status_color }} badge-lg" style="font-size:.85rem;padding:5px 12px">
              {{ $pendaftar->status_label }}
            </span>
            @if($pendaftar->gelombangPpdb)
              <span class="badge badge-secondary ml-1" style="font-size:.8rem;padding:5px 10px">
                {{ $pendaftar->gelombangPpdb->nama }} &bull; {{ $pendaftar->tahun_ajaran }}
              </span>
            @endif
          </div>
        </div>
        <div class="ml-auto d-flex" style="gap:.5rem">
          <a href="{{ route('pendaftar.cetak') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-print mr-1"></i> Cetak Bukti
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── Status Timeline ───────────────────────────────────────── --}}
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header"><h6 class="mb-0 font-weight-bold"><i class="fas fa-stream mr-2 text-primary"></i>Progres Pendaftaran</h6></div>
      <div class="card-body py-3">
        @php
          $steps = [
            ['key' => 'pending',    'label' => 'Pendaftaran Diterima', 'icon' => 'fas fa-file-alt'],
            ['key' => 'verifikasi', 'label' => 'Verifikasi Berkas',    'icon' => 'fas fa-search'],
            ['key' => 'diterima',   'label' => 'Diterima',             'icon' => 'fas fa-check-circle'],
          ];
          $statusOrder = ['pending' => 0, 'verifikasi' => 1, 'diterima' => 2, 'ditolak' => 2];
          $currentIdx  = $statusOrder[$pendaftar->status] ?? 0;
          $isDitolak   = $pendaftar->status === 'ditolak';
        @endphp
        <div class="d-flex align-items-center flex-wrap" style="gap:0">
          @foreach($steps as $i => $step)
            @php
              $done    = $i < $currentIdx || ($i === $currentIdx && !$isDitolak);
              $active  = $i === $currentIdx && !$isDitolak;
              $color   = $done ? '#28a745' : '#adb5bd';
              if ($isDitolak && $i === 2) { $color = '#dc3545'; }
            @endphp
            <div class="text-center" style="flex:1;min-width:80px">
              <div style="width:44px;height:44px;border-radius:50%;background:{{ $color }};color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:1.2rem">
                @if($isDitolak && $i === 2)
                  <i class="fas fa-times"></i>
                @else
                  <i class="{{ $step['icon'] }}"></i>
                @endif
              </div>
              <div class="mt-1 small font-weight-{{ $active ? 'bold' : 'normal' }}" style="color:{{ $color }}">
                @if($isDitolak && $i === 2) Ditolak @else {{ $step['label'] }} @endif
              </div>
            </div>
            @if(!$loop->last)
              <div style="flex:2;height:3px;background:{{ $i < $currentIdx ? '#28a745' : '#dee2e6' }};margin-bottom:20px;min-width:20px"></div>
            @endif
          @endforeach
        </div>
        @if($pendaftar->catatan_admin)
          <div class="alert alert-danger mt-3 mb-0">
            <i class="fas fa-comment-alt mr-2"></i><strong>Catatan Admin:</strong> {{ $pendaftar->catatan_admin }}
          </div>
        @endif
        @if($pendaftar->status === 'diterima' && $pendaftar->kelasDiterima)
          <div class="alert alert-success mt-3 mb-0">
            <i class="fas fa-check-circle mr-2"></i>
            Selamat! Kamu diterima di kelas <strong>{{ $pendaftar->kelasDiterima->nama_kelas ?? '-' }}</strong>.
            Silakan login kembali untuk mengakses portal siswa.
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ── Upload Berkas ─────────────────────────────────────────── --}}
@if($pendaftar->status !== 'diterima' && $pendaftar->status !== 'ditolak')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center">
        <h6 class="mb-0 font-weight-bold"><i class="fas fa-file-upload mr-2 text-primary"></i>Kelengkapan Berkas</h6>
        <span class="ml-auto small text-muted">{{ $uploadedCount }}/{{ $totalDokumen }} berkas diupload</span>
      </div>
      <div class="card-body p-0">

        {{-- Progress Bar --}}
        <div class="px-3 pt-3">
          @php $pct = $totalDokumen > 0 ? round($uploadedCount / $totalDokumen * 100) : 0; @endphp
          <div class="progress" style="height:8px">
            <div class="progress-bar bg-{{ $pct == 100 ? 'success' : 'primary' }}" style="width:{{ $pct }}%"></div>
          </div>
          <div class="text-right small text-muted mt-1">{{ $pct }}% lengkap</div>
        </div>

        <table class="table table-sm table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:5%">#</th>
              <th>Jenis Berkas</th>
              <th style="width:15%">Status</th>
              <th style="width:30%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dokumenFields as $field => $label)
              @php $hasFile = !empty($pendaftar->$field); @endphp
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  {{ $label }}
                  @if($field === 'foto')
                    <small class="text-muted">(JPG/PNG, maks 1MB)</small>
                  @else
                    <small class="text-muted">(PDF/JPG/PNG, maks 4MB)</small>
                  @endif
                </td>
                <td>
                  @if($hasFile)
                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Sudah</span>
                  @else
                    <span class="badge badge-secondary">Belum</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex align-items-center" style="gap:.4rem;flex-wrap:wrap">
                    {{-- Lihat file --}}
                    @if($hasFile)
                      <a href="{{ asset('storage/' . $pendaftar->$field) }}" target="_blank" class="btn btn-xs btn-outline-info">
                        <i class="fas fa-eye"></i> Lihat
                      </a>
                    @endif

                    {{-- Upload / Ganti --}}
                    <button type="button" class="btn btn-xs btn-{{ $hasFile ? 'outline-warning' : 'primary' }}"
                            data-toggle="modal" data-target="#modalUpload{{ $field }}">
                      <i class="fas fa-upload"></i> {{ $hasFile ? 'Ganti' : 'Upload' }}
                    </button>

                    {{-- Hapus --}}
                    @if($hasFile)
                      <form action="{{ route('pendaftar.dokumen.hapus', $field) }}" method="POST"
                            onsubmit="return confirm('Hapus berkas {{ $label }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-outline-danger">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endif

{{-- ── Info Pendaftaran ──────────────────────────────────────── --}}
<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h6 class="mb-0 font-weight-bold"><i class="fas fa-user mr-2 text-primary"></i>Data Pribadi</h6></div>
      <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
          <tr><th class="text-muted pl-3" style="width:45%">Nama Lengkap</th><td>{{ $pendaftar->nama }}</td></tr>
          <tr><th class="text-muted pl-3">Jenis Kelamin</th><td>{{ $pendaftar->jk_label }}</td></tr>
          <tr><th class="text-muted pl-3">Tempat Lahir</th><td>{{ $pendaftar->tempat_lahir }}</td></tr>
          <tr><th class="text-muted pl-3">Tanggal Lahir</th><td>{{ $pendaftar->tanggal_lahir?->format('d F Y') }}</td></tr>
          <tr><th class="text-muted pl-3">NISN</th><td>{{ $pendaftar->nisn ?: '-' }}</td></tr>
          <tr><th class="text-muted pl-3">No. HP</th><td>{{ $pendaftar->no_hp }}</td></tr>
          <tr><th class="text-muted pl-3">Email</th><td>{{ $pendaftar->email ?: '-' }}</td></tr>
          <tr><th class="text-muted pl-3">Alamat</th><td>{{ $pendaftar->alamat }}</td></tr>
          <tr><th class="text-muted pl-3">Asal Sekolah</th><td>{{ $pendaftar->asal_sekolah }}</td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h6 class="mb-0 font-weight-bold"><i class="fas fa-graduation-cap mr-2 text-primary"></i>Pilihan & Akademik</h6></div>
      <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
          <tr><th class="text-muted pl-3" style="width:45%">Jurusan Pilihan 1</th><td>{{ $pendaftar->jurusanPilihan?->nama_jurusan ?? '-' }}</td></tr>
          <tr><th class="text-muted pl-3">Jurusan Pilihan 2</th><td>{{ $pendaftar->jurusanPilihan2?->nama_jurusan ?? '-' }}</td></tr>
          <tr><th class="text-muted pl-3">Rata-rata Nilai</th><td>{{ $pendaftar->nilai_rata_rata ? number_format($pendaftar->nilai_rata_rata, 2) : '-' }}</td></tr>
          <tr><th class="text-muted pl-3">Gelombang</th><td>{{ $pendaftar->gelombangPpdb?->nama ?? '-' }}</td></tr>
          <tr><th class="text-muted pl-3">Tahun Ajaran</th><td>{{ $pendaftar->tahun_ajaran }}</td></tr>
        </table>
      </div>
    </div>
    <div class="card mt-0">
      <div class="card-header"><h6 class="mb-0 font-weight-bold"><i class="fas fa-users mr-2 text-primary"></i>Data Orang Tua</h6></div>
      <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
          <tr><th class="text-muted pl-3" style="width:45%">Nama Ayah</th><td>{{ $pendaftar->nama_ayah ?: '-' }}</td></tr>
          <tr><th class="text-muted pl-3">Nama Ibu</th><td>{{ $pendaftar->nama_ibu ?: '-' }}</td></tr>
          <tr><th class="text-muted pl-3">No. HP Ortu</th><td>{{ $pendaftar->no_hp_ortu ?: '-' }}</td></tr>
          <tr><th class="text-muted pl-3">Pekerjaan</th><td>{{ $pendaftar->pekerjaan_ortu ?: '-' }}</td></tr>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- ── Modal Upload per Berkas ───────────────────────────────── --}}
@foreach($dokumenFields as $field => $label)
<div class="modal fade" id="modalUpload{{ $field }}" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title font-weight-bold">Upload {{ $label }}</h6>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form action="{{ route('pendaftar.dokumen.upload', $field) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          @if($errors->has('file'))
            <div class="alert alert-danger py-2 small">{{ $errors->first('file') }}</div>
          @endif
          <div class="form-group mb-2">
            <label class="small text-muted">
              @if($field === 'foto') Format: JPG/PNG, maks 1MB
              @else Format: PDF/JPG/PNG, maks 4MB @endif
            </label>
            <input type="file" name="file" class="form-control-file" required
                   accept="{{ $field === 'foto' ? 'image/jpeg,image/png' : '.pdf,image/jpeg,image/png' }}">
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload mr-1"></i>Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

@endsection
