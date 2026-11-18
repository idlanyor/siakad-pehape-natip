<script setup>
defineProps({
    label: { type: String, default: '' },
    error: { type: String, default: '' },
    bantuan: { type: String, default: '' },
    wajib: { type: Boolean, default: false },
});
</script>

<template>
    <div class="form-group">
        <label v-if="label">{{ label }} <span v-if="wajib" class="text-danger">*</span></label>
        <slot />
        <small v-if="bantuan && !error" class="form-text text-muted">{{ bantuan }}</small>
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>
