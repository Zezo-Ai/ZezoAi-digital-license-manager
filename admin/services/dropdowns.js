import { ajaxGet } from '../utils/useRequest'

/**
 * Search products.
 * @param {string} search - Search term
 * @returns {Promise<Response>}
 */
export async function searchProducts(search) {
    return ajaxGet('dlm_admin_search_products', { search })
}

/**
 * Search orders.
 * @param {string} search - Search term
 * @returns {Promise<Response>}
 */
export async function searchOrders(search) {
    return ajaxGet('dlm_admin_search_orders', { search })
}

/**
 * Search users.
 * @param {string} search - Search term
 * @returns {Promise<Response>}
 */
export async function searchUsers(search) {
    return ajaxGet('dlm_admin_search_users', { search })
}

/**
 * Search generators.
 * @param {string} search - Search term
 * @returns {Promise<Response>}
 */
export async function searchGenerators(search) {
    return ajaxGet('dlm_admin_search_generators', { search })
}
