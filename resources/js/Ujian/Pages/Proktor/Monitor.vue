<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import { labelStatusSesi, tanggalWaktu } from '@/Ujian/lib/format';
import { konfirmasi, mintaAlasan } from '@/Ujian/lib/konfirmasi';

const props = defineProps({
    ujian: { type: Object, required: true },
    peserta: { type: Array, required: true },
    statistik: { type: Object, required: true },
    event: { type: Array, required: true },
    batasPelanggaran: { type: Number, required: true },
    diperbarui: { type: String, required: true },
});

// --- Polling ------------------------------------------------------------------
// Data dimuat ulang tiap 10 detik; dijeda saat tab proktor tidak terlihat.
const INTERVAL = 10000;
const memuat = ref(false);
function muatUlang() {
    if (document.hidden || memuat.value) return;
    router.reload({
        only: ['peserta', 'statistik', 'event', 'diperbarui'],
        onStart: () => { memuat.value = true; },
        onFinish: () => { memuat.value = false; },
    });
}
let polling;
const onVisibility = () => { if (!document.hidden) muatUlang(); };

// Hitung mundur sisa waktu di antara dua polling.
const detikBerlalu = ref(0);
let detak;

onMounted(() => {
    polling = setInterval(muatUlang, INTERVAL);
    detak = setInterval(() => {
        detikBerlalu.value = Math.floor((Date.now() - new Date(props.diperbarui).getTime()) / 1000);
    }, 1000);
    document.addEventListener('visibilitychange', onVisibility);
});
onBeforeUnmount(() => {
    clearInterval(polling);
    clearInterval(detak);
    document.removeEventListener('visibilitychange', onVisibility);
});

const sisa = (s) => {
    const d = Math.max(0, s.sisa_detik - detikBerlalu.value);
    return `${String(Math.floor(d / 60)).padStart(2, '0')}:${String(d % 60).padStart(2, '0')}`;
};

// --- Filter -----------------------------------------------------------------------
const filter = ref('semua');
const cari = ref('');
const statusPeserta = (p) => p.sesi?.status ?? 'belum';
const daftar = computed(() => props.peserta.filter((p) => {
    if (cari.value && !p.siswa.nama.toLowerCase().includes(cari.value.toLowerCase())) return false;
    if (filter.value === 'semua') return true;
    if (filter.value === 'pelanggaran') return (p.sesi?.jumlah_pelanggaran ?? 0) > 0;
    if (filter.value === 'offline') return p.sesi?.status === 'berlangsung' && p.sesi.koneksi !== 'online';
    return statusPeserta(p) === filter.value;
}));

const pilihanFilter = [
    ['semua', 'Semua'], ['berlangsung', 'Mengerjakan'], ['belum', 'Belum mulai'],
    ['selesai', 'Selesai'], ['offline', 'Tidak aktif'], ['pelanggaran', 'Ada pelanggaran'],
];

// --- Aksi -----------------------------------------------------------------------------
const opsi = { preserveScroll: true, only: ['peserta', 'statistik', 'event', 'diperbarui', 'flash'] };

async function paksaSelesai(p) {
    const alasan = await mintaAlasan(`Selesaikan sesi ${p.siswa.nama}?`, {
        teks: 'Jawaban yang sudah tersimpan akan dinilai.', tombol: 'Selesaikan',
    });
    if (alasan !== null) router.post(route('ujian.proktor.paksa-selesai', p.sesi.id), { alasan }, opsi);
}

async function diskualifikasi(p) {
    const alasan = await mintaAlasan(`Diskualifikasi ${p.siswa.nama}?`, {
        teks: 'Nilai menjadi 0. Tindakan ini tercatat atas nama Anda.', wajib: true, tombol: 'Diskualifikasi', bahaya: true,
    });
    if (alasan !== null) router.post(route('ujian.proktor.diskualifikasi', p.sesi.id), { alasan }, opsi);
}

async function reset(p) {
    if (!(await konfirmasi(`Reset sesi ${p.siswa.nama}? Semua jawabannya dihapus dan siswa mulai dari awal.`, { bahaya: true, tombol: 'Reset' }))) return;
    router.post(route('ujian.proktor.reset', p.sesi.id), {}, opsi);
}

const warnaKoneksi = { online: 'success', idle: 'warning', offline: 'secondary' };
const warnaSeverity = { critical: 'danger', high: 'warning', medium: 'info', low: 'secondary', info: 'primary' };
const kartu = [
    ['peserta', 'Peserta', 'bg-info'],
    ['berlangsung', 'Mengerjakan', 'bg-primary'],
    ['belum_mulai', 'Belum mulai', 'bg-secondary'],
    ['selesai', 'Selesai', 'bg-success'],
    ['diskualifikasi', 'Diskualifikasi', 'bg-danger'],
    ['total_pelanggaran', 'Pelanggaran', 'bg-warning'],
];
</script>

