<template>
    <AppLayout>
        <div class="exam-taking-container">
            <!-- Header with Timer -->
            <div class="exam-header sticky-top bg-white shadow-sm">
                <div class="container-fluid">
                    <div class="row align-items-center py-3">
                        <div class="col-md-4">
                            <h5 class="mb-0">{{ exam?.judul }}</h5>
                            <small class="text-muted">{{ exam?.mata_pelajaran }}</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="progress-info">
                                <div class="progress mb-2" style="height: 8px;">
                                    <div 
                                        class="progress-bar bg-success" 
                                        :style="{ width: `${examState.progress}%` }"
                                    ></div>
                                </div>
                                <small class="text-muted">
                                    {{ examState.answeredCount }} / {{ examState.totalQuestions }} soal terjawab
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <ExamTimer
                                ref="timerRef"
                                :duration="remainingTime"
                                :auto-start="true"
                                @expired="handleTimeExpired"
                                @warning="handleTimeWarning"
                                @danger="handleTimeDanger"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="container-fluid mt-4">
                <div class="row">
                    <!-- Question Area -->
                    <div class="col-lg-9">
                        <Loading v-if="examState.loading" centered overlay />
                        
                        <QuestionCard
                            v-else-if="examState.currentQuestion"
                            :question="examState.currentQuestion"
                            v-model="currentAnswer"
                            :question-number="examState.currentQuestionIndex + 1"
                            :total-questions="examState.totalQuestions"
                            @change="handleAnswerChange"
                        />

                        <!-- Navigation Buttons -->
                        <div class="navigation-buttons mt-4">
                            <div class="d-flex justify-content-between">
                                <Button
                                    variant="secondary"
                                    :disabled="!examState.canNavigatePrev"
                                    @click="navigatePrev"
                                >
                                    <i class="fas fa-chevron-left mr-2"></i>
                                    Sebelumnya
                                </Button>

                                <div class="d-flex gap-2">
                                    <Button
                                        v-if="examState.canNavigateNext"
                                        variant="primary"
                                        @click="navigateNext"
                                    >
                                        Selanjutnya
                                        <i class="fas fa-chevron-right ml-2"></i>
                                    </Button>
                                    <Button
                                        v-else
                                        variant="success"
                                        @click="showSubmitModal = true"
                                    >
                                        <i class="fas fa-check mr-2"></i>
                                        Selesai & Submit
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Auto-save Indicator -->
                        <div class="auto-save-indicator mt-3 text-center">
                            <small v-if="examState.saving" class="text-muted">
                                <i class="fas fa-sync fa-spin mr-1"></i>
                                Menyimpan jawaban...
                            </small>
                            <small v-else-if="lastSaved" class="text-success">
                                <i class="fas fa-check mr-1"></i>
                                Tersimpan {{ lastSaved }}
                            </small>
                        </div>
                    </div>

                    <!-- Sidebar Navigation -->
                    <div class="col-lg-3">
                        <div class="sticky-sidebar">
                            <QuestionNavigation
                                :questions="examState.questions"
                                :current-index="examState.currentQuestionIndex"
                                :answers="examState.answers"
                                @navigate="navigateToQuestion"
                            />

                            <!-- Submit Button -->
                            <Button
                                variant="success"
                                block
                                class="mt-3"
                                @click="showSubmitModal = true"
                            >
                                <i class="fas fa-paper-plane mr-2"></i>
                                Submit Ujian
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Confirmation Modal -->
        <Modal
            v-model:show="showSubmitModal"
            title="Konfirmasi Submit Ujian"
            size="md"
            confirm-text="Ya, Submit"
            confirm-variant="success"
            :confirm-loading="submitting"
            @confirm="handleSubmit"
        >
            <Alert variant="warning" icon>
                <strong>Perhatian!</strong> Setelah submit, Anda tidak dapat mengubah jawaban.
            </Alert>

            <div class="submit-summary mt-3">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="summary-box">
                            <h3 class="text-success">{{ examState.answeredCount }}</h3>
                            <small class="text-muted">Terjawab</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="summary-box">
                            <h3 class="text-danger">{{ examState.unansweredCount }}</h3>
                            <small class="text-muted">Belum Dijawab</small>
                        </div>
                    </div>
                </div>
            </div>

            <p class="mt-3 mb-0">Apakah Anda yakin ingin submit ujian sekarang?</p>
        </Modal>

        <!-- Time Warning Modal -->
        <Modal
            v-model:show="showTimeWarningModal"
            title="Peringatan Waktu"
            size="sm"
            :show-footer="false"
            :closable="true"
        >
            <Alert variant="warning" icon>
                <strong>Waktu tersisa kurang dari 5 menit!</strong>
                <p class="mb-0 mt-2">Segera selesaikan ujian Anda.</p>
            </Alert>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ExamTimer from '@/Components/Exam/ExamTimer.vue';
