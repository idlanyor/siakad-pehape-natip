import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useToast } from './useToast';

/**
 * Composable untuk monitoring proktor realtime
 * 
 * Features:
 * - Load ujian dan sesi data
 * - Subscribe ke realtime events via Laravel Echo
 * - Handle proktor actions (force finish, disqualify)
 * - Auto-refresh data
 * - Event log management
 * 
 * @param {Object} options - Configuration options
 * @param {Number} options.ujianId - ID ujian untuk monitoring (optional)
 * @param {Boolean} options.autoRefresh - Enable auto refresh (default: true)
 * @param {Number} options.refreshInterval - Refresh interval in ms (default: 30000)
 * @param {Boolean} options.enableRealtime - Enable realtime updates (default: true)
 */
export function useProktor(options = {}) {
  const {
    ujianId = null,
    autoRefresh = true,
    refreshInterval = 30000,
    enableRealtime = true,
  } = options;

  const { showSuccess, showError, showWarning, showConfirm } = useToast();

  // State
  const loading = ref(false);
  const ujians = ref([]);
  const sessions = ref([]);
  const events = ref([]);
  const statistics = ref({
    activeExams: 0,
    activeSessions: 0,
    totalViolations: 0,
    avgTabSwitch: 0,
  });

  // Realtime channels
  let proktorChannel = null;
  let refreshTimer = null;

  // Computed
  const activeSessions = computed(() => {
    return sessions.value.filter((s) => s.status === 'berlangsung');
  });

  const finishedSessions = computed(() => {
    return sessions.value.filter((s) => s.status === 'selesai');
  });

  const violationCount = computed(() => {
    return sessions.value.reduce((sum, s) => sum + (s.tab_switch_count || 0), 0);
  });

  const highViolationSessions = computed(() => {
    return sessions.value.filter((s) => (s.tab_switch_count || 0) > 5);
  });

  // Methods
  const loadUjians = async (filters = {}) => {
    loading.value = true;
    try {
      const response = await axios.get('/api/proktor/ujian', {
        params: filters,
      });
      ujians.value = response.data.data;
      updateStatistics();
      return response.data;
    } catch (error) {
      showError('Gagal memuat data ujian');
      console.error('Failed to load ujians:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const loadSessions = async (ujianIdParam = null) => {
    const targetUjianId = ujianIdParam || ujianId;
    if (!targetUjianId) {
      console.warn('No ujian ID provided for loading sessions');
      return;
    }

    loading.value = true;
    try {
      const response = await axios.get(`/api/proktor/ujian/${targetUjianId}/sessions`);
      sessions.value = response.data.data;
      updateStatistics();
      return response.data;
    } catch (error) {
      showError('Gagal memuat data sesi');
      console.error('Failed to load sessions:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  };

  const loadEvents = async (ujianIdParam = null, limit = 50) => {
    const targetUjianId = ujianIdParam || ujianId;
    
    try {
      const params = { limit };
      if (targetUjianId) {
        params.ujian_id = targetUjianId;
      }

      const response = await axios.get('/api/proktor/events/recent', { params });
      events.value = response.data.data;
      return response.data;
    } catch (error) {
      console.error('Failed to load events:', error);
      throw error;
    }
  };

  const loadSessionEvents = async (sesiId) => {
    try {
      const response = await axios.get(`/api/proktor/sesi/${sesiId}/events`);
      return response.data.data;
    } catch (error) {
      showError('Gagal memuat event sesi');
      console.error('Failed to load session events:', error);
      throw error;
    }
  };

  const forceFinishSession = async (sesiId) => {
    const confirmed = await showConfirm(
      'Paksa Selesaikan Ujian',
      'Apakah Anda yakin ingin menyelesaikan ujian siswa ini? Tindakan ini tidak dapat dibatalkan.'
    );

    if (!confirmed) return false;

    try {
      const response = await axios.post(`/api/proktor/sesi/${sesiId}/force-finish`);
      showSuccess('Sesi berhasil diselesaikan');
      
      // Update local session
      const session = sessions.value.find((s) => s.id === sesiId);
      if (session) {
        session.status = 'selesai';
        session.waktu_selesai = new Date().toISOString();
      }

      return response.data;
    } catch (error) {
      showError('Gagal menyelesaikan sesi');
      console.error('Failed to force finish session:', error);
      throw error;
    }
  };

  const disqualifySession = async (sesiId, reason = '') => {
    const confirmed = await showConfirm(
      'Diskualifikasi Siswa',
      'Apakah Anda yakin ingin mendiskualifikasi siswa ini? Siswa tidak akan mendapat nilai.'
    );

    if (!confirmed) return false;

    try {
      const response = await axios.post(`/api/proktor/sesi/${sesiId}/disqualify`, {
        reason,
      });
      showSuccess('Siswa berhasil didiskualifikasi');
      
      // Update local session
      const session = sessions.value.find((s) => s.id === sesiId);
      if (session) {
        session.status = 'diskualifikasi';
      }

      return response.data;
    } catch (error) {
      showError('Gagal mendiskualifikasi siswa');
      console.error('Failed to disqualify session:', error);
      throw error;
    }
  };

  const sendAnnouncement = async (ujianIdParam, message) => {
    const targetUjianId = ujianIdParam || ujianId;
    if (!targetUjianId) {
      showError('ID ujian tidak ditemukan');
      return false;
    }

    try {
      const response = await axios.post(`/api/proktor/ujian/${targetUjianId}/announce`, {
        message,
      });
      showSuccess('Pengumuman berhasil dikirim');
      return response.data;
    } catch (error) {
      showError('Gagal mengirim pengumuman');
      console.error('Failed to send announcement:', error);
      throw error;
    }
  };

  const updateStatistics = () => {
    if (ujians.value.length > 0) {
      statistics.value = {
        activeExams: ujians.value.filter((u) => u.is_active).length,
        activeSessions: ujians.value.reduce((sum, u) => sum + (u.active_sessions || 0), 0),
        totalViolations: ujians.value.reduce((sum, u) => sum + (u.total_violations || 0), 0),
        avgTabSwitch: ujians.value.length > 0
          ? (ujians.value.reduce((sum, u) => sum + (u.avg_tab_switch || 0), 0) / ujians.value.length).toFixed(1)
          : 0,
      };
    } else if (sessions.value.length > 0) {
      statistics.value = {
        totalPeserta: sessions.value.length,
        sedangMengerjakan: sessions.value.filter((s) => s.status === 'berlangsung').length,
        selesai: sessions.value.filter((s) => s.status === 'selesai').length,
        totalPelanggaran: sessions.value.reduce((sum, s) => sum + (s.tab_switch_count || 0), 0),
      };
    }
  };

  const refreshData = async () => {
    try {
      if (ujianId) {
        await loadSessions(ujianId);
        await loadEvents(ujianId);
      } else {
        await loadUjians();
        await loadEvents();
      }
    } catch (error) {
      console.error('Failed to refresh data:', error);
    }
  };

  const addEvent = (event) => {
    // Add to beginning of events array
    events.value.unshift(event);
    
    // Keep only last 100 events
    if (events.value.length > 100) {
      events.value = events.value.slice(0, 100);
    }

    // Update session if exists
    const session = sessions.value.find((s) => s.id === event.sesi_ujian_id);
    if (session) {
      if (event.tipe === 'tab_switch') {
        session.tab_switch_count = (session.tab_switch_count || 0) + 1;
      }
      session.last_activity = event.created_at;
    }

    // Show notification for critical events
    const criticalEvents = ['tab_switch', 'devtools_open', 'fullscreen_exit'];
    if (criticalEvents.includes(event.tipe)) {
      showWarning(`${event.siswa_nama}: ${getEventLabel(event.tipe)}`);
    }
  };

  const updateSession = (updatedSession) => {
    const index = sessions.value.findIndex((s) => s.id === updatedSession.id);
    if (index !== -1) {
      sessions.value[index] = { ...sessions.value[index], ...updatedSession };
      updateStatistics();
    }
  };

  const getEventLabel = (type) => {
    const labels = {
      tab_switch: 'berpindah tab',
      blur: 'keluar dari halaman',
      copy: 'melakukan copy',
      paste: 'melakukan paste',
      right_click: 'klik kanan',
      fullscreen_exit: 'keluar dari fullscreen',
      reconnect: 'reconnect',
      devtools_open: 'membuka developer tools',
    };
    return labels[type] || type;
  };

  // Realtime setup
  const setupRealtime = () => {
    if (!enableRealtime || !window.Echo) {
      console.warn('Realtime updates disabled or Echo not available');
      return;
    }

    try {
      if (ujianId) {
        // Subscribe to specific ujian channel
        proktorChannel = window.Echo.private(`proktor.ujian.${ujianId}`)
          .listen('ProktorEvent', (event) => {
            console.log('Received ProktorEvent:', event);
            addEvent(event);
          })
          .listen('SesiUpdated', (event) => {
            console.log('Received SesiUpdated:', event);
            updateSession(event.sesi);
          });
      } else {
        // Subscribe to global proktor channel
        proktorChannel = window.Echo.private('proktor.global')
          .listen('ProktorEvent', (event) => {
            console.log('Received ProktorEvent (global):', event);
            addEvent(event);
          });
      }

      console.log('Realtime monitoring setup complete');
    } catch (error) {
      console.error('Failed to setup realtime:', error);
      showWarning('Realtime monitoring tidak tersedia, menggunakan polling');
    }
  };

  const cleanupRealtime = () => {
    if (proktorChannel) {
      const channelName = ujianId ? `proktor.ujian.${ujianId}` : 'proktor.global';
      window.Echo.leave(channelName);
      proktorChannel = null;
      console.log('Realtime monitoring cleaned up');
    }
  };

  const startAutoRefresh = () => {
    if (!autoRefresh) return;

    refreshTimer = setInterval(() => {
      refreshData();
    }, refreshInterval);

    console.log(`Auto-refresh started (interval: ${refreshInterval}ms)`);
  };

  const stopAutoRefresh = () => {
    if (refreshTimer) {
      clearInterval(refreshTimer);
      refreshTimer = null;
      console.log('Auto-refresh stopped');
    }
  };

  // Lifecycle
  const initialize = async () => {
    await refreshData();
    setupRealtime();
    startAutoRefresh();
  };

  const cleanup = () => {
    cleanupRealtime();
    stopAutoRefresh();
  };

  // Auto-initialize on mount if configured
  onMounted(() => {
    initialize();
  });

  onUnmounted(() => {
    cleanup();
  });

  return {
    // State
    loading,
    ujians,
    sessions,
    events,
    statistics,

    // Computed
    activeSessions,
    finishedSessions,
    violationCount,
    highViolationSessions,

    // Methods
    loadUjians,
    loadSessions,
    loadEvents,
    loadSessionEvents,
    forceFinishSession,
    disqualifySession,
    sendAnnouncement,
    refreshData,
    addEvent,
    updateSession,

    // Lifecycle
    initialize,
    cleanup,
    setupRealtime,
    cleanupRealtime,
    startAutoRefresh,
    stopAutoRefresh,
  };
}
