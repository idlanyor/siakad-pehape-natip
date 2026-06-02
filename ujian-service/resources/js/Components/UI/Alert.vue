<template>
    <transition name="alert-fade">
        <div v-if="show" :class="alertClasses" role="alert">
            <button v-if="dismissible" type="button" class="close" @click="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <i v-if="icon" :class="iconClass" class="mr-2"></i>
            <strong v-if="title">{{ title }}</strong>
            <slot />
        </div>
    </transition>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'info',
        validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'].includes(value)
    },
    title: {
        type: String,
        default: null
    },
    dismissible: {
        type: Boolean,
        default: false
    },
    icon: {
        type: [String, Boolean],
        default: null
    },
    autoDismiss: {
        type: [Number, Boolean],
        default: false
    },
    modelValue: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue', 'close']);

const show = ref(props.modelValue);

const alertClasses = computed(() => {
    const classes = ['alert', `alert-${props.variant}`];
    
    if (props.dismissible) {
        classes.push('alert-dismissible');
    }
    
    return classes.join(' ');
});

const iconClass = computed(() => {
    if (typeof props.icon === 'string') {
        return props.icon;
    }
    
    if (props.icon === true) {
        const iconMap = {
            success: 'fas fa-check-circle',
            danger: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle',
            primary: 'fas fa-info-circle',
            secondary: 'fas fa-info-circle',
            light: 'fas fa-info-circle',
            dark: 'fas fa-info-circle'
        };
        return iconMap[props.variant] || 'fas fa-info-circle';
    }
    
    return null;
});

const close = () => {
    show.value = false;
    emit('update:modelValue', false);
    emit('close');
};

watch(() => props.modelValue, (newValue) => {
    show.value = newValue;
});

watch(show, (newValue) => {
    if (!newValue) {
        emit('update:modelValue', false);
    }
});

onMounted(() => {
    if (props.autoDismiss && typeof props.autoDismiss === 'number') {
        setTimeout(() => {
            close();
        }, props.autoDismiss);
    }
});
</script>

<style scoped>
.alert-fade-enter-active,
.alert-fade-leave-active {
    transition: opacity 0.3s, transform 0.3s;
}

.alert-fade-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.alert-fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
