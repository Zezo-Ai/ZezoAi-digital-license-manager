import { defineStore } from 'pinia'

export const useGlobalStore = defineStore('global', {
    state: () => ({
        loading: false,
        loadingMessage: '',
        pageTitle: '',
        breadcrumbs: [],
    }),

    getters: {
        isLoading: (state) => state.loading,
    },

    actions: {
        /**
         * Set the loading state.
         * @param {boolean} loading
         * @param {string} message
         */
        setLoading(loading, message = '') {
            this.loading = loading
            this.loadingMessage = message
        },

        /**
         * Start loading with optional message.
         * @param {string} message
         */
        startLoading(message = '') {
            this.setLoading(true, message)
        },

        /**
         * Stop loading.
         */
        stopLoading() {
            this.setLoading(false, '')
        },

        /**
         * Set the page title.
         * @param {string} title
         */
        setPageTitle(title) {
            this.pageTitle = title
        },

        /**
         * Set breadcrumbs.
         * @param {Array<{label: string, to?: string}>} items
         */
        setBreadcrumbs(items) {
            this.breadcrumbs = items
        },

        /**
         * Clear breadcrumbs.
         */
        clearBreadcrumbs() {
            this.breadcrumbs = []
        },
    },
})
