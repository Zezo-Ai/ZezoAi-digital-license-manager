<template>
    <div class="dlm-table-wrapper">
        <div class="dlm-table-container">
            <table class="dlm-table">
                <thead>
                    <tr>
                        <th v-if="selectable" class="dlm-table-checkbox">
                            <input
                                type="checkbox"
                                class="dlm-checkbox"
                                :checked="allSelected"
                                :indeterminate="someSelected"
                                @change="toggleSelectAll"
                            />
                        </th>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="getColumnClass(column)"
                            :style="column.width ? { width: column.width } : {}"
                            @click="column.sortable ? handleSort(column.key) : null"
                        >
                            <div class="dlm-table-header-cell">
                                <span>{{ column.label }}</span>
                                <span v-if="column.sortable" class="dlm-sort-icon">
                                    <svg v-if="sortKey === column.key && sortOrder === 'asc'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else-if="sortKey === column.key && sortOrder === 'desc'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.638l3.96-4.158a.75.75 0 111.08 1.04l-5.25 5.5a.75.75 0 01-1.08 0l-5.25-5.5a.75.75 0 111.08-1.04l3.96 4.158V3.75A.75.75 0 0110 3z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="dlm-text-gray-300">
                                        <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Loading skeleton -->
                    <template v-if="loading">
                        <tr v-for="i in skeletonRows" :key="`skeleton-${i}`">
                            <td v-if="selectable" class="dlm-table-checkbox">
                                <div class="dlm-skeleton dlm-skeleton-checkbox"></div>
                            </td>
                            <td v-for="column in columns" :key="`skeleton-${i}-${column.key}`">
                                <div class="dlm-skeleton" :style="getSkeletonStyle(column)"></div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <tr v-else-if="rows.length === 0">
                        <td :colspan="selectable ? columns.length + 1 : columns.length">
                            <div class="dlm-empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                                <h3>{{ emptyText }}</h3>
                            </div>
                        </td>
                    </tr>

                    <!-- Data rows -->
                    <tr
                        v-else
                        v-for="row in rows"
                        :key="row[rowKey]"
                        :class="{ 'dlm-selected': isSelected(row) }"
                    >
                        <td v-if="selectable" class="dlm-table-checkbox">
                            <input
                                type="checkbox"
                                class="dlm-checkbox"
                                :checked="isSelected(row)"
                                @change="toggleSelect(row)"
                            />
                        </td>
                        <td v-for="column in columns" :key="column.key">
                            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                                {{ row[column.key] }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { trans } from '../utils/useLang'

const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
    rows: {
        type: Array,
        default: () => [],
    },
    rowKey: {
        type: String,
        default: 'id',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    selectable: {
        type: Boolean,
        default: false,
    },
    selected: {
        type: Array,
        default: () => [],
    },
    emptyText: {
        type: String,
        default: () => trans('global.messages.no_records'),
    },
    skeletonRows: {
        type: Number,
        default: 5,
    },
})

const emit = defineEmits(['select', 'select-all', 'sort'])

const sortKey = ref('')
const sortOrder = ref('asc')

const allSelected = computed(() => {
    return props.rows.length > 0 && props.selected.length === props.rows.length
})

const someSelected = computed(() => {
    return props.selected.length > 0 && props.selected.length < props.rows.length
})

function isSelected(row) {
    return props.selected.includes(row[props.rowKey])
}

function toggleSelect(row) {
    emit('select', row[props.rowKey])
}

function toggleSelectAll(event) {
    emit('select-all', event.target.checked)
}

function handleSort(key) {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
    emit('sort', { key: sortKey.value, order: sortOrder.value })
}

function getColumnClass(column) {
    return {
        'dlm-sortable': column.sortable,
        'dlm-sorted': sortKey.value === column.key,
    }
}

function getSkeletonStyle(column) {
    const width = column.width ? parseInt(column.width) * 0.8 : 100
    return { width: `${Math.min(width, 150)}px` }
}
</script>

<style lang="scss" scoped>
.dlm-table-wrapper {
    @apply dlm-w-full;
}

.dlm-table-checkbox {
    @apply dlm-w-10 dlm-text-center;
}

.dlm-table-header-cell {
    @apply dlm-flex dlm-items-center dlm-gap-1;
}

.dlm-sortable {
    @apply dlm-cursor-pointer hover:dlm-bg-gray-100;
}

.dlm-sort-icon {
    @apply dlm-w-4 dlm-h-4;

    svg {
        @apply dlm-w-4 dlm-h-4;
    }
}

.dlm-skeleton {
    @apply dlm-h-4 dlm-bg-gray-200 dlm-rounded dlm-animate-pulse;

    &-checkbox {
        @apply dlm-w-4 dlm-h-4 dlm-mx-auto;
    }
}
</style>
