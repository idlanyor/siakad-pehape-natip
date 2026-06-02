<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detail Periode Ujian</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="primary"
                            @click="router.visit(route('periode.edit', periode.id))"
                        >
                            <i class="fas fa-edit mr-2"></i>
                            Edit
                        </Button>
                        <Button
                            variant="secondary"
                            class="ml-2"
                            @click="router.visit(route('periode.index'))"
                        >
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </Button>
                    </div>
                </div>
            </div>
        </template>

        <div class="row">
            <!-- Main Info -->
            <div class="col-lg-8">
                <Card>
                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">{{ periode.nama }}</h3>
                            <Badge :variant="getStatusVariant(periode.status)" size="lg">
                                {{ periode.status }}
                            </Badge>
                        </div>
                    </template>

                    <div class="periode-info">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-calendar text-primary"></i>
                                    <div class="info-content">
                                        <div class="info-label">Tahun Ajaran</div>
                                        <div class="info-value">{{ periode.tahun_ajaran }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-book text-info"></i>
                                    <div class="info-content">
                                        <div class="info-label">Semester</div>
                                        <div class="info-value">{{ periode.semester }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                                <div class="info-item">
                                    <i class="fas fa-calendar-check text-success"></i>
                                    <div class="info-content">
                                        <div class="info-label">Tanggal Mulai</div>
                                        <div class="info-value">{{ formatDate(periode.tanggal_mulai) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mt-3">
                                <div class="info-item">
                                    <i class="fas fa-calendar-times text-danger"></i>
                                    <div class="info-content">
                                        <div class="info-label">Tanggal Selesai</div>
                                        <div class="info-value">{{ formatDate(periode.tanggal_selesai) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="periode.deskripsi" class="periode-description">
                            <h5>Deskripsi</h5>
                            <p class="text-muted">{{ periode.deskripsi }}</p>
                        </div>
                    </div>
                </Card>

                <!-- Fase Timeline -->
                <Card class="mt-4">
                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-stream mr-2"></i>
                                Timeline Fase
                            </h5>
                            <Badge :variant="getFaseVariant(periode.fase_aktif)">
                                Fase Aktif: {{ getFaseLabel(periode.fase_aktif) }}
                            </Badge>
                        </div>
                    </template>

                    <div class="fase-timeline">
                        <div
                            v-for="(fase, index) in periode.fase"
                            :key="index"
                            class="fase-timeline-item"
                            :class="{ 'active': fase.nama === periode.fase_aktif }"
                        >
                            <div class="fase-marker">
                                <div class="fase-dot"></div>
                                <div v-if="index < periode.fase.length - 1" class="fase-line"></div>
                            </div>
                            <div class="fase-content">
                                <div class="fase-header">
                                    <h6>
                                        <Badge :variant="getFaseVariant(fase.nama)">
                                            {{ getFaseLabel(fase.nama) }}
                                        </Badge>
                                    </h6>
                                    <small class="text-muted">
                                        {{ formatDate(fase.tanggal_mulai) }} - {{ formatDate(fase.tanggal_selesai) }}
                                    </small>
                                </div>
                                <p class="fase-description text-muted mb-0">
                                    {{ getFaseDescription(fase.nama) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        <Button
                            variant="warning"
                            size="sm"
                            @click="showOverrideModal = true"
                        >
                            <i class="fas fa-cog mr-2"></i>
                            Override Fase Manual
                        </Button>
                    </div>
                </Card>

                <!-- Ujian List -->
                <Card class="mt-4">
                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-file-alt mr-2"></i>
                                Daftar Ujian
                            </h5>
                            <Button
                                variant="primary"
                                size="sm"
                                @click="router.visit(route('ujian.create', { periode_id: periode.id }))"
                            >
                                <i class="fas fa-plus mr-2"></i>
                                Tambah Ujian
                            </Button>
                        </div>
                    </template>

                    <div v-if="periode.ujian && periode.ujian.length > 0" class="list-group list-group-flush">
                        <div
                            v-for="ujian in periode.ujian"
                            :key="ujian.id"
                            class="list-group-item"
                        >
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ ujian.judul }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-book mr-1"></i>
                                        {{ ujian.mata_pelajaran }}
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-question-circle mr-1"></i>
                                        {{ ujian.jumlah_soal }} soal
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ ujian.durasi }} menit
                                    </small>
                                </div>
                                <div>
                                    <Badge :variant="ujian.status === 'published' ? 'success' : 'secondary'">
                                        {{ ujian.status }}
                                    </Badge>
                                    <Button
                                        variant="info"
                                        size="sm"
                                        class="ml-2"
                                        @click="router.visit(route('ujian.show', ujian.id))"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada ujian untuk periode ini</p>
                    </div>
                </Card>
            </div>

            <!-- Sidebar Stats -->
            <div class="col-lg-4">
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
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ periode.jumlah_ujian || 0 }}</div>
                                <div class="stat-label">Total Ujian</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ periode.ujian_published || 0 }}</div>
                                <div class="stat-label">Ujian Published</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ periode.total_peserta || 0 }}</div>
                                <div class="stat-label">Total Peserta</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">{{ periode.total_selesai || 0 }}</div>
                                <div class="stat-label">Ujian Selesai</div>
                            </div>
                        </div>
                    </div>
                </Card>

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
                            @click="router.visit(route('periode.edit', periode.id))"
                        >
                            <i class="fas fa-edit mr-2"></i>
                            Edit Periode
                        </Button>
                        <Button
                            variant="info"
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
                            Hapus Periode
                        </Button>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Override Fase Modal -->
        <Modal
            v-model:show="showOverrideModal"
            title="Override Fase Manual"
            confirm-text="Update Fase"
            :confirm-loading="overriding"
            @confirm="handleOverrideFase"
        >
            <Alert variant="info" icon>
                Override fase akan mengubah fase aktif secara manual, melewati jadwal otomatis.
            </Alert>

            <Select
                v-model="selectedFase"
                label="Pilih Fase"
                :options="faseOptions"
                required
            />
        </Modal>

        <!-- Delete Confirmation Modal -->
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
            <p>Apakah Anda yakin ingin menghapus periode <strong>{{ periode.nama }}</strong>?</p>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, Button, Badge, Modal, Alert, Select, useToast } from '@/Components';

