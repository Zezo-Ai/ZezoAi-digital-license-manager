<template>
    <Page :title="trans('generators.generate.title')">
        <form @submit.prevent="generateLicenses" class="dlm-card">
            <div class="dlm-card-body">
                <div class="dlm-grid dlm-grid-cols-2 dlm-gap-6">
                    <!-- Generator -->
                    <div class="dlm-form-group">
                        <label for="generator_id">{{ trans('generators.generate.fields.generator') }} *</label>
                        <Dropdown
                            id="generator_id"
                            v-model="form.generator_id"
                            :options="generatorOptions"
                            :placeholder="trans('generators.generate.placeholders.generator')"
                        />
                    </div>

                    <!-- Quantity -->
                    <div class="dlm-form-group">
                        <label for="quantity">{{ trans('generators.generate.fields.quantity') }} *</label>
                        <input
                            id="quantity"
                            v-model.number="form.quantity"
                            type="number"
                            min="1"
                            max="1000"
                            class="dlm-input"
                            required
                        />
                        <p class="dlm-form-hint">{{ trans('generators.generate.hints.quantity') }}</p>
                    </div>

                    <!-- Product -->
                    <div class="dlm-form-group">
                        <label for="product_id">{{ trans('generators.generate.fields.product') }}</label>
                        <AsyncSelect
                            id="product_id"
                            v-model="form.product_id"
                            search-type="product"
                            :placeholder="trans('generators.generate.placeholders.product')"
                        />
                        <p class="dlm-form-hint">{{ trans('generators.generate.hints.product') }}</p>
                    </div>

                    <!-- Order -->
                    <div class="dlm-form-group">
                        <label for="order_id">{{ trans('generators.generate.fields.order') }}</label>
                        <AsyncSelect
                            id="order_id"
                            v-model="form.order_id"
                            :search-action="'dlm_admin_search_orders'"
                            :placeholder="trans('generators.generate.placeholders.order')"
                        />
                    </div>

                    <!-- Status -->
                    <div class="dlm-form-group">
                        <label for="status">{{ trans('generators.generate.fields.status') }}</label>
                        <Dropdown
                            id="status"
                            v-model="form.status"
                            :options="statusOptions"
                        />
                    </div>

                    <!-- Valid For (days) -->
                    <div class="dlm-form-group">
                        <label for="valid_for">{{ trans('generators.generate.fields.valid_for') }}</label>
                        <input
                            id="valid_for"
                            v-model.number="form.valid_for"
                            type="number"
                            min="0"
                            class="dlm-input"
                            :placeholder="trans('generators.generate.placeholders.valid_for')"
                        />
                        <p class="dlm-form-hint">{{ trans('generators.generate.hints.valid_for') }}</p>
                    </div>

                    <!-- Save to database checkbox -->
                    <div class="dlm-form-group dlm-flex dlm-items-center dlm-gap-2">
                        <input
                            id="save"
                            v-model="form.save"
                            type="checkbox"
                            class="dlm-checkbox"
                        />
                        <label for="save" class="dlm-m-0">{{ trans('generators.generate.fields.save') }}</label>
                    </div>
                </div>
            </div>

            <div class="dlm-card-footer">
                <router-link to="/generators" class="dlm-btn dlm-btn-secondary">
                    {{ trans('global.buttons.cancel') }}
                </router-link>
                <button type="submit" class="dlm-btn dlm-btn-primary" :disabled="generating">
                    <span v-if="generating">{{ trans('generators.generate.buttons.generating') }}</span>
                    <span v-else>{{ trans('generators.generate.buttons.generate') }}</span>
                </button>
            </div>
        </form>

        <!-- Generated Licenses -->
        <div v-if="generatedLicenses.length > 0" class="dlm-card dlm-mt-6">
            <div class="dlm-card-header dlm-flex dlm-items-center dlm-justify-between">
                <h3>{{ trans('generators.generate.results.title') }} ({{ generatedLicenses.length }})</h3>
                <div class="dlm-flex dlm-gap-2">
                    <button class="dlm-btn dlm-btn-secondary dlm-btn-sm" @click="copyToClipboard">
                        {{ trans('generators.generate.buttons.copy') }}
                    </button>
                    <button class="dlm-btn dlm-btn-secondary dlm-btn-sm" @click="downloadCsv">
                        {{ trans('generators.generate.buttons.download') }}
                    </button>
                </div>
            </div>
            <div class="dlm-card-body">
                <textarea
                    ref="licensesTextarea"
                    class="dlm-textarea dlm-font-mono"
                    rows="10"
                    readonly
                    :value="generatedLicenses.join('\n')"
                ></textarea>
            </div>
        </div>
    </Page>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { trans } from '../utils/useLang'
import { useAlertStore } from '../stores/alert'
import * as generatorsService from '../services/generators'
import Page from '../components/Page.vue'
import AsyncSelect from '../components/input/AsyncSelect.vue'
import Dropdown from '../components/input/Dropdown.vue'

const alertStore = useAlertStore()

const generating = ref(false)
const generatorOptions = ref([])
const generatedLicenses = ref([])
const licensesTextarea = ref(null)

const form = reactive({
    generator_id: null,
    quantity: 10,
    product_id: null,
    order_id: null,
    status: 'inactive',
    valid_for: null,
    save: true,
})

const statusOptions = [
    { value: 'active', label: trans('licenses.statuses.active') },
    { value: 'inactive', label: trans('licenses.statuses.inactive') },
    { value: 'sold', label: trans('licenses.statuses.sold') },
    { value: 'delivered', label: trans('licenses.statuses.delivered') },
]

async function loadGenerators() {
    try {
        const response = await generatorsService.query({ per_page: 100 })
        const json = await response.json()

        if (json.success) {
            generatorOptions.value = json.data.records.map(g => ({
                value: g.id,
                label: g.name,
            }))
        }
    } catch (error) {
        console.error('Failed to load generators:', error)
    }
}

async function generateLicenses() {
    if (!form.generator_id) {
        alertStore.error(trans('generators.generate.errors.no_generator'))
        return
    }

    generating.value = true
    generatedLicenses.value = []

    try {
        const response = await generatorsService.generate(form)
        const json = await response.json()

        if (json.success) {
            generatedLicenses.value = json.data.licenses
            alertStore.success(json.data.message)
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        generating.value = false
    }
}

function copyToClipboard() {
    if (licensesTextarea.value) {
        licensesTextarea.value.select()
        document.execCommand('copy')
        alertStore.success(trans('generators.generate.messages.copied'))
    }
}

function downloadCsv() {
    const content = generatedLicenses.value.join('\n')
    const blob = new Blob([content], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `licenses-${Date.now()}.csv`
    link.click()
    window.URL.revokeObjectURL(url)
}

onMounted(() => {
    loadGenerators()
})
</script>

<style lang="scss" scoped>
.dlm-grid {
    display: grid;
}

.dlm-grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.dlm-gap-6 {
    gap: 1.5rem;
}

.dlm-mt-6 {
    margin-top: 1.5rem;
}

.dlm-font-mono {
    font-family: monospace;
}
</style>
