<script setup>
import { Link, router } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import { labelStatusSesi, tanggalWaktu } from '@/Ujian/lib/format';
import { konfirmasi } from '@/Ujian/lib/konfirmasi';

defineProps({ ujian: { type: Array, required: true } });

async function mulai(u) {
    const pesan = `Durasi ${u.durasi_menit} menit, ${u.jumlah_soal} soal. Waktu berjalan sejak Anda mulai. `
        + 'Jangan berpindah tab atau aplikasi selama ujian, karena akan tercatat sebagai pelanggaran.';
    if (await konfirmasi(pesan, { judul: `Mulai ${u.judul}?`, tombol: 'Mulai ujian' })) {
        router.post(route('ujian.siswa.mulai', u.id));
    }
}

const berakhir = (sesi) => sesi && ['selesai', 'timeout', 'diskualifikasi'].includes(sesi.status);
</script>

<template>
    <PageHeader judul="Ujian Saya" />

    <div class="row">
        <div v-for="u in ujian" :key="u.id" class="col-md-6 col-xl-4">
            <div class="card" :class="u.jendela === 'dibuka' && !berakhir(u.sesi) ? 'card-outline card-primary' : ''">
                <div class="card-header">
                    <h3 class="card-title">{{ u.judul }}</h3>
                </div>
                <div class="card-body">
                    <div class="text-muted small mb-2">{{ u.mapel }} · {{ u.tipe.toUpperCase() }}</div>
                    <p class="mb-1"><i class="far fa-clock mr-1" /> {{ tanggalWaktu(u.waktu_mulai) }} – {{ tanggalWaktu(u.waktu_selesai) }}</p>
                    <p class="mb-1"><i class="fas fa-hourglass-half mr-1" /> {{ u.durasi_menit }} menit · {{ u.jumlah_soal }} soal</p>
                    <p v-if="u.deskripsi" class="small mt-2 mb-0" style="white-space: pre-line">{{ u.deskripsi }}</p>
                </div>
                <div class="card-footer">
                    <template v-if="berakhir(u.sesi)">
                        <span class="badge mr-2" :class="`badge-${labelStatusSesi[u.sesi.status][1]}`">{{ labelStatusSesi[u.sesi.status][0] }}</span>
                        <Link :href="route('ujian.siswa.hasil', u.sesi.id)" class="btn btn-sm btn-outline-primary">Lihat hasil</Link>
                    </template>
                    <Link v-else-if="u.sesi?.status === 'berlangsung'" :href="route('ujian.siswa.kerjakan', u.sesi.id)" class="btn btn-warning btn-block">
                        <i class="fas fa-play mr-1" /> Lanjutkan ujian
                    </Link>
                    <button v-else-if="u.jendela === 'dibuka'" class="btn btn-primary btn-block" @click="mulai(u)">
                        <i class="fas fa-play mr-1" /> Mulai ujian
                    </button>
                    <span v-else-if="u.jendela === 'belum_dibuka'" class="text-muted">Belum dibuka</span>
                    <span v-else class="text-muted">Sudah ditutup (tidak mengikuti)</span>
                </div>
            </div>
        </div>
        <div v-if="!ujian.length" class="col-12">
            <div class="callout callout-info">Belum ada ujian untuk kelas Anda.</div>
        </div>
    </div>
</template>
