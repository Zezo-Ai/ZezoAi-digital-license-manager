<template>
    <div class="dlm-tabs">
        <nav class="dlm-tabs-nav">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                :class="['dlm-tab', { 'dlm-active': activeTab === tab.id }]"
                @click="changeTab(tab.id)"
            >
                <span v-if="tab.icon" class="dlm-tab-icon" v-html="tab.icon"></span>
                <span>{{ tab.label }}</span>
                <span v-if="tab.badge" class="dlm-tab-badge">{{ tab.badge }}</span>
            </button>
        </nav>
    </div>
</template>

<script setup>
defineProps({
    tabs: {
        type: Array,
        required: true,
        validator: (tabs) => tabs.every(tab => tab.id && tab.label),
    },
    activeTab: {
        type: String,
        required: true,
    },
})

const emit = defineEmits(['change'])

function changeTab(tabId) {
    emit('change', tabId)
}
</script>

<style lang="scss" scoped>
.dlm-tabs {
    @apply dlm-mb-4;
}

.dlm-tabs-nav {
    @apply dlm-flex dlm-border-b dlm-border-gray-200;
}

.dlm-tab {
    @apply dlm-flex dlm-items-center dlm-gap-2 dlm-px-4 dlm-py-3 dlm-text-sm dlm-font-medium dlm-text-gray-500;
    @apply dlm-border-b-2 dlm-border-transparent dlm--mb-px;
    @apply dlm-bg-transparent dlm-cursor-pointer;
    @apply hover:dlm-text-gray-700 hover:dlm-border-gray-300;
    @apply focus:dlm-outline-none;

    &.dlm-active {
        @apply dlm-text-primary-600 dlm-border-primary-600;
    }
}

.dlm-tab-icon {
    @apply dlm-w-5 dlm-h-5;

    svg {
        @apply dlm-w-5 dlm-h-5;
    }
}

.dlm-tab-badge {
    @apply dlm-ml-2 dlm-px-2 dlm-py-0.5 dlm-text-xs dlm-font-medium dlm-rounded-full;
    @apply dlm-bg-gray-100 dlm-text-gray-600;

    .dlm-active & {
        @apply dlm-bg-primary-100 dlm-text-primary-600;
    }
}
</style>
