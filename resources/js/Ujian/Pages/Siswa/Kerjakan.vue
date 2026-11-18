<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { useAutosave } from '@/Ujian/lib/useAutosave';
import { usePengawasanBrowser } from '@/Ujian/lib/usePengawasanBrowser';
import { konfirmasi } from '@/Ujian/lib/konfirmasi';

const props = defineProps({
    sesi: { type: Object, required: true },
    ujian: { type: Object, required: true },
    soal: { type: Array, required: true },
    progres: { type: Object, required: true },
    pengumuman: { type: Array, default: () => [] },
    tokenPerangkat: { type: String, required: true },
});

// Setiap request membawa token perangkat; perangkat/tab lain yang membuka sesi ini mengambil alih.
const headers = { 'X-Sesi-Token': props.tokenPerangkat };
const diambilAlih = ref(false);
function perangkatLain() {
    diambilAlih.value = true;
}

const daftar = reactive(props.soal.map((s) => ({ ...s })));
const indeks = ref(0);
const aktif = computed(() => daftar[indeks.value]);
const status = ref(props.sesi.status);
const pelanggaran = ref(props.sesi.jumlah_pelanggaran);
const sedangSelesai = ref(false);

// --- Timer (disinkronkan ulang dari setiap respons server) -----------------
const batas = ref(Date.now() + props.sesi.sisa_detik * 1000);
const sisa = ref(props.sesi.sisa_detik);
const timer = setInterval(() => {
    sisa.value = Math.max(0, Math.round((batas.value - Date.now()) / 1000));
    if (sisa.value === 0) waktuHabis();
}, 1000);
const sisaTeks = computed(() => {
    const j = Math.floor(sisa.value / 3600);
    const m = Math.floor((sisa.value % 3600) / 60);
    const d = sisa.value % 60;
    return [j, m, d].map((n) => String(n).padStart(2, '0')).join(':');
});

const urlHasil = route('ujian.siswa.hasil', props.sesi.id);

function terimaStatus(data) {
    const batasBaru = Date.now() + data.sisa_detik * 1000;
    if (batasBaru - batas.value > 30000) {
        window.toastr?.info(`Waktu ujian ditambah ${Math.round((batasBaru - batas.value) / 60000)} menit.`);
    }
    batas.value = batasBaru;
    pelanggaran.value = data.jumlah_pelanggaran;
    status.value = data.status;
    if (data.status !== 'berlangsung') keHasil();
}

function keHasil() {
    if (sedangSelesai.value) return;
    sedangSelesai.value = true;
    router.visit(urlHasil);
}

// --- Autosave ---------------------------------------------------------------
const autosave = useAutosave(route('ujian.siswa.jawab', props.sesi.id), {
    headers,
    onPerangkatLain: perangkatLain,
    onStatus: terimaStatus,
    onDitolak: (pesan) => {
        window.toastr?.error(pesan);
        ping();
    },
});

const payload = (s) => ({
    soal_id: s.id,
    pilihan: s.tipe === 'pilihan_ganda' ? s.jawaban_pilihan : null,
    teks: s.tipe === 'essay' ? s.jawaban_text : null,
    ragu: s.is_ragu,
});

function pilih(kode) {
    aktif.value.jawaban_pilihan = aktif.value.jawaban_pilihan === kode ? null : kode;
    autosave.simpan(payload(aktif.value));
}

function toggleRagu() {
    aktif.value.is_ragu = !aktif.value.is_ragu;
    autosave.simpan(payload(aktif.value));
}

const tundaEssay = {};
function ketikEssay(s) {
    clearTimeout(tundaEssay[s.id]);
    tundaEssay[s.id] = setTimeout(() => autosave.simpan(payload(s)), 1200);
}
function simpanEssaySekarang(s) {
    clearTimeout(tundaEssay[s.id]);
    autosave.simpan(payload(s));
}

const terjawab = (s) => (s.tipe === 'essay' ? Boolean(s.jawaban_text?.trim()) : Boolean(s.jawaban_pilihan));
const jumlahTerjawab = computed(() => daftar.filter(terjawab).length);
const jumlahRagu = computed(() => daftar.filter((s) => s.is_ragu).length);

function keSoal(i) {
    if (aktif.value?.tipe === 'essay') simpanEssaySekarang(aktif.value);
    indeks.value = i;
    window.scrollTo({ top: 0 });
}

