import { ajaxGet, ajaxPost } from '@digital-license-manager/ui/utils/useRequest'

/**
 * Get all settings.
 * @returns {Promise<Response>}
 */
export async function get() {
    return ajaxGet('dlm_admin_settings_get')
}

/**
 * Save settings for a section.
 * @param {string} section - Settings section
 * @param {Object} settings - Settings data
 * @returns {Promise<Response>}
 */
export async function save(section, settings) {
    return ajaxPost('dlm_admin_settings_save', { section, settings })
}

/**
 * Get API keys.
 * @param {Object} params - Query parameters
 * @returns {Promise<Response>}
 */
export async function getApiKeys(params = {}) {
    return ajaxGet('dlm_admin_api_keys_query', params)
}

/**
 * Create an API key.
 * @param {Object} data - API key data
 * @returns {Promise<Response>}
 */
export async function createApiKey(data) {
    return ajaxPost('dlm_admin_api_keys_store', data)
}

/**
 * Update an API key.
 * @param {number|string} id - API key ID
 * @param {Object} data - API key data
 * @returns {Promise<Response>}
 */
export async function updateApiKey(id, data) {
    return ajaxPost('dlm_admin_api_keys_store', { id, ...data })
}

/**
 * Delete an API key.
 * @param {number|string} id - API key ID
 * @returns {Promise<Response>}
 */
export async function deleteApiKey(id) {
    return ajaxPost('dlm_admin_api_keys_delete', { id })
}

/**
 * Get available REST API endpoints.
 * @returns {Promise<Response>}
 */
export async function getEndpoints() {
    return ajaxGet('dlm_admin_api_keys_endpoints')
}

/**
 * Get all registered tools.
 * @returns {Promise<Response>}
 */
export async function getTools() {
    return ajaxGet('dlm_admin_tools_list')
}

/**
 * Initialize a tool process.
 * @param {Object} data - Tool init data
 * @returns {Promise<Response>}
 */
export async function initTool(data) {
    return ajaxPost('dlm_admin_tool_init', data)
}

/**
 * Process a tool step.
 * @param {Object} data - Tool process data
 * @returns {Promise<Response>}
 */
export async function processTool(data) {
    return ajaxPost('dlm_admin_tool_process', data)
}

/**
 * Get tool migration status.
 * @param {string} identifier - Plugin identifier
 * @returns {Promise<Response>}
 */
export async function getToolStatus(identifier) {
    return ajaxGet('dlm_admin_tool_status', { identifier })
}

/**
 * Undo a tool migration.
 * @param {string} identifier - Plugin identifier
 * @returns {Promise<Response>}
 */
export async function undoTool(identifier) {
    return ajaxPost('dlm_admin_tool_undo', { identifier })
}
