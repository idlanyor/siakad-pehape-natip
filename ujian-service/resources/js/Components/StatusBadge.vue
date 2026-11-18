<template>
  <span class="badge" :class="badgeClass">
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    default: '',
  },
});

const statusMap = {
  berlangsung: { label: 'Berlangsung', class: 'badge-success' },
  aktif: { label: 'Aktif', class: 'badge-success' },
  selesai: { label: 'Selesai', class: 'badge-primary' },
  draft: { label: 'Draft', class: 'badge-secondary' },
  belum_mulai: { label: 'Belum Mulai', class: 'badge-warning' },
  dihentikan: { label: 'Dihentikan', class: 'badge-danger' },
  terkunci: { label: 'Terkunci', class: 'badge-danger' },
  diskualifikasi: { label: 'Diskualifikasi', class: 'badge-danger' },
};

const normalized = computed(() => (props.status || '').toLowerCase().replace(/\s+/g, '_'));

const badgeClass = computed(() => {
  const norm = normalized.value;
  if (statusMap[norm]) {
    return statusMap[norm].class;
  }
  if (norm.includes('selesai')) return 'badge-primary';
  if (norm.includes('aktif') || norm.includes('jalan')) return 'badge-success';
  if (norm.includes('batal') || norm.includes('henti') || norm.includes('gagal')) return 'badge-danger';
  if (norm.includes('tunda') || norm.includes('tunggu')) return 'badge-warning';
  return 'badge-info';
});

const label = computed(() => {
  const norm = normalized.value;
  if (statusMap[norm]) {
    return statusMap[norm].label;
  }
  return props.status || '-';
});
</script>
