<template>
    <div class="form-group">
        <div class="form-check" :class="{ 'form-check-inline': inline }">
            <input
                :id="id"
                type="checkbox"
                class="form-check-input"
                :class="{ 'is-invalid': hasError }"
                :checked="isChecked"
                :value="value"
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
        type: [Boolean, Array],
        default: false
    },
    value: {
        type: [String, Number, Boolean],
        default: true
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
        default: () => `checkbox-${Math.random().toString(36).substr(2, 9)}`
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const hasError = computed(() => !!props.error);

const errorMessage = computed(() => props.error);

const isChecked = computed(() => {
    if (Array.isArray(props.modelValue)) {
        return props.modelValue.includes(props.value);
    }
    return props.modelValue === props.value || props.modelValue === true;
});

const handleChange = (event) => {
    const isChecked = event.target.checked;
    
    if (Array.isArray(props.modelValue)) {
        const newValue = [...props.modelValue];
        
        if (isChecked) {
            newValue.push(props.value);
        } else {
            const index = newValue.indexOf(props.value);
            if (index > -1) {
                newValue.splice(index, 1);
            }
        }
        
        emit('update:modelValue', newValue);
        emit('change', newValue);
    } else {
        emit('update:modelValue', isChecked);
        emit('change', isChecked);
    }
};
</script>
