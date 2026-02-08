<template>
    <div class="dlm-app">
        <Alert />
        <router-view v-slot="{ Component, route }">
            <transition name="fade" mode="out-in">
                <component :is="Component" :key="route.path" />
            </transition>
        </router-view>
    </div>
</template>

<script setup>
import Alert from '@digital-license-manager/ui/components/Alert.vue'
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
