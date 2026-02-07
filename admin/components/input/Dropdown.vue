<template>
    <div class="dlm-dropdown-wrapper">
        <label v-if="label" :for="id" class="dlm-dd-label">
            {{ label }}
            <span v-if="required" class="dlm-required">*</span>
        </label>

        <div ref="containerRef" class="dlm-dd-container" :class="{ 'is-disabled': disabled }">
            <!-- Control -->
            <div
                class="dlm-dd-control"
                :class="controlClass"
                @mousedown.prevent="toggle"
            >
                <span v-if="selectedLabel" class="dlm-dd-value">{{ selectedLabel }}</span>
                <span v-else class="dlm-dd-placeholder">{{ placeholder }}</span>
                <svg viewBox="0 0 20 20" fill="currentColor" class="dlm-dd-chevron">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                </svg>
            </div>

            <!-- Options list -->
            <ul v-if="isOpen" ref="optionsListRef" class="dlm-dd-options">
                <li
                    v-if="placeholder"
                    class="dlm-dd-option is-placeholder"
                    :class="{ 'is-highlighted': highlightedIndex === -1 }"
                    @mousedown.prevent
                >
                    {{ placeholder }}
                </li>
                <li
                    v-for="(option, index) in normalizedOptions"
                    :key="option.value"
                    class="dlm-dd-option"
                    :class="{
                        'is-highlighted': index === highlightedIndex,
                        'is-selected': String(option.value) === String(modelValue),
                        'is-disabled': option.disabled,
                    }"
                    @mousedown.prevent="selectOption(option)"
                    @mouseenter="highlightedIndex = index"
                >
                    {{ option.label }}
                </li>
            </ul>
        </div>

        <p v-if="hint" class="dlm-form-hint">{{ hint }}</p>
        <p v-if="error" class="dlm-form-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

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

const emit = defineEmits(['update:modelValue'])

const containerRef = ref(null)
const optionsListRef = ref(null)
const isOpen = ref(false)
const highlightedIndex = ref(-1)

const normalizedOptions = computed(() => {
    return props.options.map(option => {
        if (typeof option === 'object') {
            return option
        }
        return { value: option, label: option }
    })
})

const selectedLabel = computed(() => {
    const opt = normalizedOptions.value.find(o => String(o.value) === String(props.modelValue))
    return opt ? opt.label : ''
})

const controlClass = computed(() => ({
    'is-open': isOpen.value,
    'is-error': !!props.error,
    'is-focused': isOpen.value,
}))

function toggle() {
    if (props.disabled) return
    if (isOpen.value) {
        close()
    } else {
        open()
    }
}

function open() {
    if (props.disabled) return
    isOpen.value = true
    // Highlight current selection
    const idx = normalizedOptions.value.findIndex(o => String(o.value) === String(props.modelValue))
    highlightedIndex.value = idx
    nextTick(() => {
        scrollToHighlighted()
    })
}

function close() {
    isOpen.value = false
    highlightedIndex.value = -1
}

function selectOption(option) {
    if (option.disabled) return
    emit('update:modelValue', option.value)
    close()
}

function scrollToHighlighted() {
    nextTick(() => {
        const list = optionsListRef.value
        if (!list || highlightedIndex.value < 0) return
        // Account for placeholder li if present
        const offset = props.placeholder ? 1 : 0
        const item = list.children[highlightedIndex.value + offset]
        if (item) item.scrollIntoView({ block: 'nearest' })
    })
}

function onKeydown(e) {
    if (!isOpen.value) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === ' ') {
            e.preventDefault()
            open()
        }
        return
    }

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault()
            if (highlightedIndex.value < normalizedOptions.value.length - 1) {
                highlightedIndex.value++
                scrollToHighlighted()
            }
            break
        case 'ArrowUp':
            e.preventDefault()
            if (highlightedIndex.value > 0) {
                highlightedIndex.value--
                scrollToHighlighted()
            }
            break
        case 'Enter':
        case ' ':
            e.preventDefault()
            if (highlightedIndex.value >= 0 && highlightedIndex.value < normalizedOptions.value.length) {
                selectOption(normalizedOptions.value[highlightedIndex.value])
            }
            break
        case 'Escape':
            e.preventDefault()
            close()
            break
    }
}

function onClickOutside(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        close()
    }
}

onMounted(() => {
    document.addEventListener('mousedown', onClickOutside)
    document.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
    document.removeEventListener('mousedown', onClickOutside)
    document.removeEventListener('keydown', onKeydown)
})
</script>

<style lang="scss" scoped>
.dlm-dropdown-wrapper {
    @apply dlm-w-full;
}

.dlm-dd-label {
    @apply dlm-block dlm-text-sm dlm-font-medium dlm-text-gray-700 dlm-mb-1;
}

.dlm-required {
    @apply dlm-text-danger-600;
}

.dlm-dd-container {
    @apply dlm-relative;

    &.is-disabled {
        @apply dlm-opacity-50 dlm-cursor-not-allowed;
    }
}

.dlm-dd-control {
    @apply dlm-flex dlm-items-center dlm-justify-between;
    @apply dlm-w-full dlm-min-h-[38px] dlm-px-3 dlm-py-1.5;
    @apply dlm-bg-white dlm-rounded-md;
    @apply dlm-text-sm dlm-cursor-pointer;
    border: 1px solid #d1d5db;
    transition: border-color 0.15s, box-shadow 0.15s;

    &.is-focused {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
    }

    &.is-error {
        border-color: #ef4444;

        &.is-focused {
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.3);
        }
    }

    .is-disabled & {
        @apply dlm-bg-gray-100 dlm-cursor-not-allowed;
    }
}

.dlm-dd-value {
    @apply dlm-flex-1 dlm-truncate dlm-text-gray-900;
}

.dlm-dd-placeholder {
    @apply dlm-flex-1 dlm-truncate dlm-text-gray-400;
}

.dlm-dd-chevron {
    @apply dlm-w-5 dlm-h-5 dlm-text-gray-400 dlm-flex-shrink-0 dlm-ml-2;
    transition: transform 0.15s;

    .is-open & {
        transform: rotate(180deg);
    }
}

.dlm-dd-options {
    @apply dlm-absolute dlm-z-50 dlm-left-0 dlm-right-0 dlm-mt-1;
    @apply dlm-bg-white dlm-rounded-md dlm-shadow-lg;
    @apply dlm-overflow-hidden dlm-m-0 dlm-p-0;
    border: 1px solid #d1d5db;
    list-style: none;
    max-height: 200px;
    overflow-y: auto;
}

.dlm-dd-option {
    @apply dlm-px-3 dlm-py-2 dlm-text-sm dlm-text-gray-900 dlm-cursor-pointer;
    transition: background-color 0.1s;

    &.is-highlighted {
        @apply dlm-bg-primary-50 dlm-text-primary-700;
    }

    &.is-selected {
        @apply dlm-bg-primary-50 dlm-font-medium;
    }

    &.is-highlighted.is-selected {
        @apply dlm-bg-primary-100;
    }

    &.is-disabled {
        @apply dlm-text-gray-400 dlm-cursor-not-allowed;
    }

    &.is-placeholder {
        @apply dlm-text-gray-400 dlm-cursor-default;
    }
}

.dlm-form-hint {
    @apply dlm-text-xs dlm-text-gray-500 dlm-mt-1;
}

.dlm-form-error {
    @apply dlm-text-sm dlm-text-danger-600 dlm-mt-1;
}
</style>
