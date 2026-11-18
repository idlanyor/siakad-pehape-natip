<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import Pagination from '@/Ujian/Components/Pagination.vue';
import { tanggalWaktu } from '@/Ujian/lib/format';

const props = defineProps({
    ujian: { type: Object, required: true },
    filter: { type: Object, default: () => ({}) },
    periode: { type: Array, required: true },
    bolehBuat: { type: Boolean, default: false },
});

const f = reactive({ periode_id: props.filter.periode_id ?? '', status: props.filter.status ?? '', cari: props.filter.cari ?? '' });
const terapkan = () => router.get(route('ujian.ujian.index'),
    Object.fromEntries(Object.entries(f).filter(([, v]) => v !== '')),
    { preserveState: true, replace: true });
</script>

<template>
    <PageHeader judul="Daftar Ujian" :jejak="[{ label: 'Ujian Online', href: route('ujian.dashboard') }, { label: 'Ujian' }]">
        <template #aksi>
            <Link v-if="bolehBuat" :href="route('ujian.ujian.create')" class="btn btn-primary"><i class="fas fa-plus mr-1" /> Buat ujian</Link>
        </template>
    </PageHeader>

    <div class="card">
        <div class="card-header">
            <form class="form-row" @submit.prevent="terapkan">
                <div class="col-md-4 mb-2">
                    <select v-model="f.periode_id" class="form-control form-control-sm" @change="terapkan">
                        <option value="">Semua periode</option>
                        <option v-for="p in periode" :key="p.value" :value="p.value">{{ p.label }}</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select v-model="f.status" class="form-control form-control-sm" @change="terapkan">
                        <option value="">Semua status</option>
                        <option value="draft">Draft</option>
                        <option value="published">Terbit</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="col-md-5 mb-2">
                    <div class="input-group input-group-sm">
                        <input v-model="f.cari" class="form-control" placeholder="Cari judul">
                        <div class="input-group-append"><button class="btn btn-default"><i class="fas fa-search" /></button></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Ujian</th><th>Kelas</th><th>Jadwal</th><th class="text-center">Soal</th><th class="text-center">Peserta</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <tr v-for="u in ujian.data" :key="u.id">
                        <td>
                            <Link :href="route('ujian.ujian.show', u.id)" class="font-weight-bold">{{ u.judul }}</Link>
                            <div class="small text-muted">{{ u.mapel }} · {{ u.tipe.toUpperCase() }} · {{ u.periode }}</div>
                            <div v-if="u.guru" class="small text-muted">{{ u.guru }}</div>
                        </td>
                        <td>{{ u.kelas }}</td>
                        <td class="small">{{ tanggalWaktu(u.waktu_mulai) }}<br>s/d {{ tanggalWaktu(u.waktu_selesai) }}</td>
                        <td class="text-center">{{ u.jumlah_soal }}</td>
                        <td class="text-center">{{ u.jumlah_sesi }}</td>
                        <td><span class="badge" :class="`badge-${u.status_badge}`">{{ u.status_label }}</span></td>
                    </tr>
                    <tr v-if="!ujian.data.length">
                        <td colspan="6" class="text-center text-muted py-4">Belum ada ujian.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer"><Pagination :data="ujian" /></div>
    </div>
</template>
