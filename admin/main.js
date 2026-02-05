import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router/router.js'
import menuFix from './utils/admin-menu-fix.js'
import './styles/main.scss'

const mountEl = document.getElementById('dlm-admin')

if (mountEl) {
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
