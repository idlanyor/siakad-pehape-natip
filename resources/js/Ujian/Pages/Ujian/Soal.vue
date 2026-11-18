<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import SoalFields from '@/Ujian/Components/SoalFields.vue';
import { potong } from '@/Ujian/lib/format';
import { konfirmasi } from '@/Ujian/lib/konfirmasi';

const props = defineProps({
    ujian: { type: Object, required: true },
    soal: { type: Array, required: true },
    bank: { type: Array, required: true },
    cari: { type: String, default: null },
    totalBobot: { type: Number, required: true },
});

// --- Form tulis/ubah soal -------------------------------------------------
const kosong = () => ({
    tipe: 'pilihan_ganda', pertanyaan: '', bobot: 1, kunci_jawaban: '',
    opsi_a: '', opsi_b: '', opsi_c: '', opsi_d: '', opsi_e: '',
});
const form = useForm(kosong());
const diedit = ref(null);
const tampilForm = ref(false);

function tulisBaru() {
    diedit.value = null;
    form.clearErrors();
    Object.assign(form, kosong());
    tampilForm.value = true;
}

function ubah(s) {
    diedit.value = s.id;
    form.clearErrors();
    Object.assign(form, {
        tipe: s.tipe, pertanyaan: s.pertanyaan, bobot: s.bobot, kunci_jawaban: s.kunci_jawaban ?? '',
        opsi_a: s.opsi_a ?? '', opsi_b: s.opsi_b ?? '', opsi_c: s.opsi_c ?? '', opsi_d: s.opsi_d ?? '', opsi_e: s.opsi_e ?? '',
    });
    tampilForm.value = true;
}

function simpan() {
    const opsi = { preserveScroll: true, onSuccess: () => { tampilForm.value = false; } };
    if (diedit.value) {
        form.put(route('ujian.soal.update', diedit.value), opsi);
    } else {
        form.post(route('ujian.soal.store', props.ujian.id), opsi);
    }
}

async function hapus(s) {
    if (await konfirmasi(`Hapus soal nomor ${s.nomor}?`, { bahaya: true, tombol: 'Hapus' })) {
        router.delete(route('ujian.soal.destroy', s.id), { preserveScroll: true });
    }
}

// --- Urutan ---------------------------------------------------------------
function geser(index, arah) {
    const ids = props.soal.map((s) => s.id);
    const tujuan = index + arah;
    [ids[index], ids[tujuan]] = [ids[tujuan], ids[index]];
    router.put(route('ujian.soal.urutan', props.ujian.id), { soal_ids: ids }, { preserveScroll: true });
}

// --- Dari bank soal ---------------------------------------------------------
const dipilih = ref([]);
const kataCari = ref(props.cari ?? '');
const cariBank = () => router.get(route('ujian.soal.index', props.ujian.id),
    kataCari.value ? { cari: kataCari.value } : {},
    { preserveState: true, preserveScroll: true, only: ['bank', 'cari'] });

const ambilDariBank = () => router.post(route('ujian.soal.dari-bank', props.ujian.id),
    { bank_soal_ids: dipilih.value },
    { preserveScroll: true, onSuccess: () => { dipilih.value = []; } });

const pilihSemua = (e) => { dipilih.value = e.target.checked ? props.bank.map((b) => b.id) : []; };
const opsiTerisi = (s) => ['a', 'b', 'c', 'd', 'e'].filter((h) => s[`opsi_${h}`]);
</script>

