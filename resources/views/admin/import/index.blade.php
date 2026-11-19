@extends('layouts.admin')
@section('title', 'Import Data Excel')
@section('page_title', 'Import Data dari Excel')

@section('content')
<div class="row">
  <div class="col-12">


    @if(session('import_result'))
      @php $r = session('import_result'); @endphp
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h6><i class="fas fa-check-circle mr-1"></i> Import {{ $r['label'] }} Selesai</h6>
        <p class="mb-0">
          <strong>{{ $r['imported'] }}</strong> baris berhasil ditambahkan.
          @if(($r['updated'] ?? 0) > 0)
            <strong>{{ $r['updated'] }}</strong> baris diperbarui.
          @endif
          @if(count($r['skipped']) > 0)
            <strong>{{ count($r['skipped']) }}</strong> baris dilewati.
          @endif
        </p>
      </div>
      @if(!empty($r['akun_token']))
      <div class="callout callout-warning mb-3">
        <h5 class="mb-1"><i class="fas fa-key mr-1"></i> Unduh daftar akun sekarang</h5>
        <p class="mb-2" style="font-size:.9rem">
          {{ $r['akun_count'] }} akun baru dibuat dengan password acak. Unduh daftarnya untuk dibagikan ke masing-masing pemilik akun.
          File hanya bisa diunduh <strong>satu kali</strong> dan berlaku 30 menit. Simpan baik-baik dan minta pengguna mengganti password setelah login pertama.
        </p>
        <a href="{{ route('admin.import.akun', $r['akun_token']) }}" class="btn btn-warning btn-sm">
          <i class="fas fa-download mr-1"></i> Unduh daftar akun (.xlsx)
        </a>
      </div>
      @endif
      @if(count($r['skipped']) > 0)
      <div class="card card-outline card-warning mb-3">
        <div class="card-header py-2">
          <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> Baris Dilewati ({{ count($r['skipped']) }})</h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
          </div>
        </div>
        <div class="card-body p-0">
          <ul class="list-group list-group-flush" style="max-height:200px;overflow-y:auto">
            @foreach($r['skipped'] as $msg)
              <li class="list-group-item py-2" style="font-size:.85rem"><i class="fas fa-chevron-right text-warning mr-1"></i> {{ $msg }}</li>
            @endforeach
          </ul>
        </div>
      </div>
      @endif
    @endif

    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-excel mr-2 text-success"></i> Import Data Master dari Excel</h3>
      </div>
      <div class="card-body">
        <p class="text-muted mb-3">
          Pilih jenis data, download template, isi data sesuai format, lalu upload file <code>.xlsx</code>.
          Batas ukuran file: <strong>2 MB</strong>.
        </p>

        <ul class="nav nav-tabs" role="tablist">
          @foreach($types as $key => $meta)
          <li class="nav-item">
            <a class="nav-link {{ (session('active_tab', 'guru') === $key) ? 'active' : '' }}"
               data-toggle="tab" href="#tab-{{ $key }}" role="tab">
              {{ $meta['label'] }}
            </a>
          </li>
          @endforeach
        </ul>

        <div class="tab-content pt-3">
          @foreach($types as $key => $meta)
          <div class="tab-pane fade {{ (session('active_tab', 'guru') === $key) ? 'show active' : '' }}"
               id="tab-{{ $key }}" role="tabpanel">

            <div class="row">
              {{-- Kolom kiri: info template --}}
              <div class="col-md-5">
                <div class="card card-outline card-info mb-0">
                  <div class="card-body">
                    <h6 class="font-weight-bold mb-2"><i class="fas fa-info-circle text-info mr-1"></i> Format {{ $meta['label'] }}</h6>

                    @if($key === 'guru')
                      <p class="mb-1" style="font-size:.85rem">Kolom yang dibutuhkan:</p>
                      <table class="table table-sm table-bordered" style="font-size:.8rem">
                        <tr><td class="font-weight-bold">NIP</td><td>Nomor Induk Pegawai (jadi username)</td></tr>
                        <tr><td class="font-weight-bold">Nama Lengkap</td><td>Wajib diisi</td></tr>
                        <tr><td class="font-weight-bold">Bidang Studi</td><td>Opsional</td></tr>
                        <tr><td class="font-weight-bold">No HP</td><td>Opsional</td></tr>
                        <tr><td class="font-weight-bold">Email</td><td>Opsional</td></tr>
                      </table>
                      <small class="text-muted">NIP sudah ada? → baris dilewati. Password dibuat acak dan bisa diunduh setelah impor.</small>
                    @elseif($key === 'siswa')
                      <p class="mb-1" style="font-size:.85rem">Kolom yang dibutuhkan:</p>
                      <table class="table table-sm table-bordered" style="font-size:.8rem">
                        <tr><td class="font-weight-bold">NISN</td><td>Wajib, Nomor Induk Siswa Nasional (jadi username)</td></tr>
                        <tr><td class="font-weight-bold">Nama Lengkap</td><td>Wajib diisi</td></tr>
                        <tr><td class="font-weight-bold">Kelas</td><td>Wajib, nama kelas persis seperti di data kelas (contoh: XII TKJ 1)</td></tr>
                        <tr><td class="font-weight-bold">No HP</td><td>Opsional</td></tr>
                        <tr><td class="font-weight-bold">Tanggal Lahir</td><td>Wajib, format YYYY-MM-DD (atau tanggal Excel)</td></tr>
                        <tr><td class="font-weight-bold">Email</td><td>Opsional</td></tr>
                      </table>
                      <small class="text-muted">Kelas kosong/tidak ditemukan? → baris dilewati (nama kelas harus sama persis). Password dibuat acak dan bisa diunduh setelah impor.</small>
                    @elseif($key === 'mapel')
                      <p class="mb-1" style="font-size:.85rem">Kolom yang dibutuhkan:</p>
                      <table class="table table-sm table-bordered" style="font-size:.8rem">
                        <tr><td class="font-weight-bold">Kode Mapel</td><td>Unik, wajib. Kode yang sudah ada → data diperbarui</td></tr>
                        <tr><td class="font-weight-bold">Nama Mapel</td><td>Wajib diisi</td></tr>
                        <tr><td class="font-weight-bold">Deskripsi</td><td>Opsional</td></tr>
                      </table>
                      <small class="text-muted">Kode mapel sudah ada? → nama & deskripsi diperbarui (upsert).</small>
                    @elseif($key === 'tagihan')
                      <p class="mb-1" style="font-size:.85rem">Kolom yang dibutuhkan:</p>
                      <table class="table table-sm table-bordered" style="font-size:.8rem">
                        <tr><td class="font-weight-bold">NISN</td><td>Harus sudah ada di database</td></tr>
                        <tr><td class="font-weight-bold">Bulan</td><td>Format YYYY-MM (contoh: 2026-01)</td></tr>
                        <tr><td class="font-weight-bold">Nominal</td><td>Angka tanpa titik/koma</td></tr>
                        <tr><td class="font-weight-bold">Keterangan</td><td>Opsional</td></tr>
                      </table>
                      <small class="text-muted">NISN + bulan sama → nominal diperbarui (upsert).</small>
                    @endif

                    <div class="mt-3">
                      <a href="{{ route('admin.import.template', $key) }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-download mr-1"></i> Download Template
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Kolom kanan: form upload --}}
              <div class="col-md-7">
                <div class="card card-outline card-primary mb-0">
                  <div class="card-body">
                    <h6 class="font-weight-bold mb-3"><i class="fas fa-upload text-primary mr-1"></i> Upload File {{ $meta['label'] }}</h6>

                    <form action="{{ route('admin.import.store') }}" method="POST" enctype="multipart/form-data">
                      @csrf
                      <input type="hidden" name="type" value="{{ $key }}">

                      <div class="form-group">
                        <label>Pilih file Excel <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <div class="custom-file">
                            <input type="file" name="file" class="custom-file-input @error('file') is-invalid @enderror"
                                   id="file-{{ $key }}" accept=".xlsx,.xls">
                            <label class="custom-file-label" for="file-{{ $key }}" data-browse="Pilih File">Belum ada file dipilih</label>
                          </div>
                        </div>
                        @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                        <small class="form-text text-muted">Format: <code>.xlsx</code> atau <code>.xls</code>, maks 2 MB.</small>
                      </div>

                      <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin import data {{ $meta['label'] }}?')">
                        <i class="fas fa-file-import mr-1"></i> Import Sekarang
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>

          </div>
          @endforeach
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.custom-file-input').forEach(function (el) {
    el.addEventListener('change', function () {
      var fileName = this.files[0] ? this.files[0].name : 'Belum ada file dipilih';
      this.nextElementSibling.textContent = fileName;
    });
  });
</script>
@endpush
