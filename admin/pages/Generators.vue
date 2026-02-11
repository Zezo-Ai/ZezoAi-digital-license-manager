<template>
    <div>
        <!-- Page header -->
        <div class="dlm-page-header">
            <h1>{{ trans('generators.title') }}</h1>
            <div class="dlm-page-actions">
                <router-link to="/generators/add" class="dlm-btn dlm-btn-primary">
                    {{ trans('global.buttons.add_new') }}
                </router-link>
                <router-link to="/generators/generate" class="dlm-btn dlm-btn-secondary">
                    {{ trans('generators.buttons.generate') }}
                </router-link>
            </div>
        </div>

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
                            @keyup.enter="loadGenerators"
                        />
                    </div>
                    <div class="dlm-filter-item">
                        <select v-model="perPage" class="dlm-select" @change="loadGenerators">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <Table
                    :columns="columns"
                    :rows="generators"
                    :loading="loading"
                    row-key="id"
                    @sort="handleSort"
                >
                    <template #cell-product_ids="{ row }">
                        <span v-if="row.product_names">{{ row.product_names }}</span>
                        <span v-else class="dlm-text-gray-400">&mdash;</span>
                    </template>
                    <template #cell-times_activated_max="{ row }">
                        {{ row.times_activated_max || '&infin;' }}
                    </template>
                    <template #cell-actions="{ row }">
                        <div class="dlm-row-actions">
                            <router-link :to="`/generators/${row.id}/edit`" class="dlm-action-link">
                                {{ trans('global.actions.edit') }}
                            </router-link>
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
            :title="trans('generators.modals.delete.title')"
            @close="showDeleteModal = false"
        >
            <p>{{ trans('generators.modals.delete.message') }}</p>
            <template #footer>
                <button class="dlm-btn dlm-btn-secondary" @click="showDeleteModal = false">
                    {{ trans('global.buttons.cancel') }}
                </button>
                <button class="dlm-btn dlm-btn-danger" :disabled="deleting" @click="deleteGenerator">
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
import * as generatorsService from '../services/generators'
import Table from '@digital-license-manager/ui/components/Table.vue'
import Pager from '@digital-license-manager/ui/components/Pager.vue'
import Modal from '@digital-license-manager/ui/components/Modal.vue'

const alertStore = useAlertStore()

const loading = ref(true)
const generators = ref([])
const search = ref('')
const perPage = ref(25)
const sortBy = ref('id')
const sortOrder = ref('desc')
const showDeleteModal = ref(false)
const generatorToDelete = ref(null)
const deleting = ref(false)

const pagination = reactive({
    currentPage: 1,
    totalPages: 1,
    total: 0,
})

const columns = computed(() => [
    { key: 'id', label: trans('generators.columns.id'), sortable: true, width: '80px' },
    { key: 'name', label: trans('generators.columns.name'), sortable: true },
    { key: 'product_ids', label: trans('generators.columns.products'), sortable: false },
    { key: 'charset', label: trans('generators.columns.charset'), sortable: false },
    { key: 'chunks', label: trans('generators.columns.chunks'), sortable: false, width: '80px' },
    { key: 'chunk_length', label: trans('generators.columns.chunk_length'), sortable: false, width: '100px' },
    { key: 'times_activated_max', label: trans('generators.columns.max_activations'), sortable: false, width: '120px' },
    { key: 'actions', label: '', sortable: false, width: '100px' },
])

async function loadGenerators() {
    loading.value = true

    try {
        const response = await generatorsService.query({
            page: pagination.currentPage,
            per_page: perPage.value,
            search: search.value,
            orderby: sortBy.value,
            order: sortOrder.value,
        })

        const json = await response.json()

        if (json.success) {
            generators.value = json.data.records
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
    loadGenerators()
}

function handleSort({ key, order }) {
    sortBy.value = key
    sortOrder.value = order
    loadGenerators()
}

function confirmDelete(generator) {
    generatorToDelete.value = generator
    showDeleteModal.value = true
}

async function deleteGenerator() {
    if (!generatorToDelete.value) return

    deleting.value = true

    try {
        const response = await generatorsService.remove(generatorToDelete.value.id)
        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            showDeleteModal.value = false
            loadGenerators()
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        deleting.value = false
    }
}

onMounted(() => {
    loadGenerators()
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
</style>
