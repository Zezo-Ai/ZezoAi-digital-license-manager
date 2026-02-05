<template>
    <span :class="['dlm-badge', badgeClass]">
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue'
import { trans } from '../utils/useLang'

const props = defineProps({
    status: {
        type: [String, Number],
        required: true,
    },
    type: {
        type: String,
        default: 'license',
    },
})

const statusConfig = {
    license: {
        active: { class: 'dlm-badge-success', label: 'licenses.statuses.active' },
        inactive: { class: 'dlm-badge-gray', label: 'licenses.statuses.inactive' },
        sold: { class: 'dlm-badge-primary', label: 'licenses.statuses.sold' },
        delivered: { class: 'dlm-badge-success', label: 'licenses.statuses.delivered' },
        disabled: { class: 'dlm-badge-danger', label: 'licenses.statuses.disabled' },
        // Numeric statuses (backwards compat)
        1: { class: 'dlm-badge-gray', label: 'licenses.statuses.inactive' },
        2: { class: 'dlm-badge-success', label: 'licenses.statuses.active' },
        3: { class: 'dlm-badge-primary', label: 'licenses.statuses.sold' },
        4: { class: 'dlm-badge-success', label: 'licenses.statuses.delivered' },
        5: { class: 'dlm-badge-danger', label: 'licenses.statuses.disabled' },
    },
}

const config = computed(() => {
    const typeConfig = statusConfig[props.type] || statusConfig.license
    return typeConfig[props.status] || { class: 'dlm-badge-gray', label: props.status }
})

const badgeClass = computed(() => config.value.class)

const label = computed(() => {
    if (config.value.label.includes('.')) {
        return trans(config.value.label)
    }
    return config.value.label
})
</script>
