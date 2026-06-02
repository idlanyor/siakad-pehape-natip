<template>
  <AppLayout :title="`Monitor: ${ujian.judul}`">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">
              <i class="fas fa-eye"></i>
              Monitor Ujian
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <Link href="/dashboard">Dashboard</Link>
              </li>
              <li class="breadcrumb-item">
                <Link href="/proktor">Proktor</Link>
              </li>
              <li class="breadcrumb-item active">Monitor</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <!-- Ujian Info Card -->
        <div class="row">
          <div class="col-12">
            <div class="card card-primary card-outline">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-clipboard-list"></i>
                  {{ ujian.judul }}
                </h3>
                <div class="card-tools">
                  <button
                    class="btn btn-sm btn-success"
                    @click="refreshData"
                    :disabled="loading"
                  >
                    <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
                    Refresh
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3">
                    <strong>Mapel:</strong> {{ ujian.mapel_nama }}
                  </div>
                  <div class="col-md-3">
                    <strong>Kelas:</strong> {{ ujian.kelas_nama }}
                  </div>
                  <div class="col-md-3">
                    <strong>Guru:</strong> {{ ujian.guru_nama }}
                  </div>
                  <div class="col-md-3">
                    <strong>Durasi:</strong> {{ ujian.durasi_menit }} menit
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-md-6">
                    <strong>Waktu Mulai:</strong>
                    {{ formatDateTime(ujian.waktu_mulai) }}
                  </div>
                  <div class="col-md-6">
                    <strong>Waktu Selesai:</strong>
                    {{ formatDateTime(ujian.waktu_selesai) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistics Row -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <StatCard
              title="Total Peserta"
              :value="statistics.totalPeserta"
              icon="fas fa-users"
              color="info"
            />
          </div>
          <div class="col-lg-3 col-6">
            <StatCard
              title="Sedang Mengerjakan"
              :value="statistics.sedangMengerjakan"
              icon="fas fa-user-clock"
              color="success"
            />
          </div>
          <div class="col-lg-3 col-6">
            <StatCard
              title="Selesai"
              :value="statistics.selesai"
              icon="fas fa-check-circle"
              color="primary"
            />
          </div>
          <div class="col-lg-3 col-6">
            <StatCard
              title="Total Pelanggaran"
              :value="statistics.totalPelanggaran"
              icon="fas fa-exclamation-triangle"
              color="warning"
            />
          </div>
        </div>

        <!-- Filter & View Options -->
        <div class="row mt-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Filter & Tampilan</h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3">
                    <FormGroup label="Status Sesi">
                      <select v-model="filters.status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="berlangsung">Sedang Mengerjakan</option>
                        <option value="selesai">Selesai</option>
                        <option value="timeout">Timeout</option>
                        <option value="diskualifikasi">Diskualifikasi</option>
                      </select>
                    </FormGroup>
                  </div>
                  <div class="col-md-3">
                    <FormGroup label="Urutan">
                      <select v-model="filters.sortBy" class="form-control">
                        <option value="nama">Nama Siswa</option>
                        <option value="violations">Pelanggaran Terbanyak</option>
                        <option value="progress">Progress</option>
                        <option value="waktu">Waktu Mulai</option>
                      </select>
                    </FormGroup>
                  </div>
                  <div class="col-md-3">
                    <FormGroup label="Tampilan">
                      <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label
                          class="btn btn-outline-primary"
                          :class="{ active: viewMode === 'grid' }"
                        >
                          <input
                            type="radio"
                            value="grid"
                            v-model="viewMode"
                          />
                          <i class="fas fa-th"></i> Grid
                        </label>
                        <label
                          class="btn btn-outline-primary"
                          :class="{ active: viewMode === 'list' }"
                        >
                          <input
                            type="radio"
                            value="list"
                            v-model="viewMode"
                          />
                          <i class="fas fa-list"></i> List
                        </label>
                      </div>
                    </FormGroup>
                  </div>
                  <div class="col-md-3">
                    <FormGroup label="Pencarian">
                      <input
                        v-model="filters.search"
                        type="text"
                        class="form-control"
                        placeholder="Cari siswa..."
                      />
                    </FormGroup>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sessions Display -->
        <div class="row mt-3">
          <div class="col-12">
            <LoadingSpinner v-if="loading" />
            
            <!-- Grid View -->
            <div v-else-if="viewMode === 'grid'" class="row">
              <div
                v-for="sesi in filteredSessions"
                :key="sesi.id"
                class="col-lg-4 col-md-6"
              >
                <ProktorCard
                  :sesi="sesi"
                  @force-finish="handleForceFinish"
                  @disqualify="handleDisqualify"
                  @view-details="handleViewDetails"
                />
              </div>
              <div v-if="filteredSessions.length === 0" class="col-12">
                <div class="text-center py-5">
                  <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                  <p class="text-muted">Tidak ada sesi yang ditemukan</p>
                </div>
              </div>
            </div>

            <!-- List View -->
            <div v-else class="card">
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Siswa</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Waktu</th>
                        <th>Pelanggaran</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="sesi in filteredSessions"
                        :key="sesi.id"
                        :class="getRowClass(sesi)"
                      >
                        <td>
                          <strong>{{ sesi.siswa_nama }}</strong>
                          <br />
                          <small class="text-muted">{{ sesi.siswa_nis }}</small>
                        </td>
                        <td>
                          <StatusBadge :status="sesi.status" />
                        </td>
                        <td>
                          <div class="progress" style="height: 20px">
                            <div
                              class="progress-bar"
                              :class="getProgressBarClass(sesi)"
                              :style="{ width: sesi.progress + '%' }"
                            >
                              {{ sesi.progress }}%
                            </div>
                          </div>
                        </td>
                        <td>
                          <small>
                            <strong>Mulai:</strong>
                            {{ formatTime(sesi.waktu_mulai) }}
                            <br />
                            <span v-if="sesi.status === 'berlangsung'">
                              <strong>Sisa:</strong>
                              {{ formatDuration(sesi.sisa_waktu) }}
                            </span>
                            <span v-else>
                              <strong>Selesai:</strong>
                              {{ formatTime(sesi.waktu_selesai) }}
                            </span>
                          </small>
                        </td>
                        <td>
                          <span
                            class="badge"
                            :class="
                              sesi.tab_switch_count > 5
                                ? 'badge-danger'
                                : sesi.tab_switch_count > 2
                                ? 'badge-warning'
                                : 'badge-success'
                            "
                          >
                            <i class="fas fa-exchange-alt"></i>
                            {{ sesi.tab_switch_count }}
                          </span>
                        </td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button
                              class="btn btn-info"
                              @click="handleViewDetails(sesi)"
                              title="Lihat Detail"
                            >
                              <i class="fas fa-eye"></i>
                            </button>
                            <button
                              v-if="sesi.status === 'berlangsung'"
                              class="btn btn-warning"
                              @click="handleForceFinish(sesi)"
                              title="Paksa Selesai"
                            >
                              <i class="fas fa-stop"></i>
                            </button>
                            <button
                              v-if="sesi.status === 'berlangsung'"
                              class="btn btn-danger"
                              @click="handleDisqualify(sesi)"
                              title="Diskualifikasi"
                            >
                              <i class="fas fa-ban"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Events Timeline -->
        <div class="row mt-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-history"></i>
                  Event Log Realtime
                </h3>
                <div class="card-tools">
                  <button
                    class="btn btn-sm btn-tool"
                    @click="clearEvents"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
              <div class="card-body" style="max-height: 400px; overflow-y: auto">
                <EventTimeline :events="recentEvents" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <Modal
      :show="showDetailModal"
      @close="showDetailModal = false"
      size="lg"
      title="Detail Sesi Ujian"
    >
      <div v-if="selectedSesi">
        <div class="row">
          <div class="col-md-6">
            <dl>
              <dt>Nama Siswa</dt>
              <dd>{{ selectedSesi.siswa_nama }}</dd>
              <dt>NIS</dt>
              <dd>{{ selectedSesi.siswa_nis }}</dd>
              <dt>Status</dt>
              <dd>
                <StatusBadge :status="selectedSesi.status" />
              </dd>
            </dl>
          </div>
          <div class="col-md-6">
            <dl>
              <dt>Waktu Mulai</dt>
              <dd>{{ formatDateTime(selectedSesi.waktu_mulai) }}</dd>
              <dt>Waktu Selesai</dt>
              <dd>{{ formatDateTime(selectedSesi.waktu_selesai) }}</dd>
              <dt>Durasi</dt>
              <dd>{{ formatDuration(selectedSesi.durasi_detik) }}</dd>
            </dl>
          </div>
        </div>
        <div class="row mt-3">
          <div class="col-12">
            <h5>Riwayat Event</h5>
            <EventTimeline :events="selectedSesiEvents" />
          </div>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import FormGroup from '@/Components/FormGroup.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import Modal from '@/Components/Modal.vue';
import ProktorCard from '@/Components/ProktorCard.vue';
import EventTimeline from '@/Components/EventTimeline.vue';
import { useToast } from '@/Composables/useToast';
import { useModal } from '@/Composables/useModal';
import axios from 'axios';

const props = defineProps({
  ujian: {
    type: Object,
    required: true,
  },
  sessions: {
    type: Array,
    default: () => [],
  },
});

const { showSuccess, showError, showConfirm } = useToast();
const { show: showModal, hide: hideModal } = useModal();

// State
const loading = ref(false);
const sessions = ref(props.sessions || []);
const recentEvents = ref([]);
const viewMode = ref('grid');
const showDetailModal = ref(false);
const selectedSesi = ref(null);
const selectedSesiEvents = ref([]);

const filters = reactive({
  status: '',
  sortBy: 'nama',
  search: '',
});

// Computed
const statistics = computed(() => {
  const totalPeserta = sessions.value.length;
  const sedangMengerjakan = sessions.value.filter(
    (s) => s.status === 'berlangsung'
  ).length;
  const selesai = sessions.value.filter((s) => s.status === 'selesai').length;
  const totalPelanggaran = sessions.value.reduce(
    (sum, s) => sum + (s.tab_switch_count || 0),
    0
  );

  return {
    totalPeserta,
    sedangMengerjakan,
    selesai,
    totalPelanggaran,
  };
});

const filteredSessions = computed(() => {
  let filtered = [...sessions.value];

  // Filter by status
  if (filters.status) {
    filtered = filtered.filter((s) => s.status === filters.status);
  }

  // Filter by search
  if (filters.search) {
    const search = filters.search.toLowerCase();
    filtered = filtered.filter(
      (s) =>
        s.siswa_nama.toLowerCase().includes(search) ||
        s.siswa_nis.toLowerCase().includes(search)
    );
  }

  // Sort
  filtered.sort((a, b) => {
    switch (filters.sortBy) {
      case 'nama':
        return a.siswa_nama.localeCompare(b.siswa_nama);
      case 'violations':
        return (b.tab_switch_count || 0) - (a.tab_switch_count || 0);
      case 'progress':
        return (b.progress || 0) - (a.progress || 0);
      case 'waktu':
        return new Date(a.waktu_mulai) - new Date(b.waktu_mulai);
      default:
        return 0;
    }
  });

  return filtered;
});

// Methods
const refreshData = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/proktor/ujian/${props.ujian.id}/sessions`);
    sessions.value = response.data.data;
    showSuccess('Data berhasil diperbarui');
  } catch (error) {
    showError('Gagal memuat data');
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const handleForceFinish = async (sesi) => {
  const confirmed = await showConfirm(
    `Paksa selesaikan ujian untuk ${sesi.siswa_nama}?`,
    'Tindakan ini tidak dapat dibatalkan'
  );

  if (confirmed) {
    try {
      await axios.post(`/api/proktor/sesi/${sesi.id}/force-finish`);
      showSuccess('Sesi berhasil diselesaikan');
      refreshData();
    } catch (error) {
      showError('Gagal menyelesaikan sesi');
      console.error(error);
    }
  }
};

const handleDisqualify = async (sesi) => {
  const confirmed = await showConfirm(
    `Diskualifikasi ${sesi.siswa_nama}?`,
    'Siswa akan didiskualifikasi dan tidak mendapat nilai'
  );

  if (confirmed) {
    try {
      await axios.post(`/api/proktor/sesi/${sesi.id}/disqualify`);
      showSuccess('Siswa berhasil didiskualifikasi');
      refreshData();
    } catch (error) {
      showError('Gagal mendiskualifikasi siswa');
      console.error(error);
    }
  }
};

const handleViewDetails = async (sesi) => {
  selectedSesi.value = sesi;
  showDetailModal.value = true;

  // Load events for this session
  try {
    const response = await axios.get(`/api/proktor/sesi/${sesi.id}/events`);
    selectedSesiEvents.value = response.data.data.events;
  } catch (error) {
    console.error('Failed to load session events:', error);
  }
};

const clearEvents = () => {
  recentEvents.value = [];
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

const formatDuration = (seconds) => {
  if (!seconds) return '-';
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;

  if (hours > 0) {
    return `${hours}j ${minutes}m ${secs}d`;
  }
  if (minutes > 0) {
    return `${minutes}m ${secs}d`;
  }
  return `${secs}d`;
};

const getRowClass = (sesi) => {
  if (sesi.status === 'diskualifikasi') return 'table-danger';
  if (sesi.status === 'berlangsung') return 'table-success';
  if (sesi.status === 'timeout') return 'table-warning';
  return '';
};

const getProgressBarClass = (sesi) => {
  if (sesi.progress >= 80) return 'bg-success';
  if (sesi.progress >= 50) return 'bg-info';
  if (sesi.progress >= 25) return 'bg-warning';
  return 'bg-danger';
};

// Realtime setup
let proktorChannel = null;

const setupRealtimeUpdates = () => {
  if (window.Echo) {
    proktorChannel = window.Echo.private(`proktor.ujian.${props.ujian.id}`)
      .listen('ProktorEvent', (event) => {
        console.log('Received proktor event:', event);

        // Add to recent events
        recentEvents.value.unshift(event);
        if (recentEvents.value.length > 50) {
          recentEvents.value.pop();
        }

        // Update session data
        const sesi = sessions.value.find((s) => s.id === event.sesi_ujian_id);
        if (sesi) {
          if (event.tipe === 'tab_switch') {
            sesi.tab_switch_count = (sesi.tab_switch_count || 0) + 1;
          }
        }

        // Show notification for critical events
        if (['tab_switch', 'devtools_open', 'fullscreen_exit'].includes(event.tipe)) {
          showError(`${event.siswa_nama}: ${event.tipe}`);
        }
      })
      .listen('SesiUpdated', (event) => {
        console.log('Session updated:', event);
        
        // Update session in list
        const index = sessions.value.findIndex((s) => s.id === event.sesi.id);
        if (index !== -1) {
          sessions.value[index] = { ...sessions.value[index], ...event.sesi };
        }
      });
  }
};

const cleanupRealtimeUpdates = () => {
  if (proktorChannel) {
    window.Echo.leave(`proktor.ujian.${props.ujian.id}`);
    proktorChannel = null;
  }
};

// Lifecycle
onMounted(() => {
  setupRealtimeUpdates();

  // Auto refresh every 30 seconds
  const refreshInterval = setInterval(() => {
    refreshData();
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

.table-warning {
  background-color: #fff3cd !important;
}

.table-danger {
  background-color: #f8d7da !important;
}
</style>