import QuestionCard from '@/Components/Exam/QuestionCard.vue';
import QuestionNavigation from '@/Components/Exam/QuestionNavigation.vue';
import { Button, Modal, Alert, Loading, useToast } from '@/Components';
import { useExam } from '@/Composables/useExam';

const props = defineProps({
    session: {
        type: Object,
        required: true
    },
    exam: {
        type: Object,
        required: true
    },
    questions: {
        type: Array,
        required: true
    },
    answers: {
        type: Object,
        default: () => ({})
    }
});

const examState = useExam();
const toast = useToast();
const timerRef = ref(null);
const showSubmitModal = ref(false);
const showTimeWarningModal = ref(false);
const submitting = ref(false);
const lastSaved = ref(null);
const autoSaveInterval = ref(null);

// Initialize exam state
examState.exam.value = props.exam;
examState.session.value = props.session;
examState.questions.value = props.questions;
examState.answers.value = { ...props.answers };

const currentAnswer = computed({
    get: () => {
        const questionId = examState.currentQuestion.value?.id;
        return questionId ? examState.answers.value[questionId] : null;
    },
    set: (value) => {
        const questionId = examState.currentQuestion.value?.id;
        if (questionId) {
            examState.answers.value[questionId] = value;
        }
    }
});

const remainingTime = computed(() => {
    const endTime = new Date(props.session.waktu_selesai).getTime();
    const now = new Date().getTime();
    const remaining = Math.max(0, Math.floor((endTime - now) / 1000));
    return remaining;
});

const handleAnswerChange = async (value) => {
    const questionId = examState.currentQuestion.value?.id;
    if (!questionId) return;

    try {
        await examState.saveAnswer(questionId, value);
        lastSaved.value = 'baru saja';
        
        setTimeout(() => {
            lastSaved.value = null;
        }, 3000);
    } catch (error) {
        toast.error('Gagal menyimpan jawaban. Akan dicoba lagi.');
    }
};

const navigateNext = () => {
    examState.navigateNext();
};

const navigatePrev = () => {
    examState.navigatePrev();
};

const navigateToQuestion = (index) => {
    examState.navigateToQuestion(index);
};

const handleSubmit = async () => {
    submitting.value = true;

    try {
        const response = await examState.submitExam();
        
        toast.success('Ujian berhasil disubmit!');
        
        // Redirect to result page
        setTimeout(() => {
            router.visit(route('exam.result', response.data.hasil_id));
        }, 1000);
    } catch (error) {
        toast.error(error.response?.data?.message || 'Gagal submit ujian');
        showSubmitModal.value = false;
    } finally {
        submitting.value = false;
    }
};

const handleTimeExpired = async () => {
    toast.warning('Waktu habis! Ujian akan otomatis disubmit.');
    
    // Auto submit
    try {
        await examState.submitExam();
        router.visit(route('exam.result', examState.session.value.id));
    } catch (error) {
        toast.error('Gagal submit ujian otomatis');
    }
};

const handleTimeWarning = () => {
    showTimeWarningModal.value = true;
    toast.warning('Waktu tersisa kurang dari 5 menit!');
};

const handleTimeDanger = () => {
    toast.error('Waktu tersisa kurang dari 1 menit!', { duration: 5000 });
};

// Auto-save every 30 seconds
const startAutoSave = () => {
    autoSaveInterval.value = setInterval(async () => {
        const questionId = examState.currentQuestion.value?.id;
        const answer = currentAnswer.value;
        
        if (questionId && answer !== null && answer !== '') {
            try {
                await examState.saveAnswer(questionId, answer);
                lastSaved.value = 'baru saja';
            } catch (error) {
                console.error('Auto-save failed:', error);
            }
        }
    }, 30000); // 30 seconds
};

// Prevent accidental page close
const handleBeforeUnload = (e) => {
    e.preventDefault();
    e.returnValue = 'Anda yakin ingin meninggalkan halaman? Jawaban yang belum tersimpan akan hilang.';
    return e.returnValue;
};

onMounted(() => {
    startAutoSave();
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
    if (autoSaveInterval.value) {
        clearInterval(autoSaveInterval.value);
    }
    window.removeEventListener('beforeunload', handleBeforeUnload);
});
</script>

<style scoped>
.exam-taking-container {
    min-height: 100vh;
    background-color: #f4f6f9;
}

.exam-header {
    z-index: 1000;
    border-bottom: 1px solid #dee2e6;
}

.sticky-sidebar {
    position: sticky;
    top: 100px;
}

.navigation-buttons {
    padding: 1rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.auto-save-indicator {
    min-height: 24px;
}

.submit-summary .summary-box {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.submit-summary h3 {
    margin-bottom: 0.25rem;
    font-weight: bold;
}

.d-flex.gap-2 {
    gap: 0.5rem;
}
</style>
