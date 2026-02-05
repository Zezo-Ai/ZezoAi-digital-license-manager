<template>
    <Page :title="isEditing ? trans('generators.titles.edit') : trans('generators.titles.add')">
        <form @submit.prevent="saveGenerator" class="dlm-card">
            <div class="dlm-card-body">
                <div class="dlm-grid dlm-grid-cols-2 dlm-gap-6">
                    <!-- Name -->
                    <div class="dlm-form-group dlm-col-span-2">
                        <label for="name">{{ trans('generators.fields.name') }} *</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="dlm-input"
                            required
                            :placeholder="trans('generators.placeholders.name')"
                        />
                    </div>

                    <!-- Products -->
                    <div class="dlm-form-group dlm-col-span-2">
                        <label for="product_ids">{{ trans('generators.fields.products') }}</label>
                        <AsyncSelect
                            id="product_ids"
                            v-model="form.product_ids"
                            search-type="product"
                            :placeholder="trans('generators.placeholders.products')"
                            :multiple="true"
                            :initial-options="initialProducts"
                        />
                        <p class="dlm-form-hint">{{ trans('generators.hints.products') }}</p>
                    </div>

                    <!-- Charset -->
                    <div class="dlm-form-group dlm-col-span-2">
                        <label for="charset">{{ trans('generators.fields.charset') }} *</label>
                        <input
                            id="charset"
                            v-model="form.charset"
                            type="text"
                            class="dlm-input"
                            required
                            :placeholder="trans('generators.placeholders.charset')"
                        />
                        <p class="dlm-form-hint">{{ trans('generators.hints.charset') }}</p>
                    </div>

                    <!-- Chunks -->
                    <div class="dlm-form-group">
                        <label for="chunks">{{ trans('generators.fields.chunks') }} *</label>
                        <input
                            id="chunks"
                            v-model.number="form.chunks"
                            type="number"
                            min="1"
                            max="10"
                            class="dlm-input"
                            required
                        />
                        <p class="dlm-form-hint">{{ trans('generators.hints.chunks') }}</p>
                    </div>

                    <!-- Chunk Length -->
                    <div class="dlm-form-group">
                        <label for="chunk_length">{{ trans('generators.fields.chunk_length') }} *</label>
                        <input
                            id="chunk_length"
                            v-model.number="form.chunk_length"
                            type="number"
                            min="1"
                            max="20"
                            class="dlm-input"
                            required
                        />
                        <p class="dlm-form-hint">{{ trans('generators.hints.chunk_length') }}</p>
                    </div>

                    <!-- Separator -->
                    <div class="dlm-form-group">
                        <label for="separator">{{ trans('generators.fields.separator') }}</label>
                        <input
                            id="separator"
                            v-model="form.separator"
                            type="text"
                            maxlength="1"
                            class="dlm-input"
                        />
                        <p class="dlm-form-hint">{{ trans('generators.hints.separator') }}</p>
                    </div>

                    <!-- Prefix -->
                    <div class="dlm-form-group">
                        <label for="prefix">{{ trans('generators.fields.prefix') }}</label>
                        <input
                            id="prefix"
                            v-model="form.prefix"
                            type="text"
                            class="dlm-input"
                        />
                        <p class="dlm-form-hint">{{ trans('generators.hints.prefix') }}</p>
                    </div>

                    <!-- Suffix -->
                    <div class="dlm-form-group">
                        <label for="suffix">{{ trans('generators.fields.suffix') }}</label>
                        <input
                            id="suffix"
                            v-model="form.suffix"
                            type="text"
                            class="dlm-input"
                        />
                        <p class="dlm-form-hint">{{ trans('generators.hints.suffix') }}</p>
                    </div>

                    <!-- Max Activations -->
                    <div class="dlm-form-group">
                        <label for="times_activated_max">{{ trans('generators.fields.max_activations') }}</label>
                        <input
                            id="times_activated_max"
                            v-model.number="form.times_activated_max"
                            type="number"
                            min="0"
                            class="dlm-input"
                            :placeholder="trans('generators.placeholders.max_activations')"
                        />
                        <p class="dlm-form-hint">{{ trans('generators.hints.max_activations') }}</p>
                    </div>

                    <!-- Expires In -->
                    <div class="dlm-form-group">
                        <label for="expires_in">{{ trans('generators.fields.expires_in') }}</label>
                        <input
                            id="expires_in"
                            v-model.number="form.expires_in"
                            type="number"
                            min="0"
                            class="dlm-input"
                            :placeholder="trans('generators.placeholders.expires_in')"
                        />
                        <p class="dlm-form-hint">{{ trans('generators.hints.expires_in') }}</p>
                    </div>

                    <!-- Preview -->
                    <div class="dlm-form-group dlm-col-span-2">
                        <label>{{ trans('generators.fields.preview') }}</label>
                        <div class="dlm-license-preview">
                            <code>{{ previewKey }}</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dlm-card-footer">
                <router-link to="/generators" class="dlm-btn dlm-btn-secondary">
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
import * as generatorsService from '../services/generators'
import Page from '../components/Page.vue'
import AsyncSelect from '../components/input/AsyncSelect.vue'

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
const generatorId = computed(() => props.id || route.params.id)

const loading = ref(false)
const saving = ref(false)
const initialProducts = ref([])

const form = reactive({
    name: '',
    product_ids: [],
    charset: 'ABCDEFGHJKMNPQRSTUVWXYZ23456789',
    chunks: 4,
    chunk_length: 4,
    separator: '-',
    prefix: '',
    suffix: '',
    times_activated_max: null,
    expires_in: null,
})

const previewKey = computed(() => {
    const { charset, chunks, chunk_length, separator, prefix, suffix } = form

    if (!charset || !chunks || !chunk_length) {
        return ''
    }

    let key = ''
    for (let i = 0; i < chunks; i++) {
        if (i > 0 && separator) {
            key += separator
        }
        for (let j = 0; j < chunk_length; j++) {
            key += charset.charAt(Math.floor(Math.random() * charset.length))
        }
    }

    return (prefix || '') + key + (suffix || '')
})

async function loadGenerator() {
    if (!generatorId.value) return

    loading.value = true

    try {
        const response = await generatorsService.find(generatorId.value)
        const json = await response.json()

        if (json.success) {
            const generator = json.data.record

            form.name = generator.name
            form.product_ids = generator.product_ids || []
            form.charset = generator.charset
            form.chunks = generator.chunks
            form.chunk_length = generator.chunk_length
            form.separator = generator.separator || '-'
            form.prefix = generator.prefix || ''
            form.suffix = generator.suffix || ''
            form.times_activated_max = generator.times_activated_max
            form.expires_in = generator.expires_in

            if (generator.products && generator.products.length > 0) {
                initialProducts.value = generator.products.map(p => ({
                    id: p.id,
                    text: p.name,
                }))
            }
        } else {
            alertStore.error(json.data.message)
            router.push('/generators')
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
        router.push('/generators')
    } finally {
        loading.value = false
    }
}

async function saveGenerator() {
    saving.value = true

    try {
        const response = isEditing.value
            ? await generatorsService.update(generatorId.value, form)
            : await generatorsService.create(form)

        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            router.push('/generators')
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        saving.value = false
    }
}

onMounted(() => {
    if (isEditing.value) {
        loadGenerator()
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

.dlm-license-preview {
    @apply dlm-p-4 dlm-bg-gray-100 dlm-rounded-md dlm-font-mono dlm-text-lg dlm-text-center;
}
</style>
