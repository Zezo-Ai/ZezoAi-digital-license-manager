<template>
    <div class="dlm-app">
        <Alert />
        <Page :section-items="navItems">
            <router-view v-slot="{ Component, route }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" :key="getRouteKey(route)" />
                </transition>
            </router-view>
        </Page>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Alert from '@digital-license-manager/ui/components/Alert.vue'
import Page from '@digital-license-manager/ui/components/Page.vue'
import { trans } from '@digital-license-manager/ui/utils/useLang'

const route = useRoute()

const navItems = computed(() => {
    if (route.path.startsWith('/settings')) return []

    return [
        {
            id: 'licenses',
            label: trans('licenses.title'),
            to: '/',
            icon: 'licenses',
            activePrefixes: ['/licenses'],
        },
        { id: 'generators', label: trans('generators.title'), to: '/generators', icon: 'generators' },
        { id: 'activations', label: trans('activations.title'), to: '/activations', icon: 'activations' },
    ]
})

/**
 * Get a stable key for route transitions.
 * Settings routes share the same key so tab changes don't remount the component.
 */
function getRouteKey(route) {
    // Settings tabs should not trigger a full page transition
    if (route.path.startsWith('/settings')) {
        return '/settings'
    }
    return route.path
}
</script>

<style>
.dlm-app {
    margin-top: 15px;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
