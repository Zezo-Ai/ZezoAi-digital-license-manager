import { defineStore } from 'pinia'

export const useAlertStore = defineStore('alert', {
    state: () => ({
        messages: [],
        type: null, // 'success', 'error', 'warning', 'info'
        autoDismiss: true,
        dismissTimeout: null,
    }),

    getters: {
        hasAlert: (state) => state.messages.length > 0,
        hasMultiple: (state) => state.messages.length > 1,
        isSuccess: (state) => state.type === 'success',
        isError: (state) => state.type === 'error',
        isWarning: (state) => state.type === 'warning',
        isInfo: (state) => state.type === 'info',
    },

    actions: {
        /**
         * Show a success alert.
         * @param {string|string[]} message
         */
        success(message) {
            this.show(message, 'success')
        },

        /**
         * Show an error alert.
         * @param {string|string[]} message
         */
        error(message) {
            this.show(message, 'error')
        },

        /**
         * Show a warning alert.
         * @param {string|string[]} message
         */
        warning(message) {
            this.show(message, 'warning')
        },

        /**
         * Show an info alert.
         * @param {string|string[]} message
         */
        info(message) {
            this.show(message, 'info')
        },

        /**
         * Show an alert with the given type.
         * @param {string|string[]} message
         * @param {string} type
         */
        show(message, type) {
            this.clear()

            if (Array.isArray(message)) {
                this.messages = message
            } else {
                this.messages = [message]
            }

            this.type = type

            // Auto-dismiss success messages after 5 seconds
            if (this.autoDismiss && type === 'success') {
                this.dismissTimeout = setTimeout(() => {
                    this.clear()
                }, 5000)
            }

            // Scroll to top to show alert
            window.scrollTo({ top: 0, behavior: 'smooth' })
        },

        /**
         * Clear all alerts.
         */
        clear() {
            if (this.dismissTimeout) {
                clearTimeout(this.dismissTimeout)
                this.dismissTimeout = null
            }
            this.messages = []
            this.type = null
        },

        /**
         * Set auto-dismiss behavior.
         * @param {boolean} value
         */
        setAutoDismiss(value) {
            this.autoDismiss = value
        },
    },
})
