<template>
  <div class="card" :class="cardClass">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-user"></i>
        {{ sesi.siswa_nama }}
      </h3>
      <div class="card-tools">
        <StatusBadge :status="sesi.status" />
      </div>
    </div>
    <div class="card-body">
      <!-- Student Info -->
      <div class="mb-3">
        <small class="text-muted">
          <i class="fas fa-id-card"></i>
          NIS: {{ sesi.siswa_nis }}
        </small>
      </div>

      <!-- Progress Bar -->
      <div class="mb-3">
        <div class="d-flex justify-content-between mb-1">
          <small><strong>Progress:</strong></small>
          <small><strong>{{ sesi.progress || 0 }}%</strong></small>
        </div>
        <div class="progress" style="height: 20px">
          <div
            class="progress-bar"
            :class="progressBarClass"
            :style="{ width: (sesi.progress || 0) + '%' }"
            role="progressbar"
          >
            {{ sesi.jawaban_count || 0 }} / {{ sesi.total_soal || 0 }}
          </div>
        </div>
      </div>

      <!-- Time Info -->
      <div class="mb-3">
        <div class="row">
          <div class="col-6">
            <small class="text-muted">
              <i class="fas fa-clock"></i>
              Mulai
            </small>
            <br />
            <strong>{{ formatTime(sesi.waktu_mulai) }}</strong>
          </div>
          <div class="col-6">
            <small class="text-muted">
              <i class="fas fa-hourglass-half"></i>
              {{ sesi.status === 'berlangsung' ? 'Sisa' : 'Durasi' }}
            </small>
            <br />
            <strong :class="timeClass">
              {{ displayTime }}
            </strong>
          </div>
        </div>
      </div>

      <!-- Violations -->
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted">
            <i class="fas fa-exclamation-triangle"></i>
            Pelanggaran
          </small>
          <span class="badge" :class="violationBadgeClass">
            <i class="fas fa-exchange-alt"></i>
            {{ sesi.tab_switch_count || 0 }} tab switch
          </span>
        </div>
      </div>

      <!-- Connection Status (for active sessions) -->
      <div v-if="sesi.status === 'berlangsung'" class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted">
            <i class="fas fa-wifi"></i>
            Koneksi
          </small>
          <span class="badge" :class="connectionBadgeClass">
            <i class="fas fa-circle"></i>
            {{ connectionStatus }}
          </span>
        </div>
      </div>

      <!-- Last Activity -->
      <div v-if="sesi.last_activity" class="mb-3">
        <small class="text-muted">
          <i class="fas fa-history"></i>
          Aktivitas terakhir: {{ formatRelativeTime(sesi.last_activity) }}
        </small>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="card-footer">
      <div class="btn-group btn-group-sm w-100">
        <button
          class="btn btn-info"
          @click="$emit('view-details', sesi)"
          title="Lihat Detail"
        >
          <i class="fas fa-eye"></i>
          Detail
        </button>
        <button
          v-if="sesi.status === 'berlangsung'"
          class="btn btn-warning"
          @click="$emit('force-finish', sesi)"
          title="Paksa Selesai"
        >
          <i class="fas fa-stop"></i>
          Selesai
        </button>
        <button
          v-if="sesi.status === 'berlangsung'"
          class="btn btn-danger"
          @click="$emit('disqualify', sesi)"
          title="Diskualifikasi"
        >
          <i class="fas fa-ban"></i>
          DQ
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  sesi: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['view-details', 'force-finish', 'disqualify']);

// Computed properties
const cardClass = computed(() => {
  const classes = [];
  
  if (props.sesi.status === 'berlangsung') {
    classes.push('border-success');
  } else if (props.sesi.status === 'diskualifikasi') {
    classes.push('border-danger');
  } else if (props.sesi.status === 'timeout') {
    classes.push('border-warning');
  }

  // Highlight if high violations
  if ((props.sesi.tab_switch_count || 0) > 5) {
    classes.push('shadow-lg');
  }

  return classes.join(' ');
});

