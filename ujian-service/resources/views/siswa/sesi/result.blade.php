@extends('layouts.admin-siswa')

@section('title', 'Hasil Ujian')

@section('page_title', 'Hasil Ujian')

@push('styles')
<style>
  .result-header {
    text-align: center;
    padding: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    margin-bottom: 30px;
  }
  
  .result-score {
    font-size: 4rem;
    font-weight: bold;
    margin: 20px 0;
  }
  
  .result-status {
    font-size: 1.5rem;
    margin-top: 10px;
  }
  
  .result-status.lulus {
    color: #28a745;
  }
  
  .result-status.tidak-lulus {
    color: #dc3545;
  }
  
  .jawaban-item {
    border-left: 4px solid #dee2e6;
    padding-left: 15px;
    margin-bottom: 20px;
  }
  
  .jawaban-item.benar {
    border-left-color: #28a745;
    background: #f0fff4;
  }
  
  .jawaban-item.salah {
    border-left-color: #dc3545;
    background: #fff5f5;
  }
  
  .jawaban-item.belum-dinilai {
    border-left-color: #ffc107;
    background: #fffbf0;
  }
  
  .opsi-label {
    display: inline-block;
    width: 30px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    border-radius: 50%;
    margin-right: 10px;
    font-weight: bold;
  }
  
  .opsi-label.correct {
    background: #28a745;
    color: white;
  }
  
  .opsi-label.wrong {
    background: #dc3545;
    color: white;
  }
  
  .opsi-label.neutral {
    background: #6c757d;
    color: white;
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Result Header -->
    <div class="result-header">
      <h2>{{ $sesi->ujian->judul }}</h2>
      <p class="mb-0">{{ $sesi->ujian->mapel_nama }} - {{ $sesi->ujian->guru_nama }}</p>
      
      @if($sesi->ujian->tampilkan_nilai)
      <div class="result-score">
        {{ number_format($sesi->nilai_akhir, 2) }}
      </div>
      
      <div class="result-status {{ $sesi->nilai_akhir >= $sesi->ujian->passing_grade ? 'lulus' : 'tidak-lulus' }}">
        @if($sesi->nilai_akhir >= $sesi->ujian->passing_grade)
        <i class="fas fa-check-circle"></i> LULUS
        @else
        <i class="fas fa-times-circle"></i> TIDAK LULUS
        @endif
      </div>
      
      <p class="mt-3 mb-0">
        <small>Passing Grade: {{ $sesi->ujian->passing_grade }}</small>
      </p>
      @else
      <div class="alert alert-info mt-4">
        <i class="fas fa-info-circle"></i> Nilai akan diumumkan oleh guru
      </div>
      @endif
    </div>

    <!-- Statistik -->
    <div class="row">
      <div class="col-md-3">
        <div class="info-box bg-success">
          <span class="info-box-icon"><i class="fas fa-check"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Benar</span>
            <span class="info-box-number">{{ $statistik['benar'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-danger">
          <span class="info-box-icon"><i class="fas fa-times"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Salah</span>
            <span class="info-box-number">{{ $statistik['salah'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-warning">
          <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Belum Dinilai</span>
            <span class="info-box-number">{{ $statistik['belum_dinilai'] }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="info-box bg-info">
          <span class="info-box-icon"><i class="fas fa-clock"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Waktu Pengerjaan</span>
            <span class="info-box-number">{{ gmdate('H:i:s', $sesi->durasi_detik - ($sesi->sisa_waktu_detik ?? 0)) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Pembahasan Soal -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Pembahasan Soal</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        @foreach($jawabans as $index => $jawaban)
        <div class="jawaban-item {{ $jawaban->is_benar === true ? 'benar' : ($jawaban->is_benar === false ? 'salah' : 'belum-dinilai') }}">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5>
              <span class="badge badge-secondary">{{ $jawaban->nomor_urut }}</span>
              @if($jawaban->tipe == 'pilihan_ganda')
              <span class="badge badge-primary">Pilihan Ganda</span>
              @elseif($jawaban->tipe == 'benar_salah')
              <span class="badge badge-info">Benar/Salah</span>
              @else
              <span class="badge badge-warning">Essay</span>
              @endif
            </h5>
            <div>
              @if($jawaban->is_benar === true)
              <span class="badge badge-success"><i class="fas fa-check"></i> Benar</span>
              @elseif($jawaban->is_benar === false)
              <span class="badge badge-danger"><i class="fas fa-times"></i> Salah</span>
              @else
              <span class="badge badge-warning"><i class="fas fa-hourglass-half"></i> Belum Dinilai</span>
              @endif
              <span class="badge badge-dark">Nilai: {{ $jawaban->nilai }}</span>
            </div>
          </div>

          <div class="pertanyaan mb-3">
            <strong>Pertanyaan:</strong>
            <div>{!! $jawaban->pertanyaan !!}</div>
          </div>

          @if($jawaban->tipe == 'pilihan_ganda')
          <div class="opsi-jawaban mb-3">
            <strong>Opsi Jawaban:</strong>
            <div class="mt-2">
              @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
                @if(isset($jawaban->opsi[$opsi]))
                <div class="mb-2">
                  <span class="opsi-label {{ $opsi == $jawaban->kunci_jawaban ? 'correct' : ($opsi == $jawaban->jawaban && $opsi != $jawaban->kunci_jawaban ? 'wrong' : 'neutral') }}">
                    {{ $opsi }}
                  </span>
                  {{ $jawaban->opsi[$opsi] }}
                  @if($opsi == $jawaban->kunci_jawaban)
                  <span class="badge badge-success ml-2"><i class="fas fa-check"></i> Kunci Jawaban</span>
                  @endif
                  @if($opsi == $jawaban->jawaban && $opsi != $jawaban->kunci_jawaban)
                  <span class="badge badge-danger ml-2"><i class="fas fa-times"></i> Jawaban Anda</span>
                  @endif
                  @if($opsi == $jawaban->jawaban && $opsi == $jawaban->kunci_jawaban)
                  <span class="badge badge-success ml-2"><i class="fas fa-check"></i> Jawaban Anda (Benar)</span>
                  @endif
                </div>
                @endif
              @endforeach
            </div>
          </div>
          @elseif($jawaban->tipe == 'benar_salah')
          <div class="opsi-jawaban mb-3">
            <strong>Jawaban Anda:</strong> 
            <span class="badge {{ $jawaban->jawaban == $jawaban->kunci_jawaban ? 'badge-success' : 'badge-danger' }}">
              {{ ucfirst($jawaban->jawaban ?? 'Tidak dijawab') }}
            </span>
            <br>
            <strong>Kunci Jawaban:</strong> 
            <span class="badge badge-success">{{ ucfirst($jawaban->kunci_jawaban) }}</span>
          </div>
          @else
          <div class="jawaban-essay mb-3">
            <strong>Jawaban Anda:</strong>
            <div class="border p-3 bg-white rounded">
              {{ $jawaban->jawaban ?? 'Tidak dijawab' }}
            </div>
          </div>
          
          @if($jawaban->feedback)
          <div class="feedback mb-3">
            <strong>Feedback Guru:</strong>
            <div class="alert alert-info mb-0">
              {{ $jawaban->feedback }}
            </div>
          </div>
          @endif
          @endif

          @if($jawaban->pembahasan)
          <div class="pembahasan">
            <strong>Pembahasan:</strong>
            <div class="alert alert-light mb-0">
              {!! $jawaban->pembahasan !!}
            </div>
          </div>
          @endif
        </div>
        @endforeach
      </div>
      <div class="card-footer">
        <a href="{{ route('siswa.ujian.index') }}" class="btn btn-primary">
          <i class="fas fa-arrow-left"></i> Kembali ke Daftar Ujian
        </a>
        <button type="button" class="btn btn-secondary" onclick="window.print()">
          <i class="fas fa-print"></i> Cetak Hasil
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
  // Print styles
  $('<style>')
    .prop('type', 'text/css')
    .html(`
      @media print {
        .main-sidebar,
        .main-header,
        .content-header,
        .card-footer,
        .btn,
        .card-tools {
          display: none !important;
        }
        .content-wrapper {
          margin: 0 !important;
          padding: 0 !important;
        }
        .card {
          border: none !important;
          box-shadow: none !important;
        }
      }
    `)
    .appendTo('head');
});
</script>
@endpush
