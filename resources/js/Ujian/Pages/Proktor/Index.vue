<script setup>
import { Link } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import { tanggalWaktu } from '@/Ujian/lib/format';

defineProps({ ujian: { type: Array, required: true } });
</script>

<template>
    <PageHeader judul="Pengawasan Ujian" :jejak="[{ label: 'Ujian Online', href: route('ujian.dashboard') }, { label: 'Pengawasan' }]" />

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ujian yang Anda awasi</h3>
            <div class="card-tools small text-muted">7 hari terakhir dan mendatang</div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Ujian</th><th>Kelas</th><th>Jadwal</th><th class="text-center">Sedang mengerjakan</th><th /></tr>
                </thead>
                <tbody>
                    <tr v-for="u in ujian" :key="u.id" :class="{ 'table-success': u.dibuka }">
                        <td>{{ u.judul }}<div class="small text-muted">{{ u.mapel }}</div></td>
                        <td>{{ u.kelas }}</td>
                        <td class="small">
                            {{ tanggalWaktu(u.waktu_mulai) }}<br>s/d {{ tanggalWaktu(u.waktu_selesai) }}
                            <span v-if="u.dibuka" class="badge badge-success ml-1">Berlangsung</span>
                        </td>
                        <td class="text-center">{{ u.berlangsung }}</td>
                        <td class="text-right">
                            <Link :href="route('ujian.proktor.monitor', u.id)" class="btn btn-sm" :class="u.dibuka ? 'btn-warning' : 'btn-outline-secondary'">
                                <i class="fas fa-desktop mr-1" /> Monitor
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="!ujian.length">
                        <td colspan="5" class="text-center text-muted py-4">Tidak ada ujian untuk diawasi.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
