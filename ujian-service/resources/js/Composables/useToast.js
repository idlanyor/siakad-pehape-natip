import { ref } from 'vue';

const toasts = ref([]);
let nextId = 0;

export function useToast() {
    const show = (message, options = {}) => {
        const toast = {
            id: nextId++,
            message,
            variant: options.variant || 'info',
            duration: options.duration || 3000,
            dismissible: options.dismissible !== false,
            icon: options.icon,
            title: options.title
        };

        toasts.value.push(toast);

        if (toast.duration > 0) {
            setTimeout(() => {
                remove(toast.id);
            }, toast.duration);
        }

        return toast.id;
    };

    const remove = (id) => {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    const clear = () => {
        toasts.value = [];
    };

    const success = (message, options = {}) => {
        return show(message, { ...options, variant: 'success', icon: 'fas fa-check-circle' });
    };

    const error = (message, options = {}) => {
        return show(message, { ...options, variant: 'danger', icon: 'fas fa-exclamation-circle' });
    };

    const warning = (message, options = {}) => {
        return show(message, { ...options, variant: 'warning', icon: 'fas fa-exclamation-triangle' });
    };

    const info = (message, options = {}) => {
        return show(message, { ...options, variant: 'info', icon: 'fas fa-info-circle' });
    };

    return {
        toasts,
        show,
        remove,
        clear,
        success,
        error,
        warning,
        info
    };
}
