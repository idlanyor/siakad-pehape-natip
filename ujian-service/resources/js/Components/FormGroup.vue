<template>
  <div class="form-group">
    <label v-if="label">{{ label }}</label>
    <slot />
    <small v-if="helpText" class="form-text text-muted">{{ helpText }}</small>
  </div>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    default: '',
  },
  helpText: {
    type: String,
    default: '',
  },
});
</script>
