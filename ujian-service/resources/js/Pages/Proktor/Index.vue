<template>
  <AppLayout title="Monitoring Proktor">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Monitoring Proktor</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <Link href="/dashboard">Dashboard</Link>
              </li>
              <li class="breadcrumb-item active">Monitoring Proktor</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <StatCard
              title="Ujian Aktif"
              :value="statistics.activeExams"
              icon="fas fa-clipboard-list"
              color="info"
              :loading="loading"
            />
          </div>
          <div class="col-lg-3 col-6">
            <StatCard
              title="Siswa Online"
              :value="statistics.activeSessions"
              icon="fas fa-users"
              color="success"
              :loading="loading"
            />
          </div>
          <div class="col-lg-3 col-6">
            <StatCard
              title="Total Pelanggaran"
              :value="statistics.totalViolations"
              icon="fas fa-exclamation-triangle"
              color="warning"
              :loading="loading"
            />
          </div>
          <div class="col-lg-3 col-6">
            <StatCard
              title="Rata-rata Tab Switch"
              :value="statistics.avgTabSwitch"
              icon="fas fa-exchange-alt"
              color="danger"
              :loading="loading"
            />
          </div>
        </div>

        <!-- Filter Section -->
        <div class="row mt-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Filter Ujian</h3>
                <div class="card-tools">
                  <button
                    type="button"
                    class="btn btn-tool"
                    @click="toggleFilters"
                  >
                    <i
                      :class="[
                        'fas',
                        showFilters ? 'fa-minus' : 'fa-plus',
                      ]"
                    ></i>
                  </button>
                </div>
              </div>
              <div v-show="showFilters" class="card-body">
                <div class="row">
                  <div class="col-md-4">
                    <FormGroup label="Periode">
                      <select
                        v-model="filters.periode_id"
                        class="form-control"
                        @change="loadUjian"
                      >
                        <option value="">Semua Periode</option>
                        <option
                          v-for="periode in periodes"
                          :key="periode.id"
                          :value="periode.id"
                        >
                          {{ periode.nama }}
                        </option>
                      </select>
                    </FormGroup>
                  </div>
                  <div class="col-md-4">
                    <FormGroup label="Status">
                      <select
                        v-model="filters.status"
                        class="form-control"
                        @change="loadUjian"
                      >
                        <option value="">Semua Status</option>
                        <option value="active">Sedang Berlangsung</option>
                        <option value="upcoming">Akan Datang</option>
                        <option value="finished">Selesai</option>
                      </select>
                    </FormGroup>
                  </div>
                  <div class="col-md-4">
                    <FormGroup label="Pencarian">
                      <input
                        v-model="filters.search"
                        type="text"
                        class="form-control"
                        placeholder="Cari ujian..."
                        @input="debounceSearch"
                      />
                    </FormGroup>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Exams List -->
        <div class="row mt-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Daftar Ujian</h3>
                <div class="card-tools">
                  <button
                    class="btn btn-sm btn-primary"
                    @click="refreshData"
                    :disabled="loading"
                  >
                    <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
                    Refresh
                  </button>
                </div>
              </div>
              <div class="card-body p-0">
                <LoadingSpinner v-if="loading && ujians.length === 0" />
                <div v-else-if="ujians.length === 0" class="text-center py-5">
                  <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                  <p class="text-muted">Tidak ada ujian yang ditemukan</p>
                </div>
                <div v-else class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Ujian</th>
                        <th>Periode</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Siswa Online</th>
                        <th>Pelanggaran</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="ujian in ujians"
                        :key="ujian.id"
                        :class="{ 'table-success': ujian.is_active }"
                      >
                        <td>
                          <strong>{{ ujian.judul }}</strong>
                          <br />
                          <small class="text-muted">
                            {{ ujian.mapel_nama }} - {{ ujian.kelas_nama }}
                          </small>
                        </td>
                        <td>
                          <span class="badge badge-info">
                            {{ ujian.periode_nama }}
                          </span>
                        </td>
                        <td>
                          <small>
                            {{ formatDateTime(ujian.waktu_mulai) }}
                            <br />
                            s/d {{ formatDateTime(ujian.waktu_selesai) }}
                          </small>
                        </td>
                        <td>
                          <StatusBadge :status="ujian.status_display" />
                        </td>
                        <td>
                          <span
                            class="badge"
                            :class="
                              ujian.active_sessions > 0
                                ? 'badge-success'
                                : 'badge-secondary'
                            "
                          >
                            <i class="fas fa-user"></i>
                            {{ ujian.active_sessions }} / {{ ujian.jumlah_peserta }}
                          </span>
                        </td>
                        <td>
                          <span
                            v-if="ujian.total_violations > 0"
                            class="badge badge-warning"
                          >
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ ujian.total_violations }}
                          </span>
                          <span v-else class="badge badge-success">
                            <i class="fas fa-check"></i> 0
                          </span>
                        </td>
                        <td>
                          <Link
                            :href="`/proktor/${ujian.id}`"
                            class="btn btn-sm btn-primary"
                          >
                            <i class="fas fa-eye"></i>
                            Monitor
                          </Link>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Events -->
        <div class="row mt-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Event Terbaru</h3>
              </div>
              <div class="card-body">
                <LoadingSpinner v-if="loadingEvents" />
                <div v-else-if="recentEvents.length === 0" class="text-center py-3">
                  <p class="text-muted">Belum ada event</p>
                </div>
                <div v-else class="timeline">
                  <div
                    v-for="event in recentEvents"
                    :key="event.id"
                    class="time-label"
                  >
                    <span
                      class="bg-red"
                      v-if="event.tipe === 'tab_switch'"
                    >
                      {{ formatTime(event.created_at) }}
                    </span>
                    <span
                      class="bg-yellow"
                      v-else-if="event.tipe === 'copy' || event.tipe === 'paste'"
                    >
                      {{ formatTime(event.created_at) }}
                    </span>
                    <span class="bg-blue" v-else>
                      {{ formatTime(event.created_at) }}
                    </span>
                  </div>
                  <div>
                    <i
                      class="fas"
                      :class="getEventIcon(event.tipe)"
                    ></i>
                    <div class="timeline-item">
                      <span class="time">
                        <i class="fas fa-clock"></i>
                        {{ formatRelativeTime(event.created_at) }}
                      </span>
                      <h3 class="timeline-header">
                        <strong>{{ event.siswa_nama }}</strong>
                        {{ getEventLabel(event.tipe) }}
                      </h3>
                      <div class="timeline-body" v-if="event.detail">
                        <small class="text-muted">
                          {{ JSON.stringify(event.detail) }}
                        </small>
                      </div>
                    </div>
                  </div>
                  <div>
                    <i class="fas fa-clock bg-gray"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import FormGroup from '@/Components/FormGroup.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

