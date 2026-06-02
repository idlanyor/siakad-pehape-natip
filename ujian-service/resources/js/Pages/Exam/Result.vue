<template>
    <AppLayout>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Hasil Ujian</h1>
                </div>
            </div>
        </template>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Result Card -->
                <Card>
                    <div class="text-center py-4">
                        <!-- Success Icon -->
                        <div class="result-icon mb-4">
                            <i class="fas fa-check-circle fa-5x" :class="getScoreColor(result.nilai)"></i>
                        </div>

                        <!-- Score -->
                        <h1 class="display-3 mb-2" :class="getScoreColor(result.nilai)">
                            {{ result.nilai }}
                        </h1>
                        <p class="text-muted mb-4">dari {{ result.total_bobot }} poin</p>

                        <!-- Grade Badge -->
                        <Badge :variant="getGradeVariant(result.grade)" size="lg" class="mb-4">
                            Grade: {{ result.grade }}
                        </Badge>

                        <!-- Status -->
                        <div class="result-status mb-4">
                            <h4 :class="result.status === 'lulus' ? 'text-success' : 'text-danger'">
                                {{ result.status === 'lulus' ? '✓ LULUS' : '✗ TIDAK LULUS' }}
                            </h4>
                        </div>
                    </div>

                    <!-- Exam Info -->
                    <div class="exam-info border-top pt-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="info-item">
                                    <i class="fas fa-book text-primary"></i>
                                    <div class="info-content">
                                        <div class="info-label">Ujian</div>
                                        <div class="info-value">{{ result.ujian.judul }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-item">
                                    <i class="fas fa-calendar text-info"></i>
                                    <div class="info-content">
                                        <div class="info-label">Tanggal</div>
                                        <div class="info-value">{{ formatDate(result.created_at) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-item">
                                    <i class="fas fa-clock text-warning"></i>
                                    <div class="info-content">
                                        <div class="info-label">Durasi Pengerjaan</div>
                                        <div class="info-value">{{ result.durasi_pengerjaan }} menit</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="info-item">
                                    <i class="fas fa-percentage text-success"></i>
                                    <div class="info-content">
                                        <div class="info-label">Persentase</div>
                                        <div class="info-value">{{ result.persentase }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Statistics Card -->
                <Card class="mt-4">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Statistik Jawaban
                        </h5>
                    </template>

                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stat-box">
                                <div class="stat-icon">
                                    <i class="fas fa-check-circle text-success"></i>
                                </div>
                                <h3 class="text-success">{{ result.jumlah_benar }}</h3>
                                <p class="text-muted mb-0">Benar</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-box">
                                <div class="stat-icon">
                                    <i class="fas fa-times-circle text-danger"></i>
                                </div>
                                <h3 class="text-danger">{{ result.jumlah_salah }}</h3>
                                <p class="text-muted mb-0">Salah</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-box">
                                <div class="stat-icon">
                                    <i class="fas fa-question-circle text-warning"></i>
                                </div>
                                <h3 class="text-warning">{{ result.tidak_dijawab }}</h3>
                                <p class="text-muted mb-0">Tidak Dijawab</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-box">
                                <div class="stat-icon">
                                    <i class="fas fa-list text-info"></i>
                                </div>
                                <h3 class="text-info">{{ result.total_soal }}</h3>
                                <p class="text-muted mb-0">Total Soal</p>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-3">
                        <div class="progress" style="height: 25px;">
                            <div 
                                class="progress-bar bg-success" 
                                :style="{ width: `${correctPercentage}%` }"
                            >
                                {{ correctPercentage }}%
                            </div>
                            <div 
                                class="progress-bar bg-danger" 
                                :style="{ width: `${wrongPercentage}%` }"
                            >
                                {{ wrongPercentage }}%
                            </div>
                            <div 
                                class="progress-bar bg-warning" 
                                :style="{ width: `${unansweredPercentage}%` }"
                            >
                                {{ unansweredPercentage }}%
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Review Answers (if allowed) -->
                <Card v-if="result.show_answers" class="mt-4">
                    <template #header>
                        <h5 class="mb-0">
                            <i class="fas fa-eye mr-2"></i>
                            Pembahasan Soal
                        </h5>
                    </template>

                    <Alert variant="info" icon>
                        Anda dapat melihat pembahasan soal dan jawaban yang benar.
                    </Alert>

                    <Button
                        variant="primary"
                        block
                        @click="viewReview"
                    >
                        <i class="fas fa-book-open mr-2"></i>
                        Lihat Pembahasan
                    </Button>
                </Card>

                <!-- Actions -->
                <div class="mt-4 text-center">
                    <Button
                        variant="primary"
                        @click="goToExamList"
                    >
                        <i class="fas fa-list mr-2"></i>
                        Kembali ke Daftar Ujian
                    </Button>
                    <Button
                        variant="secondary"
                        class="ml-2"
                        @click="printResult"
                    >
                        <i class="fas fa-print mr-2"></i>
                        Cetak Hasil
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, Button, Badge, Alert } from '@/Components';

const props = defineProps({
    result: {
        type: Object,
        required: true
    }
});

const correctPercentage = computed(() => {
    return Math.round((props.result.jumlah_benar / props.result.total_soal) * 100);
});

const wrongPercentage = computed(() => {
    return Math.round((props.result.jumlah_salah / props.result.total_soal) * 100);
});

const unansweredPercentage = computed(() => {
    return Math.round((props.result.tidak_dijawab / props.result.total_soal) * 100);
});

const getScoreColor = (score) => {
    const percentage = (score / props.result.total_bobot) * 100;
    if (percentage >= 80) return 'text-success';
    if (percentage >= 60) return 'text-info';
    if (percentage >= 40) return 'text-warning';
    return 'text-danger';
};

const getGradeVariant = (grade) => {
    const gradeMap = {
        'A': 'success',
        'B': 'info',
        'C': 'warning',
        'D': 'danger',
        'E': 'dark'
    };
    return gradeMap[grade] || 'secondary';
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const goToExamList = () => {
    router.visit(route('exam.list'));
};

const viewReview = () => {
    router.visit(route('exam.review', props.result.id));
};

const printResult = () => {
    window.print();
};
</script>

<style scoped>
.result-icon {
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.exam-info {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin: -1rem;
    margin-top: 1rem;
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
    font-size: 1.1rem;
    font-weight: 600;
}

.stat-box {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.stat-box h3 {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.progress-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

@media print {
    .btn,
    .card-header,
    nav,
    aside,
    footer {
        display: none !important;
    }
}
</style>
