<template>
    <Page :title="trans('activations.title')">

        <div class="dlm-card">
            <div class="dlm-card-body">
                <!-- Filters Row -->
                <div class="dlm-filters">
                    <div class="dlm-filter-item">
                        <input
                            v-model="search"
                            type="text"
                            class="dlm-input"
                            :placeholder="trans('global.placeholders.search')"
                            @keyup.enter="loadActivations"
                        />
                    </div>
                    <div class="dlm-filter-item">
                        <select v-model="perPage" class="dlm-select" @change="loadActivations">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="dlm-filter-item dlm-ml-auto">
                        <select v-model="bulkAction" class="dlm-select">
                            <option value="">{{ trans('global.labels.bulk_actions') }}</option>
                            <option value="delete">{{ trans('activations.actions.delete') }}</option>
                        </select>
                        <button
                            class="dlm-btn dlm-btn-secondary dlm-btn-sm"
                            :disabled="!bulkAction || selectedIds.length === 0"
                            @click="applyBulkAction"
                        >
                            {{ trans('global.buttons.apply') }}
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <Table
                    :columns="columns"
                    :rows="activations"
                    :loading="loading"
                    :selectable="true"
                    :selected="selectedIds"
                    row-key="id"
                    @select="handleSelect"
                    @select-all="handleSelectAll"
                    @sort="handleSort"
                >
                    <template #cell-license_key="{ row }">
                        <span v-if="row.license_key_partial" class="dlm-font-mono">
                            {{ row.license_key_partial }}
                        </span>
                        <span v-else class="dlm-text-gray-400">&mdash;</span>
                    </template>
                    <template #cell-source="{ row }">
                        <code class="dlm-text-xs dlm-bg-gray-100 dlm-px-2 dlm-py-1 dlm-rounded">
                            {{ row.source || 'api' }}
                        </code>
                    </template>
                    <template #cell-created_at="{ row }">
                        {{ formatDate(row.created_at) }}
                    </template>
                    <template #cell-actions="{ row }">
                        <div class="dlm-row-actions">
                            <button class="dlm-action-link dlm-text-danger-600" @click="confirmDelete(row)">
                                {{ trans('global.actions.delete') }}
                            </button>
                        </div>
                    </template>
                </Table>

                <!-- Pagination -->
                <Pager
                    v-if="pagination.total > 0"
                    :current-page="pagination.currentPage"
                    :total-pages="pagination.totalPages"
                    :total-items="pagination.total"
                    :per-page="perPage"
                    @page-change="goToPage"
                />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal
            :show="showDeleteModal"
            :title="trans('activations.modals.delete.title')"
            @close="showDeleteModal = false"
        >
            <p>{{ trans('activations.modals.delete.message') }}</p>
            <template #footer>
                <button class="dlm-btn dlm-btn-secondary" @click="showDeleteModal = false">
                    {{ trans('global.buttons.cancel') }}
                </button>
                <button class="dlm-btn dlm-btn-danger" :disabled="deleting" @click="deleteActivation">
                    {{ trans('global.buttons.delete') }}
                </button>
            </template>
        </Modal>
    </Page>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { trans } from '@digital-license-manager/ui/utils/useLang'
import { useAlertStore } from '@digital-license-manager/ui/stores/alert'
import * as activationsService from '../services/activations'
import Page from '@digital-license-manager/ui/components/Page.vue'
import Table from '@digital-license-manager/ui/components/Table.vue'
import Pager from '@digital-license-manager/ui/components/Pager.vue'
import Modal from '@digital-license-manager/ui/components/Modal.vue'

const alertStore = useAlertStore()

const loading = ref(true)
const activations = ref([])
const search = ref('')
const perPage = ref(25)
const bulkAction = ref('')
const selectedIds = ref([])
const sortBy = ref('id')
const sortOrder = ref('desc')
const showDeleteModal = ref(false)
const activationToDelete = ref(null)
const deleting = ref(false)

const pagination = reactive({
    currentPage: 1,
    totalPages: 1,
    total: 0,
})

const columns = computed(() => [
    { key: 'id', label: trans('activations.columns.id'), sortable: true, width: '80px' },
    { key: 'license_key', label: trans('activations.columns.license_key'), sortable: false },
    { key: 'label', label: trans('activations.columns.label'), sortable: true },
    { key: 'source', label: trans('activations.columns.source'), sortable: true, width: '120px' },
    { key: 'ip_address', label: trans('activations.columns.ip_address'), sortable: true, width: '130px' },
    { key: 'user_agent', label: trans('activations.columns.user_agent'), sortable: false },
    { key: 'created_at', label: trans('activations.columns.created_at'), sortable: true, width: '150px' },
    { key: 'actions', label: '', sortable: false, width: '80px' },
])

async function loadActivations() {
    loading.value = true

    try {
        const response = await activationsService.query({
            page: pagination.currentPage,
            per_page: perPage.value,
            search: search.value,
            orderby: sortBy.value,
            order: sortOrder.value,
        })

        const json = await response.json()

        if (json.success) {
            activations.value = json.data.records
            pagination.currentPage = json.data.pagination.current_page
            pagination.totalPages = json.data.pagination.total_pages
            pagination.total = json.data.pagination.total
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        loading.value = false
    }
}

function goToPage(page) {
    pagination.currentPage = page
    loadActivations()
}

function handleSort({ key, order }) {
    sortBy.value = key
    sortOrder.value = order
    loadActivations()
}

function handleSelect(id) {
    const index = selectedIds.value.indexOf(id)
    if (index > -1) {
        selectedIds.value.splice(index, 1)
    } else {
        selectedIds.value.push(id)
    }
}

function handleSelectAll(selected) {
    if (selected) {
        selectedIds.value = activations.value.map(a => a.id)
    } else {
        selectedIds.value = []
    }
}

function confirmDelete(activation) {
    activationToDelete.value = activation
    showDeleteModal.value = true
}

async function deleteActivation() {
    if (!activationToDelete.value) return

    deleting.value = true

    try {
        const response = await activationsService.remove(activationToDelete.value.id)
        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            showDeleteModal.value = false
            loadActivations()
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        deleting.value = false
    }
}

async function applyBulkAction() {
    if (!bulkAction.value || selectedIds.value.length === 0) return

    try {
        const response = await activationsService.bulkAction(bulkAction.value, selectedIds.value)
        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            selectedIds.value = []
            bulkAction.value = ''
            loadActivations()
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    }
}

function formatDate(dateString) {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleString()
}

onMounted(() => {
    loadActivations()
})
</script>

<style lang="scss" scoped>
.dlm-filter-item {
    @apply dlm-flex dlm-items-center dlm-gap-2;
}

.dlm-row-actions {
    @apply dlm-flex dlm-items-center dlm-gap-3;
}

.dlm-action-link {
    @apply dlm-text-sm dlm-cursor-pointer dlm-bg-transparent dlm-border-0 dlm-p-0;
}

.dlm-font-mono {
    font-family: monospace;
}
</style>
