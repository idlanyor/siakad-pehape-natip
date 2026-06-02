<template>
    <div class="form-group">
        <div class="form-check" :class="{ 'form-check-inline': inline }">
            <input
                :id="id"
                type="radio"
                class="form-check-input"
                :class="{ 'is-invalid': hasError }"
                :checked="isChecked"
                :value="value"
                :name="name"
                :disabled="disabled"
                :required="required"
                @change="handleChange"
            />
            <label :for="id" class="form-check-label">
                <slot>{{ label }}</slot>
            </label>
        </div>
        
        <small v-if="help && !hasError" class="form-text text-muted">
            {{ help }}
        </small>
        
        <span v-if="hasError" class="error invalid-feedback d-block">
            {{ errorMessage }}
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean],
        default: null
    },
    value: {
        type: [String, Number, Boolean],
        required: true
    },
    name: {
        type: String,
        required: true
    },
    label: {
        type: String,
        default: null
    },
    help: {
        type: String,
        default: null
    },
    error: {
        type: String,
        default: null
    },
    disabled: {
        type: Boolean,
        default: false
    },
    required: {
        type: Boolean,
        default: false
    },
    inline: {
        type: Boolean,
        default: false
    },
    id: {
        type: String,
        default: () => `radio-${Math.random().toString(36).substr(2, 9)}`
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const hasError = computed(() => !!props.error);

const errorMessage = computed(() => props.error);

const isChecked = computed(() => {
    return props.modelValue === props.value;
});

const handleChange = (event) => {
    if (event.target.checked) {
        emit('update:modelValue', props.value);
        emit('change', props.value);
    }
};
</script>
