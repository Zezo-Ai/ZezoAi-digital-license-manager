<template>
    <div>
        <!-- Page header -->
        <div class="dlm-page-header">
            <h1>{{ trans('licenses.title') }}</h1>
            <div class="dlm-page-actions">
                <router-link to="/licenses/add" class="dlm-btn dlm-btn-primary">
                    {{ trans('global.buttons.add_new') }}
                </router-link>
                <router-link to="/licenses/import" class="dlm-btn dlm-btn-secondary">
                    {{ trans('licenses.buttons.import') }}
                </router-link>
            </div>
        </div>

        <div class="dlm-card">
            <div class="dlm-card-body">
                <!-- Status Filters -->
                <div class="dlm-status-filters mb-4">
                    <button
                        v-for="status in statusFilters"
                        :key="status.value"
                        :class="['dlm-status-filter', { 'dlm-active': currentStatus === status.value }]"
                        @click="setStatus(status.value)"
                    >
                        {{ status.label }}
                        <span class="dlm-count">({{ status.count }})</span>
                    </button>
                </div>

                <!-- Filters Row -->
                <div class="dlm-filters">
                    <div class="filter-item">
                        <input
                            v-model="search"
                            type="text"
                            class="dlm-input"
                            :placeholder="trans('global.placeholders.search')"
                            @keyup.enter="loadLicenses"
                        />
                    </div>
                    <div class="filter-item">
                        <select v-model="perPage" class="dlm-select" @change="loadLicenses">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="filter-item ml-auto">
                        <select v-model="bulkAction" class="dlm-select">
                            <option value="">{{ trans('global.labels.bulk_actions') }}</option>
                            <option value="activate">{{ trans('licenses.actions.activate') }}</option>
                            <option value="deactivate">{{ trans('licenses.actions.deactivate') }}</option>
                            <option value="delete">{{ trans('licenses.actions.delete') }}</option>
                            <option value="export">{{ trans('licenses.actions.export') }}</option>
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
                    :rows="licenses"
                    :loading="loading"
                    :selectable="true"
                    :selected="selectedIds"
                    row-key="id"
                    @select="handleSelect"
                    @select-all="handleSelectAll"
                    @sort="handleSort"
                >
                    <template #cell-license_key="{ row }">
                        <LicenseKey :license="row" />
                    </template>
                    <template #cell-status="{ row }">
                        <Status :status="row.status" />
                    </template>
                    <template #cell-product_id="{ row }">
                        <a v-if="row.product_url" :href="row.product_url" target="_blank">{{ row.product_name }}</a>
                        <span v-else-if="row.product_name">{{ row.product_name }}</span>
                        <span v-else class="text-gray-400">&mdash;</span>
                    </template>
                    <template #cell-user_id="{ row }">
                        <span v-if="row.user_email">{{ row.user_email }}</span>
                        <span v-else class="text-gray-400">&mdash;</span>
                    </template>
                    <template #cell-order_id="{ row }">
                        <div v-if="row.order_id" class="dlm-order-cell">
                            <div class="dlm-order-line">
                                <span class="dashicons dashicons-cart"></span>
                                <a v-if="row.order_url" :href="row.order_url" target="_blank">{{ row.order_number }}</a>
                                <span v-else>{{ row.order_number || '#' + row.order_id }}</span>
                            </div>
                            <div v-if="row.subscription" class="dlm-order-line dlm-order-sub">
                                <span class="dashicons dashicons-update"></span>
                                <a v-if="row.subscription.url" :href="row.subscription.url" target="_blank">{{ row.subscription.label }}</a>
                                <span v-else>{{ row.subscription.label }}</span>
                            </div>
                        </div>
                        <span v-else class="text-gray-400">&mdash;</span>
                    </template>
                    <template #cell-activations="{ row }">
                        {{ row.activations_count || 0 }} / {{ row.activations_limit || '&infin;' }}
                    </template>
                    <template #cell-expires_at="{ row }">
                        <span v-if="row.expires_at">{{ formatDate(row.expires_at) }}</span>
                        <span v-else class="text-gray-400">{{ trans('licenses.labels.never') }}</span>
                    </template>
                    <template #cell-actions="{ row }">
                        <div class="dlm-row-actions">
                            <router-link :to="`/licenses/${row.id}/edit`" class="dlm-action-link">
                                {{ trans('global.actions.edit') }}
                            </router-link>
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
            :title="trans('licenses.modals.delete.title')"
            @close="showDeleteModal = false"
        >
            <p>{{ trans('licenses.modals.delete.message') }}</p>
            <template #footer>
                <button class="dlm-btn dlm-btn-secondary" @click="showDeleteModal = false">
                    {{ trans('global.buttons.cancel') }}
                </button>
                <button class="dlm-btn dlm-btn-danger" :disabled="deleting" @click="deleteLicense">
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
import * as licensesService from '../services/licenses'
import Table from '@digital-license-manager/ui/components/Table.vue'
import Pager from '@digital-license-manager/ui/components/Pager.vue'
import Modal from '@digital-license-manager/ui/components/Modal.vue'
import Status from '@digital-license-manager/ui/components/Status.vue'
import LicenseKey from '../components/LicenseKey.vue'

