import { ajaxGet, ajaxPost } from '@digital-license-manager/ui/utils/useRequest'

/**
 * Query activations with pagination and filters.
 * @param {Object} params - Query parameters
 * @returns {Promise<Response>}
 */
export async function query(params = {}) {
    return ajaxGet('dlm_admin_activations_query', params)
}

/**
 * Find a single activation by ID.
 * @param {number|string} id - Activation ID
 * @returns {Promise<Response>}
 */
export async function find(id) {
    return ajaxGet('dlm_admin_activations_find', { id })
}

/**
 * Delete an activation.
 * @param {number|string} id - Activation ID
 * @returns {Promise<Response>}
 */
export async function remove(id) {
    return ajaxPost('dlm_admin_activations_delete', { id })
}

/**
 * Perform a bulk action on activations.
 * @param {string} action - Bulk action name
 * @param {Array<number>} ids - Activation IDs
 * @returns {Promise<Response>}
 */
export async function bulkAction(action, ids) {
    return ajaxPost('dlm_admin_activations_bulk_action', { bulk_action: action, ids })
}
