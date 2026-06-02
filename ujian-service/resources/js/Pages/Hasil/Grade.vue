<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Penilaian Essay</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <Button
                            variant="secondary"
                            @click="router.visit(route('hasil.show', result.id))"
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
                <!-- Student Info -->
                <Card>
                    <div class="student-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>{{ result.siswa?.nama }}</h4>
                                <p class="text-muted mb-0">NIS: {{ result.siswa?.nis }}</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <h5>{{ result.ujian?.judul }}</h5>
                                <p class="text-muted mb-0">{{ result.ujian?.mata_pelajaran }}</p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Essay Questions -->
                <Card class="mt-4">
                    <template #header>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-pen mr-2"></i>
                                Soal Essay ({{ essayQuestions.length }})
                            </h5>
                            <div>
                                <Badge variant="info">
                                    {{ gradedCount }}/{{ essayQuestions.length }} dinilai
                                </Badge>
                            </div>
                        </div>
                    </template>

                    <div class="essay-list">
                        <div
                            v-for="(jawaban, index) in essayQuestions"
                            :key="jawaban.id"
                            class="essay-item"
                            :class="{ 'graded': jawaban.nilai !== null }"
                        >
                            <div class="essay-header">
                                <div class="soal-number">
                                    <Badge 
                                        :variant="jawaban.nilai !== null ? 'success' : 'warning'"
                                        size="lg"
                                    >
                                        {{ index + 1 }}
                                    </Badge>
                                </div>
                                <div class="soal-meta">
                                    <span>
                                        <i class="fas fa-star text-warning mr-1"></i>
                                        Bobot: {{ jawaban.soal?.bobot }} poin
                                    </span>
                                    <span v-if="jawaban.nilai !== null" class="ml-3">
                                        <i class="fas fa-check-circle text-success mr-1"></i>
                                        Nilai: {{ jawaban.nilai }} poin
                                    </span>
                                </div>
                            </div>

                            <div class="essay-content">
                                <!-- Pertanyaan -->
                                <div class="soal-pertanyaan mb-3">
                                    <h6>Pertanyaan:</h6>
                                    <div v-html="jawaban.soal?.pertanyaan"></div>
                                </div>

                                <!-- Media -->
                                <div v-if="jawaban.soal?.media_url" class="soal-media mb-3">
                                    <img
                                        v-if="isImage(jawaban.soal.media_url)"
                                        :src="jawaban.soal.media_url"
                                        alt="Media"
                                        class="img-fluid"
                                        style="max-height: 300px;"
                                    >
                                </div>

                                <!-- Kunci Jawaban -->
                                <div v-if="jawaban.soal?.kunci_jawaban_essay" class="kunci-jawaban mb-3">
                                    <h6>Kunci Jawaban / Poin Penting:</h6>
                                    <div class="kunci-content">
                                        {{ jawaban.soal.kunci_jawaban_essay }}
                                    </div>
                                </div>

                                <!-- Jawaban Siswa -->
                                <div class="jawaban-siswa mb-3">
                                    <h6>Jawaban Siswa:</h6>
                                    <div class="jawaban-content">
                                        {{ jawaban.jawaban || '(Tidak dijawab)' }}
                                    </div>
                                </div>

                                <!-- Grading Form -->
                                <div class="grading-form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <Input
                                                v-model.number="jawaban.nilai_input"
                                                type="number"
                                                label="Nilai"
                                                :placeholder="`0 - ${jawaban.soal?.bobot}`"
                                                :min="0"
                                                :max="jawaban.soal?.bobot"
                                                required
                                            />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="d-block">Quick Score</label>
                                            <div class="quick-score-buttons">
                                                <Button
                                                    variant="success"
                                                    size="sm"
                                                    @click="jawaban.nilai_input = jawaban.soal?.bobot"
                                                >
                                                    100%
                                                </Button>
                                                <Button
                                                    variant="info"
                                                    size="sm"
                                                    @click="jawaban.nilai_input = Math.round(jawaban.soal?.bobot * 0.75)"
                                                >
                                                    75%
                                                </Button>
                                                <Button
                                                    variant="warning"
                                                    size="sm"
                                                    @click="jawaban.nilai_input = Math.round(jawaban.soal?.bobot * 0.5)"
                                                >
                                                    50%
                                                </Button>
                                                <Button
                                                    variant="danger"
                                                    size="sm"
                                                    @click="jawaban.nilai_input = 0"
                                                >
                                                    0%
                                                </Button>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <Textarea
                                                v-model="jawaban.catatan"
                                                label="Catatan untuk Siswa (Opsional)"
                                                placeholder="Berikan feedback atau catatan..."
                                                :rows="3"
                                            />
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <Button
                                                variant="primary"
                                                @click="saveGrade(jawaban)"
                                                :loading="savingId === jawaban.id"
                                                :disabled="jawaban.nilai_input === null || jawaban.nilai_input === ''"
                                            >
                                                <i class="fas fa-save mr-2"></i>
                                                Simpan Nilai
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pembahasan -->
                                <div v-if="jawaban.soal?.pembahasan" class="soal-pembahasan mt-3">
                                    <h6>Pembahasan:</h6>
                                    <p class="text-muted mb-0">{{ jawaban.soal.pembahasan }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="essayQuestions.length === 0" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Tidak ada soal essay untuk dinilai</p>
                    </div>
                </Card>

                <!-- Actions -->
                <div class="mt-4">
                    <Button
                        variant="success"
                        size="lg"
                        @click="finishGrading"
                        :disabled="!allGraded"
                    >
                        <i class="fas fa-check-circle mr-2"></i>
                        Selesai & Hitung Nilai Total
                    </Button>
                    <Button
                        variant="secondary"
                        size="lg"
                        class="ml-2"
                        @click="router.visit(route('hasil.show', result.id))"
                    >
                        Simpan & Kembali
                    </Button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Progress -->
                <Card variant="primary" outline>
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie mr-2"></i>
                            Progress Penilaian
                        </h5>
                    </template>
                    <div class="progress-section">
                        <div class="progress mb-3" style="height: 30px;">
                            <div
                                class="progress-bar bg-success"
                                :style="{ width: progressPercentage + '%' }"
                            >
                                {{ progressPercentage }}%
                            </div>
                        </div>
                        <div class="progress-stats">
                            <div class="stat-item">
                                <span class="stat-label">Total Soal:</span>
                                <strong>{{ essayQuestions.length }}</strong>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Sudah Dinilai:</span>
                                <strong class="text-success">{{ gradedCount }}</strong>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Belum Dinilai:</span>
                                <strong class="text-warning">{{ ungradedCount }}</strong>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Grading Summary -->
                <Card class="mt-3">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-calculator mr-2"></i>
                            Ringkasan Nilai
                        </h5>
                    </template>
                    <div class="summary-section">
                        <div class="summary-item">
                            <span class="summary-label">Total Bobot Essay:</span>
                            <strong>{{ totalBobotEssay }} poin</strong>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Nilai Essay Saat Ini:</span>
                            <strong class="text-primary">{{ currentEssayScore }} poin</strong>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Nilai Non-Essay:</span>
                            <strong>{{ nonEssayScore }} poin</strong>
                        </div>
                        <div class="summary-item border-top pt-2 mt-2">
                            <span class="summary-label">Estimasi Nilai Total:</span>
                            <strong class="text-success">{{ estimatedTotal }}</strong>
                        </div>
                    </div>
                </Card>

                <!-- Tips -->
                <Card class="mt-3">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Tips Penilaian
                        </h5>
                    </template>
                    <ul class="tips-list mb-0">
                        <li>Baca kunci jawaban terlebih dahulu</li>
                        <li>Nilai berdasarkan poin-poin penting</li>
                        <li>Berikan feedback konstruktif</li>
                        <li>Gunakan quick score untuk efisiensi</li>
                        <li>Simpan setiap nilai sebelum lanjut</li>
                    </ul>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, Input, Textarea, Button, Badge, useToast } from '@/Components';

