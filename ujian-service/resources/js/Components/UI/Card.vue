<template>
    <div class="card" :class="cardClasses">
        <!-- Header -->
        <div v-if="$slots.header || title" class="card-header" :class="headerClasses">
            <slot name="header">
                <h3 class="card-title">{{ title }}</h3>
            </slot>
            <div v-if="$slots.tools" class="card-tools">
                <slot name="tools" />
            </div>
        </div>

        <!-- Body -->
        <div class="card-body" :class="bodyClasses">
            <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="card-footer" :class="footerClasses">
            <slot name="footer" />
        </div>

        <!-- Overlay -->
        <div v-if="loading" class="overlay">
            <i class="fas fa-2x fa-sync-alt fa-spin"></i>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: null
    },
    variant: {
        type: String,
        default: null,
        validator: (value) => !value || ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'].includes(value)
    },
    outline: {
        type: Boolean,
        default: false
    },
    collapsed: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    noPadding: {
        type: Boolean,
        default: false
    },
    headerBorder: {
        type: Boolean,
        default: true
    },
    footerBorder: {
        type: Boolean,
        default: true
    }
});

const cardClasses = computed(() => {
    const classes = [];
    
    if (props.variant) {
        if (props.outline) {
            classes.push(`card-outline card-${props.variant}`);
        } else {
            classes.push(`card-${props.variant}`);
        }
    }
    
    if (props.collapsed) {
        classes.push('collapsed-card');
    }
    
    return classes.join(' ');
});

const headerClasses = computed(() => {
    const classes = [];
    
    if (!props.headerBorder) {
        classes.push('border-0');
    }
    
    return classes.join(' ');
});

const bodyClasses = computed(() => {
    const classes = [];
    
    if (props.noPadding) {
        classes.push('p-0');
    }
    
    return classes.join(' ');
});

const footerClasses = computed(() => {
    const classes = [];
    
    if (!props.footerBorder) {
        classes.push('border-0');
    }
    
    return classes.join(' ');
});
</script>
