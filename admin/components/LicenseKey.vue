<template>
    <div class="dlm-license-key-cell">
        <span v-if="revealed" class="dlm-license-key font-mono">
            {{ decryptedKey }}
        </span>
        <span v-else class="dlm-license-key dlm-license-key-hidden">
            {{ maskedKey }}
        </span>

        <div class="dlm-license-key-actions">
            <button
                v-if="!revealed"
                type="button"
                class="dlm-key-action"
                :title="trans('licenses.actions.show_key')"
                :disabled="loading"
                @click="revealKey"
            >
                <svg v-if="loading" class="dlm-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                </svg>
            </button>

            <button
                v-if="revealed"
                type="button"
                class="dlm-key-action"
                :title="trans('licenses.actions.hide_key')"
                @click="hideKey"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z" clip-rule="evenodd" />
                    <path d="M10.748 13.93l2.523 2.523a9.987 9.987 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 012.839 6.02L6.07 9.252a4 4 0 004.678 4.678z" />
                </svg>
            </button>

            <button
                v-if="revealed"
                type="button"
                class="dlm-key-action"
                :title="trans('licenses.actions.copy_key')"
                @click="copyKey"
            >
                <svg v-if="copied" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="text-success-600">
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M7 3.5A1.5 1.5 0 018.5 2h3.879a1.5 1.5 0 011.06.44l3.122 3.12A1.5 1.5 0 0117 6.622V12.5a1.5 1.5 0 01-1.5 1.5h-1v-3.379a3 3 0 00-.879-2.121L10.5 5.379A3 3 0 008.379 4.5H7v-1z" />
                    <path d="M4.5 6A1.5 1.5 0 003 7.5v9A1.5 1.5 0 004.5 18h7a1.5 1.5 0 001.5-1.5v-5.879a1.5 1.5 0 00-.44-1.06L9.44 6.439A1.5 1.5 0 008.378 6H4.5z" />
                </svg>
            </button>

            <RouterLink
                v-if="showLink"
                :to="{ name: 'license-edit', params: { id: license.id } }"
                class="dlm-key-action"
                :title="trans('global.actions.edit')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" />
                    <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
                </svg>
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { trans } from '@digital-license-manager/ui/utils/useLang'
import { useAlertStore } from '@digital-license-manager/ui/stores/alert'
import * as licensesService from '../services/licenses'

const props = defineProps({
    license: {
        type: Object,
        required: true,
    },
    showLink: {
        type: Boolean,
        default: false,
    },
})

const alertStore = useAlertStore()

const loading = ref(false)
const revealed = ref(!!props.license.decrypted_license_key)
const decryptedKey = ref(props.license.decrypted_license_key || '')
const copied = ref(false)

const maskedKey = computed(() => {
    // Show partial key if available, otherwise show dots
    if (props.license.license_key_partial) {
        return props.license.license_key_partial
    }
    return '••••••••-••••-••••-••••'
})

async function revealKey() {
    // If we already have the decrypted key, just reveal it
    if (props.license.decrypted_license_key) {
        decryptedKey.value = props.license.decrypted_license_key
        revealed.value = true
        return
    }

    loading.value = true

    try {
        const response = await licensesService.showKey(props.license.id)
        const json = await response.json()

        if (json.success) {
            decryptedKey.value = json.data.license_key
            revealed.value = true
        } else {
            alertStore.error(json.data.message)
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        loading.value = false
    }
}

function hideKey() {
    revealed.value = false
}

async function copyKey() {
    try {
        await navigator.clipboard.writeText(decryptedKey.value)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    } catch (error) {
        // Fallback for older browsers
        const textarea = document.createElement('textarea')
        textarea.value = decryptedKey.value
        document.body.appendChild(textarea)
        textarea.select()
        document.execCommand('copy')
        document.body.removeChild(textarea)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    }
}
</script>

<style lang="scss" scoped>
.dlm-license-key-cell {
    @apply flex items-center gap-2;
}

.dlm-license-key-actions {
    @apply flex items-center gap-1;
}

.dlm-key-action {
    @apply p-1 rounded text-gray-400 hover:text-gray-600 bg-transparent border-0 cursor-pointer no-underline;
    @apply disabled:opacity-50 disabled:cursor-not-allowed;
    color: inherit;

    svg {
        @apply w-4 h-4;
    }
}

</style>
