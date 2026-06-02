@extends('layouts.admin')

@section('title', 'Mengerjakan Ujian')

@section('page_title', $sesi->ujian->judul)

@push('styles')
<style>
  /* Fullscreen mode */
  .exam-container {
    position: relative;
  }
  
  .exam-container.fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    background: #fff;
    overflow-y: auto;
  }
  
  /* Timer */
  .timer-box {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: #fff;
    border-bottom: 2px solid #dee2e6;
  }
  
  .timer-display {
    font-size: 2rem;
    font-weight: bold;
  }
  
  .timer-warning {
    color: #ffc107;
  }
  
  .timer-danger {
    color: #dc3545;
    animation: blink 1s infinite;
  }
  
  @keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.5; }
  }
  
  /* Navigation */
  .soal-nav {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
    gap: 10px;
  }
  
  .soal-nav-btn {
    width: 100%;
    height: 50px;
    border: 2px solid #dee2e6;
    background: #fff;
    cursor: pointer;
    transition: all 0.3s;
  }
  
  .soal-nav-btn:hover {
    transform: scale(1.05);
  }
  
  .soal-nav-btn.active {
    background: #007bff;
    color: #fff;
    border-color: #007bff;
  }
  
  .soal-nav-btn.answered {
    background: #28a745;
    color: #fff;
    border-color: #28a745;
  }
  
  .soal-nav-btn.doubt {
    background: #ffc107;
    color: #000;
    border-color: #ffc107;
  }
  
  /* Soal content */
  .soal-content {
    min-height: 400px;
  }
  
  .opsi-item {
    padding: 15px;
    margin-bottom: 10px;
    border: 2px solid #dee2e6;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
  }
  
  .opsi-item:hover {
    background: #f8f9fa;
    border-color: #007bff;
  }
  
  .opsi-item.selected {
    background: #007bff;
    color: #fff;
    border-color: #007bff;
  }
  
  .opsi-item input[type="radio"] {
    margin-right: 10px;
  }
  
  /* Disable copy/paste/right-click */
  .no-select {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
  }
</style>
@endpush

@section('content')
<div class="exam-container" id="exam-container">
  <!-- Timer Bar -->
  <div class="timer-box p-3">
    <div class="row align-items-center">
      <div class="col-md-4">
        <h5 class="mb-0">{{ $sesi->ujian->judul }}</h5>
        <small class="text-muted">{{ $sesi->ujian->mapel_nama }}</small>
      </div>
      <div class="col-md-4 text-center">
        <div class="timer-display" id="timer">
          <i class="fas fa-clock"></i> <span id="timer-text">--:--:--</span>
        </div>
      </div>
      <div class="col-md-4 text-right">
        <button type="button" class="btn btn-warning" id="btn-fullscreen">
          <i class="fas fa-expand"></i> Fullscreen
        </button>
        <button type="button" class="btn btn-danger" id="btn-submit">
          <i class="fas fa-paper-plane"></i> Selesai
        </button>
      </div>
    </div>
  </div>

  <div class="container-fluid py-4">
    <div class="row">
      <!-- Soal Content -->
      <div class="col-md-9">
        <div class="card">
          <div class="card-body soal-content no-select">
            <div id="soal-container">
              <!-- Soal akan dimuat via JavaScript -->
            </div>
          </div>
          <div class="card-footer">
            <div class="row">
              <div class="col-6">
                <button type="button" class="btn btn-secondary" id="btn-prev" disabled>
                  <i class="fas fa-arrow-left"></i> Sebelumnya
                </button>
              </div>
              <div class="col-6 text-right">
                <button type="button" class="btn btn-primary" id="btn-next">
                  Selanjutnya <i class="fas fa-arrow-right"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Panel -->
      <div class="col-md-3">
        <div class="card" style="position: sticky; top: 100px;">
          <div class="card-header">
            <h3 class="card-title">Navigasi Soal</h3>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <small>
                <span class="badge badge-success">{{ $progress['terjawab'] }}</span> Terjawab<br>
                <span class="badge badge-warning">{{ $progress['ragu'] }}</span> Ragu-ragu<br>
                <span class="badge badge-secondary">{{ $progress['total'] - $progress['terjawab'] - $progress['ragu'] }}</span> Belum dijawab
              </small>
            </div>
            <div class="soal-nav" id="soal-nav">
              <!-- Navigation buttons akan dimuat via JavaScript -->
            </div>
          </div>
          <div class="card-footer">
            <div class="icheck-primary">
              <input type="checkbox" id="mark-doubt">
              <label for="mark-doubt">
                Tandai ragu-ragu
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Hidden data -->
<input type="hidden" id="sesi-id" value="{{ $sesi->id }}">
<input type="hidden" id="sisa-waktu" value="{{ $sesi->sisa_waktu_detik }}">
@endsection

