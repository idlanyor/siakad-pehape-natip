@extends('layouts.admin')

@section('title', 'Dashboard Proktor Monitoring')

@section('page_title', 'Dashboard Proktor Monitoring')

@push('styles')
<style>
  .session-card {
    border-left: 4px solid #6c757d;
    transition: all 0.3s;
  }
  
  .session-card.active {
    border-left-color: #28a745;
    background: #f0fff4;
  }
  
  .session-card.warning {
    border-left-color: #ffc107;
    background: #fffbf0;
  }
  
  .session-card.danger {
    border-left-color: #dc3545;
    background: #fff5f5;
  }
  
  .event-item {
    padding: 10px;
    margin-bottom: 5px;
    border-radius: 5px;
    font-size: 0.9rem;
  }
  
  .event-item.low {
    background: #e7f3ff;
    border-left: 3px solid #17a2b8;
  }
  
  .event-item.medium {
    background: #fff3cd;
    border-left: 3px solid #ffc107;
  }
  
  .event-item.high {
    background: #f8d7da;
    border-left: 3px solid #dc3545;
  }
  
  .event-item.critical {
    background: #721c24;
    color: #fff;
    border-left: 3px solid #fff;
    animation: pulse 2s infinite;
  }
  
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
  }
  
  .stats-box {
    text-align: center;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
  }
  
  .stats-box h3 {
    font-size: 2.5rem;
    margin: 10px 0;
  }
  
  .online-indicator {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #28a745;
    animation: blink 2s infinite;
  }
  
  @keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0.3; }
  }
  
  .event-log {
    max-height: 400px;
    overflow-y: auto;
  }
  
  .filter-buttons .btn {
    margin-right: 5px;
    margin-bottom: 5px;
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Filter Ujian -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Pilih Ujian untuk Monitoring</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="ujian-select">Ujian</label>
          <select id="ujian-select" class="form-control select2">
            <option value="">Pilih Ujian...</option>
            @foreach($ujians as $ujian)
            <option value="{{ $ujian->id }}" data-periode="{{ $ujian->periode->nama }}">
              {{ $ujian->judul }} - {{ $ujian->kelas_nama }} ({{ $ujian->periode->nama }})
            </option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <!-- Statistik -->
    <div id="stats-container" style="display: none;">
      <div class="row">
        <div class="col-md-3">
          <div class="stats-box bg-info">
            <i class="fas fa-users fa-2x"></i>
            <h3 id="total-sessions">0</h3>
            <p>Total Peserta</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-box bg-success">
            <i class="fas fa-check fa-2x"></i>
            <h3 id="completed-sessions">0</h3>
            <p>Selesai</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-box bg-warning">
            <i class="fas fa-clock fa-2x"></i>
            <h3 id="active-sessions">0</h3>
            <p>Sedang Berlangsung</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-box bg-danger">
            <i class="fas fa-exclamation-triangle fa-2x"></i>
            <h3 id="suspicious-count">0</h3>
            <p>Aktivitas Mencurigakan</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Active Sessions -->
    <div id="sessions-container" style="display: none;">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <span class="online-indicator"></span> Sesi Aktif
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-sm btn-primary" id="refresh-sessions">
              <i class="fas fa-sync"></i> Refresh
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="row" id="active-sessions-list">
            <!-- Will be populated via JavaScript -->
          </div>
        </div>
      </div>
    </div>

    <!-- Event Log -->
    <div id="events-container" style="display: none;">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Log Aktivitas Realtime</h3>
          <div class="card-tools">
            <div class="filter-buttons">
              <button type="button" class="btn btn-sm btn-outline-secondary filter-btn active" data-filter="all">
                Semua
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger filter-btn" data-filter="critical">
                Critical
              </button>
              <button type="button" class="btn btn-sm btn-outline-warning filter-btn" data-filter="high">
                High
              </button>
              <button type="button" class="btn btn-sm btn-outline-info filter-btn" data-filter="medium">
                Medium
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary filter-btn" data-filter="low">
                Low
              </button>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="event-log" id="event-log">
            <div class="text-center text-muted">
              <i class="fas fa-info-circle"></i> Menunggu aktivitas...
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Hidden data -->
<input type="hidden" id="jwt-token" value="{{ session('jwt_token') }}">
@endsection

@push('scripts')
<!-- Select2 -->
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<!-- Laravel Echo & Pusher -->
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1/dist/echo.iife.js"></script>

<script>
let currentUjianId = null;
let jwtToken = $('#jwt-token').val();
let echo = null;
let eventFilter = 'all';
let sessions = {};
let eventCount = 0;

$(function() {
  // Initialize Select2
  $('#ujian-select').select2({
    theme: 'bootstrap4',
    placeholder: 'Pilih Ujian...'
  });

  // Initialize Echo
  initEcho();

  // Ujian selection
  $('#ujian-select').on('change', function() {
    let ujianId = $(this).val();
    if (ujianId) {
      loadUjian(ujianId);
    } else {
      hideAllContainers();
    }
  });

  // Refresh sessions
  $('#refresh-sessions').on('click', function() {
    if (currentUjianId) {
      loadActiveSessions(currentUjianId);
    }
  });

  // Filter buttons
  $('.filter-btn').on('click', function() {
    $('.filter-btn').removeClass('active');
    $(this).addClass('active');
    eventFilter = $(this).data('filter');
    filterEvents();
  });

  // Auto-refresh stats every 30 seconds
  setInterval(function() {
    if (currentUjianId) {
      loadStatistik(currentUjianId);
    }
  }, 30000);
});

function initEcho() {
  echo = new Echo({
    broadcaster: 'reverb',
    key: '{{ config("broadcasting.connections.reverb.key") }}',
    wsHost: '{{ config("broadcasting.connections.reverb.host") }}',
    wsPort: {{ config("broadcasting.connections.reverb.port") }},
    wssPort: {{ config("broadcasting.connections.reverb.port") }},
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
      headers: {
        Authorization: `Bearer ${jwtToken}`,
      },
    },
  });

  console.log('Echo initialized');
}

