<template>
    <div class="form-group" :class="{ 'has-error': hasError }">
        <label v-if="label" :for="id" :class="{ 'required': required }">
            {{ label }}
        </label>
        
        <textarea
            :id="id"
            :class="textareaClasses"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :required="required"
            :rows="rows"
            :maxlength="maxlength"
            @input="handleInput"
            @blur="handleBlur"
            @focus="handleFocus"
        ></textarea>
        
        <div v-if="showCharCount && maxlength" class="d-flex justify-content-between">
            <small v-if="help && !hasError" class="form-text text-muted">
                {{ help }}
            </small>
            <small class="form-text text-muted">
                {{ charCount }} / {{ maxlength }}
            </small>
        </div>
        <small v-else-if="help && !hasError" class="form-text text-muted">
            {{ help }}
        </small>
        
        <span v-if="hasError" class="error invalid-feedback d-block">
            {{ errorMessage }}
        </span>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    label: {
        type: String,
        default: null
    },
    placeholder: {
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
    readonly: {
        type: Boolean,
        default: false
    },
    required: {
        type: Boolean,
        default: false
    },
    rows: {
        type: [String, Number],
        default: 3
    },
    maxlength: {
        type: [String, Number],
        default: null
    },
    showCharCount: {
        type: Boolean,
        default: false
    },
    id: {
        type: String,
        default: () => `textarea-${Math.random().toString(36).substr(2, 9)}`
    }
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const isFocused = ref(false);

const hasError = computed(() => !!props.error);

const errorMessage = computed(() => props.error);

const charCount = computed(() => {
    return props.modelValue ? props.modelValue.length : 0;
});

const textareaClasses = computed(() => {
    const classes = ['form-control'];
    
    if (hasError.value) {
        classes.push('is-invalid');
    }
    
    return classes.join(' ');
});

const handleInput = (event) => {
    emit('update:modelValue', event.target.value);
};

const handleBlur = (event) => {
    isFocused.value = false;
    emit('blur', event);
};

const handleFocus = (event) => {
    isFocused.value = true;
    emit('focus', event);
};
</script>

<style scoped>
.required::after {
    content: ' *';
    color: #dc3545;
}
</style>