const props = defineProps({
  periodes: {
    type: Array,
    default: () => [],
  },
  initialUjians: {
    type: Array,
    default: () => [],
  },
});

const { showSuccess, showError } = useToast();

// State
const loading = ref(false);
const loadingEvents = ref(false);
const showFilters = ref(true);
const ujians = ref(props.initialUjians || []);
const recentEvents = ref([]);
const searchTimeout = ref(null);

const filters = reactive({
  periode_id: '',
  status: 'active',
  search: '',
});

const statistics = computed(() => {
  const activeExams = ujians.value.filter((u) => u.is_active).length;
  const activeSessions = ujians.value.reduce(
    (sum, u) => sum + (u.active_sessions || 0),
    0
  );
  const totalViolations = ujians.value.reduce(
    (sum, u) => sum + (u.total_violations || 0),
    0
  );
  const avgTabSwitch =
    activeSessions > 0
      ? (
          ujians.value.reduce((sum, u) => sum + (u.avg_tab_switch || 0), 0) /
          ujians.value.length
        ).toFixed(1)
      : 0;

  return {
    activeExams,
    activeSessions,
    totalViolations,
    avgTabSwitch,
  };
});

// Methods
const loadUjian = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/proktor/ujian', {
      params: filters,
    });
    ujians.value = response.data.data;
  } catch (error) {
    showError('Gagal memuat data ujian');
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const loadRecentEvents = async () => {
  loadingEvents.value = true;
  try {
    const response = await axios.get('/api/proktor/events/recent', {
      params: { limit: 10 },
    });
    recentEvents.value = response.data.data;
  } catch (error) {
    console.error('Failed to load recent events:', error);
  } finally {
    loadingEvents.value = false;
  }
};

const refreshData = async () => {
  await Promise.all([loadUjian(), loadRecentEvents()]);
  showSuccess('Data berhasil diperbarui');
};

const debounceSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value);
  }
  searchTimeout.value = setTimeout(() => {
    loadUjian();
  }, 500);
};