function loadUjian(ujianId) {
  currentUjianId = ujianId;
  
  // Show containers
  $('#stats-container').show();
  $('#sessions-container').show();
  $('#events-container').show();
  
  // Load data
  loadStatistik(ujianId);
  loadActiveSessions(ujianId);
  
  // Subscribe to proktor channel
  subscribeToProktor(ujianId);
}

function hideAllContainers() {
  currentUjianId = null;
  $('#stats-container').hide();
  $('#sessions-container').hide();
  $('#events-container').hide();
  
  // Unsubscribe from channels
  if (echo) {
    echo.leaveChannel(`proktor.ujian.${currentUjianId}`);
  }
}

function loadStatistik(ujianId) {
  $.ajax({
    url: `/api/proktor/statistik?ujian_id=${ujianId}`,
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${jwtToken}`
    },
    success: function(response) {
      let data = response.data;
      $('#total-sessions').text(data.total_sessions);
      $('#completed-sessions').text(data.completed_sessions);
      $('#active-sessions').text(data.active_sessions);
      $('#suspicious-count').text(data.suspicious_events);
    },
    error: function(xhr) {
      console.error('Failed to load statistik', xhr);
    }
  });
}

function loadActiveSessions(ujianId) {
  $.ajax({
    url: `/api/proktor/active-sessions?ujian_id=${ujianId}`,
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${jwtToken}`
    },
    success: function(response) {
      renderActiveSessions(response.data);
    },
    error: function(xhr) {
      console.error('Failed to load active sessions', xhr);
    }
  });
}

function renderActiveSessions(data) {
  let html = '';
  
  if (data.length === 0) {
    html = '<div class="col-12"><div class="alert alert-info">Tidak ada sesi aktif saat ini.</div></div>';
  } else {
    data.forEach(function(sesi) {
      sessions[sesi.id] = sesi;
      
      let cardClass = 'session-card';
      if (sesi.suspicious_count > 10) {
        cardClass += ' danger';
      } else if (sesi.suspicious_count > 5) {
        cardClass += ' warning';
      } else {
        cardClass += ' active';
      }
      
      html += `
        <div class="col-md-4">
          <div class="card ${cardClass}">
            <div class="card-body">
              <h5>${sesi.siswa_nama}</h5>
              <p class="mb-1"><small class="text-muted">Kelas: ${sesi.kelas_nama}</small></p>
              <p class="mb-2">
                <i class="fas fa-clock"></i> Sisa Waktu: 
                <strong>${formatTime(sesi.sisa_waktu_detik)}</strong>
              </p>
              <div class="progress mb-2" style="height: 20px;">
                <div class="progress-bar bg-info" style="width: ${sesi.progress_persen}%">
                  ${sesi.progress_persen}%
                </div>
              </div>
              <p class="mb-0">
                <span class="badge badge-${sesi.suspicious_count > 5 ? 'danger' : 'success'}">
                  <i class="fas fa-exclamation-triangle"></i> ${sesi.suspicious_count} pelanggaran
                </span>
              </p>
            </div>
            <div class="card-footer">
              <button type="button" class="btn btn-sm btn-info btn-view-events" data-sesi-id="${sesi.id}">
                <i class="fas fa-eye"></i> Lihat Detail
              </button>
            </div>
          </div>
        </div>
      `;
    });
  }
  
  $('#active-sessions-list').html(html);
  
  // Bind view events button
  $('.btn-view-events').on('click', function() {
    let sesiId = $(this).data('sesi-id');
    viewSesiEvents(sesiId);
  });
}

