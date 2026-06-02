<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ exam.judul }}</h1>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-lg-8">
                <Card>
                    <template #header>
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>
                            Instruksi Ujian
                        </h3>
                    </template>

                    <!-- Exam Info -->
                    <div class="exam-info mb-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="info-item">
                                    <i class="fas fa-book text-primary"></i>
                                    <div class="info-content">
                                        <div class="info-label">Mata Pelajaran</div>
                                        <div class="info-value">{{ exam.mata_pelajaran }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-item">
                                    <i class="fas fa-clock text-warning"></i>
                                    <div class="info-content">
                                        <div class="info-label">Durasi</div>
                                        <div class="info-value">{{ exam.durasi }} menit</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-item">
                                    <i class="fas fa-question-circle text-info"></i>
                                    <div class="info-content">
                                        <div class="info-label">Jumlah Soal</div>
                                        <div class="info-value">{{ exam.jumlah_soal }} soal</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-item">
                                    <i class="fas fa-star text-success"></i>
                                    <div class="info-content">
                                        <div class="info-label">Total Bobot</div>
                                        <div class="info-value">{{ exam.total_bobot }} poin</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="exam.deskripsi" class="exam-description mb-4">
                        <h5>Deskripsi</h5>
                        <p class="text-muted">{{ exam.deskripsi }}</p>
                    </div>

                    <!-- Instructions -->
                    <div class="exam-instructions mb-4">
                        <h5>Petunjuk Pengerjaan</h5>
                        <div v-if="exam.instruksi" v-html="exam.instruksi" class="instructions-content"></div>
                        <ul v-else class="instructions-list">
                            <li>Pastikan koneksi internet Anda stabil selama mengerjakan ujian</li>
                            <li>Jawaban akan tersimpan otomatis setiap 30 detik</li>
                            <li>Ujian akan otomatis tersubmit ketika waktu habis</li>
                            <li>Anda dapat menavigasi antar soal dengan bebas</li>
                            <li>Pastikan menjawab semua soal sebelum submit</li>
                            <li>Setelah submit, Anda tidak dapat mengubah jawaban</li>
                        </ul>
                    </div>

                    <!-- Warning -->
                    <Alert variant="warning" icon>
                        <strong>Perhatian!</strong> Setelah memulai ujian, timer akan berjalan dan tidak dapat dihentikan. 
                        Pastikan Anda siap sebelum memulai.
                    </Alert>

                    <!-- Checkbox Agreement -->
                    <div class="form-check mb-4">
                        <input
                            id="agree"
                            v-model="agreed"
                            type="checkbox"
                            class="form-check-input"
                        />
                        <label for="agree" class="form-check-label">
                            Saya telah membaca dan memahami instruksi ujian
                        </label>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <Button
                            variant="secondary"
                            @click="goBack"
                        >
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali
                        </Button>
                        <Button
                            variant="primary"
                            size="lg"
                            :disabled="!agreed || starting"
                            :loading="starting"
                            @click="handleStart"
                        >
                            <i class="fas fa-play mr-2"></i>
                            Mulai Ujian
                        </Button>
                    </div>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Timer Info -->
                <Card variant="primary" outline>
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-clock mr-2"></i>
                            Informasi Waktu
                        </h5>
                    </template>
                    <div class="text-center py-3">
                        <div class="timer-display">
                            <i class="fas fa-hourglass-half fa-3x text-primary mb-3"></i>
                            <h2 class="mb-0">{{ exam.durasi }}</h2>
                            <p class="text-muted">menit</p>
                        </div>
                        <p class="mb-0 small text-muted">
                            Timer akan dimulai setelah Anda klik "Mulai Ujian"
                        </p>
                    </div>
                </Card>

                <!-- Question Types -->
                <Card v-if="exam.tipe_soal" class="mt-3">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-list mr-2"></i>
                            Tipe Soal
                        </h5>
                    </template>
                    <div class="question-types">
                        <div v-if="exam.tipe_soal.pilihan_ganda" class="type-item">
                            <Badge variant="info">Pilihan Ganda</Badge>
                            <span class="ml-2">{{ exam.tipe_soal.pilihan_ganda }} soal</span>
                        </div>
                        <div v-if="exam.tipe_soal.benar_salah" class="type-item">
                            <Badge variant="success">Benar/Salah</Badge>
                            <span class="ml-2">{{ exam.tipe_soal.benar_salah }} soal</span>
                        </div>
                        <div v-if="exam.tipe_soal.essay" class="type-item">
                            <Badge variant="warning">Essay</Badge>
                            <span class="ml-2">{{ exam.tipe_soal.essay }} soal</span>
                        </div>
                    </div>
                </Card>

                <!-- Tips -->
                <Card variant="success" outline class="mt-3">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Tips
                        </h5>
                    </template>
                    <ul class="tips-list mb-0">
                        <li>Baca soal dengan teliti</li>
                        <li>Kerjakan soal yang mudah terlebih dahulu</li>
                        <li>Manajemen waktu dengan baik</li>
                        <li>Periksa kembali jawaban sebelum submit</li>
                    </ul>
                </Card>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <Modal
            v-model:show="showConfirmModal"
            title="Konfirmasi Mulai Ujian"
            confirm-text="Ya, Mulai"
            confirm-variant="primary"
            :confirm-loading="starting"
            @confirm="confirmStart"
        >
            <p>Apakah Anda yakin ingin memulai ujian sekarang?</p>
            <Alert variant="info" icon>
                Timer akan langsung berjalan setelah Anda mengkonfirmasi.
            </Alert>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, Button, Badge, Alert, Modal, useToast } from '@/Components';

const props = defineProps({
    exam: {
        type: Object,
        required: true
    }
});

const agreed = ref(false);
const starting = ref(false);
const showConfirmModal = ref(false);
const toast = useToast();

const handleStart = () => {
    if (!agreed.value) {
        toast.warning('Anda harus menyetujui instruksi terlebih dahulu');
        return;
    }
    showConfirmModal.value = true;
};

const confirmStart = async () => {
    starting.value = true;
    
    try {
        router.post(route('exam.start.submit', props.exam.id), {}, {
            onSuccess: (page) => {
                const sessionId = page.props.session?.id;
                if (sessionId) {
                    router.visit(route('exam.take', sessionId));
                }
            },
            onError: (errors) => {
                toast.error(errors.message || 'Gagal memulai ujian');
                showConfirmModal.value = false;
            },
            onFinish: () => {
                starting.value = false;
            }
        });
    } catch (error) {
        toast.error('Terjadi kesalahan saat memulai ujian');
        starting.value = false;
        showConfirmModal.value = false;
    }
};

const goBack = () => {
    router.visit(route('exam.list'));
};
</script>

<style scoped>
.exam-info {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.5rem;
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

.instructions-content,
.instructions-list {
    line-height: 1.8;
}

.instructions-list li {
    margin-bottom: 0.5rem;
}

.timer-display {
    padding: 1rem;
}

.timer-display h2 {
    font-size: 3rem;
    font-weight: bold;
    color: #007bff;
}

.question-types .type-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.question-types .type-item:last-child {
    border-bottom: none;
}

.tips-list {
    padding-left: 1.5rem;
}

.tips-list li {
    margin-bottom: 0.5rem;
}
</style>
