import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useToast } from './useToast';

/**
 * Composable untuk monitoring network status dan handling reconnection
 * 
 * Features:
 * - Detect online/offline status
 * - Auto-reconnect dengan exponential backoff
 * - Network quality detection (slow/fast)
 * - Reconnection callbacks
 * - Toast notifications untuk status changes
 * 
 * @param {Object} options - Configuration options
 * @param {Boolean} options.showToast - Show toast notifications (default: true)
 * @param {Function} options.onOnline - Callback when going online
 * @param {Function} options.onOffline - Callback when going offline
 * @param {Function} options.onReconnect - Callback on successful reconnect
 * @param {Number} options.pingInterval - Interval untuk ping check in ms (default: 30000)
 * @param {String} options.pingUrl - URL untuk ping check (default: /api/health)
 */
export function useNetworkStatus(options = {}) {
  const {
    showToast = true,
    onOnline = null,
    onOffline = null,
    onReconnect = null,
    pingInterval = 30000,
    pingUrl = '/api/health',
  } = options;

  const { showSuccess, showError, showWarning, showInfo } = useToast();

  // State
  const isOnline = ref(navigator.onLine);
  const isReconnecting = ref(false);
  const reconnectAttempts = ref(0);
  const lastOnlineTime = ref(Date.now());
  const connectionQuality = ref('good'); // good, slow, poor
  const latency = ref(0);

  // Timers
  let pingTimer = null;
  let reconnectTimer = null;

  // Computed
  const isOffline = computed(() => !isOnline.value);
  
  const offlineDuration = computed(() => {
    if (isOnline.value) return 0;
    return Math.floor((Date.now() - lastOnlineTime.value) / 1000);
  });

  const reconnectDelay = computed(() => {
    // Exponential backoff: 1s, 2s, 4s, 8s, max 30s
    return Math.min(1000 * Math.pow(2, reconnectAttempts.value), 30000);
  });

  // Methods
  const checkConnection = async () => {
    try {
      const startTime = Date.now();
      const response = await fetch(pingUrl, {
        method: 'GET',
        cache: 'no-cache',
        headers: {
          'Cache-Control': 'no-cache',
        },
      });

      const endTime = Date.now();
      latency.value = endTime - startTime;

      // Determine connection quality based on latency
      if (latency.value < 200) {
        connectionQuality.value = 'good';
      } else if (latency.value < 500) {
        connectionQuality.value = 'slow';
      } else {
        connectionQuality.value = 'poor';
      }

      return response.ok;
    } catch (error) {
      console.error('Connection check failed:', error);
      return false;
    }
  };

  const handleOnline = async () => {
    console.log('Network: Online');
    
    // Verify connection with ping
    const isActuallyOnline = await checkConnection();
    
    if (isActuallyOnline) {
      isOnline.value = true;
      isReconnecting.value = false;
      reconnectAttempts.value = 0;
      lastOnlineTime.value = Date.now();

      if (showToast) {
        showSuccess('Koneksi internet tersambung kembali');
      }

      // Call callbacks
      if (onOnline) {
        onOnline();
      }

      if (reconnectAttempts.value > 0 && onReconnect) {
        onReconnect();
      }

      // Start periodic ping
      startPing();
    } else {
      // False positive, still offline
      scheduleReconnect();
    }
  };

  const handleOffline = () => {
    console.log('Network: Offline');
    isOnline.value = false;
    lastOnlineTime.value = Date.now();

    if (showToast) {
      showError('Koneksi internet terputus');
    }

    // Call callback
    if (onOffline) {
      onOffline();
    }

    // Stop ping
    stopPing();

    // Start reconnection attempts
    scheduleReconnect();
  };

  const scheduleReconnect = () => {
    if (isReconnecting.value) return;

    isReconnecting.value = true;
    reconnectAttempts.value++;

    const delay = reconnectDelay.value;
    console.log(`Scheduling reconnect attempt ${reconnectAttempts.value} in ${delay}ms`);

    if (showToast && reconnectAttempts.value === 1) {
      showInfo('Mencoba menyambungkan kembali...');
    }

    reconnectTimer = setTimeout(async () => {
      const isConnected = await checkConnection();
      
      if (isConnected) {
        handleOnline();
      } else {
        // Continue trying
        isReconnecting.value = false;
        scheduleReconnect();
      }
    }, delay);
  };

  const startPing = () => {
    stopPing();
    
    pingTimer = setInterval(async () => {
      if (!isOnline.value) return;

      const isConnected = await checkConnection();
      
      if (!isConnected && isOnline.value) {
        // Connection lost
        handleOffline();
      } else if (connectionQuality.value === 'poor' && showToast) {
        showWarning('Koneksi internet lambat');
      }
    }, pingInterval);
  };

  const stopPing = () => {
    if (pingTimer) {
      clearInterval(pingTimer);
      pingTimer = null;
    }
  };

  const stopReconnect = () => {
    if (reconnectTimer) {
      clearTimeout(reconnectTimer);
      reconnectTimer = null;
    }
    isReconnecting.value = false;
  };

  const forceCheck = async () => {
    const isConnected = await checkConnection();
    
    if (isConnected && !isOnline.value) {
      handleOnline();
    } else if (!isConnected && isOnline.value) {
      handleOffline();
    }

    return isConnected;
  };

  // Lifecycle
  onMounted(() => {
    // Add event listeners
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    // Initial check
    forceCheck();

    // Start periodic ping if online
    if (isOnline.value) {
      startPing();
    }
  });

  onUnmounted(() => {
    // Remove event listeners
    window.removeEventListener('online', handleOnline);
    window.removeEventListener('offline', handleOffline);

    // Clear timers
    stopPing();
    stopReconnect();
  });

  return {
    // State
    isOnline,
    isOffline,
    isReconnecting,
    reconnectAttempts,
    offlineDuration,
    connectionQuality,
    latency,

    // Methods
    checkConnection,
    forceCheck,
  };
}
