<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    judul: { type: String, required: true },
    // [{ label, href? }]
    jejak: { type: Array, default: () => [] },
});
</script>

<template>
    <Head :title="judul" />
    <div class="content-header px-0">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0">{{ judul }}</h1>
                <ol v-if="jejak.length" class="breadcrumb bg-transparent p-0 m-0 small">
                    <li v-for="(item, i) in jejak" :key="i" class="breadcrumb-item" :class="{ active: !item.href }">
                        <Link v-if="item.href" :href="item.href">{{ item.label }}</Link>
                        <span v-else>{{ item.label }}</span>
                    </li>
                </ol>
            </div>
            <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                <slot name="aksi" />
            </div>
        </div>
    </div>
</template>