@push('scripts')
<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

// Global variables
let currentSoalIndex = 0;
let soals = @json($soals);
let sesiId = $('#sesi-id').val();
let sisaWaktu = parseInt($('#sisa-waktu').val());
let timerInterval;
let proktorInterval;

$(function() {
  // Initialize
  initExam();
  startTimer();
  startProktorMonitoring();
  
  // Disable right-click
  $(document).on('contextmenu', function(e) {
    e.preventDefault();
    recordProktorEvent('right_click', {
      x: e.clientX,
      y: e.clientY
    });
    return false;
  });
  
  // Disable copy
  $(document).on('copy', function(e) {
    e.preventDefault();
    recordProktorEvent('copy', {
      text_length: window.getSelection().toString().length
    });
    return false;
  });
  
  // Disable paste
  $(document).on('paste', function(e) {
    e.preventDefault();
    recordProktorEvent('paste', {});
    return false;
  });
  
  // Detect tab switch
  $(document).on('visibilitychange', function() {
    if (document.hidden) {
      recordProktorEvent('tab_switch', {
        timestamp: new Date().toISOString()
      });
      
      Swal.fire({
        icon: 'warning',
        title: 'Peringatan!',
        text: 'Jangan pindah tab atau aplikasi lain selama ujian berlangsung!',
        timer: 3000,
        timerProgressBar: true
      });
    }
  });
  
  // Detect window blur
  $(window).on('blur', function() {
    recordProktorEvent('blur', {
      timestamp: new Date().toISOString()
    });
  });
  
  // Detect window focus
  $(window).on('focus', function() {
    recordProktorEvent('focus', {
      timestamp: new Date().toISOString()
    });
  });
  
  // Fullscreen
  $('#btn-fullscreen').on('click', function() {
    toggleFullscreen();
  });
  
  // Navigation
  $('#btn-prev').on('click', function() {
    if (currentSoalIndex > 0) {
      saveCurrentAnswer();
      currentSoalIndex--;
      loadSoal();
    }
  });
  
  $('#btn-next').on('click', function() {
    if (currentSoalIndex < soals.length - 1) {
      saveCurrentAnswer();
      currentSoalIndex++;
      loadSoal();
    }
  });
  
  // Submit
  $('#btn-submit').on('click', function() {
    submitExam();
  });
  
  // Mark doubt
  $('#mark-doubt').on('change', function() {
    let isDoubt = $(this).is(':checked');
    soals[currentSoalIndex].is_ragu = isDoubt;
    updateNavigation();
    saveCurrentAnswer();
  });
  
  // Prevent accidental close
  $(window).on('beforeunload', function(e) {
    e.preventDefault();
    return 'Ujian masih berlangsung. Yakin ingin keluar?';
  });
});

function initExam() {
  loadSoal();
  renderNavigation();
}

