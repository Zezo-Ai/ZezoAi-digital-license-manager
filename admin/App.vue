<template>
    <div class="dlm-app">
        <Alert />
        <Page>
            <router-view v-slot="{ Component, route }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" :key="getRouteKey(route)" />
                </transition>
            </router-view>
        </Page>
    </div>
</template>

<script setup>
import Alert from '@digital-license-manager/ui/components/Alert.vue'
import Page from '@digital-license-manager/ui/components/Page.vue'

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

<style lang="scss">
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