function subscribeToProktor(ujianId) {
  // Leave previous channel if exists
  if (currentUjianId && currentUjianId !== ujianId) {
    echo.leaveChannel(`proktor.ujian.${currentUjianId}`);
  }
  
  // Subscribe to new channel
  echo.private(`proktor.ujian.${ujianId}`)
    .listen('.proktor.event', function(event) {
      console.log('Proktor event received:', event);
      handleProktorEvent(event);
    });
  
  console.log(`Subscribed to proktor.ujian.${ujianId}`);
}

function handleProktorEvent(event) {
  // Update session data
  if (sessions[event.sesi_ujian_id]) {
    if (event.severity === 'high' || event.severity === 'critical') {
      sessions[event.sesi_ujian_id].suspicious_count++;
    }
  }
  
  // Add to event log
  addEventToLog(event);
  
  // Update stats
  if (event.severity === 'high' || event.severity === 'critical') {
    let current = parseInt($('#suspicious-count').text());
    $('#suspicious-count').text(current + 1);
  }
  
  // Show notification for critical events
  if (event.severity === 'critical') {
    showNotification(event);
  }
  
  // Refresh active sessions
  if (currentUjianId) {
    loadActiveSessions(currentUjianId);
  }
}

function addEventToLog(event) {
  eventCount++;
  
  let severityIcon = {
    low: 'fa-info-circle',
    medium: 'fa-exclamation-circle',
    high: 'fa-exclamation-triangle',
    critical: 'fa-skull-crossbones'
  };
  
  let eventTypeLabel = {
    tab_switch: 'Pindah Tab',
    blur: 'Kehilangan Fokus',
    focus: 'Mendapat Fokus',
    copy: 'Copy Text',
    paste: 'Paste Text',
    right_click: 'Klik Kanan',
    fullscreen_exit: 'Keluar Fullscreen',
    reconnect: 'Reconnect',
    devtools_open: 'DevTools Dibuka',
    heartbeat: 'Heartbeat'
  };
  
  let html = `
    <div class="event-item ${event.severity}" data-severity="${event.severity}">
      <div class="d-flex justify-content-between">
        <div>
          <i class="fas ${severityIcon[event.severity]}"></i>
          <strong>${event.siswa_nama}</strong> - ${eventTypeLabel[event.event_type] || event.event_type}
        </div>
        <small class="text-muted">${formatTimestamp(event.timestamp)}</small>
      </div>
      ${event.event_data ? '<small class="text-muted">' + JSON.stringify(event.event_data) + '</small>' : ''}
    </div>
  `;
  
  $('#event-log').prepend(html);
  
  // Keep only last 100 events
  if (eventCount > 100) {
    $('#event-log .event-item:last').remove();
  }
  
  // Apply filter
  filterEvents();
}

function filterEvents() {
  if (eventFilter === 'all') {
    $('.event-item').show();
  } else {
    $('.event-item').hide();
    $(`.event-item[data-severity="${eventFilter}"]`).show();
  }
}

function viewSesiEvents(sesiId) {
  $.ajax({
    url: `/api/proktor/sesi/${sesiId}/events`,
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${jwtToken}`
    },
    success: function(response) {
      showEventsModal(response.data);
    },
    error: function(xhr) {
      console.error('Failed to load sesi events', xhr);
    }
  });
}

function showEventsModal(events) {
  let html = '<div class="timeline">';
  
  events.forEach(function(event) {
    let iconClass = event.severity === 'critical' ? 'fa-skull-crossbones bg-danger' : 
                    event.severity === 'high' ? 'fa-exclamation-triangle bg-warning' :
                    event.severity === 'medium' ? 'fa-exclamation-circle bg-info' :
                    'fa-info-circle bg-secondary';
    
    html += `
      <div>
        <i class="fas ${iconClass}"></i>
        <div class="timeline-item">
          <span class="time"><i class="fas fa-clock"></i> ${formatTimestamp(event.created_at)}</span>
          <h3 class="timeline-header">${event.event_type}</h3>
          ${event.event_data ? '<div class="timeline-body"><pre>' + JSON.stringify(event.event_data, null, 2) + '</pre></div>' : ''}
        </div>
      </div>
    `;
  });
  
  html += '</div>';
  
  Swal.fire({
    title: 'Detail Aktivitas Siswa',
    html: html,
    width: '800px',
    showCloseButton: true,
    showConfirmButton: false
  });
}

function showNotification(event) {
  toastr.error(
    `${event.siswa_nama} - ${event.event_type}`,
    'Aktivitas Mencurigakan!',
    {
      timeOut: 10000,
      closeButton: true,
      progressBar: true
    }
  );
}

function formatTime(seconds) {
  let hours = Math.floor(seconds / 3600);
  let minutes = Math.floor((seconds % 3600) / 60);
  let secs = seconds % 60;
  return `${pad(hours)}:${pad(minutes)}:${pad(secs)}`;
}

function pad(num) {
  return num.toString().padStart(2, '0');
}

function formatTimestamp(timestamp) {
  let date = new Date(timestamp);
  return date.toLocaleTimeString('id-ID');
}
</script>
@endpush