const props = defineProps({
    result: {
        type: Object,
        required: true
    },
    essayQuestions: {
        type: Array,
        required: true
    }
});

const toast = useToast();
const savingId = ref(null);

// Initialize nilai_input for each question
props.essayQuestions.forEach(q => {
    q.nilai_input = q.nilai;
    q.catatan = q.catatan || '';
});

const gradedCount = computed(() => {
    return props.essayQuestions.filter(q => q.nilai !== null).length;
});

const ungradedCount = computed(() => {
    return props.essayQuestions.length - gradedCount.value;
});

const progressPercentage = computed(() => {
    if (props.essayQuestions.length === 0) return 100;
    return Math.round((gradedCount.value / props.essayQuestions.length) * 100);
});

const allGraded = computed(() => {
    return ungradedCount.value === 0;
});

const totalBobotEssay = computed(() => {
    return props.essayQuestions.reduce((sum, q) => sum + (q.soal?.bobot || 0), 0);
});

const currentEssayScore = computed(() => {
    return props.essayQuestions.reduce((sum, q) => {
        return sum + (q.nilai_input || 0);
    }, 0);
});

const nonEssayScore = computed(() => {
    return props.result.nilai_non_essay || 0;
});

const estimatedTotal = computed(() => {
    const total = currentEssayScore.value + nonEssayScore.value;
    const maxScore = props.result.total_bobot || 100;
    return Math.round((total / maxScore) * 100);
});

