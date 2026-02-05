<template>
    <div class="dlm-async-select-wrapper">
        <label v-if="label" :for="id">
            {{ label }}
            <span v-if="required" class="dlm-required">*</span>
        </label>

        <div ref="selectContainer"></div>

        <p v-if="hint" class="dlm-form-hint">{{ hint }}</p>
        <p v-if="error" class="dlm-form-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import TomSelect from 'tom-select'
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

const selectContainer = ref(null)
let tomSelectInstance = null

function initTomSelect() {
    if (!selectContainer.value) return

    // Create input element
    const input = document.createElement('input')
    input.type = 'text'
    input.id = props.id
    input.placeholder = props.placeholder
    selectContainer.value.appendChild(input)

    const ajaxUrl = getAjaxUrl()
    const nonce = getNonce()
    const dropdownNonce = getDropdownNonce()
    const isLegacy = !!props.searchType

    const valueField = isLegacy ? 'id' : 'value'
    const labelField = isLegacy ? 'text' : 'label'

    const plugins = {}
    if (props.multiple) {
        plugins.remove_button = {}
    }
    if (isLegacy) {
        plugins.virtual_scroll = {}
    }

    tomSelectInstance = new TomSelect(input, {
        valueField,
        labelField,
        searchField: [labelField],
        maxItems: props.multiple ? null : 1,
        plugins,
        placeholder: props.placeholder,
        firstUrl: function(query) {
            if (isLegacy) {
                return `${ajaxUrl}?action=dlm_dropdown_search&security=${dropdownNonce}&type=${props.searchType}&term=${encodeURIComponent(query)}`
            }
            return `${ajaxUrl}?action=${props.searchAction}&_wpnonce=${nonce}&search=${encodeURIComponent(query)}`
        },

        load: function(query, callback) {
            if (query.length < props.minChars) {
                callback([])
                return
            }

            const url = this.getUrl(query)

            fetch(url)
                .then(response => response.json())
                .then(json => {
                    if (isLegacy) {
                        const results = json.results || []
                        if (json.pagination && json.pagination.more) {
                            const nextPage = (json.pagination.current || 1) + 1
                            this.setNextUrl(query, `${ajaxUrl}?action=dlm_dropdown_search&security=${dropdownNonce}&type=${props.searchType}&term=${encodeURIComponent(query)}&page=${nextPage}`)
                        }
                        callback(results)
                    } else {
                        if (json.success && json.data.results) {
                            callback(json.data.results)
                        } else {
                            callback([])
                        }
                    }
                })
                .catch(() => {
                    callback([])
                })
        },

        render: {
            option: function(data, escape) {
                return `<div class="option">${escape(data[labelField])}</div>`
            },
            item: function(data, escape) {
                return `<div class="item">${escape(data[labelField])}</div>`
            },
            no_results: function() {
                return '<div class="no-results">No results found</div>'
            },
        },

        onChange: function(value) {
            if (props.multiple) {
                emit('update:modelValue', value ? value.split(',').filter(Boolean) : [])
            } else {
                emit('update:modelValue', value || null)
            }
        },
    })

    // Set initial value(s)
    if (props.initialOption) {
        tomSelectInstance.addOption(props.initialOption)
        tomSelectInstance.setValue(props.initialOption[valueField], true)
    }

    if (props.initialOptions && props.initialOptions.length > 0) {
        props.initialOptions.forEach(opt => {
            tomSelectInstance.addOption(opt)
        })
        if (props.multiple) {
            tomSelectInstance.setValue(props.initialOptions.map(o => o[valueField]), true)
        } else if (props.initialOptions[0]) {
            tomSelectInstance.setValue(props.initialOptions[0][valueField], true)
        }
    }

    // Set value if modelValue is provided
    if (props.modelValue && !props.initialOption && !props.initialOptions.length) {
        if (props.multiple && Array.isArray(props.modelValue)) {
            tomSelectInstance.setValue(props.modelValue, true)
        } else if (props.modelValue) {
            tomSelectInstance.setValue(props.modelValue, true)
        }
    }

    // Handle disabled state
    if (props.disabled) {
        tomSelectInstance.disable()
    }
}

watch(() => props.disabled, (disabled) => {
    if (tomSelectInstance) {
        if (disabled) {
            tomSelectInstance.disable()
        } else {
            tomSelectInstance.enable()
        }
    }
})

watch(() => props.initialOption, (newOption) => {
    if (tomSelectInstance && newOption) {
        const vf = props.searchType ? 'id' : 'value'
        tomSelectInstance.clearOptions()
        tomSelectInstance.addOption(newOption)
        tomSelectInstance.setValue(newOption[vf], true)
    }
}, { deep: true })

watch(() => props.initialOptions, (newOptions) => {
    if (tomSelectInstance && newOptions && newOptions.length > 0) {
        const vf = props.searchType ? 'id' : 'value'
        tomSelectInstance.clearOptions()
        newOptions.forEach(opt => {
            tomSelectInstance.addOption(opt)
        })
        if (props.multiple) {
            tomSelectInstance.setValue(newOptions.map(o => o[vf]), true)
        }
    }
}, { deep: true })

onMounted(() => {
    nextTick(() => {
        initTomSelect()
    })
})

onUnmounted(() => {
    if (tomSelectInstance) {
        tomSelectInstance.destroy()
    }
})
</script>

<style lang="scss">
.dlm-async-select-wrapper {
    @apply dlm-w-full;

    .ts-wrapper {
        @apply dlm-w-full;

        .ts-control {
            @apply dlm-border dlm-border-gray-300 dlm-rounded-md dlm-px-3 dlm-py-2 dlm-text-sm;
            @apply dlm-bg-white;
            box-shadow: none;

            &:focus-within {
                @apply dlm-ring-2 dlm-ring-primary-500 dlm-border-primary-500;
            }

            input {
                @apply dlm-text-sm;
            }

            .item {
                @apply dlm-bg-gray-100 dlm-rounded dlm-px-2 dlm-py-1 dlm-text-sm dlm-mr-1;
            }
        }

        .ts-dropdown {
            @apply dlm-border dlm-border-gray-300 dlm-rounded-md dlm-shadow-lg dlm-mt-1;

            .option {
                @apply dlm-px-3 dlm-py-2 dlm-text-sm;

                &.active {
                    @apply dlm-bg-primary-50 dlm-text-primary-700;
                }
            }

            .no-results {
                @apply dlm-px-3 dlm-py-2 dlm-text-sm dlm-text-gray-500;
            }
        }
    }
}

.dlm-required {
    @apply dlm-text-danger-600;
}

.dlm-form-error {
    @apply dlm-text-sm dlm-text-danger-600 dlm-mt-1;
}
</style>