// --- Pengawasan browser & heartbeat -----------------------------------------
const { layarPenuh, masukLayarPenuh } = usePengawasanBrowser(async (tipe) => {
    try {
        const { data } = await axios.post(route('ujian.siswa.pelanggaran', props.sesi.id), { tipe }, { headers });
        terimaStatus(data);
        if (data.status === 'berlangsung' && props.sesi.batas_pelanggaran > 0) {
            window.toastr?.warning(`Pelanggaran tercatat (${data.jumlah_pelanggaran}/${props.sesi.batas_pelanggaran}). Tetap di halaman ujian.`);
        }
    } catch (error) {
        if (error.response?.status === 409) perangkatLain();
        // Gagal lapor (offline) tidak menghentikan ujian.
    }
}, () => status.value === 'berlangsung' && !sedangSelesai.value && !diambilAlih.value);

// --- Pengumuman proktor (dikirim lewat respons ping) ---------------------------
const pengumuman = ref([...props.pengumuman]);
const idTerakhir = () => pengumuman.value.at(-1)?.id ?? 0;

async function ping() {
    if (diambilAlih.value) return;
    try {
        const { data } = await axios.post(route('ujian.siswa.ping', props.sesi.id), { setelah: idTerakhir() }, { headers });
        for (const p of data.pengumuman ?? []) {
            pengumuman.value.push(p);
            window.toastr?.info(p.pesan, 'Pengumuman pengawas', { timeOut: 15000 });
        }
        terimaStatus(data);
    } catch (error) {
        if (error.response?.status === 409) perangkatLain();
        // Offline: timer lokal tetap berjalan, coba lagi di ping berikutnya.
    }
}
const heartbeat = setInterval(ping, 15000);

// --- Selesai ------------------------------------------------------------------
async function waktuHabis() {
    clearInterval(timer);
    await autosave.kosongkan();
    await ping();
    keHasil();
}

async function selesai() {
    if (aktif.value?.tipe === 'essay') simpanEssaySekarang(aktif.value);
    const belum = daftar.length - jumlahTerjawab.value;
    const pesan = [
        belum ? `${belum} soal belum dijawab.` : 'Semua soal sudah dijawab.',
        jumlahRagu.value ? `${jumlahRagu.value} soal masih ditandai ragu.` : '',
        'Setelah dikumpulkan, jawaban tidak bisa diubah.',
    ].filter(Boolean).join(' ');

    if (!(await konfirmasi(pesan, { judul: 'Kumpulkan jawaban?', tombol: 'Kumpulkan' }))) return;

    await ping(); // pastikan perangkat ini masih pemegang sesi
    if (diambilAlih.value || status.value !== 'berlangsung') return;

    if (!(await autosave.kosongkan())) {
        window.toastr?.error('Sebagian jawaban belum tersimpan karena koneksi terputus. Periksa internet lalu coba lagi.');
        return;
    }
    sedangSelesai.value = true;
    router.post(route('ujian.siswa.selesai', props.sesi.id), {}, {
        headers,
        onFinish: () => { sedangSelesai.value = false; },
    });
}

// --- Siklus halaman -----------------------------------------------------------
const peringatanTutup = (e) => {
    if (autosave.tertunda() && !diambilAlih.value) {
        e.preventDefault();
        e.returnValue = '';
    }
};

onMounted(() => {
    // Ciutkan sidebar AdminLTE supaya area soal lega.
    document.body.classList.add('sidebar-collapse');
    window.addEventListener('beforeunload', peringatanTutup);
});

onBeforeUnmount(() => {
    clearInterval(timer);
    clearInterval(heartbeat);
    document.body.classList.remove('sidebar-collapse');
    window.removeEventListener('beforeunload', peringatanTutup);
});

const labelSimpan = {
    tersimpan: ['fa-check-circle text-success', 'Tersimpan'],
    menyimpan: ['fa-sync fa-spin text-info', 'Menyimpan…'],
    offline: ['fa-wifi text-danger', 'Offline, mencoba lagi…'],
    diputus: ['fa-unlink text-warning', 'Diputus (perangkat lain)'],
};
const kunciSimpan = computed(() => (diambilAlih.value ? 'diputus' : autosave.status.value));
</script>

