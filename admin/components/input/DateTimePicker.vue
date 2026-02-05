<template>
    <div class="dlm-datetime-wrapper">
        <label v-if="label" :for="id">
            {{ label }}
            <span v-if="required" class="dlm-required">*</span>
        </label>

        <div class="dlm-datetime-input-wrapper">
            <input
                ref="inputRef"
                :id="id"
                type="text"
                :value="displayValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                class="dlm-input"
                readonly
            />
            <button
                v-if="modelValue && clearable"
                type="button"
                class="dlm-datetime-clear"
                @click="clear"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
            </button>
        </div>

        <p v-if="hint" class="dlm-form-hint">{{ hint }}</p>
        <p v-if="error" class="dlm-form-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

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
    dateFormat: {
        type: String,
        default: 'Y-m-d H:i:S',
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

const inputRef = ref(null)
let flatpickrInstance = null

const displayValue = computed(() => {
    if (!props.modelValue) return ''
    try {
        const date = new Date(props.modelValue)
        if (isNaN(date.getTime())) return props.modelValue

        if (props.enableTime) {
            return date.toLocaleString()
        }
        return date.toLocaleDateString()
    } catch {
        return props.modelValue
    }
})

async function initFlatpickr() {
    if (!inputRef.value) return

    // Dynamically import flatpickr
    const flatpickr = (await import('flatpickr')).default
    await import('flatpickr/dist/flatpickr.min.css')

    flatpickrInstance = flatpickr(inputRef.value, {
        enableTime: props.enableTime,
        dateFormat: props.dateFormat,
        defaultDate: props.modelValue || null,
        minDate: props.minDate,
        maxDate: props.maxDate,
        time_24hr: true,
        allowInput: false,
        onChange: (selectedDates, dateStr) => {
            emit('update:modelValue', dateStr)
        },
    })
}

function clear() {
    if (flatpickrInstance) {
        flatpickrInstance.clear()
    }
    emit('update:modelValue', '')
}

watch(() => props.modelValue, (newValue) => {
    if (flatpickrInstance && newValue !== flatpickrInstance.input.value) {
        flatpickrInstance.setDate(newValue, false)
    }
})

watch(() => props.disabled, (disabled) => {
    if (flatpickrInstance) {
        flatpickrInstance.set('clickOpens', !disabled)
    }
})

onMounted(() => {
    initFlatpickr()
})

onUnmounted(() => {
    if (flatpickrInstance) {
        flatpickrInstance.destroy()
    }
})
</script>

<style lang="scss">
@import 'flatpickr/dist/flatpickr.min.css';

.dlm-datetime-wrapper {
    @apply dlm-w-full;
}

.dlm-datetime-input-wrapper {
    @apply dlm-relative;
}

.dlm-datetime-clear {
    @apply dlm-absolute dlm-right-2 dlm-top-1/2 dlm--translate-y-1/2;
    @apply dlm-p-1 dlm-rounded dlm-text-gray-400 hover:dlm-text-gray-600;
    @apply dlm-bg-transparent dlm-border-0 dlm-cursor-pointer;

    svg {
        @apply dlm-w-4 dlm-h-4;
    }
}

.dlm-required {
    @apply dlm-text-danger-600;
}

.dlm-form-error {
    @apply dlm-text-sm dlm-text-danger-600 dlm-mt-1;
}

// Flatpickr theme overrides
.flatpickr-calendar {
    @apply dlm-shadow-lg dlm-border dlm-border-gray-200 dlm-rounded-lg;

    &.arrowTop::before,
    &.arrowTop::after {
        display: none;
    }
}

.flatpickr-months {
    @apply dlm-bg-gray-50 dlm-rounded-t-lg;
}

.flatpickr-day {
    @apply dlm-rounded;

    &.selected,
    &.startRange,
    &.endRange {
        @apply dlm-bg-primary-600 dlm-border-primary-600;
    }

    &:hover {
        @apply dlm-bg-gray-100;
    }
}
</style>
