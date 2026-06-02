@extends('layouts.admin')

@section('title', 'Ujian Tersedia')

@section('page_title', 'Ujian Tersedia')

@push('styles')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Daftar Ujian</h3>
      </div>
      <div class="card-body">
        @if($ujians->count() > 0)
        <div class="row">
          @foreach($ujians as $ujian)
          <div class="col-md-6 col-lg-4">
            <div class="card card-outline {{ $ujian->status_color }}">
              <div class="card-header">
                <h3 class="card-title">{{ $ujian->judul }}</h3>
                <div class="card-tools">
                  <span class="badge badge-{{ $ujian->status_badge }}">{{ $ujian->status_label }}</span>
                </div>
              </div>
              <div class="card-body">
                <p><strong>Mata Pelajaran:</strong> {{ $ujian->mapel_nama }}</p>
                <p><strong>Guru:</strong> {{ $ujian->guru_nama }}</p>
                <p><strong>Waktu:</strong><br>
                  <small>
                    <i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('d/m/Y H:i') }}<br>
                    <i class="far fa-clock"></i> Durasi: {{ $ujian->durasi_menit }} menit
                  </small>
                </p>
                <p><strong>Jumlah Soal:</strong> {{ $ujian->jumlah_soal }}</p>
                <p><strong>Passing Grade:</strong> {{ $ujian->passing_grade }}</p>
                
                @php
                  $sesi = $ujian->sesis()->where('siswa_id', Auth::id())->where('is_resmi', true)->first();
                  $now = now();
                  $canStart = $now->between($ujian->waktu_mulai, $ujian->waktu_selesai);
                @endphp

                @if($sesi)
                  @if($sesi->status == 'berlangsung')
                    <div class="alert alert-warning mb-2">
                      <i class="fas fa-exclamation-triangle"></i> Ujian sedang berlangsung
                    </div>
                    <a href="{{ route('siswa.sesi.show', $sesi->id) }}" class="btn btn-warning btn-block">
                      <i class="fas fa-play"></i> Lanjutkan Ujian
                    </a>
                  @elseif($sesi->status == 'selesai')
                    <div class="alert alert-success mb-2">
                      <i class="fas fa-check"></i> Ujian telah selesai
                    </div>
                    @if($ujian->tampilkan_nilai)
                    <a href="{{ route('siswa.sesi.result', $sesi->id) }}" class="btn btn-info btn-block">
                      <i class="fas fa-chart-bar"></i> Lihat Hasil
                    </a>
                    @else
                    <button class="btn btn-secondary btn-block" disabled>
                      <i class="fas fa-lock"></i> Nilai Belum Tersedia
                    </button>
                    @endif
                  @endif
                @else
                  @if($canStart)
                    <form action="{{ route('siswa.sesi.store', $ujian->id) }}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-primary btn-block btn-start-ujian">
                        <i class="fas fa-play"></i> Mulai Ujian
                      </button>
                    </form>
                  @elseif($now->lt($ujian->waktu_mulai))
                    <div class="alert alert-info mb-2">
                      <i class="fas fa-info-circle"></i> Belum dimulai
                    </div>
                    <button class="btn btn-secondary btn-block" disabled>
                      <i class="fas fa-clock"></i> Menunggu Waktu Mulai
                    </button>
                  @else
                    <div class="alert alert-danger mb-2">
                      <i class="fas fa-times-circle"></i> Waktu telah berakhir
                    </div>
                    <button class="btn btn-secondary btn-block" disabled>
                      <i class="fas fa-ban"></i> Tidak Dapat Dikerjakan
                    </button>
                  @endif
                @endif
              </div>
              <div class="card-footer">
                <small class="text-muted">
                  <i class="fas fa-info-circle"></i> 
                  @if($ujian->acak_soal) Soal diacak. @endif
                  @if($ujian->acak_opsi) Opsi diacak. @endif
                </small>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> Belum ada ujian yang tersedia untuk Anda saat ini.
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
  // Confirm before starting exam
  $('.btn-start-ujian').on('click', function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    
    Swal.fire({
      title: 'Mulai Ujian?',
      html: 'Pastikan Anda sudah siap.<br><br>' +
            '<strong>Perhatian:</strong><br>' +
            '• Jangan menutup browser atau tab<br>' +
            '• Jangan pindah ke aplikasi lain<br>' +
            '• Pastikan koneksi internet stabil<br>' +
            '• Waktu akan berjalan setelah Anda mulai',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Mulai Sekarang',
      cancelButtonText: 'Batal',
      reverseButtons: true,
      allowOutsideClick: false
    }).then(function(result) {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });
});
</script>
@endpush
