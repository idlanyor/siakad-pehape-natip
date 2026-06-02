<template>
    <teleport to="body">
        <transition name="modal-fade">
            <div v-if="show" class="modal fade show d-block" tabindex="-1" role="dialog" @click.self="handleBackdropClick">
                <div class="modal-dialog" :class="modalDialogClasses" role="document">
                    <div class="modal-content">
                        <!-- Header -->
                        <div v-if="$slots.header || title" class="modal-header">
                            <slot name="header">
                                <h5 class="modal-title">{{ title }}</h5>
                            </slot>
                            <button v-if="closable" type="button" class="close" @click="close" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer || showFooter" class="modal-footer">
                            <slot name="footer">
                                <button type="button" class="btn btn-secondary" @click="close">
                                    {{ cancelText }}
                                </button>
                                <button 
                                    v-if="showConfirm"
                                    type="button" 
                                    :class="`btn btn-${confirmVariant}`"
                                    :disabled="confirmDisabled || confirmLoading"
                                    @click="handleConfirm"
                                >
                                    <span v-if="confirmLoading" class="spinner-border spinner-border-sm mr-2"></span>
                                    {{ confirmText }}
                                </button>
                            </slot>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
        
        <!-- Backdrop -->
        <transition name="backdrop-fade">
            <div v-if="show" class="modal-backdrop fade show"></div>
        </transition>
    </teleport>
</template>

<script setup>
import { computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: null
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
    },
    centered: {
        type: Boolean,
        default: false
    },
    scrollable: {
        type: Boolean,
        default: false
    },
    closable: {
        type: Boolean,
        default: true
    },
    closeOnBackdrop: {
        type: Boolean,
        default: true
    },
    closeOnEscape: {
        type: Boolean,
        default: true
    },
    showFooter: {
        type: Boolean,
        default: true
    },
    showConfirm: {
        type: Boolean,
        default: true
    },
    confirmText: {
        type: String,
        default: 'Confirm'
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    },
    confirmVariant: {
        type: String,
        default: 'primary'
    },
    confirmDisabled: {
        type: Boolean,
        default: false
    },
    confirmLoading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:show', 'close', 'confirm']);

const modalDialogClasses = computed(() => {
    const classes = [];
    
    if (props.size !== 'md') {
        classes.push(`modal-${props.size}`);
    }
    
    if (props.centered) {
        classes.push('modal-dialog-centered');
    }
    
    if (props.scrollable) {
        classes.push('modal-dialog-scrollable');
    }
    
    return classes.join(' ');
});

const close = () => {
    emit('update:show', false);
    emit('close');
};

const handleBackdropClick = () => {
    if (props.closeOnBackdrop && props.closable) {
        close();
    }
};

const handleConfirm = () => {
    emit('confirm');
};

const handleEscape = (event) => {
    if (event.key === 'Escape' && props.show && props.closeOnEscape && props.closable) {
        close();
    }
};

// Prevent body scroll when modal is open
watch(() => props.show, (newValue) => {
    if (newValue) {
        document.body.classList.add('modal-open');
    } else {
        document.body.classList.remove('modal-open');
    }
});

onMounted(() => {
    document.addEventListener('keydown', handleEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
    document.body.classList.remove('modal-open');
});
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.backdrop-fade-enter-active,
.backdrop-fade-leave-active {
    transition: opacity 0.15s;
}

.backdrop-fade-enter-from,
.backdrop-fade-leave-to {
    opacity: 0;
}
</style>
