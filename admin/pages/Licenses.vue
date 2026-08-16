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
                <button
                    v-if="canExport"
                    type="button"
                    class="dlm-btn dlm-btn-secondary"
                    :disabled="loading || !exportAvailable"
                    @click="openExportModal()"
                >
                    {{ trans('licenses.buttons.export') }}
                </button>
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
                            :aria-label="trans('global.placeholders.search')"
                            :placeholder="trans('global.placeholders.search')"
                            @keyup.enter="applySearch"
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
                    <div v-if="selectedIds.length > 0" class="filter-item ml-auto dlm-bulk-actions">
                        <select v-model="bulkAction" class="dlm-select">
                            <option value="">{{ trans('global.labels.bulk_actions') }}</option>
                            <option value="activate">{{ trans('licenses.actions.activate') }}</option>
                            <option value="deactivate">{{ trans('licenses.actions.deactivate') }}</option>
                            <option value="delete">{{ trans('licenses.actions.delete') }}</option>
                            <option v-if="canExport" value="export">{{ trans('licenses.actions.export') }}</option>
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
                    primary-field="license_key"
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
                        <ActionMenu
                            :items="[
                                { id: 'edit', label: trans('global.actions.edit'), to: `/licenses/${row.id}/edit` },
                                { id: 'delete', label: trans('global.actions.delete'), danger: true },
                            ]"
                            @select="action => action === 'delete' && confirmDelete(row)"
                        />
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

        <!-- License Export Modal -->
        <Modal
            :show="showExportModal"
            :title="trans('licenses.modals.export.title')"
            size="lg"
            @close="closeExportModal"
        >
            <form
                id="dlm-license-export-form"
                class="dlm-export-form"
                method="post"
                :action="exportConfig.url"
                @submit="handleExportSubmit"
            >
                <input type="hidden" name="action" value="dlm_licenses_export" />
                <input type="hidden" name="_wpnonce" :value="exportConfig.nonce" />
                <input type="hidden" name="search" :value="appliedQuery.search" />
                <input type="hidden" name="status" :value="appliedQuery.status" />
                <input type="hidden" name="orderby" :value="appliedQuery.orderby" />
                <input type="hidden" name="order" :value="appliedQuery.order" />
                <input
                    v-for="id in exportScope === 'selected' ? selectedIds : []"
                    :key="`export-license-${id}`"
                    type="hidden"
                    name="ids[]"
                    :value="id"
                />

                <fieldset class="dlm-export-fieldset">
                    <legend>{{ trans('licenses.modals.export.scope') }}</legend>
                    <div class="dlm-export-scope-options">
                        <label v-if="selectedIds.length" :class="['dlm-export-scope-option', { 'is-selected': exportScope === 'selected' }]">
                            <input v-model="exportScope" type="radio" name="scope" value="selected" />
                            <span>
                                <strong>{{ replaceCount(trans('licenses.modals.export.selected'), selectedIds.length) }}</strong>
                                <small>{{ trans('licenses.modals.export.selected_description') }}</small>
                            </span>
                        </label>
                        <label :class="['dlm-export-scope-option', { 'is-selected': exportScope === 'filtered', 'is-disabled': pagination.total === 0 }]">
                            <input
                                v-model="exportScope"
                                type="radio"
                                name="scope"
                                value="filtered"
                                :disabled="pagination.total === 0"
                            />
                            <span>
                                <strong>{{ replaceCount(trans('licenses.modals.export.filtered'), pagination.total) }}</strong>
                                <small>{{ trans('licenses.modals.export.filtered_description') }}</small>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <fieldset class="dlm-export-fieldset">
                    <legend>{{ trans('licenses.modals.export.columns') }}</legend>
                    <div class="dlm-export-columns-actions">
                        <button type="button" @click="selectAllExportColumns">
                            {{ trans('licenses.modals.export.select_all') }}
                        </button>
                        <span aria-hidden="true">·</span>
                        <button type="button" @click="clearExportColumns">
                            {{ trans('licenses.modals.export.clear_all') }}
                        </button>
                    </div>

                    <div class="dlm-export-columns">
                        <label v-for="column in exportConfig.columns" :key="column.key" class="dlm-export-column">
                            <input
                                v-model="exportColumns"
                                type="checkbox"
                                name="columns[]"
                                :value="column.key"
                                class="dlm-checkbox"
                            />
                            <span>{{ column.label }}</span>
                        </label>
                    </div>
                    <p v-if="exportColumns.length === 0" class="dlm-export-error" role="alert">
                        {{ trans('licenses.modals.export.no_columns') }}
                    </p>
                </fieldset>

                <div v-if="exportsFullLicenseKeys" class="dlm-export-sensitive-notice">
                    <span class="dashicons dashicons-lock" aria-hidden="true"></span>
                    <span>{{ trans('licenses.modals.export.sensitive_notice') }}</span>
                </div>
            </form>

            <template #footer>
                <button type="button" class="dlm-btn dlm-btn-secondary" :disabled="exportSubmitting" @click="closeExportModal">
                    {{ trans('global.buttons.cancel') }}
                </button>
                <button
                    type="submit"
                    form="dlm-license-export-form"
                    class="dlm-btn dlm-btn-primary"
                    :disabled="exportSubmitting || exportColumns.length === 0 || !exportScopeAvailable"
                >
                    {{ exportSubmitting ? trans('licenses.modals.export.preparing') : trans('licenses.modals.export.download') }}
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
import ActionMenu from '@digital-license-manager/ui/components/ActionMenu.vue'
import LicenseKey from '../components/LicenseKey.vue'

