<template>
    <button
        :type="type"
        :class="buttonClasses"
        :disabled="disabled || loading"
        @click="$emit('click', $event)"
    >
        <svg
            v-if="loading"
            class="dlm-spinner dlm-mr-2"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="dlm-opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            ></circle>
            <path
                class="dlm-opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
            ></path>
        </svg>

        <span v-if="$slots.icon && !loading" class="dlm-btn-icon">
            <slot name="icon"></slot>
        </span>

        <span v-if="$slots.default">
            <slot></slot>
        </span>
    </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'danger', 'success', 'warning'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    block: {
        type: Boolean,
        default: false,
    },
})

defineEmits(['click'])

const buttonClasses = computed(() => [
    'dlm-btn',
    `dlm-btn-${props.variant}`,
    `dlm-btn-${props.size}`,
    {
        'dlm-btn-block': props.block,
        'dlm-btn-loading': props.loading,
    },
])
</script>

<style lang="scss" scoped>
.dlm-btn-icon {
    @apply dlm-mr-2;

    svg {
        @apply dlm-w-4 dlm-h-4;
    }
}

.dlm-btn-block {
    @apply dlm-w-full;
}

.dlm-btn-loading {
    @apply dlm-cursor-wait;
}

.dlm-mr-2 {
    margin-right: 0.5rem;
}
</style>
