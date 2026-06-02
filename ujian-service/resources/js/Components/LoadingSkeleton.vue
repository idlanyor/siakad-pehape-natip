<template>
  <div class="loading-skeleton" :class="skeletonClass">
    <!-- Card Skeleton -->
    <div v-if="type === 'card'" class="skeleton-card">
      <div class="skeleton-header">
        <div class="skeleton-avatar" v-if="showAvatar"></div>
        <div class="skeleton-title"></div>
      </div>
      <div class="skeleton-body">
        <div class="skeleton-line" v-for="n in lines" :key="n"></div>
      </div>
      <div class="skeleton-footer" v-if="showFooter">
        <div class="skeleton-button"></div>
        <div class="skeleton-button"></div>
      </div>
    </div>

    <!-- Table Skeleton -->
    <div v-else-if="type === 'table'" class="skeleton-table">
      <div class="skeleton-table-header">
        <div class="skeleton-th" v-for="n in columns" :key="n"></div>
      </div>
      <div class="skeleton-table-row" v-for="n in rows" :key="n">
        <div class="skeleton-td" v-for="m in columns" :key="m"></div>
      </div>
    </div>

    <!-- List Skeleton -->
    <div v-else-if="type === 'list'" class="skeleton-list">
      <div class="skeleton-list-item" v-for="n in items" :key="n">
        <div class="skeleton-avatar" v-if="showAvatar"></div>
        <div class="skeleton-content">
          <div class="skeleton-title"></div>
          <div class="skeleton-subtitle"></div>
        </div>
      </div>
    </div>

    <!-- Form Skeleton -->
    <div v-else-if="type === 'form'" class="skeleton-form">
      <div class="skeleton-form-group" v-for="n in fields" :key="n">
        <div class="skeleton-label"></div>
        <div class="skeleton-input"></div>
      </div>
      <div class="skeleton-form-actions">
        <div class="skeleton-button"></div>
        <div class="skeleton-button"></div>
      </div>
    </div>

    <!-- Text Skeleton -->
    <div v-else-if="type === 'text'" class="skeleton-text">
      <div class="skeleton-line" v-for="n in lines" :key="n" :style="getLineWidth(n)"></div>
    </div>

    <!-- Image Skeleton -->
    <div v-else-if="type === 'image'" class="skeleton-image" :style="imageStyle"></div>

    <!-- Custom Skeleton -->
    <div v-else-if="type === 'custom'" class="skeleton-custom">
      <slot></slot>
    </div>

    <!-- Default: Simple Box -->
    <div v-else class="skeleton-box" :style="boxStyle"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'box',
    validator: (value) => [
      'card',
      'table',
      'list',
      'form',
      'text',
      'image',
      'custom',
      'box',
    ].includes(value),
  },
  lines: {
    type: Number,
    default: 3,
  },
  columns: {
    type: Number,
    default: 4,
  },
  rows: {
    type: Number,
    default: 5,
  },
  items: {
    type: Number,
    default: 5,
  },
  fields: {
    type: Number,
    default: 4,
  },
  showAvatar: {
    type: Boolean,
    default: false,
  },
  showFooter: {
    type: Boolean,
    default: false,
  },
  width: {
    type: String,
    default: '100%',
  },
  height: {
    type: String,
    default: 'auto',
  },
  animated: {
    type: Boolean,
    default: true,
  },
  theme: {
    type: String,
    default: 'light',
    validator: (value) => ['light', 'dark'].includes(value),
  },
});

const skeletonClass = computed(() => ({
  'skeleton-animated': props.animated,
  [`skeleton-theme-${props.theme}`]: true,
}));

const boxStyle = computed(() => ({
  width: props.width,
  height: props.height,
}));

const imageStyle = computed(() => ({
  width: props.width,
  height: props.height || '200px',
}));

const getLineWidth = (index) => {
  // Last line is shorter
  if (index === props.lines) {
    return { width: '60%' };
  }
  return { width: '100%' };
};
</script>

<style scoped>
/* Base Skeleton Styles */
.loading-skeleton {
  width: 100%;
}

