/**
 * Translate a string using the localized i18n strings.
 * Supports dot notation for nested keys (e.g., 'licenses.labels.name').
 *
 * @param {string} key - The translation key (dot-notation supported)
 * @param {Object} replacements - Optional key-value pairs for string interpolation
 * @returns {string}
 */
export function trans(key, replacements = {}) {
    const keys = key.split('.')
    let obj = window.DLMAdmin?.i18n

    // Navigate through nested object
    while (keys.length && obj) {
        obj = obj[keys.shift()]
    }

    // Return key if translation not found
    if (obj === undefined || obj === null) {
        return key
    }

    let result = String(obj)

    // Handle replacements like :name, :count, etc.
    for (const [placeholder, value] of Object.entries(replacements)) {
        result = result.replace(new RegExp(`:${placeholder}`, 'g'), value)
    }

    return result
}

/**
 * Shorthand for trans() - commonly used in templates.
 * @param {string} key
 * @param {Object} replacements
 * @returns {string}
 */
export function __(key, replacements = {}) {
    return trans(key, replacements)
}

/**
 * Pluralize a translation based on count.
 * Expects the translation to have 'one' and 'other' keys.
 *
 * @param {string} key - The translation key
 * @param {number} count - The count for pluralization
 * @param {Object} replacements - Additional replacements
 * @returns {string}
 */
export function transChoice(key, count, replacements = {}) {
    const pluralKey = count === 1 ? `${key}.one` : `${key}.other`
    return trans(pluralKey, { count, ...replacements })
}
