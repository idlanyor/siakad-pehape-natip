<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import Field from '@/Ujian/Components/Field.vue';
import SoalFields from '@/Ujian/Components/SoalFields.vue';

const props = defineProps({
    soal: { type: Object, default: null },
    mapel: { type: Array, required: true },
});

const s = props.soal ?? {};
const form = useForm({
    mapel_id: s.mapel_id ?? props.mapel[0]?.value ?? '',
    topik: s.topik ?? '',
    tingkat_kesulitan: s.tingkat_kesulitan ?? 'sedang',
    tag: s.tag ?? [],
    tipe: s.tipe ?? 'pilihan_ganda',
    pertanyaan: s.pertanyaan ?? '',
    opsi_a: s.opsi_a ?? '', opsi_b: s.opsi_b ?? '', opsi_c: s.opsi_c ?? '', opsi_d: s.opsi_d ?? '', opsi_e: s.opsi_e ?? '',
    kunci_jawaban: s.kunci_jawaban ?? '',
    bobot: s.bobot ?? 1,
});

const tagBaru = ref('');
function tambahTag() {
    const t = tagBaru.value.trim();
    if (t && !form.tag.includes(t)) form.tag.push(t);
    tagBaru.value = '';
}

const simpan = () => (props.soal
    ? form.put(route('ujian.bank-soal.update', props.soal.id))
    : form.post(route('ujian.bank-soal.store')));
</script>

<template>
    <PageHeader :judul="soal ? 'Ubah Soal' : 'Tambah Soal'"
                :jejak="[{ label: 'Bank Soal', href: route('ujian.bank-soal.index') }, { label: soal ? 'Ubah' : 'Tambah' }]" />

    <div v-if="!mapel.length" class="alert alert-warning">
        Anda belum terdaftar sebagai pengampu mata pelajaran apa pun. Hubungi admin.
    </div>

    <form class="card" @submit.prevent="simpan">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <Field label="Mata pelajaran" :error="form.errors.mapel_id" wajib>
                        <select v-model="form.mapel_id" class="form-control" :class="{ 'is-invalid': form.errors.mapel_id }">
                            <option v-for="m in mapel" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </select>
                    </Field>
                </div>
                <div class="col-md-4">
                    <Field label="Topik" :error="form.errors.topik">
                        <input v-model="form.topik" class="form-control">
                    </Field>
                </div>
                <div class="col-md-4">
                    <Field label="Tingkat kesulitan" :error="form.errors.tingkat_kesulitan" wajib>
                        <select v-model="form.tingkat_kesulitan" class="form-control">
                            <option value="mudah">Mudah</option>
                            <option value="sedang">Sedang</option>
                            <option value="sulit">Sulit</option>
                        </select>
                    </Field>
                </div>
            </div>

            <SoalFields :form="form" />

            <Field label="Tag" :error="form.errors.tag">
                <div class="mb-1">
                    <span v-for="(t, i) in form.tag" :key="t" class="badge badge-info mr-1">
                        {{ t }} <a href="#" class="text-white ml-1" @click.prevent="form.tag.splice(i, 1)">&times;</a>
                    </span>
                </div>
                <input v-model="tagBaru" class="form-control form-control-sm" placeholder="Ketik tag lalu Enter"
                       @keydown.enter.prevent="tambahTag">
            </Field>
        </div>
        <div class="card-footer text-right">
            <Link :href="route('ujian.bank-soal.index')" class="btn btn-link">Batal</Link>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">Simpan</button>
        </div>
    </form>
</template>
