<script setup>
import { Link } from '@inertiajs/vue3';
import PageHeader from '@/Ujian/Components/PageHeader.vue';
import { angka, labelStatusSesi, tanggalWaktu } from '@/Ujian/lib/format';

defineProps({
    ujian: { type: Object, required: true },
    sesi: { type: Array, required: true },
    statistik: { type: Object, required: true },
});

const kartu = [
    ['peserta', 'Peserta'],
    ['rata_rata', 'Rata-rata'],
    ['tertinggi', 'Tertinggi'],
    ['terendah', 'Terendah'],
    ['lulus', 'Lulus KKM'],
    ['perlu_koreksi', 'Perlu koreksi'],
];
</script>

<template>
    <PageHeader :judul="`Hasil: ${ujian.judul}`"
                :jejak="[{ label: 'Ujian', href: route('ujian.ujian.index') }, { label: ujian.judul, href: route('ujian.ujian.show', ujian.id) }, { label: 'Hasil' }]">
        <template #aksi>
            <a :href="route('ujian.hasil.excel', ujian.id)" class="btn btn-success mr-1"><i class="fas fa-file-excel mr-1" /> Excel</a>
            <a :href="route('ujian.hasil.pdf', ujian.id)" class="btn btn-danger"><i class="fas fa-file-pdf mr-1" /> PDF</a>
        </template>
    </PageHeader>

    <div class="row">
        <div v-for="[kunci, label] in kartu" :key="kunci" class="col-6 col-md-4 col-xl-2">
            <div class="info-box">
                <div class="info-box-content">
                    <span class="info-box-text">{{ label }}</span>
                    <span class="info-box-number" :class="{ 'text-warning': kunci === 'perlu_koreksi' && statistik[kunci] }">
                        {{ angka(statistik[kunci]) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ ujian.mapel }} · {{ ujian.kelas }} · KKM {{ ujian.passing_grade }}</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Siswa</th><th>Status</th><th>Selesai</th>
                        <th class="text-center">PG</th><th class="text-center">Essay</th>
                        <th class="text-center">Akhir</th><th class="text-center">Predikat</th>
                        <th class="text-center">Pelanggaran</th><th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="s in sesi" :key="s.id">
                        <td>
                            {{ s.siswa }}
                            <div class="small text-muted">{{ s.nisn }} <span v-if="!s.is_resmi" class="badge badge-light">try out</span></div>
                        </td>
                        <td><span class="badge" :class="`badge-${labelStatusSesi[s.status][1]}`">{{ labelStatusSesi[s.status][0] }}</span></td>
                        <td class="small">{{ tanggalWaktu(s.waktu_selesai) }}</td>
                        <td class="text-center">{{ angka(s.nilai_otomatis) }}</td>
                        <td class="text-center">{{ angka(s.nilai_manual) }}</td>
                        <td class="text-center font-weight-bold"
                            :class="s.nilai_akhir === null ? '' : (Number(s.nilai_akhir) >= ujian.passing_grade ? 'text-success' : 'text-danger')">
                            {{ angka(s.nilai_akhir) }}
                        </td>
                        <td class="text-center">{{ s.predikat ?? '-' }}</td>
                        <td class="text-center">{{ s.jumlah_pelanggaran }}</td>
                        <td class="text-right">
                            <Link :href="route('ujian.hasil.sesi', s.id)" class="btn btn-sm" :class="s.perlu_koreksi ? 'btn-warning' : 'btn-outline-secondary'">
                                {{ s.perlu_koreksi ? 'Koreksi' : 'Detail' }}
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="!sesi.length">
                        <td colspan="9" class="text-center text-muted py-4">Belum ada siswa yang mengerjakan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
