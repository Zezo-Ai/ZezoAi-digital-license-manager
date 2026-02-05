<template>
    <Page :title="isEditing ? trans('licenses.titles.edit') : trans('licenses.titles.add')">
        <form @submit.prevent="saveLicense" class="dlm-card">
            <div class="dlm-card-body">
                <div class="dlm-grid dlm-grid-cols-2 dlm-gap-6">
                    <!-- License Key -->
                    <div class="dlm-form-group dlm-col-span-2">
                        <label for="license_key">{{ trans('licenses.fields.license_key') }}</label>
                        <div class="dlm-flex dlm-gap-2">
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
                    </div>

                    <!-- Status -->
                    <div class="dlm-form-group">
                        <label for="status">{{ trans('licenses.fields.status') }}</label>
                        <Dropdown
                            id="status"
                            v-model="form.status"
                            :options="statusOptions"
                        />
                    </div>

                    <!-- Valid For -->
                    <div class="dlm-form-group">
                        <label for="valid_for">{{ trans('licenses.fields.valid_for') }}</label>
                        <div class="dlm-flex dlm-gap-2">
                            <input
                                id="valid_for"
                                v-model="form.valid_for"
                                type="number"
                                min="0"
                                class="dlm-input"
                            />
                            <Dropdown
                                v-model="form.valid_for_unit"
                                :options="validForUnits"
                                class="dlm-w-32"
                            />
                        </div>
                        <p class="dlm-form-hint">{{ trans('licenses.hints.valid_for') }}</p>
                    </div>

                    <!-- Expires At -->
                    <div class="dlm-form-group">
                        <label for="expires_at">{{ trans('licenses.fields.expires_at') }}</label>
                        <DateTimePicker
                            id="expires_at"
                            v-model="form.expires_at"
                            :placeholder="trans('licenses.placeholders.expires_at')"
                        />
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

                    <!-- Source -->
                    <div class="dlm-form-group">
                        <label for="source">{{ trans('licenses.fields.source') }}</label>
                        <Dropdown
                            id="source"
                            v-model="form.source"
                            :options="sourceOptions"
                        />
                    </div>
                </div>
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
    </Page>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { trans } from '../utils/useLang'
import { useAlertStore } from '../stores/alert'
import * as licensesService from '../services/licenses'
import Page from '../components/Page.vue'
import AsyncSelect from '../components/input/AsyncSelect.vue'
import Dropdown from '../components/input/Dropdown.vue'
import DateTimePicker from '../components/input/DateTimePicker.vue'

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

const loading = ref(false)
const saving = ref(false)

const form = reactive({
    license_key: '',
    product_id: null,
    order_id: null,
    user_id: null,
    status: 'inactive',
    valid_for: null,
    valid_for_unit: 'days',
    expires_at: null,
    activations_limit: null,
    source: 'api',
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

const validForUnits = [
    { value: 'days', label: trans('licenses.units.days') },
    { value: 'weeks', label: trans('licenses.units.weeks') },
    { value: 'months', label: trans('licenses.units.months') },
    { value: 'years', label: trans('licenses.units.years') },
]

const sourceOptions = [
    { value: 'api', label: 'API' },
    { value: 'import', label: trans('licenses.sources.import') },
    { value: 'generator', label: trans('licenses.sources.generator') },
    { value: 'migration', label: trans('licenses.sources.migration') },
]

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
            form.status = license.status
            form.valid_for = license.valid_for
            form.valid_for_unit = license.valid_for_unit || 'days'
            form.expires_at = license.expires_at
            form.activations_limit = license.activations_limit
            form.source = license.source || 'api'

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

onMounted(() => {
    if (isEditing.value) {
        loadLicense()
    }
})
</script>

<style lang="scss" scoped>
.dlm-grid {
    display: grid;
}

.dlm-grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.dlm-col-span-2 {
    grid-column: span 2 / span 2;
}

.dlm-gap-6 {
    gap: 1.5rem;
}
</style>
