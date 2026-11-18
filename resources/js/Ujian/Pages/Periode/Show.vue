<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import Field from '@/Ujian/Components/Field.vue';
import { tanggalWaktu } from '@/Ujian/lib/format';
import { konfirmasi } from '@/Ujian/lib/konfirmasi';

const props = defineProps({
    periode: { type: Object, required: true },
    semester: { type: Array, required: true },
    tipeFase: { type: Array, required: true },
    bolehKelola: { type: Boolean, default: false },
});

const formPeriode = useForm({
    nama: props.periode.nama,
    semester_id: props.periode.semester_id,
    status: props.periode.status,
});
const simpanPeriode = () => formPeriode.put(route('ujian.periode.update', props.periode.id), { preserveScroll: true });

const faseKosong = () => ({ tipe: 'input_soal', urutan: props.periode.fases.length + 1, tgl_mulai: '', tgl_selesai: '' });
const formFase = useForm(faseKosong());
const faseDiedit = ref(null);

function editFase(fase) {
    faseDiedit.value = fase.id;
    formFase.clearErrors();
    Object.assign(formFase, { tipe: fase.tipe, urutan: fase.urutan, tgl_mulai: fase.tgl_mulai, tgl_selesai: fase.tgl_selesai });
}

function batalEdit() {
    faseDiedit.value = null;
    formFase.clearErrors();
    Object.assign(formFase, faseKosong());
}

function simpanFase() {
    const opsi = { preserveScroll: true, onSuccess: batalEdit };
    if (faseDiedit.value) {
        formFase.put(route('ujian.fase.update', faseDiedit.value), opsi);
    } else {
        formFase.post(route('ujian.fase.store', props.periode.id), opsi);
    }
}

async function hapusFase(fase) {
    if (await konfirmasi(`Hapus fase ${fase.tipe_label}?`, { bahaya: true, tombol: 'Hapus' })) {
        router.delete(route('ujian.fase.destroy', fase.id), { preserveScroll: true });
    }
}

const override = (fase, aksi) => router.post(route('ujian.fase.override', fase.id), { aksi }, { preserveScroll: true });

async function hapusPeriode() {
    if (await konfirmasi(`Hapus periode ${props.periode.nama}?`, { bahaya: true, tombol: 'Hapus' })) {
        router.delete(route('ujian.periode.destroy', props.periode.id));
    }
}
</script>

<template>
    <PageHeader :judul="periode.nama"
                :jejak="[{ label: 'Periode', href: route('ujian.periode.index') }, { label: periode.nama }]">
        <template #aksi>
            <button v-if="bolehKelola && !periode.jumlah_ujian" class="btn btn-outline-danger" @click="hapusPeriode">
                <i class="fas fa-trash mr-1" /> Hapus periode
            </button>
        </template>
    </PageHeader>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Fase</h3>
                    <div class="card-tools">Fase aktif: <strong>{{ periode.fase_aktif ?? 'tidak ada' }}</strong></div>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>#</th><th>Fase</th><th>Jadwal</th><th>Status</th><th v-if="bolehKelola" /></tr>
                        </thead>
                        <tbody>
                            <tr v-for="f in periode.fases" :key="f.id" :class="{ 'table-success': f.aktif }">
                                <td>{{ f.urutan }}</td>
                                <td>{{ f.tipe_label }}</td>
                                <td class="small">{{ tanggalWaktu(f.tgl_mulai) }}<br>s/d {{ tanggalWaktu(f.tgl_selesai) }}</td>
                                <td>
                                    {{ f.status_label }}
                                    <div v-if="f.override_oleh" class="small text-muted">oleh {{ f.override_oleh }}</div>
                                </td>
                                <td v-if="bolehKelola" class="text-right text-nowrap">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success" title="Paksa buka" @click="override(f, 'buka')"><i class="fas fa-lock-open" /></button>
                                        <button class="btn btn-outline-warning" title="Paksa tutup" @click="override(f, 'tutup')"><i class="fas fa-lock" /></button>
                                        <button v-if="f.override_status" class="btn btn-outline-secondary" title="Ikuti jadwal" @click="override(f, 'reset')"><i class="fas fa-undo" /></button>
                                        <button class="btn btn-outline-primary" title="Ubah" @click="editFase(f)"><i class="fas fa-edit" /></button>
                                        <button class="btn btn-outline-danger" title="Hapus" @click="hapusFase(f)"><i class="fas fa-trash" /></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!periode.fases.length">
                                <td colspan="5" class="text-center text-muted py-4">Belum ada fase.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <form v-if="bolehKelola" class="card" @submit.prevent="simpanFase">
                <div class="card-header"><h3 class="card-title">{{ faseDiedit ? 'Ubah fase' : 'Tambah fase' }}</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <Field label="Fase" :error="formFase.errors.tipe" wajib>
                                <select v-model="formFase.tipe" class="form-control" :class="{ 'is-invalid': formFase.errors.tipe }">
                                    <option v-for="t in tipeFase" :key="t.value" :value="t.value">{{ t.label }}</option>
                                </select>
                            </Field>
                        </div>
                        <div class="col-md-2">
                            <Field label="Urutan" :error="formFase.errors.urutan" wajib>
                                <input v-model="formFase.urutan" type="number" min="1" class="form-control">
                            </Field>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <Field label="Mulai" :error="formFase.errors.tgl_mulai" wajib>
                                <input v-model="formFase.tgl_mulai" type="datetime-local" class="form-control" :class="{ 'is-invalid': formFase.errors.tgl_mulai }">
                            </Field>
                        </div>
                        <div class="col-md-6">
                            <Field label="Selesai" :error="formFase.errors.tgl_selesai" wajib>
                                <input v-model="formFase.tgl_selesai" type="datetime-local" class="form-control" :class="{ 'is-invalid': formFase.errors.tgl_selesai }">
                            </Field>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button v-if="faseDiedit" type="button" class="btn btn-link" @click="batalEdit">Batal</button>
                    <button type="submit" class="btn btn-primary" :disabled="formFase.processing">Simpan fase</button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <form class="card" @submit.prevent="simpanPeriode">
                <div class="card-header"><h3 class="card-title">Detail periode</h3></div>
                <div class="card-body">
                    <Field label="Nama" :error="formPeriode.errors.nama" wajib>
                        <input v-model="formPeriode.nama" class="form-control" :disabled="!bolehKelola">
                    </Field>
                    <Field label="Semester" :error="formPeriode.errors.semester_id" wajib>
                        <select v-model="formPeriode.semester_id" class="form-control" :disabled="!bolehKelola">
                            <option v-for="s in semester" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                    </Field>
                    <Field label="Status" :error="formPeriode.errors.status" wajib>
                        <select v-model="formPeriode.status" class="form-control" :disabled="!bolehKelola">
                            <option value="draft">Draft</option>
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </Field>
                    <p class="text-muted small mb-0">{{ periode.jumlah_ujian }} ujian memakai periode ini.</p>
                </div>
                <div v-if="bolehKelola" class="card-footer text-right">
                    <button type="submit" class="btn btn-primary" :disabled="formPeriode.processing">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</template>
