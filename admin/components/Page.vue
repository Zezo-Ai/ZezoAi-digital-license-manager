<template>
    <div class="dlm-page">
        <TopBar />
        <div class="dlm-page-content">
            <div class="dlm-page-header" v-if="title || $slots.actions">
                <h1 v-if="title">{{ title }}</h1>
                <div class="dlm-page-actions" v-if="$slots.actions">
                    <slot name="actions" />
                </div>
            </div>
            <slot />
        </div>
    </div>
</template>

<script setup>
import TopBar from './TopBar.vue'

defineProps({
    title: {
        type: String,
        default: '',
    },
})
</script>

<style lang="scss" scoped>
.dlm-page {
    @apply dlm-min-h-full;
}

.dlm-page-content {
    // Content area below the topbar
}

.dlm-page-header {
    @apply dlm-flex dlm-items-center dlm-justify-between dlm-mb-6;

    h1 {
        @apply dlm-m-0 dlm-text-2xl dlm-font-semibold dlm-text-gray-800;
    }
}

.dlm-page-actions {
    @apply dlm-flex dlm-gap-2;
}
</style>
