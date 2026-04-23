import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { configure } from '@digital-license-manager/ui/setup'
import menuFix from '@digital-license-manager/ui/utils/admin-menu-fix.js'
import './tailwind-entry.css'
import App from './App.vue'
import router from './router/router.js'

const mountEl = document.getElementById('dlm-admin')

if (mountEl) {
    configure({
        appId: 'dlm-admin',
        i18n: window.DLMAdmin?.i18n,
        nonce: window.DLMAdmin?.nonce,
        dropdownNonce: window.DLMAdmin?.dropdownNonce,
        ajaxUrl: window.DLMAdmin?.ajaxUrl,
        restUrl: window.DLMAdmin?.restUrl,
        adminUrl: window.DLMAdmin?.adminUrl,
        pluginUrl: window.DLMAdmin?.pluginUrl,
    })

    const app = createApp(App)

    app.provide('pluginConfig', {
        prefix: 'dlm',
        nonce: window.DLMAdmin?.nonce || '',
        ajaxUrl: window.DLMAdmin?.ajaxUrl || '',
        restUrl: window.DLMAdmin?.restUrl || '',
        adminUrl: window.DLMAdmin?.adminUrl || '',
        pluginUrl: window.DLMAdmin?.pluginUrl || '',
    })

    app.use(createPinia())
    app.use(router)

    app.config.performance = true
    app.mount('#dlm-admin')

    // Fix WordPress admin menu highlighting for hash-based routes
    menuFix('dlm-licenses')
}
