<template>
    <teleport to="body">
        <div class="toast-container">
            <transition-group name="toast-slide">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="['toast', 'show', `bg-${toast.variant}`]"
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                >
                    <div class="toast-header" :class="`bg-${toast.variant} text-white`">
                        <i v-if="toast.icon" :class="toast.icon" class="mr-2"></i>
                        <strong class="mr-auto">{{ toast.title || getDefaultTitle(toast.variant) }}</strong>
                        <button
                            v-if="toast.dismissible"
                            type="button"
                            class="ml-2 mb-1 close text-white"
                            @click="remove(toast.id)"
                            aria-label="Close"
                        >
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="toast-body">
                        {{ toast.message }}
                    </div>
                </div>
            </transition-group>
        </div>
    </teleport>
</template>

<script setup>
import { useToast } from '@/Composables/useToast';

const { toasts, remove } = useToast();

const getDefaultTitle = (variant) => {
    const titles = {
        success: 'Success',
        danger: 'Error',
        warning: 'Warning',
        info: 'Information',
        primary: 'Notice',
        secondary: 'Notice',
        light: 'Notice',
        dark: 'Notice'
    };
    return titles[variant] || 'Notice';
};
</script>

<style scoped>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 350px;
}

.toast {
    min-width: 250px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.toast-slide-enter-active,
.toast-slide-leave-active {
    transition: all 0.3s ease;
}

.toast-slide-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-slide-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-slide-move {
    transition: transform 0.3s ease;
}
</style>