const alertStore = useAlertStore()
const exportConfig = window.DLMAdmin?.config?.licenseExport || {
    enabled: false,
    url: '',
    nonce: '',
    columns: [],
}

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
const showExportModal = ref(false)
const exportScope = ref('filtered')
const exportColumns = ref([])
const exportSubmitting = ref(false)

const pagination = reactive({
    currentPage: 1,
    totalPages: 1,
    total: 0,
})

const appliedQuery = reactive({
    search: '',
    status: '',
    orderby: 'id',
    order: 'desc',
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

const canExport = computed(() => Boolean(exportConfig.enabled && exportConfig.url && exportConfig.nonce))
const exportAvailable = computed(() => pagination.total > 0 || selectedIds.value.length > 0)
const exportScopeAvailable = computed(() => (
    exportScope.value === 'selected' ? selectedIds.value.length > 0 : pagination.total > 0
))
const exportsFullLicenseKeys = computed(() => exportColumns.value.includes('license_key'))

// Methods
async function loadLicenses() {
    loading.value = true

    const requestQuery = {
        page: pagination.currentPage,
        per_page: perPage.value,
        search: search.value,
        status: currentStatus.value !== 'all' ? currentStatus.value : '',
        orderby: sortBy.value,
        order: sortOrder.value,
    }

    try {
        const response = await licensesService.query(requestQuery)

        const json = await response.json()

        if (json.success) {
            licenses.value = json.data.records
            pagination.currentPage = json.data.pagination.current_page
            pagination.totalPages = json.data.pagination.total_pages
            pagination.total = json.data.pagination.total
            Object.assign(appliedQuery, {
                search: requestQuery.search,
                status: requestQuery.status,
                orderby: requestQuery.orderby,
                order: requestQuery.order,
            })

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

function applySearch() {
    pagination.currentPage = 1
    loadLicenses()
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

    if (bulkAction.value === 'export') {
        openExportModal('selected')
        bulkAction.value = ''
        return
    }

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

function openExportModal(preferredScope = '') {
    exportScope.value = preferredScope === 'selected' && selectedIds.value.length
        ? 'selected'
        : (selectedIds.value.length ? 'selected' : 'filtered')
    exportColumns.value = exportConfig.columns.map(column => column.key)
    exportSubmitting.value = false
    showExportModal.value = true
}

function closeExportModal() {
    if (exportSubmitting.value) return
    showExportModal.value = false
}

function selectAllExportColumns() {
    exportColumns.value = exportConfig.columns.map(column => column.key)
}

function clearExportColumns() {
    exportColumns.value = []
}

function handleExportSubmit(event) {
    if (!exportScopeAvailable.value || exportColumns.value.length === 0) {
        event.preventDefault()
        return
    }

    exportSubmitting.value = true
    window.setTimeout(() => {
        showExportModal.value = false
        exportSubmitting.value = false
    }, 750)
}

function replaceCount(message, count) {
    return message.replace('%d', String(count))
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

.dlm-export-form {
    display: grid;
    gap: 22px;
}

.dlm-export-fieldset {
    min-width: 0;
    margin: 0;
    padding: 0;
    border: 0;
}

.dlm-export-fieldset > legend {
    margin-bottom: 10px;
    color: var(--dlm-admin-ink);
    font-size: 13px;
    font-weight: 700;
}

.dlm-export-scope-options {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.dlm-export-scope-option {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 13px;
    background: #fff;
    border: 1px solid var(--dlm-admin-border);
    border-radius: 8px;
    cursor: pointer;
}

.dlm-export-scope-option:only-child {
    grid-column: 1 / -1;
}

.dlm-export-scope-option.is-selected {
    background: #f2fbfa;
    border-color: var(--dlm-admin-primary);
    box-shadow: 0 0 0 1px color-mix(in srgb, var(--dlm-admin-primary) 20%, transparent);
}

.dlm-export-scope-option.is-disabled {
    opacity: .55;
    cursor: not-allowed;
}

.dlm-export-scope-option input {
    margin-top: 2px;
}

.dlm-export-scope-option span {
    display: grid;
    gap: 3px;
}

.dlm-export-scope-option strong {
    color: var(--dlm-admin-ink);
    font-size: 12px;
}

.dlm-export-scope-option small {
    color: var(--dlm-admin-muted);
    font-size: 11px;
    line-height: 1.45;
}

.dlm-export-columns-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
    margin: -25px 0 10px;
    color: var(--dlm-admin-muted);
}

.dlm-export-columns-actions button {
    padding: 0;
    color: var(--dlm-admin-primary);
    font-size: 11px;
    background: transparent;
    border: 0;
    cursor: pointer;
}

.dlm-export-columns-actions button:hover {
    color: var(--dlm-admin-primary-hover);
    text-decoration: underline;
}

.dlm-export-columns {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    overflow: hidden;
    border: 1px solid var(--dlm-admin-border);
    border-radius: 8px;
}

.dlm-export-column {
    display: flex;
    min-height: 40px;
    align-items: center;
    gap: 8px;
    padding: 9px 12px;
    color: var(--dlm-admin-ink);
    font-size: 12px;
    border-bottom: 1px solid #edf1ef;
    cursor: pointer;
}

.dlm-export-column:nth-child(odd) {
    border-right: 1px solid #edf1ef;
}

.dlm-export-column:last-child {
    border-bottom: 0;
}

.dlm-export-error {
    margin: 8px 0 0;
    color: #b42318;
    font-size: 11px;
}

.dlm-export-sensitive-notice {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 11px 12px;
    color: #8a4b08;
    font-size: 11px;
    line-height: 1.45;
    background: #fffaeb;
    border: 1px solid #fedf89;
    border-radius: 8px;
}

.dlm-export-sensitive-notice .dashicons {
    width: 17px;
    height: 17px;
    flex: 0 0 17px;
    font-size: 17px;
}

@media (max-width: 600px) {
    .dlm-export-scope-options,
    .dlm-export-columns {
        grid-template-columns: minmax(0, 1fr);
    }

    .dlm-export-column:nth-child(odd) {
        border-right: 0;
    }

}
</style>
