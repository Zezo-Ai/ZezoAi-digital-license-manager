<template>
    <div class="dlm-dropdown-wrapper">
        <label v-if="label" :for="id">
            {{ label }}
            <span v-if="required" class="dlm-required">*</span>
        </label>

        <select
            :id="id"
            :value="modelValue"
            :disabled="disabled"
            :required="required"
            class="dlm-select"
            :class="selectClass"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
            <option
                v-for="option in normalizedOptions"
                :key="option.value"
                :value="option.value"
                :disabled="option.disabled"
            >
                {{ option.label }}
            </option>
        </select>

        <p v-if="hint" class="dlm-form-hint">{{ hint }}</p>
        <p v-if="error" class="dlm-form-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    id: {
        type: String,
        default: () => `select-${Math.random().toString(36).substring(7)}`,
    },
    modelValue: {
        type: [String, Number, Boolean],
        default: '',
    },
    options: {
        type: Array,
        required: true,
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
    required: {
        type: Boolean,
        default: false,
    },
})

defineEmits(['update:modelValue'])

const normalizedOptions = computed(() => {
    return props.options.map(option => {
        if (typeof option === 'object') {
            return option
        }
        return { value: option, label: option }
    })
})

const selectClass = computed(() => ({
    'dlm-select-error': props.error,
}))
</script>

<style lang="scss" scoped>
.dlm-dropdown-wrapper {
    @apply dlm-w-full;
}

.dlm-required {
    @apply dlm-text-danger-600;
}

.dlm-select-error {
    @apply dlm-border-danger-500 focus:dlm-ring-danger-500 focus:dlm-border-danger-500;
}

.dlm-form-error {
    @apply dlm-text-sm dlm-text-danger-600 dlm-mt-1;
}
</style>
