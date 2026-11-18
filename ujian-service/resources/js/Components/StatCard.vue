<template>
  <div class="small-box" :class="`bg-${color || 'info'}`">
    <div class="inner">
      <h3 v-if="loading">
        <i class="fas fa-spinner fa-spin"></i>
      </h3>
      <h3 v-else>{{ value ?? 0 }}</h3>
      <p>{{ title }}</p>
    </div>
    <div class="icon" v-if="icon">
      <i :class="icon"></i>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [Number, String],
    default: 0,
  },
  icon: {
    type: String,
    default: '',
  },
  color: {
    type: String,
    default: 'info',
  },
  loading: {
    type: Boolean,
    default: false,
  },
});
</script>
