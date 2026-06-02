<template>
    <div
        v-show="isActive"
        class="tab-pane fade"
        :class="{ 'show active': isActive }"
        role="tabpanel"
    >
        <slot />
    </div>
</template>

<script setup>
import { inject, computed, onMounted } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    icon: {
        type: String,
        default: null
    },
    badge: {
        type: [String, Number],
        default: null
    }
});

const tabs = inject('tabs');

const index = computed(() => {
    return tabs.registerTab ? tabs.registerTab(props) : 0;
});

const isActive = computed(() => {
    return tabs.activeTab.value === tabs.registerTab ? 
        tabs.activeTab.value === index.value : 
        false;
});

onMounted(() => {
    if (tabs.registerTab) {
        tabs.registerTab(props);
    }
});
</script>
