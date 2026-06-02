<template>
  <div class="empty-state" :class="stateClass">
    <div class="empty-state-container">
      <!-- Icon -->
      <div class="empty-state-icon" :class="`text-${iconColor}`">
        <i class="fas" :class="iconClass"></i>
      </div>

      <!-- Title -->
      <h3 class="empty-state-title">{{ title }}</h3>

      <!-- Description -->
      <p v-if="description" class="empty-state-description">
        {{ description }}
      </p>

      <!-- Custom content slot -->
      <div v-if="$slots.default" class="empty-state-content">
        <slot></slot>
      </div>

      <!-- Actions -->
      <div v-if="showAction || $slots.action" class="empty-state-actions">
        <slot name="action">
          <button
            v-if="showAction"
            class="btn"
            :class="actionButtonClass"
            @click="handleAction"
          >
            <i v-if="actionIcon" class="fas" :class="actionIcon"></i>
            {{ actionText }}
          </button>
        </slot>
      </div>

      <!-- Secondary action -->
      <div v-if="showSecondaryAction" class="empty-state-secondary">
        <button
          class="btn btn-link"
          @click="handleSecondaryAction"
        >
          {{ secondaryActionText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'empty',
    validator: (value) => [
      'empty',
      'error',
      'not-found',
      'no-results',
      'no-access',
      'offline',
      'maintenance',
    ].includes(value),
  },
  title: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
  icon: {
    type: String,
    default: '',
  },
  iconColor: {
    type: String,
    default: 'muted',
  },
  showAction: {
    type: Boolean,
    default: false,
  },
  actionText: {
    type: String,
    default: 'Coba Lagi',
  },
  actionIcon: {
    type: String,
    default: 'fa-redo',
  },
  actionVariant: {
    type: String,
    default: 'primary',
  },
  showSecondaryAction: {
    type: Boolean,
    default: false,
  },
  secondaryActionText: {
    type: String,
    default: 'Kembali',
  },
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value),
  },
});

const emit = defineEmits(['action', 'secondary-action']);

// Computed
const stateClass = computed(() => ({
  [`empty-state-${props.size}`]: true,
}));

const iconClass = computed(() => {
  if (props.icon) return props.icon;

  const iconMap = {
    empty: 'fa-inbox',
    error: 'fa-exclamation-triangle',
    'not-found': 'fa-search',
    'no-results': 'fa-filter',
    'no-access': 'fa-lock',
    offline: 'fa-wifi-slash',
    maintenance: 'fa-tools',
  };

  return iconMap[props.type] || 'fa-inbox';
});

const defaultTitle = computed(() => {
  const titleMap = {
    empty: 'Tidak Ada Data',
    error: 'Terjadi Kesalahan',
    'not-found': 'Tidak Ditemukan',
    'no-results': 'Tidak Ada Hasil',
    'no-access': 'Akses Ditolak',
    offline: 'Tidak Ada Koneksi',
    maintenance: 'Sedang Maintenance',
  };

  return props.title || titleMap[props.type] || 'Tidak Ada Data';
});

const defaultDescription = computed(() => {
  const descMap = {
    empty: 'Belum ada data yang tersedia.',
    error: 'Maaf, terjadi kesalahan. Silakan coba lagi.',
    'not-found': 'Halaman atau data yang Anda cari tidak ditemukan.',
    'no-results': 'Tidak ada hasil yang sesuai dengan pencarian Anda.',
    'no-access': 'Anda tidak memiliki izin untuk mengakses halaman ini.',
    offline: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.',
    maintenance: 'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.',
  };

  return props.description || descMap[props.type] || '';
});

const actionButtonClass = computed(() => {
  return `btn-${props.actionVariant}`;
});

// Methods
const handleAction = () => {
  emit('action');
};

const handleSecondaryAction = () => {
  emit('secondary-action');
};
</script>

<style scoped>
.empty-state {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem 1.5rem;
  min-height: 300px;
}

.empty-state-container {
  max-width: 500px;
  width: 100%;
  text-align: center;
}

.empty-state-icon {
  font-size: 4rem;
  margin-bottom: 1.5rem;
  opacity: 0.5;
}

.empty-state-small .empty-state-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-state-large .empty-state-icon {
  font-size: 5rem;
  margin-bottom: 2rem;
}

.empty-state-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.75rem;
}

.empty-state-small .empty-state-title {
  font-size: 1.25rem;
}

.empty-state-large .empty-state-title {
  font-size: 1.75rem;
}

.empty-state-description {
  font-size: 1rem;
  color: #666;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.empty-state-small .empty-state-description {
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.empty-state-large .empty-state-description {
  font-size: 1.125rem;
  margin-bottom: 2rem;
}

.empty-state-content {
  margin-bottom: 1.5rem;
}

.empty-state-actions {
  margin-bottom: 1rem;
}

.empty-state-actions .btn {
  min-width: 120px;
}

.empty-state-secondary {
  margin-top: 0.5rem;
}

.empty-state-secondary .btn-link {
  color: #6c757d;
  text-decoration: none;
}

.empty-state-secondary .btn-link:hover {
  color: #495057;
  text-decoration: underline;
}

/* Icon colors */
.text-muted {
  color: #6c757d !important;
}

.text-primary {
  color: #007bff !important;
}

.text-danger {
  color: #dc3545 !important;
}

.text-warning {
  color: #ffc107 !important;
}

.text-info {
  color: #17a2b8 !important;
}

.text-success {
  color: #28a745 !important;
}

/* Responsive */
@media (max-width: 576px) {
  .empty-state {
    padding: 2rem 1rem;
    min-height: 250px;
  }

  .empty-state-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
  }

  .empty-state-title {
    font-size: 1.25rem;
  }

  .empty-state-description {
    font-size: 0.875rem;
  }

  .empty-state-actions .btn {
    width: 100%;
  }
}

/* Animation */
.empty-state-container {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
