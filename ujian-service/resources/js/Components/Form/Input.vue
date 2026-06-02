<template>
    <div class="form-group" :class="{ 'has-error': hasError }">
        <label v-if="label" :for="id" :class="{ 'required': required }">
            {{ label }}
        </label>
        
        <div :class="inputGroupClasses">
            <div v-if="$slots.prepend || prepend" class="input-group-prepend">
                <slot name="prepend">
                    <span class="input-group-text">{{ prepend }}</span>
                </slot>
            </div>
            
            <input
                :id="id"
                :type="type"
                :class="inputClasses"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :required="required"
                :min="min"
                :max="max"
                :step="step"
                :maxlength="maxlength"
                @input="handleInput"
                @blur="handleBlur"
                @focus="handleFocus"
            />
            
            <div v-if="$slots.append || append" class="input-group-append">
                <slot name="append">
                    <span class="input-group-text">{{ append }}</span>
                </slot>
            </div>
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
        type: [String, Number],
        default: ''
    },
    type: {
        type: String,
        default: 'text',
        validator: (value) => ['text', 'email', 'password', 'number', 'tel', 'url', 'search', 'date', 'time', 'datetime-local'].includes(value)
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
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    prepend: {
        type: String,
        default: null
    },
    append: {
        type: String,
        default: null
    },
    min: {
        type: [String, Number],
        default: null
    },
    max: {
        type: [String, Number],
        default: null
    },
    step: {
        type: [String, Number],
        default: null
    },
    maxlength: {
        type: [String, Number],
        default: null
    },
    id: {
        type: String,
        default: () => `input-${Math.random().toString(36).substr(2, 9)}`
    }
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const isFocused = ref(false);

const hasError = computed(() => !!props.error);

const errorMessage = computed(() => props.error);

const inputGroupClasses = computed(() => {
    if (props.prepend || props.append || props.$slots.prepend || props.$slots.append) {
        return 'input-group';
    }
    return null;
});

const inputClasses = computed(() => {
    const classes = ['form-control'];
    
    if (props.size === 'sm') {
        classes.push('form-control-sm');
    } else if (props.size === 'lg') {
        classes.push('form-control-lg');
    }
    
    if (hasError.value) {
        classes.push('is-invalid');
    }
    
    return classes.join(' ');
});

const handleInput = (event) => {
    let value = event.target.value;
    
    if (props.type === 'number') {
        value = value === '' ? null : Number(value);
    }
    
    emit('update:modelValue', value);
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
