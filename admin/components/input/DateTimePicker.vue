<template>
    <div class="dlm-datetime-wrapper">
        <label v-if="label" :for="id">
            {{ label }}
            <span v-if="required" class="dlm-required">*</span>
        </label>

        <VueDatePicker
            :uid="id"
            :model-value="internalValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :enable-time-picker="enableTime"
            :clearable="clearable"
            :min-date="minDate"
            :max-date="maxDate"
            :is-24="true"
            auto-apply
            text-input
            teleport
            @update:model-value="onUpdate"
        />

        <p v-if="hint" class="dlm-form-hint">{{ hint }}</p>
        <p v-if="error" class="dlm-form-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { VueDatePicker } from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

const props = defineProps({
    id: {
        type: String,
        default: () => `datetime-${Math.random().toString(36).substring(7)}`,
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
        default: 'Select date...',
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
    enableTime: {
        type: Boolean,
        default: true,
    },
    clearable: {
        type: Boolean,
        default: true,
    },
    minDate: {
        type: String,
        default: null,
    },
    maxDate: {
        type: String,
        default: null,
    },
})

const emit = defineEmits(['update:modelValue'])

const internalValue = computed(() => {
    if (!props.modelValue) return null
    const date = new Date(props.modelValue)
    return isNaN(date.getTime()) ? null : date
})

function pad(n) {
    return String(n).padStart(2, '0')
}

function onUpdate(value) {
    if (!value) {
        emit('update:modelValue', '')
        return
    }
    const d = value instanceof Date ? value : new Date(value)
    if (isNaN(d.getTime())) {
        emit('update:modelValue', '')
        return
    }
    const str = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`
    emit('update:modelValue', str)
}
</script>

<style lang="scss">
.dlm-datetime-wrapper {
    @apply dlm-w-full;

    .dp__theme_light {
        --dp-primary-color: #2563eb;
        --dp-primary-disabled-color: #93c5fd;
        --dp-primary-text-color: #fff;
        --dp-border-color: #d1d5db;
        --dp-border-color-hover: #9ca3af;
        --dp-border-color-focus: #2563eb;
        --dp-background-color: #fff;
        --dp-text-color: #1f2937;
        --dp-hover-color: #f3f4f6;
        --dp-hover-text-color: #1f2937;
        --dp-disabled-color: #f9fafb;
        --dp-disabled-color-text: #9ca3af;
        --dp-icon-color: #6b7280;
        --dp-danger-color: #dc2626;
        --dp-success-color: #16a34a;
        --dp-menu-border-color: #e5e7eb;
    }
}

.dlm-required {
    @apply dlm-text-danger-600;
}

.dlm-form-error {
    @apply dlm-text-sm dlm-text-danger-600 dlm-mt-1;
}
</style>
