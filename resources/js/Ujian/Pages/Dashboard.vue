<script setup>
// Placeholder Fase 1: membuktikan pipeline Inertia jalan di dalam shell AdminLTE.
// Diganti port Dashboard dari ujian-service di Fase 4.
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <Head title="Dashboard" />

    <div class="content-header px-0">
        <h1 class="m-0">Ujian Online</h1>
    </div>

    <div class="card">
        <div class="card-body">
            Halo, {{ page.props.auth.user?.name }} ({{ page.props.auth.user?.role }}).
            Modul ujian sedang dimigrasikan.
        </div>
    </div>
</template>
