<script setup>
import { Link } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import { angka, labelStatusSesi, tanggalWaktu } from '@/Ujian/lib/format';

defineProps({
    sesi: { type: Object, required: true },
    ujian: { type: Object, required: true },
    tampilkan_nilai: { type: Boolean, required: true },
    nilai: { type: Object, default: null },
    progres: { type: Object, required: true },
});
</script>

<template>
    <PageHeader :judul="`Hasil: ${ujian.judul}`" :jejak="[{ label: 'Ujian Saya', href: route('ujian.siswa.index') }, { label: 'Hasil' }]" />

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card text-center">
                <div class="card-body">
                    <span class="badge" :class="`badge-${labelStatusSesi[sesi.status][1]}`" style="font-size: 1rem">
                        {{ labelStatusSesi[sesi.status][0] }}
                    </span>

                    <div v-if="sesi.status === 'diskualifikasi'" class="alert alert-danger mt-3 mb-0">
                        Sesi Anda didiskualifikasi. Hubungi pengawas atau guru bila merasa ini keliru.
                    </div>

                    <template v-else-if="tampilkan_nilai && nilai">
                        <div class="display-3 my-3" :class="nilai.lulus ? 'text-success' : 'text-danger'">{{ angka(nilai.akhir) }}</div>
                        <p class="mb-1">Predikat <strong>{{ nilai.predikat ?? '-' }}</strong> · KKM {{ ujian.passing_grade }}</p>
                        <p class="mb-0">
                            <span v-if="nilai.lulus" class="text-success"><i class="fas fa-check-circle" /> Tuntas</span>
                            <span v-else class="text-danger"><i class="fas fa-times-circle" /> Belum tuntas</span>
                        </p>
                        <div v-if="nilai.menunggu_koreksi" class="alert alert-info mt-3 mb-0">
                            Sebagian jawaban essay belum dinilai guru. Nilai bisa berubah setelah koreksi.
                        </div>
                    </template>

                    <p v-else class="mt-3 mb-0 text-muted">Jawaban Anda sudah diterima. Nilai akan diumumkan oleh guru.</p>
                </div>
                <div class="card-footer small text-muted">
                    {{ progres.terjawab }} dari {{ progres.total }} soal dijawab ·
                    {{ tanggalWaktu(sesi.waktu_mulai) }} – {{ tanggalWaktu(sesi.waktu_selesai) }}
                </div>
            </div>
            <div class="text-center">
                <Link :href="route('ujian.siswa.index')" class="btn btn-default">Kembali ke daftar ujian</Link>
            </div>
        </div>
    </div>
</template>
