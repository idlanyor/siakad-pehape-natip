<script setup>
import { Link } from '@inertiajs/vue3';

// Paginator Laravel yang diserialisasi Inertia: { links, from, to, total }.
defineProps({ data: { type: Object, required: true } });
</script>

<template>
    <div v-if="data.total > 0" class="d-flex flex-wrap justify-content-between align-items-center">
        <small class="text-muted mb-2">Menampilkan {{ data.from }}–{{ data.to }} dari {{ data.total }}</small>
        <ul v-if="data.links.length > 3" class="pagination pagination-sm m-0">
            <li v-for="(link, i) in data.links" :key="i" class="page-item"
                :class="{ active: link.active, disabled: !link.url }">
                <Link v-if="link.url" class="page-link" :href="link.url" preserve-scroll v-html="link.label" />
                <span v-else class="page-link" v-html="link.label" />
            </li>
        </ul>
    </div>
</template>
