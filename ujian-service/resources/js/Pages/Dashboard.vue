<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </template>

        <!-- Info boxes -->
        <div class="row">
            <!-- Total Ujian -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ stats.total_ujian || 0 }}</h3>
                        <p>Total Ujian</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <Link :href="route('ujian.index')" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </Link>
                </div>
            </div>

            <!-- Ujian Aktif -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ stats.ujian_aktif || 0 }}</h3>
                        <p>Ujian Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <Link :href="route('ujian.index')" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </Link>
                </div>
            </div>

            <!-- Total Siswa (for Guru/Admin) or Ujian Tersedia (for Siswa) -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ isSiswa ? stats.ujian_tersedia || 0 : stats.total_siswa || 0 }}</h3>
                        <p>{{ isSiswa ? 'Ujian Tersedia' : 'Total Siswa' }}</p>
                    </div>
                    <div class="icon">
                        <i :class="isSiswa ? 'fas fa-clipboard-list' : 'fas fa-users'"></i>
                    </div>
                    <Link :href="isSiswa ? route('sesi.index') : '#'" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </Link>
                </div>
            </div>

            <!-- Periode Aktif -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ stats.periode_aktif || 0 }}</h3>
                        <p>Periode Aktif</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <Link :href="route('periode.index')" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <!-- Ujian Terbaru (for Guru/Admin) -->
            <div class="col-md-6" v-if="!isSiswa">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ujian Terbaru</h3>
                        <div class="card-tools">
                            <Link :href="route('ujian.index')" class="btn btn-tool btn-sm">
                                <i class="fas fa-eye"></i> Lihat Semua
                            </Link>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                            <li class="item" v-for="ujian in recentUjian" :key="ujian.id">
                                <div class="product-info">
                                    <Link :href="route('ujian.show', ujian.id)" class="product-title">
                                        {{ ujian.judul }}
                                        <span class="badge badge-info float-right">{{ ujian.kelas_nama }}</span>
                                    </Link>
                                    <span class="product-description">
                                        <i class="far fa-calendar"></i> {{ formatDate(ujian.waktu_mulai) }}
                                        <span class="ml-2">
                                            <i class="far fa-clock"></i> {{ ujian.durasi_menit }} menit
                                        </span>
                                    </span>
                                </div>
                            </li>
                            <li v-if="!recentUjian || recentUjian.length === 0" class="item text-center text-muted py-3">
                                Belum ada ujian
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Ujian Tersedia (for Siswa) -->
            <div class="col-md-6" v-if="isSiswa">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ujian Tersedia</h3>
                        <div class="card-tools">
                            <Link :href="route('sesi.index')" class="btn btn-tool btn-sm">
                                <i class="fas fa-eye"></i> Lihat Semua
                            </Link>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                            <li class="item" v-for="ujian in availableUjian" :key="ujian.id">
                                <div class="product-info">
                                    <a href="#" class="product-title">
                                        {{ ujian.judul }}
                                        <span class="badge badge-success float-right">Tersedia</span>
                                    </a>
                                    <span class="product-description">
                                        <i class="far fa-calendar"></i> {{ formatDate(ujian.waktu_mulai) }}
                                        <span class="ml-2">
                                            <i class="far fa-clock"></i> {{ ujian.durasi_menit }} menit
                                        </span>
                                    </span>
                                </div>
                            </li>
                            <li v-if="!availableUjian || availableUjian.length === 0" class="item text-center text-muted py-3">
                                Tidak ada ujian tersedia
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Statistik Periode -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Periode Ujian Aktif</h3>
                    </div>
                    <div class="card-body">
                        <div v-if="activePeriode">
                            <h5>{{ activePeriode.nama }}</h5>
                            <p class="text-muted">
                                {{ activePeriode.tahun_ajaran }} - Semester {{ activePeriode.semester }}
                            </p>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" :style="{ width: periodeProgress + '%' }"></div>
                            </div>
                            <p class="text-sm">
                                <i class="far fa-calendar"></i> 
                                {{ formatDate(activePeriode.tanggal_mulai) }} - {{ formatDate(activePeriode.tanggal_selesai) }}
                            </p>
                            <p class="text-sm">
                                <i class="fas fa-info-circle"></i> 
                                Fase: <strong>{{ activePeriode.fase_aktif || 'Belum ada fase aktif' }}</strong>
                            </p>
                        </div>
                        <div v-else class="text-center text-muted py-3">
                            Tidak ada periode aktif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row" v-if="!isSiswa">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <Link :href="route('ujian.create')" class="btn btn-primary mr-2">
                            <i class="fas fa-plus"></i> Buat Ujian Baru
                        </Link>
                        <Link :href="route('periode.create')" class="btn btn-success mr-2" v-if="isAdmin">
                            <i class="fas fa-plus"></i> Buat Periode Baru
                        </Link>
                        <Link :href="route('bank-soal.index')" class="btn btn-info mr-2">
                            <i class="fas fa-database"></i> Bank Soal
                        </Link>
                        <Link :href="route('hasil.index')" class="btn btn-warning">
                            <i class="fas fa-chart-bar"></i> Lihat Hasil
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({})
    },
    recentUjian: {
        type: Array,
        default: () => []
    },
    availableUjian: {
        type: Array,
        default: () => []
    },
    activePeriode: {
        type: Object,
        default: null
    }
});

const isSiswa = computed(() => {
    return $page.props.auth.user?.role === 'siswa';
});

const isAdmin = computed(() => {
    return ['admin', 'superadmin'].includes($page.props.auth.user?.role);
});

const periodeProgress = computed(() => {
    if (!props.activePeriode) return 0;
    
    const start = new Date(props.activePeriode.tanggal_mulai);
    const end = new Date(props.activePeriode.tanggal_selesai);
    const now = new Date();
    
    const total = end - start;
    const elapsed = now - start;
    
    return Math.min(Math.max((elapsed / total) * 100, 0), 100);
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
