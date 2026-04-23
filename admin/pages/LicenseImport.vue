<template>
    <div>
        <!-- Page header -->
        <div class="dlm-page-header">
            <h1>{{ trans('licenses.titles.import') }}</h1>
        </div>

        <form @submit.prevent="importLicenses" class="dlm-card">
            <div class="dlm-card-body">
                <div class="dlm-form-grid">
                    <!-- License Keys -->
                    <TextArea
                        id="license_keys"
                        v-model="form.license_keys"
                        class="col-span-2"
                        :label="trans('licenses.import.fields.license_keys')"
                        :placeholder="trans('licenses.import.placeholders.license_keys')"
                        :hint="trans('licenses.import.hints.license_keys')"
                        :rows="10"
                    />
                    <!-- Product -->
                    <div class="dlm-form-group">
                        <label for="product_id">{{ trans('licenses.fields.product') }}</label>
                        <AsyncSelect
                            id="product_id"
                            v-model="form.product_id"
                            search-type="product"
                            :placeholder="trans('licenses.placeholders.product')"
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
                        <input
                            id="valid_for"
                            v-model="form.valid_for"
                            type="number"
                            min="0"
                            class="dlm-input"
                            :placeholder="trans('licenses.placeholders.valid_for_days')"
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
                    </div>
                </div>
            </div>

            <div class="dlm-card-footer">
                <router-link to="/" class="dlm-btn dlm-btn-secondary">
                    {{ trans('global.buttons.cancel') }}
                </router-link>
                <button type="submit" class="dlm-btn dlm-btn-primary" :disabled="importing">
                    <span v-if="importing">{{ trans('licenses.import.buttons.importing') }}</span>
                    <span v-else>{{ trans('licenses.import.buttons.import') }}</span>
                </button>
            </div>
        </form>

        <!-- Import Results -->
        <div v-if="results" class="dlm-card mt-6">
            <div class="dlm-card-header">
                <h3>{{ trans('licenses.import.results.title') }}</h3>
            </div>
            <div class="dlm-card-body">
                <div class="dlm-import-stats">
                    <div class="dlm-stat">
                        <span class="dlm-stat-value text-success-600">{{ results.imported }}</span>
                        <span class="dlm-stat-label">{{ trans('licenses.import.results.imported') }}</span>
                    </div>
                    <div class="dlm-stat">
                        <span class="dlm-stat-value text-warning-600">{{ results.skipped }}</span>
                        <span class="dlm-stat-label">{{ trans('licenses.import.results.skipped') }}</span>
                    </div>
                    <div class="dlm-stat">
                        <span class="dlm-stat-value text-danger-600">{{ results.failed }}</span>
                        <span class="dlm-stat-label">{{ trans('licenses.import.results.failed') }}</span>
                    </div>
                </div>

                <div v-if="results.errors && results.errors.length > 0" class="mt-4">
                    <h4>{{ trans('licenses.import.results.errors') }}</h4>
                    <ul class="dlm-error-list">
                        <li v-for="(error, index) in results.errors" :key="index">{{ error }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { trans } from '@digital-license-manager/ui/utils/useLang'
import { useAlertStore } from '@digital-license-manager/ui/stores/alert'
import * as licensesService from '../services/licenses'
import AsyncSelect from '@digital-license-manager/ui/components/input/AsyncSelect.vue'
import Dropdown from '@digital-license-manager/ui/components/input/Dropdown.vue'
import TextArea from '@digital-license-manager/ui/components/input/TextArea.vue'

const alertStore = useAlertStore()

const importing = ref(false)
const results = ref(null)

const form = reactive({
    license_keys: '',
    product_id: null,
    status: 'inactive',
    valid_for: null,
    activations_limit: null,
})

const statusOptions = [
    { value: 'active', label: trans('licenses.statuses.active') },
    { value: 'inactive', label: trans('licenses.statuses.inactive') },
    { value: 'sold', label: trans('licenses.statuses.sold') },
    { value: 'delivered', label: trans('licenses.statuses.delivered') },
    { value: 'disabled', label: trans('licenses.statuses.disabled') },
]

async function importLicenses() {
    if (!form.license_keys.trim()) {
        alertStore.error(trans('licenses.import.errors.empty'))
        return
    }

    importing.value = true
    results.value = null

    try {
        const response = await licensesService.importLicenses(form)
        const json = await response.json()

        if (json.success) {
            results.value = json.data
            alertStore.success(json.data.message)
            form.license_keys = ''
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        importing.value = false
    }
}

</script>

<style lang="scss" scoped>
.dlm-import-stats {
    @apply flex gap-8;
}

.dlm-stat {
    @apply text-center;

    &-value {
        @apply block text-3xl font-bold;
    }

    &-label {
        @apply text-sm text-gray-500;
    }
}

.dlm-error-list {
    @apply mt-2 pl-5 text-sm text-danger-600 list-disc;
}
</style>
