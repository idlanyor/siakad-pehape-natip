<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import Field from '@/Ujian/Components/Field.vue';

const props = defineProps({
    ujian: { type: Object, default: null },
    periode: { type: Array, required: true },
    mapel: { type: Array, required: true },
    kelas: { type: Array, required: true },
    guru: { type: Array, default: () => [] },
    tipe: { type: Array, required: true },
});

const u = props.ujian ?? {};
const form = useForm({
    periode_id: u.periode_id ?? props.periode[0]?.value ?? '',
    judul: u.judul ?? '',
    deskripsi: u.deskripsi ?? '',
    tipe: u.tipe ?? 'uas',
    mapel_id: u.mapel_id ?? props.mapel[0]?.value ?? '',
    kelas_id: u.kelas_id ?? '',
    guru_id: u.guru_id ?? '',
    waktu_mulai: u.waktu_mulai ?? '',
    waktu_selesai: u.waktu_selesai ?? '',
    durasi_menit: u.durasi_menit ?? 90,
    passing_grade: u.passing_grade ?? 75,
    acak_soal: u.acak_soal ?? false,
    acak_opsi: u.acak_opsi ?? false,
    tampilkan_nilai: u.tampilkan_nilai ?? true,
});

const simpan = () => (props.ujian
    ? form.put(route('ujian.ujian.update', props.ujian.id))
    : form.post(route('ujian.ujian.store')));

const pengaturan = [
    ['acak_soal', 'Acak urutan soal', 'Urutan dikunci per siswa saat mulai.'],
    ['acak_opsi', 'Acak urutan opsi', 'Huruf yang dilihat siswa ikut berubah; kunci tetap benar.'],
    ['tampilkan_nilai', 'Tampilkan nilai ke siswa', 'Siswa melihat nilai setelah selesai.'],
];
</script>

<template>
    <PageHeader :judul="ujian ? 'Ubah Ujian' : 'Buat Ujian'"
                :jejak="[{ label: 'Ujian', href: route('ujian.ujian.index') }, { label: ujian ? ujian.judul : 'Baru' }]" />

    <div v-if="!periode.length" class="alert alert-warning">Belum ada periode ujian yang terbuka. Minta admin membuat periode.</div>
    <div v-if="!mapel.length" class="alert alert-warning">Anda belum terdaftar sebagai pengampu mata pelajaran apa pun.</div>

    <form @submit.prevent="simpan">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Informasi ujian</h3></div>
                    <div class="card-body">
                        <Field label="Judul" :error="form.errors.judul" wajib>
                            <input v-model="form.judul" class="form-control" :class="{ 'is-invalid': form.errors.judul }">
                        </Field>
                        <Field label="Deskripsi / petunjuk" :error="form.errors.deskripsi">
                            <textarea v-model="form.deskripsi" rows="3" class="form-control" />
                        </Field>
                        <div class="row">
                            <div class="col-md-6">
                                <Field label="Periode" :error="form.errors.periode_id" wajib>
                                    <select v-model="form.periode_id" class="form-control" :class="{ 'is-invalid': form.errors.periode_id }">
                                        <option v-for="p in periode" :key="p.value" :value="p.value">{{ p.label }}</option>
                                    </select>
                                </Field>
                            </div>
                            <div class="col-md-6">
                                <Field label="Jenis" :error="form.errors.tipe" wajib>
                                    <select v-model="form.tipe" class="form-control">
                                        <option v-for="t in tipe" :key="t" :value="t">{{ t.toUpperCase() }}</option>
                                    </select>
                                </Field>
                            </div>
                            <div class="col-md-6">
                                <Field label="Mata pelajaran" :error="form.errors.mapel_id" wajib>
                                    <select v-model="form.mapel_id" class="form-control" :class="{ 'is-invalid': form.errors.mapel_id }">
                                        <option v-for="m in mapel" :key="m.value" :value="m.value">{{ m.label }}</option>
                                    </select>
                                </Field>
                            </div>
                            <div class="col-md-6">
                                <Field label="Kelas" :error="form.errors.kelas_id" wajib>
                                    <select v-model="form.kelas_id" class="form-control" :class="{ 'is-invalid': form.errors.kelas_id }">
                                        <option value="" disabled>Pilih kelas</option>
                                        <option v-for="k in kelas" :key="k.value" :value="k.value">{{ k.label }}</option>
                                    </select>
                                </Field>
                            </div>
                            <div v-if="guru.length" class="col-md-6">
                                <Field label="Guru pemilik" :error="form.errors.guru_id" bantuan="Guru yang mengelola dan menilai ujian ini.">
                                    <select v-model="form.guru_id" class="form-control">
                                        <option value="">— Tanpa guru (dikelola admin) —</option>
                                        <option v-for="g in guru" :key="g.value" :value="g.value">{{ g.label }}</option>
                                    </select>
                                </Field>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Waktu & penilaian</h3></div>
                    <div class="card-body">
                        <Field label="Dibuka" :error="form.errors.waktu_mulai" wajib>
                            <input v-model="form.waktu_mulai" type="datetime-local" class="form-control" :class="{ 'is-invalid': form.errors.waktu_mulai }">
                        </Field>
                        <Field label="Ditutup" :error="form.errors.waktu_selesai" wajib>
                            <input v-model="form.waktu_selesai" type="datetime-local" class="form-control" :class="{ 'is-invalid': form.errors.waktu_selesai }">
                        </Field>
                        <Field label="Durasi (menit)" :error="form.errors.durasi_menit"
                               bantuan="Waktu tiap siswa sejak mulai, tidak melewati jam tutup." wajib>
                            <input v-model="form.durasi_menit" type="number" min="1" class="form-control" :class="{ 'is-invalid': form.errors.durasi_menit }">
                        </Field>
                        <Field label="KKM (0–100)" :error="form.errors.passing_grade" wajib>
                            <input v-model="form.passing_grade" type="number" min="0" max="100" step="0.01" class="form-control">
                        </Field>
                        <div v-for="[kunci, label, bantu] in pengaturan" :key="kunci" class="custom-control custom-switch mb-2">
                            <input :id="kunci" v-model="form[kunci]" type="checkbox" class="custom-control-input">
                            <label :for="kunci" class="custom-control-label">{{ label }}</label>
                            <small class="form-text text-muted mt-0">{{ bantu }}</small>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <Link :href="ujian ? route('ujian.ujian.show', ujian.id) : route('ujian.ujian.index')" class="btn btn-link">Batal</Link>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>
