<template>
    <div class="tabs-container">
        <!-- Nav Tabs -->
        <ul class="nav nav-tabs" :class="navClasses" role="tablist">
            <li v-for="(tab, index) in tabs" :key="index" class="nav-item">
                <a
                    :class="['nav-link', { active: activeTab === index }]"
                    :href="`#tab-${index}`"
                    role="tab"
                    @click.prevent="selectTab(index)"
                >
                    <i v-if="tab.icon" :class="tab.icon" class="mr-2"></i>
                    {{ tab.title }}
                    <span v-if="tab.badge" class="badge badge-primary ml-2">{{ tab.badge }}</span>
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" :class="contentClasses">
            <slot />
        </div>
    </div>
</template>

<script setup>
import { ref, provide, computed, onMounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: Number,
        default: 0
    },
    pills: {
        type: Boolean,
        default: false
    },
    vertical: {
        type: Boolean,
        default: false
    },
    justified: {
        type: Boolean,
        default: false
    },
    fill: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const activeTab = ref(props.modelValue);
const tabs = ref([]);

const navClasses = computed(() => {
    const classes = [];
    
    if (props.pills) {
        classes.push('nav-pills');
    }
    
    if (props.justified) {
        classes.push('nav-justified');
    }
    
    if (props.fill) {
        classes.push('nav-fill');
    }
    
    return classes.join(' ');
});

const contentClasses = computed(() => {
    const classes = ['mt-3'];
    
    return classes.join(' ');
});

const selectTab = (index) => {
    activeTab.value = index;
    emit('update:modelValue', index);
    emit('change', index);
};

const registerTab = (tab) => {
    tabs.value.push(tab);
};

// Provide methods for child TabPane components
provide('tabs', {
    activeTab,
    registerTab
});

onMounted(() => {
    activeTab.value = props.modelValue;
});
</script>
