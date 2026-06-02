<template>
    <div v-if="show" :class="containerClasses">
        <div :class="spinnerClasses" role="status">
            <span class="sr-only">{{ text }}</span>
        </div>
        <p v-if="text && showText" class="mt-2 mb-0">{{ text }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: true
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'].includes(value)
    },
    type: {
        type: String,
        default: 'border',
        validator: (value) => ['border', 'grow'].includes(value)
    },
    text: {
        type: String,
        default: 'Loading...'
    },
    showText: {
        type: Boolean,
        default: false
    },
    centered: {
        type: Boolean,
        default: false
    },
    overlay: {
        type: Boolean,
        default: false
    }
});

const containerClasses = computed(() => {
    const classes = [];
    
    if (props.centered) {
        classes.push('d-flex', 'flex-column', 'align-items-center', 'justify-content-center');
    }
    
    if (props.overlay) {
        classes.push('loading-overlay');
    }
    
    return classes.join(' ');
});

const spinnerClasses = computed(() => {
    const classes = [];
    
    if (props.type === 'border') {
        classes.push('spinner-border');
    } else {
        classes.push('spinner-grow');
    }
    
    classes.push(`text-${props.variant}`);
    
    if (props.size === 'sm') {
        classes.push(`spinner-${props.type}-sm`);
    } else if (props.size === 'lg') {
        // Custom large size
        classes.push('spinner-lg');
    }
    
    return classes.join(' ');
});
</script>

<style scoped>
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.spinner-lg {
    width: 3rem;
    height: 3rem;
    border-width: 0.3em;
}
</style>
