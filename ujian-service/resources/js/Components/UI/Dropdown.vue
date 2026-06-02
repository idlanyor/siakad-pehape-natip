<template>
    <div class="dropdown" :class="{ show: isOpen }" v-click-outside="close">
        <button
            :class="buttonClasses"
            type="button"
            @click="toggle"
            aria-haspopup="true"
            :aria-expanded="isOpen"
        >
            <slot name="trigger">
                <span>{{ triggerText }}</span>
                <i class="fas fa-caret-down ml-2"></i>
            </slot>
        </button>

        <transition name="dropdown-fade">
            <div v-if="isOpen" :class="menuClasses">
                <slot />
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    triggerText: {
        type: String,
        default: 'Dropdown'
    },
    variant: {
        type: String,
        default: 'secondary',
        validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    align: {
        type: String,
        default: 'left',
        validator: (value) => ['left', 'right'].includes(value)
    },
    split: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['toggle', 'open', 'close']);

const isOpen = ref(false);

const buttonClasses = computed(() => {
    const classes = ['btn', `btn-${props.variant}`, 'dropdown-toggle'];
    
    if (props.size === 'sm') {
        classes.push('btn-sm');
    } else if (props.size === 'lg') {
        classes.push('btn-lg');
    }
    
    return classes.join(' ');
});

const menuClasses = computed(() => {
    const classes = ['dropdown-menu'];
    
    if (isOpen.value) {
        classes.push('show');
    }
    
    if (props.align === 'right') {
        classes.push('dropdown-menu-right');
    }
    
    return classes.join(' ');
});

const toggle = () => {
    isOpen.value = !isOpen.value;
    emit('toggle', isOpen.value);
    
    if (isOpen.value) {
        emit('open');
    } else {
        emit('close');
    }
};

const close = () => {
    if (isOpen.value) {
        isOpen.value = false;
        emit('close');
    }
};

// Click outside directive
const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            if (!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener('click', el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent);
    }
};
</script>

<style scoped>
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
    transition: opacity 0.15s, transform 0.15s;
}

.dropdown-fade-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}

.dropdown-fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
