import { ajaxGet, ajaxPost } from '../utils/useRequest'

/**
 * Query generators with pagination.
 * @param {Object} params - Query parameters
 * @returns {Promise<Response>}
 */
export async function query(params = {}) {
    return ajaxGet('dlm_admin_generators_query', params)
}

/**
 * Find a single generator by ID.
 * @param {number|string} id - Generator ID
 * @returns {Promise<Response>}
 */
export async function find(id) {
    return ajaxGet('dlm_admin_generators_find', { id })
}

/**
 * Create a new generator.
 * @param {Object} data - Generator data
 * @returns {Promise<Response>}
 */
export async function create(data) {
    return ajaxPost('dlm_admin_generators_store', data)
}

/**
 * Update an existing generator.
 * @param {number|string} id - Generator ID
 * @param {Object} data - Generator data
 * @returns {Promise<Response>}
 */
export async function update(id, data) {
    return ajaxPost('dlm_admin_generators_store', { id, ...data })
}

/**
 * Delete a generator.
 * @param {number|string} id - Generator ID
 * @returns {Promise<Response>}
 */
export async function remove(id) {
    return ajaxPost('dlm_admin_generators_delete', { id })
}

/**
 * Generate licenses using a generator.
 * @param {Object} data - Generation parameters
 * @returns {Promise<Response>}
 */
export async function generate(data) {
    return ajaxPost('dlm_admin_generators_generate', data)
}
