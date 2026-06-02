<template>
    <span :class="badgeClasses">
        <i v-if="icon" :class="icon" class="mr-1"></i>
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'].includes(value)
    },
    pill: {
        type: Boolean,
        default: false
    },
    icon: {
        type: String,
        default: null
    }
});

const badgeClasses = computed(() => {
    const classes = ['badge', `badge-${props.variant}`];
    
    if (props.pill) {
        classes.push('badge-pill');
    }
    
    return classes.join(' ');
});
</script>
