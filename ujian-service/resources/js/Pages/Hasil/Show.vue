<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detail Hasil Ujian</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="info"
                            @click="downloadPDF"
                        >
                            <i class="fas fa-file-pdf mr-2"></i>
                            Download PDF
                        </Button>
                        <Button
                            variant="secondary"
                            class="ml-2"
                            @click="router.visit(route('hasil.index'))"
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
                    <div class="result-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>{{ result.siswa?.nama }}</h4>
                                <p class="text-muted mb-0">NIS: {{ result.siswa?.nis }}</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="nilai-display">
                                    <div class="nilai-value" :class="getNilaiClass(result.nilai, result.ujian?.passing_grade)">
                                        {{ result.nilai || 0 }}
                                    </div>
                                    <Badge 
                                        :variant="result.nilai >= result.ujian?.passing_grade ? 'success' : 'danger'"
                                        size="lg"
                                    >
                                        {{ result.nilai >= result.ujian?.passing_grade ? 'LULUS' : 'TIDAK LULUS' }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-book text-primary"></i>
                                    <div>
                                        <strong>{{ result.ujian?.judul }}</strong>
                                        <br>
                                        <small class="text-muted">{{ result.ujian?.mata_pelajaran }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-calendar text-info"></i>
                                    <div>
                                        <strong>{{ formatDateTime(result.tanggal_mulai) }}</strong>
                                        <br>
                                        <small class="text-muted">Waktu Pengerjaan: {{ formatDuration(result.durasi_pengerjaan) }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Statistics -->
                <Card class="mt-4">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Statistik Pengerjaan
                        </h5>
                    </template>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-box">
                                <i class="fas fa-check-circle text-success"></i>
                                <div class="stat-value">{{ result.jumlah_benar || 0 }}</div>
                                <div class="stat-label">Benar</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <i class="fas fa-times-circle text-danger"></i>
                                <div class="stat-value">{{ result.jumlah_salah || 0 }}</div>
                                <div class="stat-label">Salah</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <i class="fas fa-minus-circle text-warning"></i>
                                <div class="stat-value">{{ result.jumlah_kosong || 0 }}</div>
                                <div class="stat-label">Tidak Dijawab</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <i class="fas fa-star text-info"></i>
                                <div class="stat-value">{{ result.total_bobot || 0 }}</div>
                                <div class="stat-label">Total Poin</div>
                            </div>
                        </div>
                    </div>

                    <div class="progress mt-3" style="height: 30px;">
                        <div 
                            class="progress-bar bg-success" 
                            :style="{ width: getPercentage(result.jumlah_benar, result.jumlah_soal) + '%' }"
                        >
                            {{ getPercentage(result.jumlah_benar, result.jumlah_soal) }}%
                        </div>
                        <div 
                            class="progress-bar bg-danger" 
                            :style="{ width: getPercentage(result.jumlah_salah, result.jumlah_soal) + '%' }"
                        >
                            {{ getPercentage(result.jumlah_salah, result.jumlah_soal) }}%
                        </div>
                        <div 
                            class="progress-bar bg-warning" 
                            :style="{ width: getPercentage(result.jumlah_kosong, result.jumlah_soal) + '%' }"
                        >
                            {{ getPercentage(result.jumlah_kosong, result.jumlah_soal) }}%
                        </div>
                    </div>
                </Card>

                <!-- Jawaban Detail -->
                <Card class="mt-4">
                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list mr-2"></i>
                                Detail Jawaban
                            </h5>
                            <div>
                                <Button
                                    v-if="result.has_essay && result.status === 'selesai'"
                                    variant="warning"
                                    size="sm"
                                    @click="router.visit(route('hasil.grade', result.id))"
                                >
                                    <i class="fas fa-pen mr-2"></i>
                                    Nilai Essay
                                </Button>
                            </div>
                        </div>
                    </template>

                    <div class="jawaban-list">
                        <div
                            v-for="(jawaban, index) in result.jawaban"
                            :key="jawaban.id"
                            class="jawaban-item"
                            :class="getJawabanClass(jawaban)"
                        >
                            <div class="jawaban-header">
                                <div class="soal-number">
                                    <Badge 
                                        :variant="getJawabanBadgeVariant(jawaban)"
                                        size="lg"
                                    >
                                        {{ index + 1 }}
                                    </Badge>
                                </div>
                                <div class="soal-meta">
                                    <Badge :variant="getTipeSoalVariant(jawaban.soal?.tipe)">
                                        {{ getTipeSoalLabel(jawaban.soal?.tipe) }}
                                    </Badge>
                                    <span class="ml-2">
                                        <i class="fas fa-star text-warning mr-1"></i>
                                        {{ jawaban.soal?.bobot }} poin
                                    </span>
                                    <span v-if="jawaban.nilai !== null" class="ml-2">
                                        <i class="fas fa-check-circle mr-1" :class="jawaban.is_correct ? 'text-success' : 'text-danger'"></i>
                                        {{ jawaban.nilai }} poin
                                    </span>
                                </div>
                            </div>

                            <div class="jawaban-content">
                                <!-- Pertanyaan -->
                                <div class="soal-pertanyaan" v-html="jawaban.soal?.pertanyaan"></div>

                                <!-- Media -->
                                <div v-if="jawaban.soal?.media_url" class="soal-media mt-2">
                                    <img
                                        v-if="isImage(jawaban.soal.media_url)"
                                        :src="jawaban.soal.media_url"
                                        alt="Media"
                                        class="img-fluid"
                                        style="max-height: 300px;"
                                    >
                                </div>

                                <!-- Pilihan Ganda -->
                                <div v-if="jawaban.soal?.tipe === 'pilihan_ganda'" class="soal-opsi mt-3">
                                    <div
                                        v-for="(opsi, opsiIndex) in jawaban.soal.opsi"
                                        :key="opsiIndex"
                                        class="opsi-item"
                                        :class="{
                                            'selected': jawaban.jawaban === opsi.text,
                                            'correct': opsi.is_correct
                                        }"
                                    >
                                        <span class="opsi-label">{{ String.fromCharCode(65 + opsiIndex) }}.</span>
                                        <span class="opsi-text">{{ opsi.text }}</span>
                                        <div class="opsi-icons ml-auto">
                                            <i v-if="jawaban.jawaban === opsi.text" class="fas fa-user-check text-primary mr-2" title="Jawaban siswa"></i>
                                            <i v-if="opsi.is_correct" class="fas fa-check-circle text-success" title="Jawaban benar"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Benar/Salah -->
                                <div v-if="jawaban.soal?.tipe === 'benar_salah'" class="soal-opsi mt-3">
                                    <div class="opsi-item" :class="{ 'selected': jawaban.jawaban === 'true', 'correct': jawaban.soal.jawaban_benar === true }">
                                        <span class="opsi-label">Benar</span>
                                        <div class="opsi-icons ml-auto">
                                            <i v-if="jawaban.jawaban === 'true'" class="fas fa-user-check text-primary mr-2"></i>
                                            <i v-if="jawaban.soal.jawaban_benar === true" class="fas fa-check-circle text-success"></i>
                                        </div>
                                    </div>
                                    <div class="opsi-item" :class="{ 'selected': jawaban.jawaban === 'false', 'correct': jawaban.soal.jawaban_benar === false }">
                                        <span class="opsi-label">Salah</span>
                                        <div class="opsi-icons ml-auto">
                                            <i v-if="jawaban.jawaban === 'false'" class="fas fa-user-check text-primary mr-2"></i>
                                            <i v-if="jawaban.soal.jawaban_benar === false" class="fas fa-check-circle text-success"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Essay -->
                                <div v-if="jawaban.soal?.tipe === 'essay'" class="essay-answer mt-3">
                                    <h6>Jawaban Siswa:</h6>
                                    <div class="essay-content">
                                        {{ jawaban.jawaban || '(Tidak dijawab)' }}
                                    </div>
                                    <div v-if="jawaban.nilai !== null" class="essay-score mt-2">
                                        <Badge variant="info">
                                            Nilai: {{ jawaban.nilai }}/{{ jawaban.soal.bobot }}
                                        </Badge>
                                    </div>
                                    <div v-else class="mt-2">
                                        <Alert variant="warning" size="sm">
                                            <i class="fas fa-clock mr-2"></i>
                                            Menunggu penilaian guru
                                        </Alert>
                                    </div>
                                </div>

                                <!-- Pembahasan -->
                                <div v-if="jawaban.soal?.pembahasan && result.ujian?.tampilkan_pembahasan" class="soal-pembahasan mt-3">
                                    <h6>Pembahasan:</h6>
                                    <p class="text-muted mb-0">{{ jawaban.soal.pembahasan }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Stats -->
                <Card variant="primary" outline>
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle mr-2"></i>
                            Ringkasan
                        </h5>
                    </template>
                    <div class="summary-list">
                        <div class="summary-item">
                            <span class="summary-label">Status:</span>
                            <Badge :variant="getStatusVariant(result.status)">
                                {{ getStatusLabel(result.status) }}
                            </Badge>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Nilai:</span>
                            <strong :class="getNilaiClass(result.nilai, result.ujian?.passing_grade)">
                                {{ result.nilai || 0 }}
                            </strong>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Passing Grade:</span>
                            <strong>{{ result.ujian?.passing_grade }}%</strong>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Total Soal:</span>
                            <strong>{{ result.jumlah_soal }}</strong>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Dijawab:</span>
                            <strong>{{ result.jumlah_dijawab }}</strong>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Benar:</span>
                            <strong class="text-success">{{ result.jumlah_benar }}</strong>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Salah:</span>
                            <strong class="text-danger">{{ result.jumlah_salah }}</strong>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Durasi:</span>
                            <strong>{{ formatDuration(result.durasi_pengerjaan) }}</strong>
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
                            v-if="result.has_essay && result.status === 'selesai'"
                            variant="warning"
                            block
                            @click="router.visit(route('hasil.grade', result.id))"
                        >
                            <i class="fas fa-pen mr-2"></i>
                            Nilai Essay
                        </Button>
                        <Button
                            variant="info"
                            block
                            class="mt-2"
                            @click="downloadPDF"
                        >
                            <i class="fas fa-file-pdf mr-2"></i>
                            Download PDF
                        </Button>
                        <Button
                            variant="secondary"
                            block
                            class="mt-2"
                            @click="printResult"
                        >
                            <i class="fas fa-print mr-2"></i>
                            Print
                        </Button>
                        <Button
                            variant="danger"
                            block
                            class="mt-2"
                            @click="confirmReset"
                        >
                            <i class="fas fa-redo mr-2"></i>
                            Reset Ujian
                        </Button>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Reset Modal -->
        <Modal
            v-model:show="showResetModal"
            title="Konfirmasi Reset"
            confirm-text="Ya, Reset"
            confirm-variant="danger"
            :confirm-loading="resetting"
            @confirm="handleReset"
        >
            <Alert variant="danger" icon>
                <strong>Perhatian!</strong> Tindakan ini tidak dapat dibatalkan.
            </Alert>
            <p>Reset ujian akan menghapus semua jawaban dan nilai. Siswa dapat mengerjakan ujian dari awal.</p>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, Button, Badge, Modal, Alert, useToast } from '@/Components';

const props = defineProps({
    result: {
        type: Object,
        required: true
    }
});

const toast = useToast();
const showResetModal = ref(false);
const resetting = ref(false);

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

const formatDuration = (minutes) => {
    if (!minutes) return '-';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return hours > 0 ? `${hours}j ${mins}m` : `${mins}m`;
};

const getPercentage = (value, total) => {
    if (!total) return 0;
    return Math.round((value / total) * 100);
};

const getNilaiClass = (nilai, passingGrade) => {
    if (nilai >= passingGrade) return 'text-success';
    return 'text-danger';
};

const getStatusVariant = (status) => {
    const variants = {
        'belum_mulai': 'secondary',
        'sedang_mengerjakan': 'warning',
        'selesai': 'success'
    };
    return variants[status] || 'secondary';
};

const getStatusLabel = (status) => {
    const labels = {
        'belum_mulai': 'Belum Mulai',
        'sedang_mengerjakan': 'Sedang Mengerjakan',
        'selesai': 'Selesai'
    };
    return labels[status] || status;
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

const getJawabanClass = (jawaban) => {
    if (jawaban.soal?.tipe === 'essay' && jawaban.nilai === null) return 'pending';
    if (jawaban.is_correct) return 'correct';
    if (jawaban.jawaban === null) return 'unanswered';
    return 'incorrect';
};

const getJawabanBadgeVariant = (jawaban) => {
    if (jawaban.soal?.tipe === 'essay' && jawaban.nilai === null) return 'warning';
    if (jawaban.is_correct) return 'success';
    if (jawaban.jawaban === null) return 'secondary';
    return 'danger';
};

const isImage = (url) => {
    return /\.(jpg|jpeg|png|gif|webp)$/i.test(url);
};

const downloadPDF = () => {
    window.location.href = route('hasil.pdf', props.result.id);
};

const printResult = () => {
    window.print();
};

const confirmReset = () => {
    showResetModal.value = true;
};

const handleReset = () => {
    resetting.value = true;

    router.post(route('hasil.reset', props.result.id), {}, {
        onSuccess: () => {
            toast.success('Ujian berhasil direset');
            router.visit(route('hasil.index'));
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal reset ujian');
        },
        onFinish: () => {
            resetting.value = false;
            showResetModal.value = false;
        }
    });
};
</script>

<style scoped>
.result-header {
    padding: 1rem 0;
}

.nilai-display {
    text-align: center;
}

.nilai-value {
    font-size: 3rem;
    font-weight: bold;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.info-item i {
    font-size: 2rem;
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

.jawaban-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.jawaban-item {
    border: 2px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: hidden;
}

.jawaban-item.correct {
    border-color: #28a745;
    background: #f0fff4;
}

.jawaban-item.incorrect {
    border-color: #dc3545;
    background: #fff5f5;
}

.jawaban-item.unanswered {
    border-color: #ffc107;
    background: #fffbf0;
}

.jawaban-item.pending {
    border-color: #17a2b8;
    background: #f0f9ff;
}

.jawaban-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.soal-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.jawaban-content {
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

.opsi-item.selected {
    border-color: #007bff;
    background: #e7f3ff;
}

.opsi-item.correct {
    border-color: #28a745;
    background: #d4edda;
}

.opsi-label {
    font-weight: 600;
    margin-right: 0.75rem;
}

.opsi-text {
    flex: 1;
}

.opsi-icons {
    display: flex;
    align-items: center;
}

.essay-content {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.25rem;
    white-space: pre-wrap;
    min-height: 100px;
}

.soal-pembahasan {
    padding: 1rem;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 0.25rem;
}

.summary-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-label {
    font-weight: 600;
    color: #6c757d;
}

@media print {
    .btn, .card-header, .action-buttons {
        display: none !important;
    }
}
</style>
