<template>
    <div class="dlm-form-group">
        <label v-if="label" :for="id">
            {{ label }}
            <span v-if="required" class="dlm-required">*</span>
        </label>

        <div class="dlm-input-wrapper" :class="{ 'dlm-has-prefix': $slots.prefix, 'dlm-has-suffix': $slots.suffix }">
            <span v-if="$slots.prefix" class="dlm-input-prefix">
                <slot name="prefix"></slot>
            </span>

            <input
                :id="id"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :required="required"
                :min="min"
                :max="max"
                :step="step"
                :maxlength="maxlength"
                :autocomplete="autocomplete"
                class="dlm-input"
                :class="inputClass"
                @input="$emit('update:modelValue', $event.target.value)"
                @blur="$emit('blur', $event)"
                @focus="$emit('focus', $event)"
            />

            <span v-if="$slots.suffix" class="dlm-input-suffix">
                <slot name="suffix"></slot>
            </span>
        </div>

        <p v-if="hint" class="dlm-form-hint">{{ hint }}</p>
        <p v-if="error" class="dlm-form-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    id: {
        type: String,
        default: () => `input-${Math.random().toString(36).substring(7)}`,
    },
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    label: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    hint: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    min: {
        type: [String, Number],
        default: undefined,
    },
    max: {
        type: [String, Number],
        default: undefined,
    },
    step: {
        type: [String, Number],
        default: undefined,
    },
    maxlength: {
        type: [String, Number],
        default: undefined,
    },
    autocomplete: {
        type: String,
        default: 'off',
    },
})

defineEmits(['update:modelValue', 'blur', 'focus'])

const inputClass = computed(() => ({
    'dlm-input-error': props.error,
}))
</script>

<style lang="scss" scoped>
.dlm-required {
    @apply dlm-text-danger-600;
}

.dlm-input-wrapper {
    @apply dlm-relative dlm-flex dlm-items-center;

    &.dlm-has-prefix .dlm-input {
        @apply dlm-pl-10;
    }

    &.dlm-has-suffix .dlm-input {
        @apply dlm-pr-10;
    }
}

.dlm-input-prefix,
.dlm-input-suffix {
    @apply dlm-absolute dlm-inset-y-0 dlm-flex dlm-items-center dlm-text-gray-400;

    svg {
        @apply dlm-w-5 dlm-h-5;
    }
}

.dlm-input-prefix {
    @apply dlm-left-3;
}

.dlm-input-suffix {
    @apply dlm-right-3;
}

.dlm-input-error {
    @apply dlm-border-danger-500 focus:dlm-ring-danger-500 focus:dlm-border-danger-500;
}

.dlm-form-error {
    @apply dlm-text-sm dlm-text-danger-600 dlm-mt-1;
}
</style>
