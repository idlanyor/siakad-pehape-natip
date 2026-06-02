import { ref, computed } from 'vue';
import axios from 'axios';

export function useExam() {
    const exam = ref(null);
    const session = ref(null);
    const questions = ref([]);
    const answers = ref({});
    const currentQuestionIndex = ref(0);
    const loading = ref(false);
    const saving = ref(false);
    const error = ref(null);

    const currentQuestion = computed(() => {
        return questions.value[currentQuestionIndex.value] || null;
    });

    const totalQuestions = computed(() => questions.value.length);

    const answeredCount = computed(() => {
        return Object.keys(answers.value).length;
    });

    const unansweredCount = computed(() => {
        return totalQuestions.value - answeredCount.value;
    });

    const progress = computed(() => {
        if (totalQuestions.value === 0) return 0;
        return (answeredCount.value / totalQuestions.value) * 100;
    });

    const isAnswered = (questionId) => {
        return questionId in answers.value && answers.value[questionId] !== null;
    };

    const isCurrentAnswered = computed(() => {
        return currentQuestion.value ? isAnswered(currentQuestion.value.id) : false;
    });

    const canNavigateNext = computed(() => {
        return currentQuestionIndex.value < totalQuestions.value - 1;
    });

    const canNavigatePrev = computed(() => {
        return currentQuestionIndex.value > 0;
    });

    const startExam = async (ujianId) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post(`/api/sesi/start/${ujianId}`);
            
            exam.value = response.data.data.ujian;
            session.value = response.data.data.sesi;
            questions.value = response.data.data.soal;
            
            // Initialize answers from existing session if any
            if (session.value.jawaban) {
                answers.value = { ...session.value.jawaban };
            }

            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to start exam';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const loadSession = async (sesiId) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(`/api/sesi/${sesiId}`);
            
            exam.value = response.data.data.ujian;
            session.value = response.data.data.sesi;
            questions.value = response.data.data.soal;
            
            // Load existing answers
            if (session.value.jawaban) {
                answers.value = { ...session.value.jawaban };
            }

            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load session';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const saveAnswer = async (questionId, answer) => {
        // Update local state immediately
        answers.value[questionId] = answer;

        saving.value = true;
        error.value = null;

        try {
            const response = await axios.post(`/api/sesi/${session.value.id}/jawab`, {
                soal_id: questionId,
                jawaban: answer
            });

            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to save answer';
            // Don't throw, allow retry
            return null;
        } finally {
            saving.value = false;
        }
    };

    const submitExam = async () => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.post(`/api/sesi/${session.value.id}/submit`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to submit exam';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const navigateToQuestion = (index) => {
        if (index >= 0 && index < totalQuestions.value) {
            currentQuestionIndex.value = index;
        }
    };

    const navigateNext = () => {
        if (canNavigateNext.value) {
            currentQuestionIndex.value++;
        }
    };

    const navigatePrev = () => {
        if (canNavigatePrev.value) {
            currentQuestionIndex.value--;
        }
    };

    const getQuestionStatus = (questionId) => {
        if (isAnswered(questionId)) {
            return 'answered';
        }
        return 'unanswered';
    };

    const reset = () => {
        exam.value = null;
        session.value = null;
        questions.value = [];
        answers.value = {};
        currentQuestionIndex.value = 0;
        loading.value = false;
        saving.value = false;
        error.value = null;
    };

    return {
        // State
        exam,
        session,
        questions,
        answers,
        currentQuestionIndex,
        loading,
        saving,
        error,

        // Computed
        currentQuestion,
        totalQuestions,
        answeredCount,
        unansweredCount,
        progress,
        isCurrentAnswered,
        canNavigateNext,
        canNavigatePrev,

        // Methods
        startExam,
        loadSession,
        saveAnswer,
        submitExam,
        navigateToQuestion,
        navigateNext,
        navigatePrev,
        isAnswered,
        getQuestionStatus,
        reset
    };
}
