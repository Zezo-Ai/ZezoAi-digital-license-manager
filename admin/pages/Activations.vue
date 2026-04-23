<template>
    <div>
        <!-- Page header -->
        <div class="dlm-page-header">
            <h1>{{ trans('activations.title') }}</h1>
        </div>

        <div class="dlm-card">
            <div class="dlm-card-body">
                <!-- Filters Row -->
                <div class="dlm-filters">
                    <div class="filter-item">
                        <input
                            v-model="filterLicenseKey"
                            type="text"
                            class="dlm-input dlm-input-sm"
                            :placeholder="trans('activations.filters.license_key')"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div class="filter-item">
                        <select v-model="filterSource" class="dlm-select">
                            <option value="">{{ trans('activations.filters.all_sources') }}</option>
                            <option v-for="(label, value) in sourceOptions" :key="value" :value="value">{{ label }}</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <button
                            class="dlm-btn dlm-btn-secondary dlm-btn-sm"
                            @click="applyFilters"
                        >
                            {{ trans('activations.filters.filter') }}
                        </button>
                    </div>
                    <div class="filter-item">
                        <select v-model="perPage" class="dlm-select" @change="applyFilters">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="filter-item ml-auto">
                        <select v-model="bulkAction" class="dlm-select">
                            <option value="">{{ trans('global.labels.bulk_actions') }}</option>
                            <option value="enable">{{ trans('activations.actions.enable') }}</option>
                            <option value="disable">{{ trans('activations.actions.disable') }}</option>
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
                        <LicenseKey :license="{ id: row.license_id, license_key_partial: row.license_key_partial }" show-link />
                    </template>
                    <template #cell-source="{ row }">
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                            {{ row.source_label }}
                        </code>
                    </template>
                    <template #cell-status="{ row }">
                        <span
                            class="dlm-badge"
                            :class="row.deactivated_at ? 'dlm-badge-danger' : 'dlm-badge-success'"
                        >
                            {{ row.deactivated_at ? trans('activations.labels.disabled') : trans('activations.labels.enabled') }}
                        </span>
                    </template>
                    <template #cell-created_at="{ row }">
                        {{ formatDate(row.created_at) }}
                    </template>
                    <template #cell-actions="{ row }">
                        <div class="dlm-row-actions">
                            <button
                                class="dlm-action-link"
                                :class="row.deactivated_at ? 'text-success-600' : 'text-warning-600'"
                                @click="toggleActivation(row)"
                            >
                                {{ row.deactivated_at ? trans('activations.actions.enable') : trans('activations.actions.disable') }}
                            </button>
                            <button class="dlm-action-link text-danger-600" @click="confirmDelete(row)">
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
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { trans } from '@digital-license-manager/ui/utils/useLang'
import { useAlertStore } from '@digital-license-manager/ui/stores/alert'
import * as activationsService from '../services/activations'
import Table from '@digital-license-manager/ui/components/Table.vue'
import Pager from '@digital-license-manager/ui/components/Pager.vue'
import Modal from '@digital-license-manager/ui/components/Modal.vue'
import LicenseKey from '../components/LicenseKey.vue'

const alertStore = useAlertStore()

const loading = ref(true)
const activations = ref([])
const filterLicenseKey = ref('')
const filterSource = ref('')
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

const sourceOptions = window.DLMAdmin?.config?.activationSources || {}

const columns = computed(() => [
    { key: 'id', label: trans('activations.columns.id'), sortable: true, width: '80px' },
    { key: 'license_key', label: trans('activations.columns.license_key'), sortable: false },
    { key: 'label', label: trans('activations.columns.label'), sortable: true },
    { key: 'source', label: trans('activations.columns.source'), sortable: true, width: '120px' },
    { key: 'ip_address', label: trans('activations.columns.ip_address'), sortable: true, width: '130px' },
    { key: 'user_agent', label: trans('activations.columns.user_agent'), sortable: false },
    { key: 'status', label: trans('activations.columns.status'), sortable: false, width: '100px' },
    { key: 'created_at', label: trans('activations.columns.created_at'), sortable: true, width: '150px' },
    { key: 'actions', label: '', sortable: false, width: '120px' },
])

async function loadActivations() {
    loading.value = true

    try {
        const params = {
            page: pagination.currentPage,
            per_page: perPage.value,
            orderby: sortBy.value,
            order: sortOrder.value,
        }
        if (filterLicenseKey.value) {
            params.license_key = filterLicenseKey.value
        }
        if (filterSource.value) {
            params.source = filterSource.value
        }
        const response = await activationsService.query(params)

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

function applyFilters() {
    pagination.currentPage = 1
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

async function toggleActivation(row) {
    const action = row.deactivated_at ? 'enable' : 'disable'

    try {
        const response = await activationsService.bulkAction(action, [row.id])
        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            loadActivations()
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
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
    @apply flex items-center gap-2;
}

.dlm-row-actions {
    @apply flex items-center gap-3;
}

.dlm-action-link {
    @apply text-sm cursor-pointer bg-transparent border-0 p-0;
}

</style>