<template>
    <Head :title="ujian.judul" />

    <div v-if="diambilAlih" class="ujian-diambil-alih">
        <div class="card shadow-lg" style="max-width: 32rem">
            <div class="card-body text-center">
                <i class="fas fa-laptop-house fa-3x text-warning mb-3" />
                <h4>Ujian dibuka di perangkat atau tab lain</h4>
                <p class="text-muted">
                    Hanya satu perangkat yang boleh mengerjakan. Jawaban yang sudah tersimpan tetap aman.
                    Jika Anda ingin melanjutkan di sini, perangkat lain akan diputus dan kejadian ini terlihat oleh pengawas.
                </p>
                <a :href="route('ujian.siswa.kerjakan', sesi.id)" class="btn btn-primary">Lanjutkan di perangkat ini</a>
            </div>
        </div>
    </div>

    <div class="pt-3 ujian-kerjakan">
        <div v-if="pengumuman.length" class="alert alert-info">
            <h6 class="mb-1"><i class="fas fa-bullhorn mr-1" /> Pengumuman pengawas</h6>
            <div v-for="p in pengumuman.slice(-3).reverse()" :key="p.id" class="small">
                {{ p.pesan }} <span class="text-muted">— {{ p.oleh }}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title mb-0">Soal {{ indeks + 1 }} dari {{ daftar.length }}</h3>
                        <span class="ml-auto small">
                            <i class="fas mr-1" :class="labelSimpan[kunciSimpan][0]" />{{ labelSimpan[kunciSimpan][1] }}
                        </span>
                    </div>

                    <div class="card-body">
                        <!-- HTML dirender & disaring di server (App\Modules\Ujian\Support\TeksSoal). -->
                        <div class="lead teks-soal" v-html="aktif.pertanyaan_html" />

                        <div v-if="aktif.tipe === 'pilihan_ganda'" class="list-group">
                            <button v-for="o in aktif.opsi" :key="o.kode" type="button"
                                    class="list-group-item list-group-item-action d-flex align-items-start"
                                    :class="{ active: aktif.jawaban_pilihan === o.kode }"
                                    @click="pilih(o.kode)">
                                <strong class="mr-3">{{ o.label }}.</strong>
                                <span class="teks-soal" v-html="o.html" />
                            </button>
                        </div>

                        <textarea v-else v-model="aktif.jawaban_text" rows="8" class="form-control"
                                  placeholder="Tulis jawaban Anda di sini"
                                  @input="ketikEssay(aktif)" @blur="simpanEssaySekarang(aktif)" />
                    </div>

                    <div class="card-footer d-flex flex-wrap">
                        <button class="btn btn-default mb-1" :disabled="indeks === 0" @click="keSoal(indeks - 1)">
                            <i class="fas fa-chevron-left mr-1" /> Sebelumnya
                        </button>
                        <button class="btn mb-1 mx-2" :class="aktif.is_ragu ? 'btn-warning' : 'btn-outline-warning'" @click="toggleRagu">
                            <i class="fas fa-flag mr-1" /> Ragu-ragu
                        </button>
                        <button v-if="indeks < daftar.length - 1" class="btn btn-primary mb-1 ml-auto" @click="keSoal(indeks + 1)">
                            Berikutnya <i class="fas fa-chevron-right ml-1" />
                        </button>
                        <button v-else class="btn btn-success mb-1 ml-auto" @click="selesai">
                            <i class="fas fa-check mr-1" /> Kumpulkan
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="small text-muted text-truncate">{{ ujian.judul }}</div>
                        <div class="h2 my-1 text-monospace" :class="{ 'text-danger': sisa <= 300 }">{{ sisaTeks }}</div>
                        <div class="small text-muted">sisa waktu</div>
                        <div v-if="pelanggaran" class="small text-danger mt-2">
                            <i class="fas fa-exclamation-triangle" /> {{ pelanggaran }} pelanggaran
                            <span v-if="sesi.batas_pelanggaran">(batas {{ sesi.batas_pelanggaran }})</span>
                        </div>
                        <button v-if="!layarPenuh" class="btn btn-sm btn-outline-secondary mt-2" @click="masukLayarPenuh">
                            <i class="fas fa-expand mr-1" /> Layar penuh
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Navigasi</h3>
                        <div class="card-tools small">{{ jumlahTerjawab }}/{{ daftar.length }} terjawab</div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap" style="gap: .35rem">
                            <button v-for="(s, i) in daftar" :key="s.id" type="button" class="btn btn-sm nav-soal"
                                    :class="[
                                        s.is_ragu ? 'btn-warning' : (terjawab(s) ? 'btn-success' : 'btn-outline-secondary'),
                                        { 'nav-soal-aktif': i === indeks },
                                    ]"
                                    @click="keSoal(i)">{{ i + 1 }}</button>
                        </div>
                        <div class="small text-muted mt-3">
                            <span class="badge badge-success">&nbsp;</span> terjawab
                            <span class="badge badge-warning ml-2">&nbsp;</span> ragu
                            <span class="badge border ml-2">&nbsp;</span> belum
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-success btn-block" :disabled="sedangSelesai" @click="selesai">
                            <i class="fas fa-paper-plane mr-1" /> Kumpulkan jawaban
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ujian-kerjakan { user-select: none; }
.ujian-kerjakan textarea { user-select: text; }
.nav-soal { width: 2.5rem; }
.teks-soal :deep(p:last-child) { margin-bottom: 0; }
.teks-soal :deep(img) { max-height: 22rem; }
.ujian-diambil-alih {
    position: fixed; inset: 0; z-index: 2000; display: flex; align-items: center; justify-content: center;
    background: rgba(0, 0, 0, .6); padding: 1rem;
}
.nav-soal-aktif { box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .5); }
</style>
