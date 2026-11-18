<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import { tanggalWaktu } from '@/Ujian/lib/format';
import { konfirmasi } from '@/Ujian/lib/konfirmasi';

const props = defineProps({
    ujian: { type: Object, required: true },
    izin: { type: Object, required: true },
    guru: { type: Array, default: () => [] },
});

const pengawasDipilih = ref(props.ujian.pengawas.map((g) => g.id));

async function ubahStatus(status, pesan) {
    if (await konfirmasi(pesan)) {
        router.post(route('ujian.ujian.status', props.ujian.id), { status }, { preserveScroll: true });
    }
}

async function hapus() {
    if (await konfirmasi(`Hapus ujian "${props.ujian.judul}"?`, { bahaya: true, tombol: 'Hapus' })) {
        router.delete(route('ujian.ujian.destroy', props.ujian.id));
    }
}

const simpanPengawas = () => router.put(route('ujian.ujian.pengawas', props.ujian.id),
    { guru_ids: pengawasDipilih.value }, { preserveScroll: true });

const ya = (v) => (v ? 'Ya' : 'Tidak');
</script>

<template>
    <PageHeader :judul="ujian.judul" :jejak="[{ label: 'Ujian', href: route('ujian.ujian.index') }, { label: ujian.judul }]">
        <template #aksi>
            <span class="badge mr-2" :class="`badge-${ujian.status_badge}`" style="font-size: 1rem">{{ ujian.status_label }}</span>
            <template v-if="izin.ubah">
                <button v-if="ujian.status === 'draft'" class="btn btn-success mr-1"
                        @click="ubahStatus('published', 'Terbitkan ujian? Siswa bisa mengerjakan saat jadwal dibuka.')">
                    <i class="fas fa-paper-plane mr-1" /> Terbitkan
                </button>
                <button v-if="ujian.status === 'published' && !ujian.jumlah_sesi" class="btn btn-outline-secondary mr-1"
                        @click="ubahStatus('draft', 'Kembalikan ujian ke draft?')">Kembalikan ke draft</button>
                <button v-if="ujian.status === 'published'" class="btn btn-outline-dark mr-1"
                        @click="ubahStatus('selesai', 'Tandai ujian selesai? Siswa tidak bisa mulai lagi.')">Tandai selesai</button>
            </template>
        </template>
    </PageHeader>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Mata pelajaran</dt><dd class="col-sm-8">{{ ujian.mapel }}</dd>
                        <dt class="col-sm-4">Kelas</dt><dd class="col-sm-8">{{ ujian.kelas }}</dd>
                        <dt class="col-sm-4">Periode</dt><dd class="col-sm-8">{{ ujian.periode }}</dd>
                        <dt class="col-sm-4">Guru</dt><dd class="col-sm-8">{{ ujian.guru ?? '—' }}</dd>
                        <dt class="col-sm-4">Jadwal</dt>
                        <dd class="col-sm-8">{{ tanggalWaktu(ujian.waktu_mulai) }} s/d {{ tanggalWaktu(ujian.waktu_selesai) }}</dd>
                        <dt class="col-sm-4">Durasi</dt><dd class="col-sm-8">{{ ujian.durasi_menit }} menit</dd>
                        <dt class="col-sm-4">KKM</dt><dd class="col-sm-8">{{ ujian.passing_grade }}</dd>
                        <dt class="col-sm-4">Acak soal / opsi</dt><dd class="col-sm-8">{{ ya(ujian.acak_soal) }} / {{ ya(ujian.acak_opsi) }}</dd>
                        <dt class="col-sm-4">Nilai ditampilkan</dt><dd class="col-sm-8">{{ ya(ujian.tampilkan_nilai) }}</dd>
                    </dl>
                    <p v-if="ujian.deskripsi" class="mt-3 mb-0" style="white-space: pre-line">{{ ujian.deskripsi }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <p class="mb-2"><strong>{{ ujian.jumlah_soal }}</strong> soal · <strong>{{ ujian.jumlah_sesi }}</strong> siswa sudah mulai</p>
                    <Link v-if="izin.ubah" :href="route('ujian.soal.index', ujian.id)" class="btn btn-block btn-primary">
                        <i class="fas fa-list-ol mr-1" /> Susun soal
                    </Link>
                    <Link v-if="izin.nilai" :href="route('ujian.hasil.index', ujian.id)" class="btn btn-block btn-info">
                        <i class="fas fa-chart-bar mr-1" /> Hasil & koreksi
                    </Link>
                    <Link v-if="izin.ubah" :href="route('ujian.ujian.edit', ujian.id)" class="btn btn-block btn-outline-primary">
                        <i class="fas fa-edit mr-1" /> Ubah pengaturan
                    </Link>
                    <button v-if="izin.hapus" class="btn btn-block btn-outline-danger" @click="hapus">
                        <i class="fas fa-trash mr-1" /> Hapus ujian
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Pengawas</h3></div>
                <div class="card-body">
                    <template v-if="izin.tugaskanPengawas">
                        <select v-model="pengawasDipilih" multiple size="6" class="form-control mb-2">
                            <option v-for="g in guru" :key="g.value" :value="g.value">{{ g.label }}</option>
                        </select>
                        <small class="text-muted d-block mb-2">Ctrl/Cmd + klik untuk memilih lebih dari satu.</small>
                        <button class="btn btn-sm btn-primary" @click="simpanPengawas">Simpan pengawas</button>
                    </template>
                    <template v-else>
                        <p v-if="!ujian.pengawas.length" class="text-muted mb-0">Belum ada pengawas ditugaskan.</p>
                        <ul v-else class="mb-0 pl-3">
                            <li v-for="g in ujian.pengawas" :key="g.id">{{ g.nama }}</li>
                        </ul>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
