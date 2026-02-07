<template>
    <div class="dlm-async-select-wrapper">
        <label v-if="label" :for="id" class="dlm-as-label">
            {{ label }}
            <span v-if="required" class="dlm-required">*</span>
        </label>

        <div ref="containerRef" class="dlm-as-container" :class="{ 'is-disabled': disabled }">
            <!-- Control area -->
            <div
                class="dlm-as-control"
                :class="controlClass"
                @mousedown.prevent="onControlClick"
            >
                <!-- Multi mode: tags + inline input -->
                <template v-if="multiple">
                    <span
                        v-for="tag in selectedTags"
                        :key="tag.value"
                        class="dlm-as-tag"
                    >
                        <span class="dlm-as-tag-text">{{ tag.label }}</span>
                        <button
                            v-if="!disabled"
                            type="button"
                            class="dlm-as-tag-remove"
                            @mousedown.prevent.stop="removeTag(tag.value)"
                        >
                            <svg viewBox="0 0 20 20" fill="currentColor" class="dlm-as-tag-remove-icon">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </span>
                    <input
                        ref="searchInputRef"
                        v-model="searchQuery"
                        type="text"
                        class="dlm-as-inline-input"
                        :placeholder="selectedTags.length === 0 ? placeholder : ''"
                        :disabled="disabled"
                        @input="onSearchInput"
                        @keydown="onKeydown"
                        @focus="onFocus"
                    />
                </template>

                <!-- Single mode: display value + clear + chevron -->
                <template v-else>
                    <span v-if="selectedLabel" class="dlm-as-single-value">{{ selectedLabel }}</span>
                    <span v-else class="dlm-as-placeholder">{{ placeholder }}</span>
                    <div class="dlm-as-actions">
                        <button
                            v-if="selectedLabel && !disabled"
                            type="button"
                            class="dlm-as-clear"
                            @mousedown.prevent.stop="clearSelection"
                        >
                            <svg viewBox="0 0 20 20" fill="currentColor" class="dlm-as-clear-icon">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                        <svg viewBox="0 0 20 20" fill="currentColor" class="dlm-as-chevron">
                            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </template>
            </div>

            <!-- Dropdown -->
            <div v-if="isOpen" class="dlm-as-dropdown">
                <!-- Search input (single mode only) -->
                <div v-if="!multiple" class="dlm-as-search-wrapper">
                    <input
                        ref="searchInputRef"
                        v-model="searchQuery"
                        type="text"
                        class="dlm-as-search-input"
                        placeholder="Type to search..."
                        @input="onSearchInput"
                        @keydown="onKeydown"
                    />
                </div>

                <!-- Options list -->
                <ul ref="optionsListRef" class="dlm-as-options" @scroll="onOptionsScroll">
                    <!-- Min chars hint -->
                    <li v-if="searchQuery.length > 0 && searchQuery.length < minChars" class="dlm-as-hint">
                        Type at least {{ minChars }} characters to search
                    </li>

                    <!-- Loading -->
                    <li v-else-if="isLoading && options.length === 0" class="dlm-as-loading">
                        <svg class="dlm-as-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="dlm-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="dlm-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Searching...
                    </li>

                    <!-- No results -->
                    <li v-else-if="!isLoading && options.length === 0 && searchQuery.length >= minChars" class="dlm-as-no-results">
                        No results found
                    </li>

                    <!-- Options -->
                    <template v-else>
                        <li
                            v-for="(option, index) in options"
                            :key="option[valueField]"
                            class="dlm-as-option"
                            :class="{
                                'is-highlighted': index === highlightedIndex,
                                'is-selected': isOptionSelected(option),
                            }"
                            @mousedown.prevent="selectOption(option)"
                            @mouseenter="highlightedIndex = index"
                        >
                            {{ option[labelField] }}
                        </li>

                        <!-- Load more spinner -->
                        <li v-if="isLoading && options.length > 0" class="dlm-as-loading">
                            <svg class="dlm-as-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="dlm-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="dlm-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Loading more...
                        </li>
                    </template>

                    <!-- Empty state when no search yet -->
                    <li v-if="searchQuery.length === 0" class="dlm-as-hint">
                        Type to search...
                    </li>
                </ul>
            </div>
        </div>

        <p v-if="hint" class="dlm-form-hint">{{ hint }}</p>
        <p v-if="error" class="dlm-form-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { getAjaxUrl, getNonce, getDropdownNonce } from '../../utils/useRequest'