.skeleton-animated .skeleton-line,
.skeleton-animated .skeleton-title,
.skeleton-animated .skeleton-subtitle,
.skeleton-animated .skeleton-avatar,
.skeleton-animated .skeleton-button,
.skeleton-animated .skeleton-input,
.skeleton-animated .skeleton-label,
.skeleton-animated .skeleton-th,
.skeleton-animated .skeleton-td,
.skeleton-animated .skeleton-box,
.skeleton-animated .skeleton-image {
  animation: skeleton-loading 1.5s ease-in-out infinite;
  background: linear-gradient(
    90deg,
    #f0f0f0 25%,
    #e0e0e0 50%,
    #f0f0f0 75%
  );
  background-size: 200% 100%;
}

.skeleton-theme-dark .skeleton-animated .skeleton-line,
.skeleton-theme-dark .skeleton-animated .skeleton-title,
.skeleton-theme-dark .skeleton-animated .skeleton-subtitle,
.skeleton-theme-dark .skeleton-animated .skeleton-avatar,
.skeleton-theme-dark .skeleton-animated .skeleton-button,
.skeleton-theme-dark .skeleton-animated .skeleton-input,
.skeleton-theme-dark .skeleton-animated .skeleton-label,
.skeleton-theme-dark .skeleton-animated .skeleton-th,
.skeleton-theme-dark .skeleton-animated .skeleton-td,
.skeleton-theme-dark .skeleton-animated .skeleton-box,
.skeleton-theme-dark .skeleton-animated .skeleton-image {
  background: linear-gradient(
    90deg,
    #2a2a2a 25%,
    #1a1a1a 50%,
    #2a2a2a 75%
  );
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Card Skeleton */
.skeleton-card {
  background: #fff;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.skeleton-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.skeleton-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #e0e0e0;
  flex-shrink: 0;
}

.skeleton-title {
  height: 24px;
  background: #e0e0e0;
  border-radius: 4px;
  flex: 1;
}

.skeleton-body {
  margin-bottom: 1rem;
}

.skeleton-line {
  height: 16px;
  background: #e0e0e0;
  border-radius: 4px;
  margin-bottom: 0.75rem;
}

.skeleton-line:last-child {
  margin-bottom: 0;
}

.skeleton-footer {
  display: flex;
  gap: 0.5rem;
  padding-top: 1rem;
  border-top: 1px solid #f0f0f0;
}

.skeleton-button {
  height: 36px;
  background: #e0e0e0;
  border-radius: 4px;
  flex: 1;
}

/* Table Skeleton */
.skeleton-table {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}

.skeleton-table-header {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-bottom: 1px solid #e0e0e0;
}

.skeleton-th {
  height: 20px;
  background: #e0e0e0;
  border-radius: 4px;
  flex: 1;
}

.skeleton-table-row {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.skeleton-table-row:last-child {
  border-bottom: none;
}

.skeleton-td {
  height: 16px;
  background: #e0e0e0;
  border-radius: 4px;
  flex: 1;
}

/* List Skeleton */
.skeleton-list {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}

.skeleton-list-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.skeleton-list-item:last-child {
  border-bottom: none;
}

.skeleton-content {
  flex: 1;
}

.skeleton-subtitle {
  height: 14px;
  background: #e0e0e0;
  border-radius: 4px;
  margin-top: 0.5rem;
  width: 70%;
}

/* Form Skeleton */
.skeleton-form {
  background: #fff;
  border-radius: 8px;
  padding: 1.5rem;
}

.skeleton-form-group {
  margin-bottom: 1.5rem;
}

.skeleton-label {
  height: 16px;
  background: #e0e0e0;
  border-radius: 4px;
  width: 30%;
  margin-bottom: 0.5rem;
}

.skeleton-input {
  height: 40px;
  background: #e0e0e0;
  border-radius: 4px;
}

.skeleton-form-actions {
  display: flex;
  gap: 0.5rem;
  padding-top: 1rem;
  border-top: 1px solid #f0f0f0;
}

/* Text Skeleton */
.skeleton-text {
  padding: 0.5rem 0;
}

/* Image Skeleton */
.skeleton-image {
  background: #e0e0e0;
  border-radius: 8px;
}

/* Box Skeleton */
.skeleton-box {
  background: #e0e0e0;
  border-radius: 4px;
  min-height: 100px;
}

/* Responsive */
@media (max-width: 768px) {
  .skeleton-card {
    padding: 1rem;
  }

  .skeleton-table-header,
  .skeleton-table-row {
    padding: 0.75rem;
  }

  .skeleton-list-item {
    padding: 0.75rem;
  }

  .skeleton-form {
    padding: 1rem;
  }
}
</style>
