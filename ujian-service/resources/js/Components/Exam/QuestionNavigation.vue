<template>
    <Card>
        <template #header>
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>
                Navigasi Soal
            </h5>
        </template>

        <!-- Progress Summary -->
        <div class="progress-summary mb-3">
            <div class="row text-center">
                <div class="col-4">
                    <div class="summary-item">
                        <div class="summary-value text-primary">{{ totalQuestions }}</div>
                        <div class="summary-label">Total</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="summary-item">
                        <div class="summary-value text-success">{{ answeredCount }}</div>
                        <div class="summary-label">Terjawab</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="summary-item">
                        <div class="summary-value text-secondary">{{ unansweredCount }}</div>
                        <div class="summary-label">Belum</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress mb-3" style="height: 8px;">
            <div 
                class="progress-bar bg-success" 
                role="progressbar" 
                :style="{ width: `${progress}%` }"
                :aria-valuenow="progress"
                aria-valuemin="0"
                aria-valuemax="100"
            ></div>
        </div>

        <!-- Question Grid -->
        <div class="question-grid">
            <button
                v-for="(question, index) in questions"
                :key="question.id"
                type="button"
                class="question-number-btn"
                :class="getQuestionClass(question, index)"
                @click="navigateToQuestion(index)"
            >
                {{ index + 1 }}
            </button>
        </div>

        <!-- Legend -->
        <div class="legend mt-3 pt-3 border-top">
            <div class="legend-item">
                <span class="legend-badge badge-current"></span>
                <span class="legend-label">Soal Aktif</span>
            </div>
            <div class="legend-item">
                <span class="legend-badge badge-answered"></span>
                <span class="legend-label">Terjawab</span>
            </div>
            <div class="legend-item">
                <span class="legend-badge badge-unanswered"></span>
                <span class="legend-label">Belum Dijawab</span>
            </div>
        </div>
    </Card>
</template>

<script setup>
import { computed } from 'vue';
import { Card } from '@/Components';

const props = defineProps({
    questions: {
        type: Array,
        required: true
    },
    currentIndex: {
        type: Number,
        required: true
    },
    answers: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['navigate']);

const totalQuestions = computed(() => props.questions.length);

const answeredCount = computed(() => {
    return props.questions.filter(q => isAnswered(q.id)).length;
});

const unansweredCount = computed(() => {
    return totalQuestions.value - answeredCount.value;
});

const progress = computed(() => {
    if (totalQuestions.value === 0) return 0;
    return (answeredCount.value / totalQuestions.value) * 100;
});

const isAnswered = (questionId) => {
    return questionId in props.answers && props.answers[questionId] !== null && props.answers[questionId] !== '';
};

const getQuestionClass = (question, index) => {
    const classes = [];
    
    if (index === props.currentIndex) {
        classes.push('current');
    } else if (isAnswered(question.id)) {
        classes.push('answered');
    } else {
        classes.push('unanswered');
    }
    
    return classes.join(' ');
};

const navigateToQuestion = (index) => {
    emit('navigate', index);
};
</script>

<style scoped>
.progress-summary {
    padding: 0.5rem 0;
}

.summary-item {
    padding: 0.5rem;
}

.summary-value {
    font-size: 1.5rem;
    font-weight: bold;
    line-height: 1;
}

.summary-label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.question-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(45px, 1fr));
    gap: 0.5rem;
}

.question-number-btn {
    width: 100%;
    aspect-ratio: 1;
    border: 2px solid;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.question-number-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.question-number-btn.current {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.question-number-btn.answered {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.question-number-btn.unanswered {
    background-color: white;
    border-color: #dee2e6;
    color: #6c757d;
}

.legend {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.legend-badge {
    width: 20px;
    height: 20px;
    border-radius: 0.25rem;
    border: 2px solid;
}

.legend-badge.badge-current {
    background-color: #007bff;
    border-color: #007bff;
}

.legend-badge.badge-answered {
    background-color: #28a745;
    border-color: #28a745;
}

.legend-badge.badge-unanswered {
    background-color: white;
    border-color: #dee2e6;
}

.legend-label {
    color: #6c757d;
}
</style>
