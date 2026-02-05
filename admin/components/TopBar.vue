<template>
    <div class="dlm-topbar">
        <div class="dlm-topbar-inner">
            <div class="dlm-topbar-brand">
                <svg class="dlm-topbar-logo" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="dlm-topbar-title">Digital License Manager</span>
            </div>
            <nav class="dlm-topbar-nav">
                <a
                    href="https://developer.ideologix.com/plugins/digital-license-manager/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="dlm-topbar-link"
                >
                    {{ trans('global.nav.documentation') }}
                </a>
                <a
                    href="https://developer.ideologix.com/support/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="dlm-topbar-link"
                >
                    {{ trans('global.nav.support') }}
                </a>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { trans } from '../utils/useLang'
</script>

<style lang="scss" scoped>
.dlm-topbar {
    @apply dlm-bg-white dlm-border-b dlm-border-gray-200 dlm-mb-6;
    margin-left: -20px;
    margin-right: -20px;
    margin-top: -15px;
    padding: 0 20px;
}

.dlm-topbar-inner {
    @apply dlm-flex dlm-items-center dlm-justify-between dlm-py-3;
    max-width: 100%;
}

.dlm-topbar-brand {
    @apply dlm-flex dlm-items-center dlm-gap-2;
}

.dlm-topbar-logo {
    @apply dlm-w-6 dlm-h-6 dlm-text-primary-600;
}

.dlm-topbar-title {
    @apply dlm-text-lg dlm-font-semibold dlm-text-gray-800;
}

.dlm-topbar-nav {
    @apply dlm-flex dlm-items-center dlm-gap-4;
}

.dlm-topbar-link {
    @apply dlm-text-sm dlm-text-gray-600 hover:dlm-text-primary-600 dlm-no-underline;
}
</style>
