import { ref } from 'vue';

const modals = ref({});

export function useModal() {
    const register = (id) => {
        if (!modals.value[id]) {
            modals.value[id] = ref(false);
        }
        return modals.value[id];
    };

    const show = (id) => {
        if (modals.value[id]) {
            modals.value[id].value = true;
        }
    };

    const hide = (id) => {
        if (modals.value[id]) {
            modals.value[id].value = false;
        }
    };

    const toggle = (id) => {
        if (modals.value[id]) {
            modals.value[id].value = !modals.value[id].value;
        }
    };

    const isOpen = (id) => {
        return modals.value[id]?.value || false;
    };

    return {
        register,
        show,
        hide,
        toggle,
        isOpen
    };
}