const progressBarClass = computed(() => {
  const progress = props.sesi.progress || 0;
  if (progress >= 80) return 'bg-success';
  if (progress >= 50) return 'bg-info';
  if (progress >= 25) return 'bg-warning';
  return 'bg-danger';
});

const violationBadgeClass = computed(() => {
  const count = props.sesi.tab_switch_count || 0;
  if (count > 5) return 'badge-danger';
  if (count > 2) return 'badge-warning';
  return 'badge-success';
});

const connectionBadgeClass = computed(() => {
  if (!props.sesi.last_activity) return 'badge-secondary';
  
  const now = new Date();
  const lastActivity = new Date(props.sesi.last_activity);
  const diffSeconds = Math.floor((now - lastActivity) / 1000);
  
  if (diffSeconds < 30) return 'badge-success';
  if (diffSeconds < 60) return 'badge-warning';
  return 'badge-danger';
});

const connectionStatus = computed(() => {
  if (!props.sesi.last_activity) return 'Unknown';
  
  const now = new Date();
  const lastActivity = new Date(props.sesi.last_activity);
  const diffSeconds = Math.floor((now - lastActivity) / 1000);
  
  if (diffSeconds < 30) return 'Online';
  if (diffSeconds < 60) return 'Idle';
  return 'Offline';
});

const displayTime = computed(() => {
  if (props.sesi.status === 'berlangsung') {
    return formatDuration(props.sesi.sisa_waktu || 0);
  }
  return formatDuration(props.sesi.durasi_detik || 0);
});

const timeClass = computed(() => {
  if (props.sesi.status !== 'berlangsung') return '';
  
  const sisaWaktu = props.sesi.sisa_waktu || 0;
  if (sisaWaktu < 300) return 'text-danger'; // < 5 minutes
  if (sisaWaktu < 600) return 'text-warning'; // < 10 minutes
  return 'text-success';
});

// Methods
const formatTime = (datetime) => {
  if (!datetime) return '-';
  return new Date(datetime).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatDuration = (seconds) => {
  if (!seconds || seconds < 0) return '0m 0d';
  
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;

  if (hours > 0) {
    return `${hours}j ${minutes}m`;
  }
  if (minutes > 0) {
    return `${minutes}m ${secs}d`;
  }
  return `${secs}d`;
};

const formatRelativeTime = (datetime) => {
  if (!datetime) return '-';
  
  const now = new Date();
  const date = new Date(datetime);
  const diff = Math.floor((now - date) / 1000); // seconds

  if (diff < 60) return `${diff} detik yang lalu`;
  if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`;
  if (diff < 86400) return `${Math.floor(diff / 3600)} jam yang lalu`;
  return `${Math.floor(diff / 86400)} hari yang lalu`;
};

// Real-time countdown for active sessions
const currentTime = ref(Date.now());
let countdownInterval = null;

onMounted(() => {
  if (props.sesi.status === 'berlangsung') {
    countdownInterval = setInterval(() => {
      currentTime.value = Date.now();
      
      // Update sisa_waktu
      if (props.sesi.waktu_selesai) {
        const waktuSelesai = new Date(props.sesi.waktu_selesai);
        const sisaDetik = Math.floor((waktuSelesai - currentTime.value) / 1000);
        props.sesi.sisa_waktu = Math.max(0, sisaDetik);
      }
    }, 1000);
  }
});

onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval);
  }
});
</script>

<style scoped>
.card {
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.border-success {
  border-left: 4px solid #28a745 !important;
}

.border-danger {
  border-left: 4px solid #dc3545 !important;
}

.border-warning {
  border-left: 4px solid #ffc107 !important;
}

.shadow-lg {
  box-shadow: 0 1rem 3rem rgba(220, 53, 69, 0.175) !important;
}

.card-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
}

.progress {
  border-radius: 0.25rem;
  overflow: hidden;
}

.progress-bar {
  transition: width 0.6s ease;
  font-size: 0.75rem;
  line-height: 20px;
}

.badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
}

.btn-group-sm .btn {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
}
</style>
