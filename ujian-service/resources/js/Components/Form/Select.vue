<template>
    <div class="form-group" :class="{ 'has-error': hasError }">
        <label v-if="label" :for="id" :class="{ 'required': required }">
            {{ label }}
        </label>
        
        <select
            :id="id"
            :class="selectClasses"
            :value="modelValue"
            :disabled="disabled"
            :required="required"
            :multiple="multiple"
            @change="handleChange"
            @blur="handleBlur"
            @focus="handleFocus"
        >
            <option v-if="placeholder && !multiple" value="" disabled>
                {{ placeholder }}
            </option>
            <option
                v-for="option in normalizedOptions"
                :key="option.value"
                :value="option.value"
                :disabled="option.disabled"
            >
                {{ option.label }}
            </option>
        </select>
        
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
        type: [String, Number, Array],
        default: ''
    },
    options: {
        type: Array,
        required: true,
        validator: (value) => {
            return value.every(option => {
                return typeof option === 'object' 
                    ? 'value' in option && 'label' in option
                    : typeof option === 'string' || typeof option === 'number';
            });
        }
    },
    label: {
        type: String,
        default: null
    },
    placeholder: {
        type: String,
        default: 'Select an option'
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
    multiple: {
        type: Boolean,
        default: false
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    id: {
        type: String,
        default: () => `select-${Math.random().toString(36).substr(2, 9)}`
    }
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'change']);

const isFocused = ref(false);

const hasError = computed(() => !!props.error);

const errorMessage = computed(() => props.error);

const normalizedOptions = computed(() => {
    return props.options.map(option => {
        if (typeof option === 'object') {
            return {
                value: option.value,
                label: option.label,
                disabled: option.disabled || false
            };
        }
        return {
            value: option,
            label: option,
            disabled: false
        };
    });
});

const selectClasses = computed(() => {
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

const handleChange = (event) => {
    let value;
    
    if (props.multiple) {
        value = Array.from(event.target.selectedOptions).map(option => option.value);
    } else {
        value = event.target.value;
    }
    
    emit('update:modelValue', value);
    emit('change', value);
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
