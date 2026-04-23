<template>
    <div>
        <!-- Page header -->
        <div class="dlm-page-header">
            <h1>{{ pageTitle }}</h1>
        </div>

        <form @submit.prevent="saveLicense" class="dlm-card">
        <div class="dlm-card-body">
            <div class="dlm-form-grid">
                <!-- License Key -->
                <div class="dlm-form-group col-span-2">
                    <label for="license_key">{{ trans('licenses.fields.license_key') }}</label>
                    <div class="flex gap-2">
                        <input
                            id="license_key"
                            v-model="form.license_key"
                            type="text"
                            class="dlm-input"
                            :placeholder="trans('licenses.placeholders.license_key')"
                        />
                        <button
                            type="button"
                            class="dlm-btn dlm-btn-secondary"
                            @click="generateKey"
                        >
                            {{ trans('licenses.buttons.generate') }}
                        </button>
                    </div>
                    <p class="dlm-form-hint">{{ trans('licenses.hints.license_key') }}</p>
                </div>

                <!-- Status -->
                <div class="dlm-form-group">
                    <label for="status">{{ trans('licenses.fields.status') }}</label>
                    <Dropdown
                        id="status"
                        v-model="form.status"
                        :options="statusOptions"
                    />
                    <p class="dlm-form-hint">{{ trans('licenses.hints.status') }}</p>
                </div>

                <!-- Product -->
                <div class="dlm-form-group">
                    <label for="product_id">{{ trans('licenses.fields.product') }}</label>
                    <AsyncSelect
                        id="product_id"
                        v-model="form.product_id"
                        search-type="product"
                        :placeholder="trans('licenses.placeholders.product')"
                        :initial-option="initialProduct"
                    />
                    <p class="dlm-form-hint">{{ trans('licenses.hints.product') }}</p>
                </div>

                <!-- Order -->
                <div class="dlm-form-group">
                    <label for="order_id">{{ trans('licenses.fields.order') }}</label>
                    <AsyncSelect
                        id="order_id"
                        v-model="form.order_id"
                        :search-action="'dlm_admin_search_orders'"
                        :placeholder="trans('licenses.placeholders.order')"
                        :initial-option="initialOrder"
                    />
                    <p class="dlm-form-hint">{{ trans('licenses.hints.order') }}</p>
                </div>

                <!-- User -->
                <div class="dlm-form-group">
                    <label for="user_id">{{ trans('licenses.fields.user') }}</label>
                    <AsyncSelect
                        id="user_id"
                        v-model="form.user_id"
                        search-type="user"
                        :placeholder="trans('licenses.placeholders.user')"
                        :initial-option="initialUser"
                    />
                    <p class="dlm-form-hint">{{ trans('licenses.hints.user') }}</p>
                </div>

                <!-- Valid For -->
                <div v-if="isStockLicense" class="dlm-form-group">
                    <label for="valid_for">{{ trans('licenses.fields.valid_for') }}</label>
                    <input
                        id="valid_for"
                        v-model="form.valid_for"
                        type="number"
                        min="0"
                        class="dlm-input"
                        :placeholder="trans('licenses.placeholders.valid_for_days')"
                    />
                    <p class="dlm-form-hint">{{ trans('licenses.hints.valid_for') }}</p>
                </div>

                <!-- Expires At -->
                <div v-if="!isStockLicense" class="dlm-form-group">
                    <label for="expires_at">{{ trans('licenses.fields.expires_at') }}</label>
                    <DateTimePicker
                        id="expires_at"
                        v-model="form.expires_at"
                        :placeholder="trans('licenses.placeholders.expires_at')"
                    />
                    <p class="dlm-form-hint">{{ trans('licenses.hints.expires_at') }}</p>
                </div>

                <!-- Activations Limit -->
                <div class="dlm-form-group">
                    <label for="activations_limit">{{ trans('licenses.fields.activations_limit') }}</label>
                    <input
                        id="activations_limit"
                        v-model="form.activations_limit"
                        type="number"
                        min="0"
                        class="dlm-input"
                        :placeholder="trans('licenses.placeholders.activations_limit')"
                    />
                    <p class="dlm-form-hint">{{ trans('licenses.hints.activations_limit') }}</p>
                </div>
            </div>

            <!-- Extension panels (added by PRO or other addons) -->
            <template v-if="extensionPanels.length > 0">
                <div
                    v-for="panel in extensionPanels"
                    :key="panel.id"
                    class="dlm-extension-panel"
                >
                    <h3 v-if="panel.title" class="dlm-extension-panel-title">{{ panel.title }}</h3>
                    <div class="dlm-extension-panel-content" v-html="panel.content"></div>
                </div>
            </template>
        </div>

        <div class="dlm-card-footer">
            <router-link to="/" class="dlm-btn dlm-btn-secondary">
                {{ trans('global.buttons.cancel') }}
            </router-link>
            <button type="submit" class="dlm-btn dlm-btn-primary" :disabled="saving">
                <span v-if="saving">{{ trans('global.buttons.saving') }}</span>
                <span v-else>{{ isEditing ? trans('global.buttons.update') : trans('global.buttons.create') }}</span>
            </button>
        </div>
    </form>

        <!-- Activations Panel (edit mode only) -->
        <div v-if="isEditing" class="dlm-card mt-4">
            <div class="dlm-card-body">
                <h3 class="mb-4">{{ trans('activations.title') }}</h3>

                <Table
                    :columns="activationColumns"
                    :rows="activations"
                    :loading="activationsLoading"
                    row-key="id"
                >
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
                            <button class="dlm-action-link text-danger-600" @click="confirmDeleteActivation(row)">
                                {{ trans('activations.actions.delete') }}
                            </button>
                        </div>
                    </template>
                </Table>

                <Pager
                    v-if="activationsPagination.total > 0"
                    :current-page="activationsPagination.currentPage"
                    :total-pages="activationsPagination.totalPages"
                    :total-items="activationsPagination.total"
                    :per-page="activationsPagination.perPage"
                    @page-change="goToActivationsPage"
                />
            </div>
        </div>

        <!-- Delete Activation Confirmation Modal -->
        <Modal
            :show="showDeleteActivationModal"
            :title="trans('activations.modals.delete.title')"
            @close="showDeleteActivationModal = false"
        >
            <p>{{ trans('activations.modals.delete.message') }}</p>
            <template #footer>
                <button class="dlm-btn dlm-btn-secondary" @click="showDeleteActivationModal = false">
                    {{ trans('global.buttons.cancel') }}
                </button>
                <button class="dlm-btn dlm-btn-danger" :disabled="deletingActivation" @click="deleteActivation">
                    {{ trans('global.buttons.delete') }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { trans } from '@digital-license-manager/ui/utils/useLang'
import { useAlertStore } from '@digital-license-manager/ui/stores/alert'
import * as licensesService from '../services/licenses'
import * as activationsService from '../services/activations'
import AsyncSelect from '@digital-license-manager/ui/components/input/AsyncSelect.vue'
import Dropdown from '@digital-license-manager/ui/components/input/Dropdown.vue'
import DateTimePicker from '@digital-license-manager/ui/components/input/DateTimePicker.vue'
import Table from '@digital-license-manager/ui/components/Table.vue'
import Pager from '@digital-license-manager/ui/components/Pager.vue'
import Modal from '@digital-license-manager/ui/components/Modal.vue'

const props = defineProps({
    id: {
        type: [String, Number],
        default: null,
    },
})

const route = useRoute()
const router = useRouter()
const alertStore = useAlertStore()

const isEditing = computed(() => !!props.id || !!route.params.id)
const licenseId = computed(() => props.id || route.params.id)
const isStockLicense = computed(() => form.status === 'active')
const pageTitle = computed(() => {
    return isEditing.value
        ? `${trans('licenses.titles.edit')} #${licenseId.value}`
        : trans('licenses.titles.add')
})

const loading = ref(false)
const saving = ref(false)
const extensionPanels = ref([])

// Activations panel state
const activations = ref([])
const activationsLoading = ref(false)
const showDeleteActivationModal = ref(false)
const activationToDelete = ref(null)
const deletingActivation = ref(false)
const activationsPagination = reactive({
    currentPage: 1,
    totalPages: 1,
    total: 0,
    perPage: 25,
})

const activationColumns = computed(() => [
    { key: 'id', label: trans('activations.columns.id'), sortable: false, width: '80px' },
    { key: 'label', label: trans('activations.columns.label'), sortable: false },
    { key: 'source', label: trans('activations.columns.source'), sortable: false, width: '120px' },
    { key: 'ip_address', label: trans('activations.columns.ip_address'), sortable: false, width: '130px' },
    { key: 'status', label: trans('activations.columns.status'), sortable: false, width: '100px' },
    { key: 'created_at', label: trans('activations.columns.created_at'), sortable: false, width: '150px' },
    { key: 'actions', label: '', sortable: false, width: '120px' },
])

const form = reactive({
    license_key: '',
    product_id: null,
    order_id: null,
    user_id: null,
    status: 'inactive',
    valid_for: null,
    expires_at: null,
    activations_limit: null,
})

const initialProduct = ref(null)
const initialOrder = ref(null)
const initialUser = ref(null)

const statusOptions = [
    { value: 'active', label: trans('licenses.statuses.active') },
    { value: 'inactive', label: trans('licenses.statuses.inactive') },
    { value: 'sold', label: trans('licenses.statuses.sold') },
    { value: 'delivered', label: trans('licenses.statuses.delivered') },
    { value: 'disabled', label: trans('licenses.statuses.disabled') },
]

const skipStatusWatch = ref(false)

watch(() => form.status, (newStatus) => {
    if (skipStatusWatch.value) {
        skipStatusWatch.value = false
        return
    }
    if (newStatus === 'active') {
        form.expires_at = null
    } else {
        form.valid_for = null
    }
})

async function loadLicense() {
    if (!licenseId.value) return

    loading.value = true

    try {
        const response = await licensesService.find(licenseId.value)
        const json = await response.json()

        if (json.success) {
            const license = json.data.record

            form.license_key = license.decrypted_license_key || ''
            form.product_id = license.product_id
            form.order_id = license.order_id
            form.user_id = license.user_id
            skipStatusWatch.value = true
            form.status = license.status
            form.valid_for = license.valid_for
            form.expires_at = license.expires_at
            form.activations_limit = license.activations_limit

            // Set initial options for async selects
            if (license.product_name) {
                initialProduct.value = { id: license.product_id, text: license.product_name }
            }
            if (license.order_id) {
                initialOrder.value = { value: license.order_id, label: `#${license.order_id}` }
            }
            if (license.user_email) {
                initialUser.value = { id: license.user_id, text: license.user_email }
            }

            // Load extension panels (added by PRO or other addons)
            if (license.extension_panels && Array.isArray(license.extension_panels)) {
                extensionPanels.value = license.extension_panels
            }

            // Load activations for this license
            loadActivations()
        } else {
            alertStore.error(json.data.message)
            router.push('/')
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
        router.push('/')
    } finally {
        loading.value = false
    }
}

async function saveLicense() {
    saving.value = true

    try {
        const data = { ...form }

        const response = isEditing.value
            ? await licensesService.update(licenseId.value, data)
            : await licensesService.create(data)

        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            router.push('/')
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        saving.value = false
    }
}

async function generateKey() {
    try {
        const response = await licensesService.generateKey()
        const json = await response.json()

        if (json.success) {
            form.license_key = json.data.license_key
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    }
}

// Activations panel methods
async function loadActivations() {
    if (!licenseId.value) return

    activationsLoading.value = true

    try {
        const response = await activationsService.query({
            license_id: licenseId.value,
            page: activationsPagination.currentPage,
            per_page: activationsPagination.perPage,
        })

        const json = await response.json()

        if (json.success) {
            activations.value = json.data.records
            activationsPagination.currentPage = json.data.pagination.current_page
            activationsPagination.totalPages = json.data.pagination.total_pages
            activationsPagination.total = json.data.pagination.total
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        activationsLoading.value = false
    }
}

function goToActivationsPage(page) {
    activationsPagination.currentPage = page
    loadActivations()
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

function confirmDeleteActivation(row) {
    activationToDelete.value = row
    showDeleteActivationModal.value = true
}

async function deleteActivation() {
    if (!activationToDelete.value) return

    deletingActivation.value = true

    try {
        const response = await activationsService.remove(activationToDelete.value.id)
        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            showDeleteActivationModal.value = false
            loadActivations()
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        deletingActivation.value = false
    }
}

function formatDate(dateString) {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleString()
}

onMounted(() => {
    if (isEditing.value) {
        loadLicense()
    }
})

</script>

<style scoped>
@reference "../tailwind-entry.css";

.dlm-row-actions {
    @apply flex items-center gap-3;
}

.dlm-action-link {
    @apply text-sm cursor-pointer bg-transparent border-0 p-0;
}
</style>