function loadSoal() {
  let soal = soals[currentSoalIndex];
  let html = '';
  
  html += '<div class="mb-4">';
  html += '<h5>Soal No. ' + (currentSoalIndex + 1) + '</h5>';
  html += '<div class="soal-text">' + soal.pertanyaan + '</div>';
  html += '</div>';
  
  if (soal.tipe === 'pilihan_ganda') {
    html += '<div class="opsi-list">';
    for (let key in soal.opsi) {
      let isSelected = soal.jawaban === key;
      html += '<div class="opsi-item ' + (isSelected ? 'selected' : '') + '" data-opsi="' + key + '">';
      html += '<input type="radio" name="jawaban" value="' + key + '" ' + (isSelected ? 'checked' : '') + '>';
      html += '<strong>' + key + '.</strong> ' + soal.opsi[key];
      html += '</div>';
    }
    html += '</div>';
  } else if (soal.tipe === 'benar_salah') {
    html += '<div class="opsi-list">';
    let isBenar = soal.jawaban === 'benar';
    let isSalah = soal.jawaban === 'salah';
    html += '<div class="opsi-item ' + (isBenar ? 'selected' : '') + '" data-opsi="benar">';
    html += '<input type="radio" name="jawaban" value="benar" ' + (isBenar ? 'checked' : '') + '> Benar';
    html += '</div>';
    html += '<div class="opsi-item ' + (isSalah ? 'selected' : '') + '" data-opsi="salah">';
    html += '<input type="radio" name="jawaban" value="salah" ' + (isSalah ? 'checked' : '') + '> Salah';
    html += '</div>';
    html += '</div>';
  } else {
    html += '<textarea class="form-control" rows="10" id="essay-answer" placeholder="Tulis jawaban Anda di sini...">' + (soal.jawaban || '') + '</textarea>';
  }
  
  $('#soal-container').html(html);
  
  // Update mark doubt checkbox
  $('#mark-doubt').prop('checked', soal.is_ragu);
  
  // Update navigation buttons
  $('#btn-prev').prop('disabled', currentSoalIndex === 0);
  $('#btn-next').prop('disabled', currentSoalIndex === soals.length - 1);
  
  // Bind opsi click
  $('.opsi-item').on('click', function() {
    let opsi = $(this).data('opsi');
    $('input[name="jawaban"][value="' + opsi + '"]').prop('checked', true);
    $('.opsi-item').removeClass('selected');
    $(this).addClass('selected');
    soals[currentSoalIndex].jawaban = opsi;
    updateNavigation();
  });
}

function saveCurrentAnswer() {
  let soal = soals[currentSoalIndex];
  let jawaban = null;
  
  if (soal.tipe === 'essay') {
    jawaban = $('#essay-answer').val();
  } else {
    jawaban = $('input[name="jawaban"]:checked').val();
  }
  
  soal.jawaban = jawaban;
  
  // Send to server
  $.ajax({
    url: '/siswa/sesi/' + sesiId + '/answer',
    method: 'POST',
    data: {
      jawaban_id: soal.jawaban_id,
      jawaban: jawaban,
      is_ragu: soal.is_ragu
    }),
    success: function(response) {
      console.log('Answer saved');
    },
    error: function(xhr) {
      console.error('Failed to save answer', xhr);
    }
  });
  
  updateNavigation();
}

function renderNavigation() {
  let html = '';
  soals.forEach(function(soal, index) {
    let classes = ['soal-nav-btn'];
    if (index === currentSoalIndex) classes.push('active');
    if (soal.jawaban) classes.push('answered');
    if (soal.is_ragu) classes.push('doubt');
    
    html += '<button type="button" class="' + classes.join(' ') + '" data-index="' + index + '">';
    html += (index + 1);
    html += '</button>';
  });
  
  $('#soal-nav').html(html);
  
  // Bind click
  $('.soal-nav-btn').on('click', function() {
    saveCurrentAnswer();
    currentSoalIndex = parseInt($(this).data('index'));
    loadSoal();
    renderNavigation();
  });
}

function updateNavigation() {
  renderNavigation();
  
  // Update progress
  let terjawab = soals.filter(s => s.jawaban).length;
  let ragu = soals.filter(s => s.is_ragu).length;
  let belum = soals.length - terjawab;
  
  $('.badge-success').text(terjawab);
  $('.badge-warning').text(ragu);
  $('.badge-secondary').text(belum);
}

