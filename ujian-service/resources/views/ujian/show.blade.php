@extends('layouts.admin')

@section('title', 'Detail Ujian')

@section('page_title', 'Detail Ujian')

@push('styles')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-md-8">
    <!-- Informasi Ujian -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Informasi Ujian</h3>
        <div class="card-tools">
          @if($ujian->is_published)
          <span class="badge badge-success">Published</span>
          @else
          <span class="badge badge-secondary">Draft</span>
          @endif
        </div>
      </div>
      <div class="card-body">
        <table class="table table-borderless">
          <tr>
            <th width="30%">Judul</th>
            <td>{{ $ujian->judul }}</td>
          </tr>
          <tr>
            <th>Periode</th>
            <td>{{ $ujian->periode->nama }} ({{ $ujian->periode->tahun_ajaran }})</td>
          </tr>
          <tr>
            <th>Kelas</th>
            <td>{{ $ujian->kelas_nama }}</td>
          </tr>
          <tr>
            <th>Mata Pelajaran</th>
            <td>{{ $ujian->mapel_nama }}</td>
          </tr>
          <tr>
            <th>Guru</th>
            <td>{{ $ujian->guru_nama }}</td>
          </tr>
          <tr>
            <th>Deskripsi</th>
            <td>{{ $ujian->deskripsi ?? '-' }}</td>
          </tr>
          <tr>
            <th>Waktu Mulai</th>
            <td>{{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('d/m/Y H:i') }}</td>
          </tr>
          <tr>
            <th>Waktu Selesai</th>
            <td>{{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('d/m/Y H:i') }}</td>
          </tr>
          <tr>
            <th>Durasi</th>
            <td>{{ $ujian->durasi_menit }} menit</td>
          </tr>
          <tr>
            <th>Passing Grade</th>
            <td>{{ $ujian->passing_grade }}</td>
          </tr>
          <tr>
            <th>Jumlah Soal</th>
            <td>
              <span class="badge {{ $ujian->soals_count >= $ujian->jumlah_soal ? 'badge-success' : 'badge-warning' }}">
                {{ $ujian->soals_count }} / {{ $ujian->jumlah_soal }}
              </span>
            </td>
          </tr>
          <tr>
            <th>Pengaturan</th>
            <td>
              @if($ujian->acak_soal)
              <span class="badge badge-info">Acak Soal</span>
              @endif
              @if($ujian->acak_opsi)
              <span class="badge badge-info">Acak Opsi</span>
              @endif
              @if($ujian->tampilkan_nilai)
              <span class="badge badge-info">Tampilkan Nilai</span>
              @endif
            </td>
          </tr>
        </table>
      </div>
      <div class="card-footer">
        @if(Auth::user()->isGuru() && $ujian->guru_id == Auth::id() || Auth::user()->isAdmin())
          @if(!$ujian->is_published)
          <a href="{{ route('ujian.edit', $ujian->id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
          </a>
          @endif
          
          @if($ujian->is_published)
          <form action="{{ route('ujian.unpublish', $ujian->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-secondary btn-unpublish">
              <i class="fas fa-eye-slash"></i> Unpublish
            </button>
          </form>
          @else
          <form action="{{ route('ujian.publish', $ujian->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success btn-publish">
              <i class="fas fa-check"></i> Publish
            </button>
          </form>
          @endif
          
          @if(!$ujian->has_sessions)
          <form action="{{ route('ujian.destroy', $ujian->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-delete">
              <i class="fas fa-trash"></i> Hapus
            </button>
          </form>
          @endif
        @endif
        
        <a href="{{ route('ujian.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>
    </div>

    <!-- Daftar Soal -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Soal ({{ $ujian->soals_count }})</h3>
        <div class="card-tools">
          @if(!$ujian->is_published && (Auth::user()->isGuru() && $ujian->guru_id == Auth::id() || Auth::user()->isAdmin()))
          <a href="{{ route('soal.create', $ujian->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Soal
          </a>
          <a href="{{ route('soal.import-bank', $ujian->id) }}" class="btn btn-info btn-sm">
            <i class="fas fa-download"></i> Import dari Bank Soal
          </a>
          @endif
        </div>
      </div>
      <div class="card-body">
        @if($ujian->soals_count > 0)
        <table id="soal-table" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th width="5%">No</th>
              <th>Pertanyaan</th>
              <th width="15%">Tipe</th>
              <th width="10%">Bobot</th>
              <th width="15%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($ujian->soals as $soal)
            <tr>
              <td>{{ $soal->nomor_urut }}</td>
              <td>{{ Str::limit(strip_tags($soal->pertanyaan), 100) }}</td>
              <td>
                @if($soal->tipe == 'pilihan_ganda')
                <span class="badge badge-primary">Pilihan Ganda</span>
                @elseif($soal->tipe == 'benar_salah')
                <span class="badge badge-info">Benar/Salah</span>
                @else
                <span class="badge badge-warning">Essay</span>
                @endif
              </td>
              <td>{{ $soal->bobot_nilai }}</td>
              <td>
                <a href="{{ route('soal.show', [$ujian->id, $soal->id]) }}" class="btn btn-info btn-sm" title="Detail">
                  <i class="fas fa-eye"></i>
                </a>
                @if(!$ujian->is_published)
                <a href="{{ route('soal.edit', [$ujian->id, $soal->id]) }}" class="btn btn-warning btn-sm" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('soal.destroy', [$ujian->id, $soal->id]) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm btn-delete-soal" title="Hapus">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @else
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> Belum ada soal. Silakan tambah soal terlebih dahulu.
        </div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <!-- Statistik -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Statistik</h3>
      </div>
      <div class="card-body">
        <div class="info-box bg-info">
          <span class="info-box-icon"><i class="fas fa-users"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Total Peserta</span>
            <span class="info-box-number">{{ $ujian->sesis_count }}</span>
          </div>
        </div>

        <div class="info-box bg-success">
          <span class="info-box-icon"><i class="fas fa-check"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Selesai</span>
            <span class="info-box-number">{{ $ujian->sesis()->where('status', 'selesai')->count() }}</span>
          </div>
        </div>

        <div class="info-box bg-warning">
          <span class="info-box-icon"><i class="fas fa-clock"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Berlangsung</span>
            <span class="info-box-number">{{ $ujian->sesis()->where('status', 'berlangsung')->count() }}</span>
          </div>
        </div>

        @if($ujian->sesis()->where('status', 'selesai')->count() > 0)
        <hr>
        <div class="row">
          <div class="col-6">
            <div class="description-block">
              <h5 class="description-header">{{ number_format($ujian->sesis()->where('status', 'selesai')->avg('nilai_akhir'), 2) }}</h5>
              <span class="description-text">Rata-rata Nilai</span>
            </div>
          </div>
          <div class="col-6">
            <div class="description-block">
              <h5 class="description-header">{{ number_format($ujian->sesis()->where('status', 'selesai')->max('nilai_akhir'), 2) }}</h5>
              <span class="description-text">Nilai Tertinggi</span>
            </div>
          </div>
        </div>
        @endif
      </div>
      @if($ujian->sesis_count > 0)
      <div class="card-footer">
        <a href="{{ route('hasil.ujian', $ujian->id) }}" class="btn btn-primary btn-block">
          <i class="fas fa-chart-bar"></i> Lihat Hasil Lengkap
        </a>
      </div>
      @endif
    </div>

    <!-- Status Timeline -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Status</h3>
      </div>
      <div class="card-body">
        <div class="timeline">
          <div class="time-label">
            <span class="bg-primary">{{ \Carbon\Carbon::parse($ujian->created_at)->format('d M Y') }}</span>
          </div>
          <div>
            <i class="fas fa-plus bg-blue"></i>
            <div class="timeline-item">
              <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($ujian->created_at)->format('H:i') }}</span>
              <h3 class="timeline-header">Ujian dibuat</h3>
            </div>
          </div>

          @if($ujian->is_published)
          <div>
            <i class="fas fa-check bg-green"></i>
            <div class="timeline-item">
              <h3 class="timeline-header">Ujian dipublish</h3>
            </div>
          </div>
          @endif

          @if(now()->between($ujian->waktu_mulai, $ujian->waktu_selesai))
          <div>
            <i class="fas fa-play bg-warning"></i>
            <div class="timeline-item">
              <h3 class="timeline-header">Ujian sedang berlangsung</h3>
            </div>
          </div>
          @elseif(now()->gt($ujian->waktu_selesai))
          <div>
            <i class="fas fa-flag-checkered bg-gray"></i>
            <div class="timeline-item">
              <h3 class="timeline-header">Ujian telah selesai</h3>
            </div>
          </div>
          @endif

          <div>
            <i class="fas fa-clock bg-gray"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- DataTables -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<script>
$(function() {
  // Initialize DataTable
  $('#soal-table').DataTable({
    responsive: true,
    paging: false,
    searching: false,
    info: false,
    order: [[0, 'asc']]
  });

  // Delete confirmation
  $('.btn-delete').on('click', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    
    Swal.fire({
      title: 'Konfirmasi Hapus',
      text: 'Apakah Anda yakin ingin menghapus ujian ini?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then(function(result) {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  // Delete soal confirmation
  $('.btn-delete-soal').on('click', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    
    Swal.fire({
      title: 'Konfirmasi Hapus',
      text: 'Apakah Anda yakin ingin menghapus soal ini?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then(function(result) {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  // Publish confirmation
  $('.btn-publish').on('click', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    
    Swal.fire({
      title: 'Konfirmasi Publish',
      text: 'Ujian yang sudah dipublish akan dapat diakses oleh siswa. Pastikan semua soal sudah lengkap. Lanjutkan?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Publish',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then(function(result) {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  // Unpublish confirmation
  $('.btn-unpublish').on('click', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    
    Swal.fire({
      title: 'Konfirmasi Unpublish',
      text: 'Ujian akan ditarik dari akses siswa. Lanjutkan?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Unpublish',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then(function(result) {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });
});
</script>
@endpush