<template>
    <PageHeader :judul="`Monitor: ${ujian.judul}`"
                :jejak="[{ label: 'Pengawasan', href: route('ujian.proktor.index') }, { label: ujian.judul }]">
        <template #aksi>
            <span class="small text-muted mr-2">
                <i class="fas fa-sync-alt mr-1" :class="{ 'fa-spin': memuat }" />
                diperbarui {{ detikBerlalu }} dtk lalu
            </span>
            <button class="btn btn-sm btn-default" @click="muatUlang">Muat ulang</button>
        </template>
    </PageHeader>

    <p class="text-muted">
        {{ ujian.mapel }} · {{ ujian.kelas }} · {{ tanggalWaktu(ujian.waktu_mulai) }} – {{ tanggalWaktu(ujian.waktu_selesai) }}
        <span v-if="ujian.dibuka" class="badge badge-success ml-1">Berlangsung</span>
        <span v-else class="badge badge-secondary ml-1">Di luar jadwal</span>
    </p>

    <div class="row">
        <div v-for="[kunci, label, warna] in kartu" :key="kunci" class="col-6 col-md-4 col-xl-2">
            <div class="small-box mb-3" :class="warna">
                <div class="inner py-2"><h3 class="mb-0">{{ statistik[kunci] }}</h3><p class="mb-0">{{ label }}</p></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-9">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center" style="gap: .5rem">
                        <div class="btn-group btn-group-sm flex-wrap">
                            <button v-for="[kunci, label] in pilihanFilter" :key="kunci" class="btn"
                                    :class="filter === kunci ? 'btn-primary' : 'btn-default'" @click="filter = kunci">{{ label }}</button>
                        </div>
                        <input v-model="cari" class="form-control form-control-sm ml-auto" style="max-width: 14rem" placeholder="Cari nama siswa">
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Siswa</th><th>Status</th><th>Progres</th><th class="text-center">Sisa</th>
                                <th class="text-center">Pelanggaran</th><th class="text-center">Nilai</th><th />
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in daftar" :key="p.siswa.id">
                                <td>
                                    <i v-if="p.sesi" class="fas fa-circle mr-1 small" :class="`text-${warnaKoneksi[p.sesi.koneksi]}`"
                                       :title="`Koneksi: ${p.sesi.koneksi}`" />
                                    {{ p.siswa.nama }}
                                    <div class="small text-muted">{{ p.siswa.nisn }}</div>
                                </td>
                                <td>
                                    <span class="badge" :class="`badge-${labelStatusSesi[statusPeserta(p)][1]}`">{{ labelStatusSesi[statusPeserta(p)][0] }}</span>
                                </td>
                                <td style="min-width: 8rem">
                                    <template v-if="p.sesi && p.sesi.total_soal">
                                        <div class="progress progress-xs mb-1">
                                            <div class="progress-bar bg-primary" :style="{ width: `${(p.sesi.terjawab / p.sesi.total_soal) * 100}%` }" />
                                        </div>
                                        <small>{{ p.sesi.terjawab }}/{{ p.sesi.total_soal }}</small>
                                    </template>
                                    <span v-else class="text-muted">—</span>
                                </td>
                                <td class="text-center text-monospace">{{ p.sesi?.status === 'berlangsung' ? sisa(p.sesi) : '—' }}</td>
                                <td class="text-center">
                                    <span v-if="p.sesi?.jumlah_pelanggaran" class="badge"
                                          :class="batasPelanggaran && p.sesi.jumlah_pelanggaran >= batasPelanggaran - 1 ? 'badge-danger' : 'badge-warning'">
                                        {{ p.sesi.jumlah_pelanggaran }}<template v-if="batasPelanggaran">/{{ batasPelanggaran }}</template>
                                    </span>
                                    <span v-else class="text-muted">0</span>
                                </td>
                                <td class="text-center">{{ p.sesi?.nilai_akhir ?? '—' }}</td>
                                <td class="text-right text-nowrap">
                                    <template v-if="p.sesi">
                                        <button v-if="p.sesi.status === 'berlangsung'" class="btn btn-xs btn-outline-primary" title="Paksa selesai" @click="paksaSelesai(p)">
                                            <i class="fas fa-stop" />
                                        </button>
                                        <button v-if="p.sesi.status !== 'diskualifikasi' && p.sesi.status !== 'belum'" class="btn btn-xs btn-outline-danger ml-1" title="Diskualifikasi" @click="diskualifikasi(p)">
                                            <i class="fas fa-ban" />
                                        </button>
                                        <button v-if="p.sesi.status !== 'belum'" class="btn btn-xs btn-outline-secondary ml-1" title="Reset sesi" @click="reset(p)">
                                            <i class="fas fa-undo" />
                                        </button>
                                    </template>
                                </td>
                            </tr>
                            <tr v-if="!daftar.length">
                                <td colspan="7" class="text-center text-muted py-4">Tidak ada peserta pada filter ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Kejadian terbaru</h3></div>
                <ul class="list-group list-group-flush small" style="max-height: 32rem; overflow-y: auto">
                    <li v-for="e in event" :key="e.id" class="list-group-item py-2">
                        <span class="badge mr-1" :class="`badge-${warnaSeverity[e.severity]}`">{{ e.label }}</span>
                        <strong>{{ e.siswa }}</strong>
                        <div class="text-muted">{{ tanggalWaktu(e.waktu) }}<span v-if="e.oleh"> · {{ e.oleh }}</span></div>
                        <div v-if="e.keterangan">{{ e.keterangan }}</div>
                    </li>
                    <li v-if="!event.length" class="list-group-item text-muted">Belum ada kejadian.</li>
                </ul>
            </div>
            <Link :href="route('ujian.ujian.show', ujian.id)" class="btn btn-block btn-default">Detail ujian</Link>
        </div>
    </div>
</template>