function startTimer() {
  updateTimerDisplay();
  
  timerInterval = setInterval(function() {
    sisaWaktu--;
    updateTimerDisplay();
    
    if (sisaWaktu <= 0) {
      clearInterval(timerInterval);
      autoSubmit();
    }
  }, 1000);
}

function updateTimerDisplay() {
  let hours = Math.floor(sisaWaktu / 3600);
  let minutes = Math.floor((sisaWaktu % 3600) / 60);
  let seconds = sisaWaktu % 60;
  
  let display = pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
  $('#timer-text').text(display);
  
  // Warning colors
  if (sisaWaktu <= 300) { // 5 minutes
    $('#timer').addClass('timer-danger');
  } else if (sisaWaktu <= 600) { // 10 minutes
    $('#timer').addClass('timer-warning');
  }
}

function pad(num) {
  return num.toString().padStart(2, '0');
}

function toggleFullscreen() {
  let elem = document.getElementById('exam-container');
  
  if (!document.fullscreenElement) {
    elem.requestFullscreen().catch(err => {
      console.error('Error attempting to enable fullscreen:', err);
    });
    $('#btn-fullscreen').html('<i class="fas fa-compress"></i> Exit Fullscreen');
  } else {
    document.exitFullscreen();
    $('#btn-fullscreen').html('<i class="fas fa-expand"></i> Fullscreen');
    
    recordProktorEvent('fullscreen_exit', {
      timestamp: new Date().toISOString()
    });
  }
}

function submitExam() {
  Swal.fire({
    title: 'Selesai Ujian?',
    html: 'Pastikan semua jawaban sudah benar.<br><br>' +
          '<strong>Terjawab:</strong> ' + soals.filter(s => s.jawaban).length + ' / ' + soals.length + '<br>' +
          '<strong>Ragu-ragu:</strong> ' + soals.filter(s => s.is_ragu).length,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Selesai',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    allowOutsideClick: false
  }).then(function(result) {
    if (result.isConfirmed) {
      saveCurrentAnswer();
      
      $.ajax({
        url: '/siswa/sesi/' + sesiId + '/submit',
        method: 'POST',
        success: function(response) {
          clearInterval(timerInterval);
          clearInterval(proktorInterval);
          $(window).off('beforeunload');
          
          Swal.fire({
            icon: 'success',
            title: 'Ujian Selesai!',
            text: 'Jawaban Anda telah tersimpan.',
            allowOutsideClick: false
          }).then(function() {
            window.location.href = '/siswa/sesi/' + sesiId + '/result';
          });
        },
        error: function(xhr) {
          Swal.fire({
            icon: 'error',
            title: 'Gagal Submit',
            text: 'Terjadi kesalahan. Silakan coba lagi.'
          });
        }
      });
    }
  });
}

function autoSubmit() {
  Swal.fire({
    icon: 'warning',
    title: 'Waktu Habis!',
    text: 'Ujian akan otomatis diselesaikan.',
    timer: 3000,
    timerProgressBar: true,
    allowOutsideClick: false
  }).then(function() {
    saveCurrentAnswer();
    
    $.ajax({
      url: '/siswa/sesi/' + sesiId + '/submit',
      method: 'POST',
      success: function(response) {
        $(window).off('beforeunload');
        window.location.href = '/siswa/sesi/' + sesiId + '/result';
      }
    });
  });
}

function startProktorMonitoring() {
  // Send heartbeat every 30 seconds
  proktorInterval = setInterval(function() {
    recordProktorEvent('heartbeat', {
      soal_index: currentSoalIndex,
      sisa_waktu: sisaWaktu
    });
  }, 30000);
}

function recordProktorEvent(eventType, eventData) {
  $.ajax({
    url: '/siswa/sesi/' + sesiId + '/violation',
    method: 'POST',
    data: {
      type: eventType,
    },
    error: function(xhr) {
      console.error('Failed to record proktor event', xhr);
    }
  });
}
</script>
@endpush
