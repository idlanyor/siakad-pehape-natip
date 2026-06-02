<template>
  <div class="timeline">
    <div v-if="events.length === 0" class="text-center py-3">
      <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
      <p class="text-muted">Belum ada event</p>
    </div>

    <template v-else>
      <div v-for="(event, index) in events" :key="event.id || index">
        <!-- Time Label (group by time) -->
        <div
          v-if="shouldShowTimeLabel(event, index)"
          class="time-label"
        >
          <span :class="getTimeLabelClass(event)">
            {{ formatTimeLabel(event.created_at) }}
          </span>
        </div>

        <!-- Event Item -->
        <div>
          <i class="fas" :class="getEventIconClass(event.tipe)"></i>
          <div class="timeline-item">
            <span class="time">
              <i class="fas fa-clock"></i>
              {{ formatTime(event.created_at) }}
            </span>
            <h3 class="timeline-header">
              <strong>{{ event.siswa_nama || 'Unknown' }}</strong>
              {{ getEventLabel(event.tipe) }}
            </h3>
            <div v-if="event.detail && Object.keys(event.detail).length > 0" class="timeline-body">
              <div class="event-details">
                <template v-if="event.tipe === 'tab_switch'">
                  <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    Berpindah dari tab ujian
                  </small>
                </template>
                <template v-else-if="event.tipe === 'copy'">
                  <small class="text-muted">
                    <i class="fas fa-copy"></i>
                    Mencoba menyalin konten
                  </small>
                </template>
                <template v-else-if="event.tipe === 'paste'">
                  <small class="text-muted">
                    <i class="fas fa-paste"></i>
                    Mencoba menempel konten
                  </small>
                </template>
                <template v-else-if="event.tipe === 'devtools_open'">
                  <small class="text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Membuka Developer Tools
                  </small>
                </template>
                <template v-else-if="event.tipe === 'fullscreen_exit'">
                  <small class="text-warning">
                    <i class="fas fa-compress"></i>
                    Keluar dari mode fullscreen
                  </small>
                </template>
                <template v-else-if="event.tipe === 'reconnect'">
                  <small class="text-info">
                    <i class="fas fa-wifi"></i>
                    Reconnect ke server
                  </small>
                </template>
                <template v-else>
                  <small class="text-muted">
                    {{ JSON.stringify(event.detail) }}
                  </small>
                </template>
              </div>
            </div>
            <div v-if="showActions" class="timeline-footer">
              <button
                class="btn btn-sm btn-primary"
                @click="$emit('view-session', event.sesi_ujian_id)"
              >
                <i class="fas fa-eye"></i>
                Lihat Sesi
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- End marker -->
      <div>
        <i class="fas fa-clock bg-gray"></i>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  events: {
    type: Array,
    default: () => [],
  },
  showActions: {
    type: Boolean,
    default: false,
  },
  groupByTime: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['view-session']);

// Methods
const shouldShowTimeLabel = (event, index) => {
  if (!props.groupByTime || index === 0) return true;
  
  const currentTime = new Date(event.created_at);
  const prevTime = new Date(props.events[index - 1].created_at);
  
  // Show label if different hour
  return currentTime.getHours() !== prevTime.getHours();
};

const getTimeLabelClass = (event) => {
  const severity = getEventSeverity(event.tipe);
  
  switch (severity) {
    case 'critical':
      return 'bg-danger';
    case 'warning':
      return 'bg-warning';
    case 'info':
      return 'bg-info';
    default:
      return 'bg-primary';
  }
};

const getEventSeverity = (type) => {
  const criticalEvents = ['devtools_open', 'fullscreen_exit'];
  const warningEvents = ['tab_switch', 'copy', 'paste', 'right_click'];
  const infoEvents = ['reconnect', 'blur', 'focus'];
  
  if (criticalEvents.includes(type)) return 'critical';
  if (warningEvents.includes(type)) return 'warning';
  if (infoEvents.includes(type)) return 'info';
  return 'normal';
};

const getEventIconClass = (type) => {
  const iconMap = {
    tab_switch: 'fa-exchange-alt bg-danger',
    blur: 'fa-eye-slash bg-warning',
    focus: 'fa-eye bg-success',
    copy: 'fa-copy bg-warning',
    paste: 'fa-paste bg-warning',
    right_click: 'fa-mouse-pointer bg-orange',
    fullscreen_exit: 'fa-compress bg-danger',
    reconnect: 'fa-wifi bg-info',
    devtools_open: 'fa-code bg-danger',
  };
  
  return iconMap[type] || 'fa-info-circle bg-info';
};