const isImage = (url) => {
    return /\.(jpg|jpeg|png|gif|webp)$/i.test(url);
};

const saveGrade = (jawaban) => {
    if (jawaban.nilai_input === null || jawaban.nilai_input === '') {
        toast.error('Masukkan nilai terlebih dahulu');
        return;
    }

    if (jawaban.nilai_input > jawaban.soal?.bobot) {
        toast.error(`Nilai tidak boleh lebih dari ${jawaban.soal?.bobot}`);
        return;
    }

    savingId.value = jawaban.id;

    router.post(route('hasil.save-grade', [props.result.id, jawaban.id]), {
        nilai: jawaban.nilai_input,
        catatan: jawaban.catatan
    }, {
        onSuccess: () => {
            toast.success('Nilai berhasil disimpan');
            jawaban.nilai = jawaban.nilai_input;
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal menyimpan nilai');
        },
        onFinish: () => {
            savingId.value = null;
        }
    });
};

const finishGrading = () => {
    if (!allGraded.value) {
        toast.error('Nilai semua soal essay terlebih dahulu');
        return;
    }

    router.post(route('hasil.finish-grading', props.result.id), {}, {
        onSuccess: () => {
            toast.success('Penilaian selesai. Nilai total telah dihitung.');
            router.visit(route('hasil.show', props.result.id));
        },
        onError: (errors) => {
            toast.error(errors.message || 'Gagal menyelesaikan penilaian');
        }
    });
};
</script>

<style scoped>
.student-header {
    padding: 1rem 0;
}

.essay-list {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.essay-item {
    border: 2px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: hidden;
}

.essay-item.graded {
    border-color: #28a745;
    background: #f0fff4;
}

.essay-header {
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

.essay-content {
    padding: 1.5rem;
}

.soal-pertanyaan {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.kunci-jawaban {
    padding: 1rem;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 0.25rem;
}

.kunci-content {
    white-space: pre-wrap;
    line-height: 1.6;
}

.jawaban-siswa {
    padding: 1rem;
    background: #e7f3ff;
    border-left: 4px solid #007bff;
    border-radius: 0.25rem;
}

.jawaban-content {
    white-space: pre-wrap;
    line-height: 1.6;
    min-height: 100px;
}

.grading-form {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border: 2px dashed #dee2e6;
}

.quick-score-buttons {
    display: flex;
    gap: 0.5rem;
}

.soal-pembahasan {
    padding: 1rem;
    background: #d4edda;
    border-left: 4px solid #28a745;
    border-radius: 0.25rem;
}

.progress-section {
    padding: 1rem 0;
}

.progress-stats {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #dee2e6;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-weight: 600;
    color: #6c757d;
}

.summary-section {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
}

.summary-label {
    font-weight: 600;
    color: #6c757d;
}

.tips-list {
    padding-left: 1.5rem;
}

.tips-list li {
    margin-bottom: 0.75rem;
}
</style>
