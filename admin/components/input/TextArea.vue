<template>
    <div class="dlm-form-group">
        <label v-if="label" :for="id">
            {{ label }}
            <span v-if="required" class="dlm-required">*</span>
        </label>

        <textarea
            :id="id"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :required="required"
            :rows="rows"
            :maxlength="maxlength"
            class="dlm-textarea dlm-w-full"
            :class="textareaClass"
            @input="$emit('update:modelValue', $event.target.value)"
            @blur="$emit('blur', $event)"
            @focus="$emit('focus', $event)"
        ></textarea>

        <p v-if="hint" class="dlm-form-hint">{{ hint }}</p>
        <p v-if="error" class="dlm-form-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    id: {
        type: String,
        default: () => `textarea-${Math.random().toString(36).substring(7)}`,
    },
    modelValue: {
        type: String,
        default: '',
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
    rows: {
        type: [String, Number],
        default: 10,
    },
    maxlength: {
        type: [String, Number],
        default: undefined,
    },
})

defineEmits(['update:modelValue', 'blur', 'focus'])

const textareaClass = computed(() => ({
    'dlm-input-error': props.error,
}))
</script>

<style lang="scss" scoped>
.dlm-required {
    @apply dlm-text-danger-600;
}

.dlm-input-error {
    @apply dlm-border-danger-500 focus:dlm-ring-danger-500 focus:dlm-border-danger-500;
}

.dlm-form-error {
    @apply dlm-text-sm dlm-text-danger-600 dlm-mt-1;
}
</style>