const alertStore = useAlertStore()

// State
const loading = ref(true)
const licenses = ref([])
const search = ref('')
const perPage = ref(25)
const currentStatus = ref('all')
const bulkAction = ref('')
const selectedIds = ref([])
const sortBy = ref('id')
const sortOrder = ref('desc')
const showDeleteModal = ref(false)
const licenseToDelete = ref(null)
const deleting = ref(false)

const pagination = reactive({
    currentPage: 1,
    totalPages: 1,
    total: 0,
})

const statusFilters = ref([
    { value: 'all', label: trans('licenses.statuses.all'), count: 0 },
    { value: 'active', label: trans('licenses.statuses.active'), count: 0 },
    { value: 'inactive', label: trans('licenses.statuses.inactive'), count: 0 },
    { value: 'sold', label: trans('licenses.statuses.sold'), count: 0 },
    { value: 'delivered', label: trans('licenses.statuses.delivered'), count: 0 },
    { value: 'disabled', label: trans('licenses.statuses.disabled'), count: 0 },
])

const columns = computed(() => [
    { key: 'id', label: trans('licenses.columns.id'), sortable: true, width: '80px' },
    { key: 'license_key', label: trans('licenses.columns.license_key'), sortable: false },
    { key: 'product_id', label: trans('licenses.columns.product'), sortable: true },
    { key: 'user_id', label: trans('licenses.columns.user'), sortable: true },
    { key: 'order_id', label: trans('licenses.columns.order'), sortable: true },
    { key: 'status', label: trans('licenses.columns.status'), sortable: true, width: '120px' },
    { key: 'activations', label: trans('licenses.columns.activations'), sortable: false, width: '120px' },
    { key: 'expires_at', label: trans('licenses.columns.expires_at'), sortable: true, width: '150px' },
    { key: 'actions', label: '', sortable: false, width: '100px' },
])

// Methods
async function loadLicenses() {
    loading.value = true

    try {
        const response = await licensesService.query({
            page: pagination.currentPage,
            per_page: perPage.value,
            search: search.value,
            status: currentStatus.value !== 'all' ? currentStatus.value : '',
            orderby: sortBy.value,
            order: sortOrder.value,
        })

        const json = await response.json()

        if (json.success) {
            licenses.value = json.data.records
            pagination.currentPage = json.data.pagination.current_page
            pagination.totalPages = json.data.pagination.total_pages
            pagination.total = json.data.pagination.total

            // Update status counts
            if (json.data.counts) {
                statusFilters.value.forEach(filter => {
                    filter.count = json.data.counts[filter.value] || 0
                })
            }
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        loading.value = false
    }
}

function setStatus(status) {
    currentStatus.value = status
    pagination.currentPage = 1
    loadLicenses()
}

function goToPage(page) {
    pagination.currentPage = page
    loadLicenses()
}

function handleSort({ key, order }) {
    sortBy.value = key
    sortOrder.value = order
    loadLicenses()
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
        selectedIds.value = licenses.value.map(l => l.id)
    } else {
        selectedIds.value = []
    }
}

function confirmDelete(license) {
    licenseToDelete.value = license
    showDeleteModal.value = true
}

async function deleteLicense() {
    if (!licenseToDelete.value) return

    deleting.value = true

    try {
        const response = await licensesService.remove(licenseToDelete.value.id)
        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            showDeleteModal.value = false
            loadLicenses()
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
        const response = await licensesService.bulkAction(bulkAction.value, selectedIds.value)
        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            selectedIds.value = []
            bulkAction.value = ''
            loadLicenses()
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
    return date.toLocaleDateString()
}

// Lifecycle
onMounted(() => {
    loadLicenses()
})

</script>

<style scoped>
@reference "../tailwind-entry.css";

.dlm-status-filters {
    @apply flex flex-wrap gap-2 border-b border-gray-200 pb-3;
}

.dlm-status-filter {
    @apply px-3 py-1 text-sm text-gray-600 bg-transparent border-0 cursor-pointer;
    @apply hover:text-primary-600;

    &.dlm-active {
        @apply text-primary-600 font-medium;
    }

    .dlm-count {
        @apply text-gray-400;
    }
}

.dlm-filter-item {
    @apply flex items-center gap-2;
}

.dlm-row-actions {
    @apply flex items-center gap-3;
}

.dlm-action-link {
    @apply text-sm cursor-pointer bg-transparent border-0 p-0;
}

.dlm-order-cell {
    @apply flex flex-col gap-1;
}

.dlm-order-line {
    @apply flex items-center gap-1;

    .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
        @apply text-gray-400;
    }
}

.dlm-order-sub {
    @apply text-xs text-gray-500;
}
</style>
