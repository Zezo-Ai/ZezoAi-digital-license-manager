<template>
    <div class="dlm-pager">
        <div class="dlm-pager-info">
            {{ trans('global.pagination.showing', { from: from, to: to, total: totalItems }) }}
        </div>

        <div class="dlm-pager-controls">
            <button
                class="dlm-pager-btn"
                :disabled="currentPage <= 1"
                @click="goToPage(1)"
                :title="trans('global.pagination.first')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M15.79 14.77a.75.75 0 01-1.06.02l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 111.04 1.08L11.832 10l3.938 3.71a.75.75 0 01.02 1.06zm-6 0a.75.75 0 01-1.06.02l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 111.04 1.08L5.832 10l3.938 3.71a.75.75 0 01.02 1.06z" clip-rule="evenodd" />
                </svg>
            </button>

            <button
                class="dlm-pager-btn"
                :disabled="currentPage <= 1"
                @click="goToPage(currentPage - 1)"
                :title="trans('global.pagination.previous')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                </svg>
            </button>

            <div class="dlm-pager-pages">
                <button
                    v-for="page in visiblePages"
                    :key="page"
                    :class="['dlm-pager-page', { 'dlm-active': page === currentPage }]"
                    :disabled="page === '...'"
                    @click="page !== '...' ? goToPage(page) : null"
                >
                    {{ page }}
                </button>
            </div>

            <button
                class="dlm-pager-btn"
                :disabled="currentPage >= totalPages"
                @click="goToPage(currentPage + 1)"
                :title="trans('global.pagination.next')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
            </button>

            <button
                class="dlm-pager-btn"
                :disabled="currentPage >= totalPages"
                @click="goToPage(totalPages)"
                :title="trans('global.pagination.last')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.21 5.23a.75.75 0 011.06-.02l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 11-1.04-1.08L8.168 10 4.23 6.29a.75.75 0 01-.02-1.06zm6 0a.75.75 0 011.06-.02l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 11-1.04-1.08L14.168 10l-3.938-3.71a.75.75 0 01-.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { trans } from '../utils/useLang'

const props = defineProps({
    currentPage: {
        type: Number,
        required: true,
    },
    totalPages: {
        type: Number,
        required: true,
    },
    totalItems: {
        type: Number,
        required: true,
    },
    perPage: {
        type: Number,
        default: 25,
    },
    maxVisiblePages: {
        type: Number,
        default: 5,
    },
})

const emit = defineEmits(['page-change'])

const from = computed(() => {
    return (props.currentPage - 1) * props.perPage + 1
})

const to = computed(() => {
    return Math.min(props.currentPage * props.perPage, props.totalItems)
})

const visiblePages = computed(() => {
    const pages = []
    const { currentPage, totalPages, maxVisiblePages } = props

    if (totalPages <= maxVisiblePages) {
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i)
        }
    } else {
        const half = Math.floor(maxVisiblePages / 2)
        let start = currentPage - half
        let end = currentPage + half

        if (start < 1) {
            start = 1
            end = maxVisiblePages
        }

        if (end > totalPages) {
            end = totalPages
            start = totalPages - maxVisiblePages + 1
        }

        if (start > 1) {
            pages.push(1)
            if (start > 2) {
                pages.push('...')
            }
        }

        for (let i = start; i <= end; i++) {
            if (i > 0 && i <= totalPages) {
                pages.push(i)
            }
        }

        if (end < totalPages) {
            if (end < totalPages - 1) {
                pages.push('...')
            }
            pages.push(totalPages)
        }
    }

    return pages
})

function goToPage(page) {
    if (page >= 1 && page <= props.totalPages) {
        emit('page-change', page)
    }
}
</script>

<style lang="scss" scoped>
.dlm-pager {
    @apply dlm-flex dlm-items-center dlm-justify-between dlm-mt-4 dlm-pt-4 dlm-border-t dlm-border-gray-200;
}

.dlm-pager-info {
    @apply dlm-text-sm dlm-text-gray-600;
}

.dlm-pager-controls {
    @apply dlm-flex dlm-items-center dlm-gap-1;
}

.dlm-pager-btn {
    @apply dlm-p-2 dlm-rounded dlm-border dlm-border-gray-300 dlm-bg-white dlm-text-gray-600;
    @apply hover:dlm-bg-gray-50 disabled:dlm-opacity-50 disabled:dlm-cursor-not-allowed;

    svg {
        @apply dlm-w-4 dlm-h-4;
    }
}

.dlm-pager-pages {
    @apply dlm-flex dlm-items-center dlm-gap-1 dlm-mx-2;
}

.dlm-pager-page {
    @apply dlm-px-3 dlm-py-1 dlm-rounded dlm-border dlm-border-gray-300 dlm-bg-white dlm-text-sm dlm-text-gray-600;
    @apply hover:dlm-bg-gray-50 disabled:dlm-cursor-default;

    &.dlm-active {
        @apply dlm-bg-primary-600 dlm-border-primary-600 dlm-text-white;
    }
}
</style>
