<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import { angka, labelStatusSesi, tanggalWaktu } from '@/Ujian/lib/format';

const props = defineProps({
    sesi: { type: Object, required: true },
    jawaban: { type: Array, required: true },
    event: { type: Array, required: true },
});

// Nilai essay yang sedang diketik, per id jawaban.
const nilaiEssay = reactive(Object.fromEntries(
    props.jawaban.filter((j) => j.tipe === 'essay').map((j) => [j.id, j.nilai ?? '']),
));

const simpanNilai = (j) => router.put(route('ujian.hasil.nilai-essay', j.id), { nilai: nilaiEssay[j.id] }, { preserveScroll: true });

const warnaSeverity = { critical: 'danger', high: 'warning', medium: 'info', low: 'secondary', info: 'primary' };
</script>

<template>
    <PageHeader :judul="sesi.siswa"
                :jejak="[{ label: sesi.ujian, href: route('ujian.hasil.index', sesi.ujian_id) }, { label: sesi.siswa }]" />

    <div class="row">
        <div class="col-lg-8">
            <div v-for="j in jawaban" :key="j.id" class="card"
                 :class="j.tipe === 'essay' ? (j.nilai === null ? 'card-outline card-warning' : 'card-outline card-info') : (j.is_benar ? 'card-outline card-success' : 'card-outline card-danger')">
                <div class="card-header">
                    <h3 class="card-title">Soal {{ j.nomor }} <small class="text-muted">· bobot {{ j.bobot }}</small></h3>
                    <div class="card-tools">
                        <span v-if="j.tipe === 'pilihan_ganda'" class="badge" :class="j.is_benar ? 'badge-success' : 'badge-danger'">
                            {{ j.is_benar ? 'Benar' : (j.jawaban_pilihan ? 'Salah' : 'Kosong') }}
                        </span>
                        <span v-else-if="j.nilai === null" class="badge badge-warning">Belum dinilai</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="teks-soal mb-3" v-html="j.pertanyaan_html" />

                    <ul v-if="j.tipe === 'pilihan_ganda'" class="list-unstyled mb-0">
                        <li v-for="(teks, huruf) in j.opsi" :key="huruf" class="py-1 px-2 rounded"
                            :class="{ 'bg-success': huruf === j.kunci_jawaban, 'bg-danger': huruf === j.jawaban_pilihan && huruf !== j.kunci_jawaban }">
                            <strong>{{ huruf }}.</strong> <span class="teks-soal" v-html="teks" />
                            <i v-if="huruf === j.jawaban_pilihan" class="fas fa-user-check ml-1" title="Jawaban siswa" />
                        </li>
                    </ul>

                    <template v-else>
                        <div class="border rounded p-2 bg-light mb-2" style="white-space: pre-line">
                            {{ j.jawaban_text || '(tidak dijawab)' }}
                        </div>
                        <form class="form-inline" @submit.prevent="simpanNilai(j)">
                            <label class="mr-2">Nilai</label>
                            <input v-model="nilaiEssay[j.id]" type="number" min="0" :max="j.bobot" step="0.01"
                                   class="form-control form-control-sm mr-2" style="width: 6rem">
                            <span class="mr-2 text-muted">/ {{ j.bobot }}</span>
                            <button class="btn btn-sm btn-primary">Simpan</button>
                        </form>
                    </template>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Status</dt>
                        <dd><span class="badge" :class="`badge-${labelStatusSesi[sesi.status][1]}`">{{ labelStatusSesi[sesi.status][0] }}</span></dd>
                        <dt>Waktu</dt>
                        <dd class="small">{{ tanggalWaktu(sesi.waktu_mulai) }} – {{ tanggalWaktu(sesi.waktu_selesai) }}</dd>
                        <dt>Nilai PG / Essay</dt>
                        <dd>{{ angka(sesi.nilai_otomatis) }} / {{ angka(sesi.nilai_manual) }}</dd>
                        <dt>Nilai akhir</dt>
                        <dd class="h3" :class="Number(sesi.nilai_akhir) >= sesi.passing_grade ? 'text-success' : 'text-danger'">
                            {{ angka(sesi.nilai_akhir) }} <small>{{ sesi.predikat }}</small>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Log ({{ sesi.jumlah_pelanggaran }} pelanggaran)</h3></div>
                <ul class="list-group list-group-flush small">
                    <li v-for="e in event" :key="e.id" class="list-group-item">
                        <span class="badge mr-1" :class="`badge-${warnaSeverity[e.severity]}`">{{ e.label }}</span>
                        <span class="text-muted">{{ tanggalWaktu(e.waktu) }}</span>
                        <div v-if="e.keterangan || e.oleh">{{ e.keterangan }} <em v-if="e.oleh">— {{ e.oleh }}</em></div>
                    </li>
                    <li v-if="!event.length" class="list-group-item text-muted">Tidak ada catatan.</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
.teks-soal :deep(p:last-child) { margin-bottom: 0; }
.teks-soal :deep(img) { max-height: 16rem; }
</style>