const toggleFilters = () => {
  showFilters.value = !showFilters.value;
};

// Formatting helpers
const formatDateTime = (datetime) => {
  if (!datetime) return '-';
  return new Date(datetime).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatTime = (datetime) => {
  if (!datetime) return '-';
  return new Date(datetime).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  });
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

const getEventIcon = (type) => {
  const icons = {
    tab_switch: 'fa-exchange-alt bg-red',
    blur: 'fa-eye-slash bg-warning',
    copy: 'fa-copy bg-yellow',
    paste: 'fa-paste bg-yellow',
    right_click: 'fa-mouse-pointer bg-orange',
    fullscreen_exit: 'fa-compress bg-red',
    reconnect: 'fa-wifi bg-blue',
    devtools_open: 'fa-code bg-danger',
  };
  return icons[type] || 'fa-info-circle bg-info';
};

const getEventLabel = (type) => {
  const labels = {
    tab_switch: 'berpindah tab',
    blur: 'keluar dari halaman ujian',
    copy: 'melakukan copy',
    paste: 'melakukan paste',
    right_click: 'klik kanan',
    fullscreen_exit: 'keluar dari fullscreen',
    reconnect: 'reconnect',
    devtools_open: 'membuka developer tools',
  };
  return labels[type] || type;
};

// Setup realtime updates
let eventChannel = null;

const setupRealtimeUpdates = () => {
  if (window.Echo) {
    // Subscribe to global proktor channel for all events
    eventChannel = window.Echo.private('proktor.global')
      .listen('ProktorEvent', (event) => {
        console.log('Received proktor event:', event);
        
        // Add to recent events
        recentEvents.value.unshift(event);
        if (recentEvents.value.length > 10) {
          recentEvents.value.pop();
        }

        // Update ujian statistics
        const ujian = ujians.value.find((u) => u.id === event.ujian_id);
        if (ujian) {
          ujian.total_violations = (ujian.total_violations || 0) + 1;
        }

        // Show toast for critical events
        if (['tab_switch', 'devtools_open', 'fullscreen_exit'].includes(event.tipe)) {
          showError(`${event.siswa_nama} ${getEventLabel(event.tipe)}`);
        }
      });
  }
};

const cleanupRealtimeUpdates = () => {
  if (eventChannel) {
    window.Echo.leave('proktor.global');
    eventChannel = null;
  }
};

// Lifecycle
onMounted(() => {
  loadUjian();
  loadRecentEvents();
  setupRealtimeUpdates();

  // Auto refresh every 30 seconds
  const refreshInterval = setInterval(() => {
    loadUjian();
  }, 30000);

  onUnmounted(() => {
    clearInterval(refreshInterval);
    cleanupRealtimeUpdates();
  });
});
</script>

<style scoped>
.table-success {
  background-color: #d4edda !important;
}

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
  padding: 5px;
  display: inline-block;
  background-color: #fff;
  border-radius: 4px;
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

.time {
  color: #999;
  float: right;
  padding: 10px;
  font-size: 12px;
}
</style>
