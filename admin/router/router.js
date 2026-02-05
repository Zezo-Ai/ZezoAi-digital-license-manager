import { createRouter, createWebHashHistory } from 'vue-router'

// Pages
import Licenses from '../pages/Licenses.vue'
import LicenseForm from '../pages/LicenseForm.vue'
import LicenseImport from '../pages/LicenseImport.vue'
import Generators from '../pages/Generators.vue'
import GeneratorForm from '../pages/GeneratorForm.vue'
import GeneratorGenerate from '../pages/GeneratorGenerate.vue'
import Activations from '../pages/Activations.vue'
import Settings from '../pages/Settings.vue'

const routes = [
    // Licenses
    { path: '/', name: 'licenses', component: Licenses },
    { path: '/licenses/add', name: 'license-add', component: LicenseForm },
    { path: '/licenses/:id/edit', name: 'license-edit', component: LicenseForm, props: true },
    { path: '/licenses/import', name: 'license-import', component: LicenseImport },

    // Generators
    { path: '/generators', name: 'generators', component: Generators },
    { path: '/generators/add', name: 'generator-add', component: GeneratorForm },
    { path: '/generators/:id/edit', name: 'generator-edit', component: GeneratorForm, props: true },
    { path: '/generators/generate', name: 'generator-generate', component: GeneratorGenerate },

    // Activations
    { path: '/activations', name: 'activations', component: Activations },

    // Settings
    { path: '/settings', name: 'settings', component: Settings },
    { path: '/settings/:tab', name: 'settings-tab', component: Settings, props: true },
]

const router = createRouter({
    history: createWebHashHistory(),
    routes,
})

export default router
