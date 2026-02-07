import { ajaxGet, ajaxPost } from '@digital-license-manager/ui/utils/useRequest'

/**
 * Query licenses with pagination and filters.
 * @param {Object} params - Query parameters
 * @returns {Promise<Response>}
 */
export async function query(params = {}) {
    return ajaxGet('dlm_admin_licenses_query', params)
}

/**
 * Find a single license by ID.
 * @param {number|string} id - License ID
 * @returns {Promise<Response>}
 */
export async function find(id) {
    return ajaxGet('dlm_admin_licenses_find', { id })
}

/**
 * Create a new license.
 * @param {Object} data - License data
 * @returns {Promise<Response>}
 */
export async function create(data) {
    return ajaxPost('dlm_admin_licenses_store', data)
}

/**
 * Update an existing license.
 * @param {number|string} id - License ID
 * @param {Object} data - License data
 * @returns {Promise<Response>}
 */
export async function update(id, data) {
    return ajaxPost('dlm_admin_licenses_store', { id, ...data })
}

/**
 * Delete a license.
 * @param {number|string} id - License ID
 * @returns {Promise<Response>}
 */
export async function remove(id) {
    return ajaxPost('dlm_admin_licenses_delete', { id })
}

/**
 * Perform a bulk action on licenses.
 * @param {string} action - Bulk action name
 * @param {Array<number>} ids - License IDs
 * @returns {Promise<Response>}
 */
export async function bulkAction(action, ids) {
    return ajaxPost('dlm_admin_licenses_bulk_action', { action, ids })
}

/**
 * Show (decrypt) a license key.
 * @param {number|string} id - License ID
 * @returns {Promise<Response>}
 */
export async function showKey(id) {
    return ajaxPost('dlm_admin_licenses_show_key', { id })
}

/**
 * Generate a new license key without saving.
 * @returns {Promise<Response>}
 */
export async function generateKey() {
    return ajaxGet('dlm_admin_licenses_generate_key')
}

/**
 * Import licenses.
 * @param {Object} data - Import data
 * @returns {Promise<Response>}
 */
export async function importLicenses(data) {
    return ajaxPost('dlm_admin_licenses_import', data)
}

/**
 * Export licenses.
 * @param {Object} params - Export parameters
 * @returns {Promise<Response>}
 */
export async function exportLicenses(params = {}) {
    return ajaxGet('dlm_admin_licenses_export', params)
}
