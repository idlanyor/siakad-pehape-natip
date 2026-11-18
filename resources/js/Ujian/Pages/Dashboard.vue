<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import { tanggalWaktu } from '@/Ujian/lib/format';

defineProps({
    statistik: { type: Object, default: null },
    mendatang: { type: Array, required: true },
});

const peran = usePage().props.auth.user?.role;
const kartu = [
    ['draft', 'Draft', 'bg-secondary', 'fa-pencil-alt'],
    ['terbit', 'Terbit', 'bg-info', 'fa-paper-plane'],
    ['berlangsung', 'Sedang berlangsung', 'bg-success', 'fa-play-circle'],
    ['peserta_aktif', 'Siswa mengerjakan', 'bg-warning', 'fa-user-edit'],
];
</script>

<template>
    <PageHeader judul="Ujian Online">
        <template #aksi>
            <Link v-if="peran === 'guru' || peran === 'admin'" :href="route('ujian.ujian.create')" class="btn btn-primary">
                <i class="fas fa-plus mr-1" /> Buat ujian
            </Link>
        </template>
    </PageHeader>

    <div v-if="statistik" class="row">
        <div v-for="[kunci, label, warna, ikon] in kartu" :key="kunci" class="col-6 col-lg-3">
            <div class="small-box" :class="warna">
                <div class="inner">
                    <h3>{{ statistik[kunci] }}</h3>
                    <p>{{ label }}</p>
                </div>
                <div class="icon"><i class="fas" :class="ikon" /></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Ujian mendatang & berlangsung</h3></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Ujian</th><th>Kelas</th><th>Waktu</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <tr v-for="u in mendatang" :key="u.id">
                        <td>
                            <Link v-if="peran !== 'siswa'" :href="route('ujian.ujian.show', u.id)">{{ u.judul }}</Link>
                            <span v-else>{{ u.judul }}</span>
                            <div class="small text-muted">{{ u.mapel }}</div>
                        </td>
                        <td>{{ u.kelas }}</td>
                        <td class="small">{{ tanggalWaktu(u.waktu_mulai) }}<br>s/d {{ tanggalWaktu(u.waktu_selesai) }}</td>
                        <td>
                            <span v-if="u.sedang_berlangsung" class="badge badge-success">Berlangsung</span>
                            <span v-else class="badge" :class="`badge-${u.status_badge}`">{{ u.status_label }}</span>
                        </td>
                    </tr>
                    <tr v-if="!mendatang.length">
                        <td colspan="4" class="text-center text-muted py-4">Tidak ada ujian mendatang.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
