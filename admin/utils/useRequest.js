/**
 * Get the WordPress AJAX URL from localized script data.
 * @returns {string}
 */
export function getAjaxUrl() {
    return window.DLMAdmin?.ajaxUrl || '/wp-admin/admin-ajax.php'
}

/**
 * Get the WordPress REST API URL from localized script data.
 * @returns {string}
 */
export function getRestUrl() {
    return window.DLMAdmin?.restUrl || '/wp-json/dlm/v1/'
}

/**
 * Get the security nonce from localized script data.
 * @returns {string}
 */
export function getNonce() {
    return window.DLMAdmin?.nonce || ''
}

/**
 * Get the dropdown search nonce from localized script data.
 * @returns {string}
 */
export function getDropdownNonce() {
    return window.DLMAdmin?.dropdownNonce || ''
}

/**
 * Get the WordPress admin URL.
 * @returns {string}
 */
export function getAdminUrl() {
    return window.DLMAdmin?.adminUrl || '/wp-admin/'
}

/**
 * Get the plugin URL.
 * @returns {string}
 */
export function getPluginUrl() {
    return window.DLMAdmin?.pluginUrl || ''
}

/**
 * Build an AJAX URL with action and nonce.
 * @param {string} action - The AJAX action name
 * @returns {string}
 */
export function buildAjaxUrl(action) {
    const ajaxUrl = getAjaxUrl()
    const nonce = getNonce()
    return `${ajaxUrl}?action=${action}&_wpnonce=${nonce}`
}

/**
 * Make a GET request to an AJAX endpoint.
 * @param {string} action - The AJAX action name
 * @param {Object} params - Query parameters
 * @returns {Promise<Response>}
 */
export async function ajaxGet(action, params = {}) {
    const url = new URL(buildAjaxUrl(action), window.location.origin)

    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
            url.searchParams.append(key, value)
        }
    })

    return fetch(url.toString())
}

/**
 * Make a POST request to an AJAX endpoint.
 * @param {string} action - The AJAX action name
 * @param {Object|FormData} data - The data to send
 * @returns {Promise<Response>}
 */
export async function ajaxPost(action, data = {}) {
    const url = buildAjaxUrl(action)

    let formData
    if (data instanceof FormData) {
        formData = data
    } else {
        formData = new FormData()
        appendToFormData(formData, data)
    }

    return fetch(url, {
        method: 'POST',
        body: formData,
    })
}

/**
 * Recursively append data to FormData, handling nested objects and arrays.
 * @param {FormData} formData
 * @param {Object} data
 * @param {string} prefix
 */
function appendToFormData(formData, data, prefix = '') {
    for (const key in data) {
        if (!Object.prototype.hasOwnProperty.call(data, key)) continue

        const value = data[key]
        const fieldName = prefix ? `${prefix}[${key}]` : key

        if (value === null || value === undefined) {
            continue
        } else if (Array.isArray(value)) {
            value.forEach((item, index) => {
                if (typeof item === 'object' && item !== null) {
                    appendToFormData(formData, item, `${fieldName}[${index}]`)
                } else {
                    formData.append(`${fieldName}[${index}]`, item)
                }
            })
        } else if (typeof value === 'object' && !(value instanceof File)) {
            appendToFormData(formData, value, fieldName)
        } else if (typeof value === 'boolean') {
            formData.append(fieldName, value ? '1' : '0')
        } else {
            formData.append(fieldName, value)
        }
    }
}