const props = defineProps({
    periode: {
        type: Object,
        required: true
    }
});

const toast = useToast();
const showOverrideModal = ref(false);
const showDeleteModal = ref(false);
const selectedFase = ref(props.periode.fase_aktif);
const overriding = ref(false);
const deleting = ref(false);

const faseOptions = [
    { value: 'persiapan', label: 'Persiapan' },
    { value: 'pendaftaran', label: 'Pendaftaran' },
    { value: 'pelaksanaan', label: 'Pelaksanaan' },
    { value: 'penilaian', label: 'Penilaian' },
    { value: 'selesai', label: 'Selesai' }
];

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

const getStatusVariant = (status) => {
    const variants = {
        'aktif': 'success',
        'nonaktif': 'secondary',
        'selesai': 'dark'
    };
    return variants[status] || 'secondary';
};

const getFaseVariant = (fase) => {
    const variants = {
        'persiapan': 'info',
        'pendaftaran': 'primary',
        'pelaksanaan': 'success',
        'penilaian': 'warning',
        'selesai': 'secondary'
    };
    return variants[fase] || 'secondary';
};

const getFaseLabel = (fase) => {
    const labels = {
        'persiapan': 'Persiapan',
        'pendaftaran': 'Pendaftaran',
        'pelaksanaan': 'Pelaksanaan',
        'penilaian': 'Penilaian',
        'selesai': 'Selesai'
    };
    return labels[fase] || fase;
};

const getFaseDescription = (fase) => {
    const descriptions = {
        'persiapan': 'Persiapan soal dan konfigurasi ujian oleh admin/guru',
        'pendaftaran': 'Siswa dapat melihat jadwal ujian yang tersedia',
        'pelaksanaan': 'Ujian dapat dikerjakan oleh siswa',
        'penilaian': 'Guru melakukan penilaian dan grading',
        'selesai': 'Hasil ujian dapat dilihat oleh siswa'
    };
    return descriptions[fase] || '';
};

const handleOverrideFase = () => {
    overriding.value = true;

    router.post(route('periode.override-fase', props.periode.id), {
        fase: selectedFase.value
    }, {
        onSuccess: () => {
            toast.success('Fase berhasil diupdate');
            showOverrideModal.value = false;
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal update fase');
        },
        onFinish: () => {
            overriding.value = false;
        }
    });
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const handleDelete = () => {
    deleting.value = true;

    router.delete(route('periode.destroy', props.periode.id), {
        onSuccess: () => {
            toast.success('Periode berhasil dihapus');
            router.visit(route('periode.index'));
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal menghapus periode');
            showDeleteModal.value = false;
        },
        onFinish: () => {
            deleting.value = false;
        }
    });
};

const exportData = () => {
    window.location.href = route('periode.export', props.periode.id);
};
</script>

<style scoped>
.periode-info {
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

.fase-timeline {
    position: relative;
}

.fase-timeline-item {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem 0;
}

.fase-timeline-item.active .fase-dot {
    background: #007bff;
    border-color: #007bff;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
}

.fase-timeline-item.active .fase-content {
    background: #f0f8ff;
    padding: 1rem;
    border-radius: 0.5rem;
}

.fase-marker {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.fase-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: white;
    border: 3px solid #dee2e6;
    z-index: 1;
}

.fase-line {
    width: 2px;
    flex: 1;
    background: #dee2e6;
    margin-top: 0.5rem;
}

.fase-content {
    flex: 1;
}

.fase-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.fase-header h6 {
    margin: 0;
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

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
</style>
