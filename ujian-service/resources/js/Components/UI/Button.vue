<template>
    <component
        :is="href ? Link : 'button'"
        :href="href"
        :type="type"
        :class="buttonClasses"
        :disabled="disabled || loading"
        @click="handleClick"
    >
        <span v-if="loading" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
        <i v-if="icon && !loading" :class="icon" class="mr-2"></i>
        <slot />
    </component>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'link'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    outline: {
        type: Boolean,
        default: false
    },
    block: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    icon: {
        type: String,
        default: null
    },
    href: {
        type: String,
        default: null
    },
    type: {
        type: String,
        default: 'button'
    }
});

const emit = defineEmits(['click']);

const buttonClasses = computed(() => {
    const classes = ['btn'];
    
    // Variant
    if (props.outline) {
        classes.push(`btn-outline-${props.variant}`);
    } else {
        classes.push(`btn-${props.variant}`);
    }
    
    // Size
    if (props.size === 'sm') {
        classes.push('btn-sm');
    } else if (props.size === 'lg') {
        classes.push('btn-lg');
    }
    
    // Block
    if (props.block) {
        classes.push('btn-block');
    }
    
    return classes.join(' ');
});

const handleClick = (event) => {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
};
</script>
