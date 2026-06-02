@extends('layouts.admin')

@section('title', 'Detail Soal')

@section('page_title', 'Detail Soal - ' . $ujian->judul)

@push('styles')
<style>
  .soal-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
  }
  
  .opsi-item {
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 8px;
    border: 2px solid #dee2e6;
    background: #fff;
    transition: all 0.3s;
  }
  
  .opsi-item.correct {
    border-color: #28a745;
    background: #d4edda;
  }
  
  .opsi-item .opsi-label {
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    border-radius: 50%;
    background: #6c757d;
    color: #fff;
    font-weight: bold;
    margin-right: 15px;
  }
  
  .opsi-item.correct .opsi-label {
    background: #28a745;
  }
  
  .pembahasan-box {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 20px;
    border-radius: 5px;
  }
  
  .media-preview {
    max-width: 100%;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Ujian Info -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Informasi Ujian</h3>
        <div class="card-tools">
          <a href="{{ route('ujian.show', $ujian->id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Ujian
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <strong>Judul:</strong> {{ $ujian->judul }}<br>
            <strong>Kelas:</strong> {{ $ujian->kelas_nama }}<br>
            <strong>Mapel:</strong> {{ $ujian->mapel_nama }}
          </div>
          <div class="col-md-6">
            <strong>Nomor Soal:</strong> {{ $soal->nomor_urut }} / {{ $ujian->jumlah_soal }}<br>
            <strong>Tipe:</strong> 
            <span class="badge badge-info">
              {{ ucwords(str_replace('_', ' ', $soal->tipe)) }}
            </span><br>
            <strong>Bobot:</strong> {{ $soal->bobot_nilai }} poin
          </div>
        </div>
      </div>
    </div>

    <!-- Soal Content -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-question-circle"></i> Soal Nomor {{ $soal->nomor_urut }}
        </h3>
        <div class="card-tools">
          <a href="{{ route('soal.edit', [$ujian->id, $soal->id]) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Edit Soal
          </a>
          <button type="button" class="btn btn-danger btn-sm" id="btn-delete">
            <i class="fas fa-trash"></i> Hapus
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="soal-content">
          <h5>Pertanyaan:</h5>
          <div class="pertanyaan-text">
            {!! $soal->pertanyaan !!}
          </div>
        </div>

        @if($soal->media_url)
        <div class="mb-4">
          <h5>Media Pendukung:</h5>
          @if(Str::startsWith($soal->media_type, 'image/'))
          <img src="{{ $soal->media_url }}" alt="Media Soal" class="media-preview">
          @elseif(Str::startsWith($soal->media_type, 'video/'))
          <video controls class="media-preview">
            <source src="{{ $soal->media_url }}" type="{{ $soal->media_type }}">
            Browser Anda tidak mendukung video.
          </video>
          @elseif(Str::startsWith($soal->media_type, 'audio/'))
          <audio controls class="w-100">
            <source src="{{ $soal->media_url }}" type="{{ $soal->media_type }}">
            Browser Anda tidak mendukung audio.
          </audio>
          @else
          <a href="{{ $soal->media_url }}" target="_blank" class="btn btn-info">
            <i class="fas fa-file"></i> Lihat Media
          </a>
          @endif
        </div>
        @endif

        @if($soal->tipe === 'pilihan_ganda')
        <div class="mb-4">
          <h5>Opsi Jawaban:</h5>
          
          <div class="opsi-item {{ $soal->kunci_jawaban === 'A' ? 'correct' : '' }}">
            <span class="opsi-label">A</span>
            <span class="opsi-text">{{ $soal->opsi_a }}</span>
            @if($soal->kunci_jawaban === 'A')
            <span class="badge badge-success float-right">
              <i class="fas fa-check"></i> Jawaban Benar
            </span>
            @endif
          </div>

          <div class="opsi-item {{ $soal->kunci_jawaban === 'B' ? 'correct' : '' }}">
            <span class="opsi-label">B</span>
            <span class="opsi-text">{{ $soal->opsi_b }}</span>
            @if($soal->kunci_jawaban === 'B')
            <span class="badge badge-success float-right">
              <i class="fas fa-check"></i> Jawaban Benar
            </span>
            @endif
          </div>

          <div class="opsi-item {{ $soal->kunci_jawaban === 'C' ? 'correct' : '' }}">
            <span class="opsi-label">C</span>
            <span class="opsi-text">{{ $soal->opsi_c }}</span>
            @if($soal->kunci_jawaban === 'C')
            <span class="badge badge-success float-right">
              <i class="fas fa-check"></i> Jawaban Benar
            </span>
            @endif
          </div>

          <div class="opsi-item {{ $soal->kunci_jawaban === 'D' ? 'correct' : '' }}">
            <span class="opsi-label">D</span>
            <span class="opsi-text">{{ $soal->opsi_d }}</span>
            @if($soal->kunci_jawaban === 'D')
            <span class="badge badge-success float-right">
              <i class="fas fa-check"></i> Jawaban Benar
            </span>
            @endif
          </div>

          @if($soal->opsi_e)
          <div class="opsi-item {{ $soal->kunci_jawaban === 'E' ? 'correct' : '' }}">
            <span class="opsi-label">E</span>
            <span class="opsi-text">{{ $soal->opsi_e }}</span>
            @if($soal->kunci_jawaban === 'E')
            <span class="badge badge-success float-right">
              <i class="fas fa-check"></i> Jawaban Benar
            </span>
            @endif
          </div>
          @endif
        </div>
        @elseif($soal->tipe === 'benar_salah')
        <div class="mb-4">
          <h5>Kunci Jawaban:</h5>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> 
            <strong>{{ ucfirst($soal->kunci_jawaban) }}</strong>
          </div>
        </div>
        @elseif($soal->tipe === 'essay')
        <div class="mb-4">
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Soal essay akan dinilai secara manual oleh guru
          </div>
        </div>
        @endif

        @if($soal->pembahasan)
        <div class="pembahasan-box">
          <h5><i class="fas fa-lightbulb"></i> Pembahasan:</h5>
          <div class="pembahasan-text">
            {!! $soal->pembahasan !!}
          </div>
        </div>
        @endif
      </div>
      <div class="card-footer">
        <div class="row">
          <div class="col-md-6">
            @if($prevSoal)
            <a href="{{ route('soal.show', [$ujian->id, $prevSoal->id]) }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left"></i> Soal Sebelumnya
            </a>
            @endif
          </div>
          <div class="col-md-6 text-right">
            @if($nextSoal)
            <a href="{{ route('soal.show', [$ujian->id, $nextSoal->id]) }}" class="btn btn-secondary">
              Soal Selanjutnya <i class="fas fa-arrow-right"></i>
            </a>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Statistik Jawaban (jika ada sesi) -->
    @if($statistik)
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-chart-bar"></i> Statistik Jawaban
        </h3>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <div class="info-box bg-info">
              <span class="info-box-icon"><i class="fas fa-users"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Total Peserta</span>
                <span class="info-box-number">{{ $statistik['total_peserta'] }}</span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="info-box bg-success">
              <span class="info-box-icon"><i class="fas fa-check"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Jawaban Benar</span>
                <span class="info-box-number">{{ $statistik['jawaban_benar'] }}</span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="info-box bg-danger">
              <span class="info-box-icon"><i class="fas fa-times"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Jawaban Salah</span>
                <span class="info-box-number">{{ $statistik['jawaban_salah'] }}</span>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="info-box bg-warning">
              <span class="info-box-icon"><i class="fas fa-question"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Tidak Dijawab</span>
                <span class="info-box-number">{{ $statistik['tidak_dijawab'] }}</span>
              </div>
            </div>
          </div>
        </div>

        @if($soal->tipe === 'pilihan_ganda' && isset($statistik['distribusi_jawaban']))
        <div class="mt-3">
          <h5>Distribusi Jawaban:</h5>
          <canvas id="chart-distribusi" height="80"></canvas>
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>
</div>

<!-- Hidden form for delete -->
<form id="form-delete" action="{{ route('soal.destroy', [$ujian->id, $soal->id]) }}" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>
@endsection

@push('scripts')
@if($soal->tipe === 'pilihan_ganda' && isset($statistik['distribusi_jawaban']))
<!-- Chart.js -->
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<script>
$(function() {
  // Distribusi Jawaban Chart
  var ctx = document.getElementById('chart-distribusi').getContext('2d');
  var distribusi = @json($statistik['distribusi_jawaban']);
  
  var labels = Object.keys(distribusi);
  var data = Object.values(distribusi);
  var backgroundColors = labels.map(function(label) {
    return label === '{{ $soal->kunci_jawaban }}' ? '#28a745' : '#6c757d';
  });
  
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Jumlah Siswa',
        data: data,
        backgroundColor: backgroundColors,
        borderColor: backgroundColors,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              var label = context.label || '';
              var value = context.parsed.y || 0;
              var total = {{ $statistik['total_peserta'] }};
              var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
              return label + ': ' + value + ' siswa (' + percentage + '%)';
            }
          }
        }
      }
    }
  });
});
</script>
@endif

<script>
$(function() {
  // Delete button
  $('#btn-delete').on('click', function() {
    Swal.fire({
      title: 'Hapus Soal?',
      text: 'Soal nomor {{ $soal->nomor_urut }} akan dihapus permanen',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $('#form-delete').submit();
      }
    });
  });
});
</script>
@endpush