<template>
    <PageHeader :judul="`Soal: ${ujian.judul}`"
                :jejak="[{ label: 'Ujian', href: route('ujian.ujian.index') }, { label: ujian.judul, href: route('ujian.ujian.show', ujian.id) }, { label: 'Soal' }]">
        <template #aksi>
            <Link :href="route('ujian.ujian.show', ujian.id)" class="btn btn-default">Selesai menyusun</Link>
        </template>
    </PageHeader>

    <div v-if="ujian.terkunci" class="alert alert-warning">
        <i class="fas fa-lock mr-1" /> Ujian sudah mulai dikerjakan siswa, soal tidak bisa diubah lagi.
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ soal.length }} soal · total bobot {{ totalBobot }}</h3>
                    <div class="card-tools">
                        <button v-if="!ujian.terkunci" class="btn btn-sm btn-primary" @click="tulisBaru">
                            <i class="fas fa-plus mr-1" /> Tulis soal
                        </button>
                    </div>
                </div>

                <form v-if="tampilForm" class="card-body border-bottom bg-light" @submit.prevent="simpan">
                    <h5>{{ diedit ? 'Ubah soal' : 'Soal baru' }}</h5>
                    <SoalFields :form="form" />
                    <div class="text-right">
                        <button type="button" class="btn btn-link" @click="tampilForm = false">Batal</button>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">Simpan soal</button>
                    </div>
                </form>

                <ul class="list-group list-group-flush">
                    <li v-for="(s, i) in soal" :key="s.id" class="list-group-item">
                        <div class="d-flex">
                            <div class="mr-3 text-center">
                                <div class="font-weight-bold">{{ s.nomor }}</div>
                                <div v-if="!ujian.terkunci" class="btn-group-vertical btn-group-sm mt-1">
                                    <button class="btn btn-light" :disabled="i === 0" title="Naik" @click="geser(i, -1)"><i class="fas fa-chevron-up" /></button>
                                    <button class="btn btn-light" :disabled="i === soal.length - 1" title="Turun" @click="geser(i, 1)"><i class="fas fa-chevron-down" /></button>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div style="white-space: pre-line">{{ s.pertanyaan }}</div>
                                <ol v-if="s.tipe === 'pilihan_ganda'" type="A" class="mb-1 mt-1 pl-4 small">
                                    <li v-for="h in opsiTerisi(s)" :key="h"
                                        :class="{ 'text-success font-weight-bold': s.kunci_jawaban === h.toUpperCase() }">
                                        {{ s[`opsi_${h}`] }}
                                    </li>
                                </ol>
                                <div class="small text-muted">
                                    {{ s.tipe === 'essay' ? 'Essay' : 'Pilihan ganda' }} · bobot {{ s.bobot }}
                                    <span v-if="s.bank_soal_id"> · dari bank soal</span>
                                </div>
                            </div>
                            <div v-if="!ujian.terkunci" class="ml-2 text-nowrap">
                                <button class="btn btn-sm btn-outline-primary" @click="ubah(s)"><i class="fas fa-edit" /></button>
                                <button class="btn btn-sm btn-outline-danger ml-1" @click="hapus(s)"><i class="fas fa-trash" /></button>
                            </div>
                        </div>
                    </li>
                    <li v-if="!soal.length" class="list-group-item text-center text-muted py-4">
                        Belum ada soal. Tulis soal baru atau ambil dari bank soal.
                    </li>
                </ul>
            </div>
        </div>

        <div v-if="!ujian.terkunci" class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Ambil dari bank soal</h3></div>
                <div class="card-body pb-0">
                    <form class="input-group input-group-sm mb-2" @submit.prevent="cariBank">
                        <input v-model="kataCari" class="form-control" placeholder="Cari pertanyaan">
                        <div class="input-group-append"><button class="btn btn-default"><i class="fas fa-search" /></button></div>
                    </form>
                    <p class="small text-muted">Hanya soal dengan mapel yang sama dan belum terpasang.</p>
                </div>
                <div class="table-responsive" style="max-height: 480px">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="width: 2rem"><input type="checkbox" :checked="bank.length && dipilih.length === bank.length" @change="pilihSemua"></th>
                                <th>Pertanyaan</th><th>Tipe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="b in bank" :key="b.id">
                                <td><input v-model="dipilih" type="checkbox" :value="b.id"></td>
                                <td class="small">
                                    {{ potong(b.pertanyaan, 90) }}
                                    <div class="text-muted">{{ b.topik }} · {{ b.tingkat_kesulitan }} · bobot {{ b.bobot }}</div>
                                </td>
                                <td class="small">{{ b.tipe === 'essay' ? 'Essay' : 'PG' }}</td>
                            </tr>
                            <tr v-if="!bank.length">
                                <td colspan="3" class="text-center text-muted py-3">Tidak ada soal tersedia.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-right">
                    <button class="btn btn-sm btn-primary" :disabled="!dipilih.length" @click="ambilDariBank">
                        Tambahkan {{ dipilih.length || '' }} soal
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
