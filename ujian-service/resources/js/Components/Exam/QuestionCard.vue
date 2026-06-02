<template>
    <Card :loading="loading">
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    Soal {{ questionNumber }} dari {{ totalQuestions }}
                </h5>
                <Badge :variant="isAnswered ? 'success' : 'secondary'">
                    {{ isAnswered ? 'Terjawab' : 'Belum Dijawab' }}
                </Badge>
            </div>
        </template>

        <!-- Question Text -->
        <div class="question-content mb-4">
            <div class="question-text" v-html="question.pertanyaan"></div>
            
            <!-- Question Media -->
            <div v-if="question.media_url" class="question-media mt-3">
                <img 
                    v-if="isImage(question.media_url)" 
                    :src="question.media_url" 
                    :alt="question.media_caption || 'Question media'"
                    class="img-fluid rounded"
                />
                <audio 
                    v-else-if="isAudio(question.media_url)"
                    :src="question.media_url"
                    controls
                    class="w-100"
                ></audio>
                <video
                    v-else-if="isVideo(question.media_url)"
                    :src="question.media_url"
                    controls
                    class="w-100 rounded"
                ></video>
                <p v-if="question.media_caption" class="text-muted small mt-2">
                    {{ question.media_caption }}
                </p>
            </div>
        </div>

        <!-- Answer Options -->
        <div class="answer-options">
            <!-- Multiple Choice -->
            <div v-if="question.tipe === 'pilihan_ganda'" class="multiple-choice">
                <div 
                    v-for="(option, index) in options"
                    :key="index"
                    class="form-check mb-3"
                >
                    <input
                        :id="`option-${index}`"
                        type="radio"
                        class="form-check-input"
                        :name="`question-${question.id}`"
                        :value="option.key"
                        :checked="modelValue === option.key"
                        :disabled="disabled"
                        @change="handleChange(option.key)"
                    />
                    <label :for="`option-${index}`" class="form-check-label">
                        <strong>{{ option.key }}.</strong> {{ option.text }}
                    </label>
                </div>
            </div>

            <!-- True/False -->
            <div v-else-if="question.tipe === 'benar_salah'" class="true-false">
                <div class="form-check mb-3">
                    <input
                        id="option-true"
                        type="radio"
                        class="form-check-input"
                        :name="`question-${question.id}`"
                        value="benar"
                        :checked="modelValue === 'benar'"
                        :disabled="disabled"
                        @change="handleChange('benar')"
                    />
                    <label for="option-true" class="form-check-label">
                        <i class="fas fa-check-circle text-success mr-2"></i>
                        <strong>Benar</strong>
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input
                        id="option-false"
                        type="radio"
                        class="form-check-input"
                        :name="`question-${question.id}`"
                        value="salah"
                        :checked="modelValue === 'salah'"
                        :disabled="disabled"
                        @change="handleChange('salah')"
                    />
                    <label for="option-false" class="form-check-label">
                        <i class="fas fa-times-circle text-danger mr-2"></i>
                        <strong>Salah</strong>
                    </label>
                </div>
            </div>

            <!-- Essay -->
            <div v-else-if="question.tipe === 'essay'" class="essay">
                <Textarea
                    :model-value="modelValue"
                    :rows="8"
                    :maxlength="5000"
                    show-char-count
                    placeholder="Tulis jawaban Anda di sini..."
                    :disabled="disabled"
                    @update:model-value="handleChange"
                />
            </div>
        </div>

        <!-- Question Info -->
        <div class="question-info mt-4 pt-3 border-top">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="fas fa-star mr-1"></i>
                        Bobot: {{ question.bobot }} poin
                    </small>
                </div>
                <div class="col-md-6 text-right">
                    <small class="text-muted">
                        <i class="fas fa-tag mr-1"></i>
                        {{ questionTypeLabel }}
                    </small>
                </div>
            </div>
        </div>
    </Card>
</template>

<script setup>
import { computed } from 'vue';
import { Card, Badge, Textarea } from '@/Components';

const props = defineProps({
    question: {
        type: Object,
        required: true
    },
    modelValue: {
        type: [String, Number, null],
        default: null
    },
    questionNumber: {
        type: Number,
        required: true
    },
    totalQuestions: {
        type: Number,
        required: true
    },
    disabled: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const options = computed(() => {
    if (props.question.tipe !== 'pilihan_ganda') return [];
    
    return [
        { key: 'A', text: props.question.pilihan_a },
        { key: 'B', text: props.question.pilihan_b },
        { key: 'C', text: props.question.pilihan_c },
        { key: 'D', text: props.question.pilihan_d },
        { key: 'E', text: props.question.pilihan_e }
    ].filter(option => option.text);
});

const isAnswered = computed(() => {
    return props.modelValue !== null && props.modelValue !== '';
});

const questionTypeLabel = computed(() => {
    const types = {
        'pilihan_ganda': 'Pilihan Ganda',
        'benar_salah': 'Benar/Salah',
        'essay': 'Essay'
    };
    return types[props.question.tipe] || props.question.tipe;
});

const isImage = (url) => {
    return /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(url);
};

const isAudio = (url) => {
    return /\.(mp3|wav|ogg|m4a)$/i.test(url);
};

const isVideo = (url) => {
    return /\.(mp4|webm|ogg)$/i.test(url);
};

const handleChange = (value) => {
    emit('update:modelValue', value);
    emit('change', value);
};
</script>

<style scoped>
.question-content {
    font-size: 1.1rem;
    line-height: 1.6;
}

.question-text {
    margin-bottom: 1rem;
}

.question-text :deep(p) {
    margin-bottom: 0.5rem;
}

.question-text :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.25rem;
    margin: 1rem 0;
}

.question-media img,
.question-media video {
    max-width: 100%;
    max-height: 400px;
    object-fit: contain;
}

.form-check {
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    transition: all 0.2s;
    cursor: pointer;
}

.form-check:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.form-check-input:checked ~ .form-check-label {
    font-weight: 600;
}

.form-check-input {
    margin-top: 0.5rem;
    cursor: pointer;
}

.form-check-label {
    cursor: pointer;
    width: 100%;
    margin-left: 0.5rem;
}

.question-info {
    background-color: #f8f9fa;
    padding: 0.75rem;
    border-radius: 0.25rem;
    margin: -1rem;
    margin-top: 1rem;
}
</style>
