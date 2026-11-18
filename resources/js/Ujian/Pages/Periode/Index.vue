<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import Pagination from '@/Ujian/Components/Pagination.vue';
import Field from '@/Ujian/Components/Field.vue';

const props = defineProps({
    periode: { type: Object, required: true },
    semester: { type: Array, required: true },
    bolehKelola: { type: Boolean, default: false },
});

const form = useForm({ nama: '', semester_id: props.semester[0]?.value ?? '', status: 'draft' });
const simpan = () => form.post(route('ujian.periode.store'));
const badge = { draft: 'secondary', aktif: 'success', selesai: 'dark' };
</script>

<template>
    <PageHeader judul="Periode Ujian" :jejak="[{ label: 'Ujian Online', href: route('ujian.dashboard') }, { label: 'Periode' }]" />

    <div class="row">
        <div :class="bolehKelola ? 'col-lg-8' : 'col-12'">
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Nama</th><th>Semester</th><th>Fase aktif</th><th class="text-center">Ujian</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in periode.data" :key="p.id">
                                <td><Link :href="route('ujian.periode.show', p.id)">{{ p.nama }}</Link></td>
                                <td>{{ p.semester ?? '-' }}</td>
                                <td>{{ p.fase_aktif ?? '—' }} <small class="text-muted">({{ p.jumlah_fase }} fase)</small></td>
                                <td class="text-center">{{ p.jumlah_ujian }}</td>
                                <td><span class="badge" :class="`badge-${badge[p.status]}`">{{ p.status }}</span></td>
                            </tr>
                            <tr v-if="!periode.data.length">
                                <td colspan="5" class="text-center text-muted py-4">Belum ada periode ujian.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer"><Pagination :data="periode" /></div>
            </div>
        </div>

        <div v-if="bolehKelola" class="col-lg-4">
            <form class="card" @submit.prevent="simpan">
                <div class="card-header"><h3 class="card-title">Periode baru</h3></div>
                <div class="card-body">
                    <Field label="Nama" :error="form.errors.nama" bantuan="Contoh: UAS Ganjil 2026/2027" wajib>
                        <input v-model="form.nama" class="form-control" :class="{ 'is-invalid': form.errors.nama }">
                    </Field>
                    <Field label="Semester" :error="form.errors.semester_id" wajib>
                        <select v-model="form.semester_id" class="form-control">
                            <option v-for="s in semester" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                    </Field>
                    <Field label="Status" :error="form.errors.status" wajib>
                        <select v-model="form.status" class="form-control">
                            <option value="draft">Draft</option>
                            <option value="aktif">Aktif</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </Field>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</template>