const props = defineProps({
    id: {
        type: String,
        default: () => `async-select-${Math.random().toString(36).substring(7)}`,
    },
    modelValue: {
        type: [String, Number, Array],
        default: null,
    },
    searchAction: {
        type: String,
        default: '',
    },
    searchType: {
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
    required: {
        type: Boolean,
        default: false,
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    initialOption: {
        type: Object,
        default: null,
    },
    initialOptions: {
        type: Array,
        default: () => [],
    },
    minChars: {
        type: Number,
        default: 2,
    },
})

const emit = defineEmits(['update:modelValue'])

// --- Refs ---
const containerRef = ref(null)
const searchInputRef = ref(null)
const optionsListRef = ref(null)

const isOpen = ref(false)
const isLoading = ref(false)
const searchQuery = ref('')
const options = ref([])
const highlightedIndex = ref(-1)
const nextPageUrl = ref(null)

// Store known options (for displaying selected items)
const knownOptions = ref(new Map())

// Debounce timer
let debounceTimer = null
// Current search ID for race condition handling
let currentSearchId = 0

// --- Computed ---
const isLegacy = computed(() => !!props.searchType)
const valueField = computed(() => isLegacy.value ? 'id' : 'value')
const labelField = computed(() => isLegacy.value ? 'text' : 'label')

const controlClass = computed(() => ({
    'is-open': isOpen.value,
    'is-error': !!props.error,
    'is-focused': isOpen.value,
}))

// Single mode: label of selected option
const selectedLabel = computed(() => {
    if (props.multiple || !props.modelValue) return ''
    const known = knownOptions.value.get(String(props.modelValue))
    return known ? known[labelField.value] : ''
})

// Multi mode: tags for selected options
const selectedTags = computed(() => {
    if (!props.multiple || !Array.isArray(props.modelValue)) return []
    return props.modelValue.map(val => {
        const known = knownOptions.value.get(String(val))
        return {
            value: val,
            label: known ? known[labelField.value] : val,
        }
    })
})

// --- Methods ---
function open() {
    if (props.disabled || isOpen.value) return
    isOpen.value = true
    highlightedIndex.value = -1
    nextTick(() => {
        searchInputRef.value?.focus()
    })
}

function close() {
    isOpen.value = false
    searchQuery.value = ''
    options.value = []
    highlightedIndex.value = -1
    nextPageUrl.value = null
}

function onControlClick() {
    if (props.disabled) return
    if (isOpen.value && !props.multiple) {
        close()
    } else {
        open()
    }
}

function onFocus() {
    if (!isOpen.value) {
        open()
    }
}

function onSearchInput() {
    highlightedIndex.value = -1
    if (debounceTimer) clearTimeout(debounceTimer)

    if (searchQuery.value.length < props.minChars) {
        options.value = []
        nextPageUrl.value = null
        return
    }

    debounceTimer = setTimeout(() => {
        fetchOptions(searchQuery.value)
    }, 300)
}

async function fetchOptions(query, append = false) {
    const searchId = ++currentSearchId
    isLoading.value = true

    if (!append) {
        nextPageUrl.value = null
    }

    const ajaxUrl = getAjaxUrl()
    const nonce = getNonce()
    const dropdownNonce = getDropdownNonce()

    let url
    if (append && nextPageUrl.value) {
        url = nextPageUrl.value
    } else if (isLegacy.value) {
        url = `${ajaxUrl}?action=dlm_dropdown_search&security=${dropdownNonce}&type=${props.searchType}&term=${encodeURIComponent(query)}`
    } else {
        url = `${ajaxUrl}?action=${props.searchAction}&_wpnonce=${nonce}&search=${encodeURIComponent(query)}`
    }

    try {
        const response = await fetch(url)
        const json = await response.json()

        // Discard stale response
        if (searchId !== currentSearchId) return

        let results = []
        if (isLegacy.value) {
            results = json.results || []
            if (json.pagination && json.pagination.more) {
                const nextPage = (json.pagination.current || 1) + 1
                nextPageUrl.value = `${ajaxUrl}?action=dlm_dropdown_search&security=${dropdownNonce}&type=${props.searchType}&term=${encodeURIComponent(query)}&page=${nextPage}`
            } else {
                nextPageUrl.value = null
            }
        } else {
            if (json.success && json.data && json.data.results) {
                results = json.data.results
            }
            nextPageUrl.value = null
        }

        // Store in known options
        results.forEach(opt => {
            knownOptions.value.set(String(opt[valueField.value]), opt)
        })

        if (append) {
            options.value = [...options.value, ...results]
        } else {
            options.value = results
        }
    } catch {
        if (searchId !== currentSearchId) return
        if (!append) {
            options.value = []
        }
    } finally {
        if (searchId === currentSearchId) {
            isLoading.value = false
        }
    }
}

function selectOption(option) {
    const val = option[valueField.value]
    knownOptions.value.set(String(val), option)

    if (props.multiple) {
        const current = Array.isArray(props.modelValue) ? [...props.modelValue] : []
        const idx = current.indexOf(val)
        if (idx === -1) {
            current.push(val)
        } else {
            current.splice(idx, 1)
        }
        emit('update:modelValue', current)
        // Keep dropdown open, reset search for next pick
        searchQuery.value = ''
        options.value = []
        nextTick(() => {
            searchInputRef.value?.focus()
        })
    } else {
        emit('update:modelValue', val)
        close()
    }
}

function removeTag(val) {
    if (props.disabled) return
    const current = Array.isArray(props.modelValue) ? [...props.modelValue] : []
    const idx = current.indexOf(val)
    if (idx !== -1) {
        current.splice(idx, 1)
    }
    emit('update:modelValue', current)
}

function clearSelection() {
    emit('update:modelValue', props.multiple ? [] : null)
    close()
}

function isOptionSelected(option) {
    const val = option[valueField.value]
    if (props.multiple) {
        return Array.isArray(props.modelValue) && props.modelValue.includes(val)
    }
    return props.modelValue === val
}

function onKeydown(e) {
    if (!isOpen.value && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
        open()
        return
    }

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault()
            if (highlightedIndex.value < options.value.length - 1) {
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
            e.preventDefault()
            if (highlightedIndex.value >= 0 && highlightedIndex.value < options.value.length) {
                selectOption(options.value[highlightedIndex.value])
            }
            break
        case 'Escape':
            e.preventDefault()
            close()
            break
        case 'Backspace':
            if (props.multiple && searchQuery.value === '' && selectedTags.value.length > 0) {
                const last = selectedTags.value[selectedTags.value.length - 1]
                removeTag(last.value)
            }
            break
    }
}

function scrollToHighlighted() {
    nextTick(() => {
        const list = optionsListRef.value
        if (!list) return
        const item = list.children[highlightedIndex.value]
        if (!item) return
        item.scrollIntoView({ block: 'nearest' })
    })
}

function onOptionsScroll() {
    if (!isLegacy.value || !nextPageUrl.value || isLoading.value) return
    const list = optionsListRef.value
    if (!list) return
    const threshold = 30
    if (list.scrollTop + list.clientHeight >= list.scrollHeight - threshold) {
        fetchOptions(searchQuery.value, true)
    }
}

function onClickOutside(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        close()
    }
}

// --- Initialize known options ---
function initKnownOptions() {
    const vf = valueField.value

    if (props.initialOption) {
        knownOptions.value.set(String(props.initialOption[vf]), props.initialOption)
    }

    if (props.initialOptions && props.initialOptions.length > 0) {
        props.initialOptions.forEach(opt => {
            knownOptions.value.set(String(opt[vf]), opt)
        })
    }
}

// --- Watchers ---
watch(() => props.disabled, (disabled) => {
    if (disabled && isOpen.value) {
        close()
    }
})

watch(() => props.initialOption, (newOption) => {
    if (newOption) {
        const vf = valueField.value
        knownOptions.value.set(String(newOption[vf]), newOption)
    }
}, { deep: true })

watch(() => props.initialOptions, (newOptions) => {
    if (newOptions && newOptions.length > 0) {
        const vf = valueField.value
        newOptions.forEach(opt => {
            knownOptions.value.set(String(opt[vf]), opt)
        })
    }
}, { deep: true })

// --- Lifecycle ---
onMounted(() => {
    initKnownOptions()
    document.addEventListener('mousedown', onClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('mousedown', onClickOutside)
    if (debounceTimer) clearTimeout(debounceTimer)
})
</script>

<style lang="scss" scoped>
.dlm-async-select-wrapper {
    @apply dlm-w-full;
}

.dlm-as-label {
    @apply dlm-block dlm-text-sm dlm-font-medium dlm-text-gray-700 dlm-mb-1;
}

.dlm-required {
    @apply dlm-text-danger-600;
}

.dlm-as-container {
    @apply dlm-relative;

    &.is-disabled {
        @apply dlm-opacity-50 dlm-cursor-not-allowed;
    }
}

.dlm-as-control {
    @apply dlm-flex dlm-flex-wrap dlm-items-center dlm-gap-1;
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

.dlm-as-single-value {
    @apply dlm-flex-1 dlm-truncate dlm-text-gray-900;
}

.dlm-as-placeholder {
    @apply dlm-flex-1 dlm-truncate dlm-text-gray-400;
}

.dlm-as-actions {
    @apply dlm-flex dlm-items-center dlm-gap-1 dlm-ml-auto dlm-flex-shrink-0;
}

.dlm-as-clear {
    @apply dlm-p-0.5 dlm-rounded hover:dlm-bg-gray-100 dlm-text-gray-400 hover:dlm-text-gray-600;
    @apply dlm-border-0 dlm-bg-transparent dlm-cursor-pointer;
    line-height: 0;
}

.dlm-as-clear-icon {
    @apply dlm-w-4 dlm-h-4;
}

.dlm-as-chevron {
    @apply dlm-w-5 dlm-h-5 dlm-text-gray-400 dlm-flex-shrink-0;
    transition: transform 0.15s;

    .is-open & {
        transform: rotate(180deg);
    }
}

// Tags (multi mode)
.dlm-as-tag {
    @apply dlm-inline-flex dlm-items-center dlm-gap-0.5;
    @apply dlm-bg-gray-100 dlm-rounded dlm-px-2 dlm-py-1 dlm-text-sm dlm-text-gray-700;
    @apply dlm-max-w-[200px];
}

.dlm-as-tag-text {
    @apply dlm-truncate;
}

.dlm-as-tag-remove {
    @apply dlm-p-0 dlm-border-0 dlm-bg-transparent dlm-cursor-pointer;
    @apply dlm-text-gray-400 hover:dlm-text-gray-600;
    @apply dlm-rounded hover:dlm-bg-gray-200;
    line-height: 0;
    flex-shrink: 0;
}

.dlm-as-tag-remove-icon {
    @apply dlm-w-3.5 dlm-h-3.5;
}

.dlm-as-inline-input {
    @apply dlm-flex-1 dlm-min-w-[60px] dlm-border-0 dlm-outline-none dlm-p-0;
    @apply dlm-text-sm dlm-bg-transparent dlm-text-gray-900;
    box-shadow: none;

    &::placeholder {
        @apply dlm-text-gray-400;
    }

    &:focus {
        box-shadow: none;
        outline: none;
    }
}

// Dropdown
.dlm-as-dropdown {
    @apply dlm-absolute dlm-z-50 dlm-left-0 dlm-right-0 dlm-mt-1;
    @apply dlm-bg-white dlm-rounded-md dlm-shadow-lg;
    @apply dlm-overflow-hidden;
    border: 1px solid #d1d5db;
}

.dlm-as-search-wrapper {
    @apply dlm-p-2 dlm-border-b dlm-border-gray-200;
}

.dlm-as-search-input {
    @apply dlm-w-full dlm-px-3 dlm-py-2 dlm-text-sm;
    @apply dlm-rounded-md;
    border: 1px solid #d1d5db;
    @apply dlm-outline-none;

    &:focus {
        @apply dlm-ring-2 dlm-ring-primary-500 dlm-border-primary-500;
    }
}

.dlm-as-options {
    @apply dlm-m-0 dlm-p-0;
    list-style: none;
    max-height: 200px;
    overflow-y: auto;
}

.dlm-as-option {
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
}

.dlm-as-hint,
.dlm-as-no-results {
    @apply dlm-px-3 dlm-py-2 dlm-text-sm dlm-text-gray-500;
}

.dlm-as-loading {
    @apply dlm-flex dlm-items-center dlm-gap-2 dlm-px-3 dlm-py-2 dlm-text-sm dlm-text-gray-500;
}

.dlm-as-spinner {
    @apply dlm-animate-spin dlm-h-4 dlm-w-4 dlm-text-primary-600;
}

.dlm-form-hint {
    @apply dlm-text-xs dlm-text-gray-500 dlm-mt-1;
}

.dlm-form-error {
    @apply dlm-text-sm dlm-text-danger-600 dlm-mt-1;
}
</style>
