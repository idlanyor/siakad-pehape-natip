<template>
  <div v-if="hasError" class="error-boundary">
    <div class="error-container">
      <div class="error-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <h3 class="error-title">{{ errorTitle }}</h3>
      <p class="error-message">{{ errorMessage }}</p>
      
      <div v-if="showDetails && errorDetails" class="error-details">
        <button
          class="btn btn-sm btn-link"
          @click="toggleDetails"
        >
          <i class="fas" :class="detailsExpanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
          {{ detailsExpanded ? 'Sembunyikan' : 'Tampilkan' }} Detail
        </button>
        <div v-show="detailsExpanded" class="error-details-content">
          <pre>{{ errorDetails }}</pre>
        </div>
      </div>

      <div class="error-actions">
        <button
          class="btn btn-primary"
          @click="handleRetry"
        >
          <i class="fas fa-redo"></i>
          Coba Lagi
        </button>
        <button
          v-if="showReload"
          class="btn btn-secondary ml-2"
          @click="handleReload"
        >
          <i class="fas fa-sync"></i>
          Muat Ulang Halaman
        </button>
        <button
          v-if="showGoBack"
          class="btn btn-outline-secondary ml-2"
          @click="handleGoBack"
        >
          <i class="fas fa-arrow-left"></i>
          Kembali
        </button>
      </div>
    </div>
  </div>
  <slot v-else></slot>
</template>

<script setup>
import { ref, onErrorCaptured, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  fallbackTitle: {
    type: String,
    default: 'Terjadi Kesalahan',
  },
  fallbackMessage: {
    type: String,
    default: 'Maaf, terjadi kesalahan yang tidak terduga. Silakan coba lagi.',
  },
  showDetails: {
    type: Boolean,
    default: import.meta.env.DEV, // Show details only in development
  },
  showReload: {
    type: Boolean,
    default: true,
  },
  showGoBack: {
    type: Boolean,
    default: true,
  },
  onError: {
    type: Function,
    default: null,
  },
  onRetry: {
    type: Function,
    default: null,
  },
});

const emit = defineEmits(['error', 'retry']);

// State
const hasError = ref(false);
const error = ref(null);
const errorInfo = ref(null);
const detailsExpanded = ref(false);

// Computed
const errorTitle = computed(() => {
  if (!error.value) return props.fallbackTitle;
  
  // Custom error titles based on error type
  if (error.value.response) {
    const status = error.value.response.status;
    if (status === 404) return 'Halaman Tidak Ditemukan';
    if (status === 403) return 'Akses Ditolak';
    if (status === 401) return 'Sesi Berakhir';
    if (status === 500) return 'Kesalahan Server';
    if (status >= 400 && status < 500) return 'Permintaan Tidak Valid';
    if (status >= 500) return 'Kesalahan Server';
  }
  
  if (error.value.message) {
    if (error.value.message.includes('Network')) return 'Kesalahan Jaringan';
    if (error.value.message.includes('timeout')) return 'Waktu Habis';
  }
  
  return props.fallbackTitle;
});

const errorMessage = computed(() => {
  if (!error.value) return props.fallbackMessage;
  
  // Custom error messages
  if (error.value.response) {
    const status = error.value.response.status;
    if (status === 404) return 'Halaman yang Anda cari tidak ditemukan.';
    if (status === 403) return 'Anda tidak memiliki izin untuk mengakses halaman ini.';
    if (status === 401) return 'Sesi Anda telah berakhir. Silakan login kembali.';
    if (status === 500) return 'Terjadi kesalahan pada server. Tim kami telah diberitahu.';
    if (status === 422) return 'Data yang Anda masukkan tidak valid. Silakan periksa kembali.';
    
    // Try to get message from response
    if (error.value.response.data?.message) {
      return error.value.response.data.message;
    }
  }
  
  if (error.value.message) {
    if (error.value.message.includes('Network')) {
      return 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
    }
    if (error.value.message.includes('timeout')) {
      return 'Permintaan memakan waktu terlalu lama. Silakan coba lagi.';
    }
  }
  
  return error.value.message || props.fallbackMessage;
});

const errorDetails = computed(() => {
  if (!error.value) return null;
  
  const details = {
    message: error.value.message,
    stack: error.value.stack,
  };
  
  if (error.value.response) {
    details.status = error.value.response.status;
    details.statusText = error.value.response.statusText;
    details.data = error.value.response.data;
  }
  
  if (errorInfo.value) {
    details.componentStack = errorInfo.value.componentStack;
  }
  
  return JSON.stringify(details, null, 2);
});

// Methods
const toggleDetails = () => {
  detailsExpanded.value = !detailsExpanded.value;
};

const handleRetry = () => {
  if (props.onRetry) {
    props.onRetry();
  }
  
  emit('retry');
  reset();
};

const handleReload = () => {
  window.location.reload();
};

const handleGoBack = () => {
  if (window.history.length > 1) {
    router.visit(window.history.back());
  } else {
    router.visit('/dashboard');
  }
};

const reset = () => {
  hasError.value = false;
  error.value = null;
  errorInfo.value = null;
  detailsExpanded.value = false;
};

// Error capture
onErrorCaptured((err, instance, info) => {
  hasError.value = true;
  error.value = err;
  errorInfo.value = { componentStack: info };
  
  // Log error to console in development
  if (import.meta.env.DEV) {
    console.error('Error captured by ErrorBoundary:', err);
    console.error('Component info:', info);
  }
  
  // Call custom error handler
  if (props.onError) {
    props.onError(err, instance, info);
  }
  
  emit('error', { error: err, info });
  
  // Prevent error from propagating
  return false;
});

// Expose reset method
defineExpose({
  reset,
});
</script>

<style scoped>
.error-boundary {
  min-height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.error-container {
  max-width: 600px;
  width: 100%;
  text-align: center;
  background: #fff;
  border-radius: 8px;
  padding: 3rem 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.error-icon {
  font-size: 4rem;
  color: #dc3545;
  margin-bottom: 1.5rem;
}

.error-title {
  font-size: 1.75rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 1rem;
}

.error-message {
  font-size: 1rem;
  color: #666;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.error-details {
  margin-bottom: 2rem;
  text-align: left;
}

.error-details-content {
  margin-top: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 4px;
  max-height: 300px;
  overflow-y: auto;
}

.error-details-content pre {
  margin: 0;
  font-size: 0.875rem;
  color: #495057;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.error-actions {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.error-actions .btn {
  min-width: 120px;
}

/* Responsive */
@media (max-width: 576px) {
  .error-container {
    padding: 2rem 1rem;
  }

  .error-icon {
    font-size: 3rem;
  }

  .error-title {
    font-size: 1.5rem;
  }

  .error-actions {
    flex-direction: column;
  }

  .error-actions .btn {
    width: 100%;
    margin-left: 0 !important;
  }
}
</style>
