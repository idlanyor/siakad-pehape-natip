<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detail Ujian</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="primary"
                            @click="router.visit(route('ujian.edit', ujian.id))"
                        >
                            <i class="fas fa-edit mr-2"></i>
                            Edit
                        </Button>
                        <Button
                            variant="secondary"
                            class="ml-2"
                            @click="router.visit(route('ujian.index'))"
                        >
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </Button>
                    </div>
                </div>
            </div>
        </template>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Header Info -->
                <Card>
                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">{{ ujian.judul }}</h3>
                            <div>
                                <Badge :variant="getStatusVariant(ujian.status)" size="lg">
                                    {{ ujian.status }}
                                </Badge>
                                <Badge variant="info" size="lg" class="ml-2">
                                    {{ getJenisLabel(ujian.jenis) }}
                                </Badge>
                            </div>
                        </div>
                    </template>

                    <div class="ujian-info">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-book text-primary"></i>
                                    <div class="info-content">
                                        <div class="info-label">Mata Pelajaran</div>
                                        <div class="info-value">{{ ujian.mata_pelajaran }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-calendar text-info"></i>
                                    <div class="info-content">
                                        <div class="info-label">Periode</div>
                                        <div class="info-value">{{ ujian.periode?.nama }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <i class="fas fa-question-circle text-primary"></i>
                                    <div class="stat-value">{{ ujian.jumlah_soal }}</div>
                                    <div class="stat-label">Soal</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <i class="fas fa-clock text-info"></i>
                                    <div class="stat-value">{{ ujian.durasi }}</div>
                                    <div class="stat-label">Menit</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <i class="fas fa-star text-warning"></i>
                                    <div class="stat-value">{{ ujian.passing_grade }}%</div>
                                    <div class="stat-label">Passing Grade</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-box">
                                    <i class="fas fa-redo text-success"></i>
                                    <div class="stat-value">{{ ujian.max_attempts }}x</div>
                                    <div class="stat-label">Max Attempts</div>
                                </div>
                            </div>
                        </div>

                        <div v-if="ujian.deskripsi" class="mb-3">
                            <h5>Deskripsi</h5>
                            <p class="text-muted">{{ ujian.deskripsi }}</p>
                        </div>

                        <div v-if="ujian.instruksi" class="mb-3">
                            <h5>Instruksi</h5>
                            <div class="instruksi-box">
                                {{ ujian.instruksi }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6>Jadwal Mulai</h6>
                                <p class="text-muted">
                                    <i class="fas fa-calendar-check mr-2"></i>
                                    {{ formatDateTime(ujian.tanggal_mulai) }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Jadwal Selesai</h6>
                                <p class="text-muted">
                                    <i class="fas fa-calendar-times mr-2"></i>
                                    {{ formatDateTime(ujian.tanggal_selesai) }}
                                </p>
                            </div>
                        </div>

                        <div class="settings-list">
                            <h6>Pengaturan</h6>
                            <ul class="list-unstyled">
                                <li>
                                    <i :class="ujian.acak_soal ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-muted'"></i>
                                    Acak urutan soal
                                </li>
                                <li>
                                    <i :class="ujian.acak_opsi ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-muted'"></i>
                                    Acak urutan opsi
                                </li>
                                <li>
                                    <i :class="ujian.tampilkan_nilai ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-muted'"></i>
                                    Tampilkan nilai
                                </li>
                                <li>
                                    <i :class="ujian.tampilkan_pembahasan ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-muted'"></i>
                                    Tampilkan pembahasan
                                </li>
                                <li>
                                    <i :class="ujian.izinkan_review ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-muted'"></i>
                                    Izinkan review
                                </li>
                            </ul>
                        </div>
                    </div>
                </Card>

                <!-- Soal Preview -->
                <Card class="mt-4">
                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list mr-2"></i>
                                Preview Soal ({{ soals.length }})
                            </h5>
                            <Button
                                variant="primary"
                                size="sm"
                                @click="router.visit(route('ujian.soal', ujian.id))"
                            >
                                <i class="fas fa-cog mr-2"></i>
                                Kelola Soal
                            </Button>
                        </div>
                    </template>

                    <div v-if="soals.length > 0" class="soal-list">
                        <div
                            v-for="(soal, index) in soals"
                            :key="soal.id"
                            class="soal-item"
                        >
                            <div class="soal-header">
                                <Badge variant="primary">{{ index + 1 }}</Badge>
                                <Badge :variant="getTipeSoalVariant(soal.tipe)">
                                    {{ getTipeSoalLabel(soal.tipe) }}
                                </Badge>
                                <span class="ml-auto">
                                    <i class="fas fa-star text-warning mr-1"></i>
                                    {{ soal.bobot }} poin
                                </span>
                            </div>
                            <div class="soal-content">
                                <div class="soal-pertanyaan" v-html="soal.pertanyaan"></div>
                                
                                <!-- Media -->
                                <div v-if="soal.media" class="soal-media mt-2">
                                    <img 
                                        v-if="soal.media.type === 'image'"
                                        :src="soal.media.url" 
                                        :alt="soal.media.name"
                                        class="img-fluid"
                                        style="max-height: 300px;"
                                    >
                                </div>

                                <!-- Pilihan Ganda -->
                                <div v-if="soal.tipe === 'pilihan_ganda'" class="soal-opsi mt-3">
                                    <div
                                        v-for="(opsi, opsiIndex) in soal.opsi"
                                        :key="opsiIndex"
                                        class="opsi-item"
                                        :class="{ 'correct': opsi.is_correct }"
                                    >
                                        <span class="opsi-label">{{ String.fromCharCode(65 + opsiIndex) }}.</span>
                                        <span class="opsi-text">{{ opsi.text }}</span>
                                        <i v-if="opsi.is_correct" class="fas fa-check-circle text-success ml-2"></i>
                                    </div>
                                </div>

                                <!-- Benar/Salah -->
                                <div v-if="soal.tipe === 'benar_salah'" class="soal-opsi mt-3">
                                    <div class="opsi-item" :class="{ 'correct': soal.jawaban_benar === true }">
                                        <span class="opsi-label">Benar</span>
                                        <i v-if="soal.jawaban_benar === true" class="fas fa-check-circle text-success ml-2"></i>
                                    </div>
                                    <div class="opsi-item" :class="{ 'correct': soal.jawaban_benar === false }">
                                        <span class="opsi-label">Salah</span>
                                        <i v-if="soal.jawaban_benar === false" class="fas fa-check-circle text-success ml-2"></i>
                                    </div>
                                </div>

                                <!-- Essay -->
                                <div v-if="soal.tipe === 'essay'" class="mt-3">
                                    <Alert variant="info" size="sm">
                                        <i class="fas fa-pen mr-2"></i>
                                        Soal essay - Penilaian manual oleh guru
                                    </Alert>
                                </div>

                                <!-- Pembahasan -->
                                <div v-if="soal.pembahasan" class="soal-pembahasan mt-3">
                                    <strong>Pembahasan:</strong>
                                    <p class="text-muted mb-0">{{ soal.pembahasan }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada soal untuk ujian ini</p>
                        <Button
                            variant="primary"
                            @click="router.visit(route('ujian.soal', ujian.id))"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Soal
                        </Button>
                    </div>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Statistics -->
                <Card variant="primary" outline>
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Statistik
                        </h5>
                    </template>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ statistics.total_peserta || 0 }}</div>
                                <div class="stat-label">Total Peserta</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ statistics.selesai || 0 }}</div>
                                <div class="stat-label">Selesai</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ statistics.sedang_mengerjakan || 0 }}</div>
                                <div class="stat-label">Sedang Mengerjakan</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ statistics.rata_rata || 0 }}</div>
                                <div class="stat-label">Rata-rata Nilai</div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Actions -->
                <Card class="mt-3">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-cog mr-2"></i>
                            Aksi
                        </h5>
                    </template>
                    <div class="action-buttons">
                        <Button
                            variant="primary"
                            block
                            @click="router.visit(route('ujian.edit', ujian.id))"
                        >
                            <i class="fas fa-edit mr-2"></i>
                            Edit Ujian
                        </Button>
                        <Button
                            variant="info"
                            block
                            class="mt-2"
                            @click="router.visit(route('ujian.soal', ujian.id))"
                        >
                            <i class="fas fa-list mr-2"></i>
                            Kelola Soal
                        </Button>
                        <Button
                            v-if="ujian.status === 'draft'"
                            variant="success"
                            block
                            class="mt-2"
                            @click="publishUjian"
                        >
                            <i class="fas fa-check-circle mr-2"></i>
                            Publish Ujian
                        </Button>
                        <Button
                            v-if="ujian.status === 'published'"
                            variant="warning"
                            block
                            class="mt-2"
                            @click="unpublishUjian"
                        >
                            <i class="fas fa-times-circle mr-2"></i>
                            Unpublish
                        </Button>
                        <Button
                            variant="secondary"
                            block
                            class="mt-2"
                            @click="duplicateUjian"
                        >
                            <i class="fas fa-copy mr-2"></i>
                            Duplikat Ujian
                        </Button>
                        <Button
                            variant="info"
                            block
                            class="mt-2"
                            @click="router.visit(route('ujian.results', ujian.id))"
                        >
                            <i class="fas fa-chart-bar mr-2"></i>
                            Lihat Hasil
                        </Button>
                        <Button
                            variant="secondary"
                            block
                            class="mt-2"
                            @click="exportData"
                        >
                            <i class="fas fa-download mr-2"></i>
                            Export Data
                        </Button>
                        <Button
                            variant="danger"
                            block
                            class="mt-2"
                            @click="confirmDelete"
                        >
                            <i class="fas fa-trash mr-2"></i>
                            Hapus Ujian
                        </Button>
                    </div>
                </Card>

                <!-- Info -->
                <Card class="mt-3">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle mr-2"></i>
                            Informasi
                        </h5>
                    </template>
                    <div class="info-list">
                        <div class="info-row">
                            <span class="info-label">Dibuat:</span>
                            <span class="info-value">{{ formatDate(ujian.created_at) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Diupdate:</span>
                            <span class="info-value">{{ formatDate(ujian.updated_at) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Pembuat:</span>
                            <span class="info-value">{{ ujian.created_by?.name || '-' }}</span>
                        </div>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Delete Modal -->
        <Modal
            v-model:show="showDeleteModal"
            title="Konfirmasi Hapus"
            confirm-text="Ya, Hapus"
            confirm-variant="danger"
            :confirm-loading="deleting"
            @confirm="handleDelete"
        >
            <Alert variant="danger" icon>
                <strong>Perhatian!</strong> Tindakan ini tidak dapat dibatalkan.
            </Alert>
            <p>Apakah Anda yakin ingin menghapus ujian <strong>{{ ujian.judul }}</strong>?</p>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, Button, Badge, Modal, Alert, useToast } from '@/Components';

const props = defineProps({
    ujian: {
        type: Object,
        required: true
    },
    soals: {
        type: Array,
        default: () => []
    },
    statistics: {
        type: Object,
        default: () => ({})
    }
});

const toast = useToast();
const showDeleteModal = ref(false);
const deleting = ref(false);

const getStatusVariant = (status) => {
    const variants = {
        'draft': 'warning',
        'published': 'success',
        'archived': 'secondary'
    };
    return variants[status] || 'secondary';
};

const getJenisLabel = (jenis) => {
    const labels = {
        'uts': 'UTS',
        'uas': 'UAS',
        'quiz': 'Quiz',
        'tugas': 'Tugas'
    };
    return labels[jenis] || jenis;
};

const getTipeSoalVariant = (tipe) => {
    const variants = {
        'pilihan_ganda': 'primary',
        'benar_salah': 'info',
        'essay': 'warning'
    };
    return variants[tipe] || 'secondary';
};

const getTipeSoalLabel = (tipe) => {
    const labels = {
        'pilihan_ganda': 'Pilihan Ganda',
        'benar_salah': 'Benar/Salah',
        'essay': 'Essay'
    };
    return labels[tipe] || tipe;
};

const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const publishUjian = () => {
    router.post(route('ujian.publish', props.ujian.id), {}, {
        onSuccess: () => {
            toast.success('Ujian berhasil dipublish');
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal publish ujian');
        }
    });
};

const unpublishUjian = () => {
    if (!confirm('Unpublish ujian ini?')) return;
    
    router.post(route('ujian.unpublish', props.ujian.id), {}, {
        onSuccess: () => {
            toast.success('Ujian berhasil di-unpublish');
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal unpublish ujian');
        }
    });
};

const duplicateUjian = () => {
    if (!confirm('Duplikat ujian ini?')) return;
    
    router.post(route('ujian.duplicate', props.ujian.id), {}, {
        onSuccess: () => {
            toast.success('Ujian berhasil diduplikat');
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal duplikat ujian');
        }
    });
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const handleDelete = () => {
    deleting.value = true;

    router.delete(route('ujian.destroy', props.ujian.id), {
        onSuccess: () => {
            toast.success('Ujian berhasil dihapus');
            router.visit(route('ujian.index'));
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal menghapus ujian');
            showDeleteModal.value = false;
        },
        onFinish: () => {
            deleting.value = false;
        }
    });
};

const exportData = () => {
    window.location.href = route('ujian.export', props.ujian.id);
};
</script>

<style scoped>
.ujian-info {
    padding: 1rem 0;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.info-item i {
    font-size: 2rem;
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1.25rem;
    font-weight: 600;
}

.stat-box {
    text-align: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.stat-box i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.instruksi-box {
    padding: 1rem;
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    border-radius: 0.25rem;
    white-space: pre-wrap;
}

.settings-list ul li {
    padding: 0.5rem 0;
}

.soal-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.soal-item {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: hidden;
}

.soal-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.soal-content {
    padding: 1.5rem;
}

.soal-pertanyaan {
    font-size: 1.1rem;
    line-height: 1.6;
}

.soal-opsi {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.opsi-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f8f9fa;
    border-radius: 0.25rem;
    border: 2px solid transparent;
}

.opsi-item.correct {
    background: #d4edda;
    border-color: #28a745;
}

.opsi-label {
    font-weight: 600;
    margin-right: 0.75rem;
}

.opsi-text {
    flex: 1;
}

.soal-pembahasan {
    padding: 1rem;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 0.25rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-content {
    flex: 1;
}

.stat-content .stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
}

.stat-content .stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #6c757d;
}

.info-value {
    text-align: right;
}
</style>
