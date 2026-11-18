<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import Pagination from '@/Ujian/Components/Pagination.vue';
import { potong } from '@/Ujian/lib/format';
import { konfirmasi } from '@/Ujian/lib/konfirmasi';

const props = defineProps({
    soal: { type: Object, required: true },
    filter: { type: Object, default: () => ({}) },
    mapel: { type: Array, required: true },
});

const f = reactive({
    mapel_id: props.filter.mapel_id ?? '',
    tipe: props.filter.tipe ?? '',
    tingkat_kesulitan: props.filter.tingkat_kesulitan ?? '',
    cari: props.filter.cari ?? '',
});

const terapkan = () => router.get(route('ujian.bank-soal.index'),
    Object.fromEntries(Object.entries(f).filter(([, v]) => v !== '')),
    { preserveState: true, replace: true });

async function hapus(s) {
    if (await konfirmasi('Hapus soal ini dari bank soal? Ujian yang sudah memakainya tidak terpengaruh.', { bahaya: true, tombol: 'Hapus' })) {
        router.delete(route('ujian.bank-soal.destroy', s.id), { preserveScroll: true });
    }
}
</script>

<template>
    <PageHeader judul="Bank Soal" :jejak="[{ label: 'Ujian Online', href: route('ujian.dashboard') }, { label: 'Bank Soal' }]">
        <template #aksi>
            <Link :href="route('ujian.bank-soal.create')" class="btn btn-primary"><i class="fas fa-plus mr-1" /> Tambah soal</Link>
        </template>
    </PageHeader>

    <div class="card">
        <div class="card-header">
            <form class="form-row" @submit.prevent="terapkan">
                <div class="col-md-3 mb-2">
                    <select v-model="f.mapel_id" class="form-control form-control-sm" @change="terapkan">
                        <option value="">Semua mapel</option>
                        <option v-for="m in mapel" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select v-model="f.tipe" class="form-control form-control-sm" @change="terapkan">
                        <option value="">Semua tipe</option>
                        <option value="pilihan_ganda">Pilihan ganda</option>
                        <option value="essay">Essay</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select v-model="f.tingkat_kesulitan" class="form-control form-control-sm" @change="terapkan">
                        <option value="">Semua tingkat</option>
                        <option value="mudah">Mudah</option>
                        <option value="sedang">Sedang</option>
                        <option value="sulit">Sulit</option>
                    </select>
                </div>
                <div class="col-md-5 mb-2">
                    <div class="input-group input-group-sm">
                        <input v-model="f.cari" class="form-control" placeholder="Cari pertanyaan atau topik">
                        <div class="input-group-append"><button class="btn btn-default"><i class="fas fa-search" /></button></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Pertanyaan</th><th>Mapel</th><th>Tipe</th><th>Tingkat</th><th class="text-center">Bobot</th><th>Pembuat</th><th /></tr>
                </thead>
                <tbody>
                    <tr v-for="s in soal.data" :key="s.id">
                        <td style="white-space: pre-line">
                            {{ potong(s.pertanyaan) }}
                            <div v-if="s.topik" class="small text-muted">{{ s.topik }}</div>
                        </td>
                        <td>{{ s.mapel }}</td>
                        <td>{{ s.tipe === 'essay' ? 'Essay' : 'PG' }}</td>
                        <td class="text-capitalize">{{ s.tingkat_kesulitan }}</td>
                        <td class="text-center">{{ s.bobot }}</td>
                        <td class="small">{{ s.pembuat ?? '-' }}</td>
                        <td class="text-right text-nowrap">
                            <template v-if="s.bisa_ubah">
                                <Link :href="route('ujian.bank-soal.edit', s.id)" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit" /></Link>
                                <button class="btn btn-sm btn-outline-danger ml-1" @click="hapus(s)"><i class="fas fa-trash" /></button>
                            </template>
                        </td>
                    </tr>
                    <tr v-if="!soal.data.length">
                        <td colspan="7" class="text-center text-muted py-4">Tidak ada soal.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer"><Pagination :data="soal" /></div>
    </div>
</template>