const getEventLabel = (type) => {
  const labelMap = {
    tab_switch: 'berpindah tab',
    blur: 'keluar dari halaman ujian',
    focus: 'kembali ke halaman ujian',
    copy: 'melakukan copy',
    paste: 'melakukan paste',
    right_click: 'klik kanan',
    fullscreen_exit: 'keluar dari fullscreen',
    reconnect: 'reconnect',
    devtools_open: 'membuka developer tools',
  };
  
  return labelMap[type] || type;
};

const formatTimeLabel = (datetime) => {
  if (!datetime) return '-';
  
  const date = new Date(datetime);
  const now = new Date();
  const isToday = date.toDateString() === now.toDateString();
  
  if (isToday) {
    return date.toLocaleTimeString('id-ID', {
      hour: '2-digit',
      minute: '2-digit',
    });
  }
  
  return date.toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatTime = (datetime) => {
  if (!datetime) return '-';
  
  const now = new Date();
  const date = new Date(datetime);
  const diff = Math.floor((now - date) / 1000); // seconds

  if (diff < 60) return `${diff} detik yang lalu`;
  if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`;
  if (diff < 86400) return `${Math.floor(diff / 3600)} jam yang lalu`;
  
  return date.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<style scoped>
.timeline {
  position: relative;
  margin: 0 0 30px 0;
  padding: 0;
  list-style: none;
}

.timeline:before {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #ddd;
  left: 31px;
  margin: 0;
  border-radius: 2px;
}

.timeline > div {
  margin-bottom: 15px;
  position: relative;
}

.timeline > div > .timeline-item {
  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
  border-radius: 3px;
  margin-top: 0;
  background: #fff;
  color: #444;
  margin-left: 60px;
  margin-right: 15px;
  padding: 10px;
  position: relative;
}

.timeline > div > .timeline-item:hover {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.timeline > div > .fas,
.timeline > div > .far,
.timeline > div > .fab,
.timeline > div > .fal,
.timeline > div > .fad,
.timeline > div > .svg-inline--fa {
  width: 30px;
  height: 30px;
  font-size: 15px;
  line-height: 30px;
  position: absolute;
  color: #666;
  background: #d2d6de;
  border-radius: 50%;
  text-align: center;
  left: 18px;
  top: 0;
}

.timeline > .time-label > span {
  font-weight: 600;
  padding: 5px 10px;
  display: inline-block;
  background-color: #fff;
  border-radius: 4px;
  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
}

.timeline-header {
  margin: 0;
  color: #555;
  border-bottom: 1px solid #f4f4f4;
  padding: 5px 0;
  font-size: 16px;
  line-height: 1.1;
}

.timeline-body {
  padding: 10px 0;
}

.timeline-footer {
  padding: 5px 0 0;
  border-top: 1px solid #f4f4f4;
}

.time {
  color: #999;
  float: right;
  padding: 10px;
  font-size: 12px;
}

.event-details {
  background-color: #f8f9fa;
  padding: 8px 12px;
  border-radius: 4px;
  border-left: 3px solid #007bff;
}

/* Icon background colors */
.bg-danger {
  background-color: #dc3545 !important;
  color: #fff !important;
}

.bg-warning {
  background-color: #ffc107 !important;
  color: #212529 !important;
}

.bg-info {
  background-color: #17a2b8 !important;
  color: #fff !important;
}

.bg-success {
  background-color: #28a745 !important;
  color: #fff !important;
}

.bg-primary {
  background-color: #007bff !important;
  color: #fff !important;
}

.bg-orange {
  background-color: #fd7e14 !important;
  color: #fff !important;
}

.bg-gray {
  background-color: #6c757d !important;
  color: #fff !important;
}

/* Responsive */
@media (max-width: 767px) {
  .timeline:before {
    left: 15px;
  }

  .timeline > div > .fas,
  .timeline > div > .far,
  .timeline > div > .fab,
  .timeline > div > .fal,
  .timeline > div > .fad,
  .timeline > div > .svg-inline--fa {
    left: 2px;
  }

  .timeline > div > .timeline-item {
    margin-left: 45px;
    margin-right: 10px;
  }

  .time {
    float: none;
    padding: 5px 0;
    display: block;
  }
}
</style>
