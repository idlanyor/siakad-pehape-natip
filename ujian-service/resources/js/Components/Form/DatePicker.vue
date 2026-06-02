<template>
    <div class="form-group" :class="{ 'has-error': hasError }">
        <label v-if="label" :for="id" :class="{ 'required': required }">
            {{ label }}
        </label>
        
        <div :class="inputGroupClasses">
            <div v-if="showIcon" class="input-group-prepend">
                <span class="input-group-text">
                    <i class="fas fa-calendar-alt"></i>
                </span>
            </div>
            
            <input
                :id="id"
                type="date"
                :class="inputClasses"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :required="required"
                :min="min"
                :max="max"
                @input="handleInput"
                @blur="handleBlur"
                @focus="handleFocus"
            />
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
    showIcon: {
        type: Boolean,
        default: true
    },
    min: {
        type: String,
        default: null
    },
    max: {
        type: String,
        default: null
    },
    id: {
        type: String,
        default: () => `datepicker-${Math.random().toString(36).substr(2, 9)}`
    }
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const isFocused = ref(false);

const hasError = computed(() => !!props.error);

const errorMessage = computed(() => props.error);

const inputGroupClasses = computed(() => {
    if (props.showIcon) {
        return 'input-group';
    }
    return null;
});

const inputClasses = computed(() => {
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
