<template>
    <div class="dlm-form-group">
        <label v-if="label" :for="id">
            {{ label }}
            <span v-if="required" class="dlm-required">*</span>
        </label>

        <div class="dlm-image-upload-field">
            <div class="dlm-image-upload-preview" @click="openMedia">
                <img v-if="previewUrl" :src="previewUrl" :alt="label" />
                <div v-else class="dlm-image-upload-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <span>{{ trans('settings.general.upload') }}</span>
                </div>
                <div v-if="previewUrl" class="dlm-image-upload-overlay">
                    <span>{{ trans('global.actions.change') }}</span>
                </div>
            </div>

            <div class="dlm-image-upload-actions">
                <button
                    v-if="!hasImage"
                    type="button"
                    class="dlm-image-upload-link"
                    :disabled="disabled"
                    @click="openMedia"
                >
                    {{ trans('settings.general.upload') }}
                </button>
                <button
                    v-if="hasImage"
                    type="button"
                    class="dlm-image-upload-link dlm-image-upload-link--danger"
                    :disabled="disabled"
                    @click="remove"
                >
                    {{ trans('global.actions.remove') }}
                </button>
            </div>
        </div>

        <p v-if="hint" class="dlm-form-hint" v-html="hint"></p>
        <p v-if="error" class="dlm-form-error">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { trans } from '../../utils/useLang'

const props = defineProps({
    id: {
        type: String,
        default: () => `image-upload-${Math.random().toString(36).substring(7)}`,
    },
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    hint: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    imageUrl: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['update:modelValue'])

const previewUrl = ref('')

const hasImage = computed(() => {
    return props.modelValue && String(props.modelValue).length > 0
})

function resolvePreview(attachmentId) {
    if (!attachmentId || !String(attachmentId).match(/^\d+$/)) {
        previewUrl.value = ''
        return
    }

    if (window.wp && window.wp.media) {
        const attachment = window.wp.media.attachment(Number(attachmentId))
        attachment.fetch().then(() => {
            const sizes = attachment.get('sizes')
            previewUrl.value = sizes?.medium?.url || sizes?.thumbnail?.url || attachment.get('url') || ''
        }).catch(() => {
            previewUrl.value = ''
        })
    }
}

function openMedia() {
    if (props.disabled) return
    if (!window.wp || !window.wp.media) return

    const frame = window.wp.media({
        title: trans('settings.general.upload'),
        button: { text: trans('settings.general.upload') },
        multiple: false,
    })

    frame.on('select', () => {
        const attachment = frame.state().get('selection').first().toJSON()
        emit('update:modelValue', String(attachment.id))
        previewUrl.value = attachment.sizes?.medium?.url || attachment.sizes?.thumbnail?.url || attachment.url || ''
    })

    frame.open()
}

function remove() {
    emit('update:modelValue', '')
    previewUrl.value = ''
}

watch(() => props.modelValue, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        if (!newVal || !String(newVal).match(/^\d+$/)) {
            previewUrl.value = ''
        } else if (!previewUrl.value) {
            resolvePreview(newVal)
        }
    }
})

onMounted(() => {
    if (props.imageUrl) {
        previewUrl.value = props.imageUrl
    } else if (hasImage.value) {
        resolvePreview(props.modelValue)
    }
})
</script>

<style lang="scss" scoped>
.dlm-required {
    @apply dlm-text-danger-600;
}

.dlm-image-upload-field {
    max-width: 480px;
}

.dlm-image-upload-preview {
    @apply dlm-relative dlm-cursor-pointer dlm-rounded-lg dlm-border-2 dlm-border-dashed dlm-border-gray-300 dlm-overflow-hidden dlm-inline-block;
    @apply dlm-transition-all;
    width: 200px;
    height: 200px;

    &:hover {
        @apply dlm-border-primary-400;
    }

    img {
        @apply dlm-w-full dlm-h-full;
        object-fit: cover;
    }

    // Solid border when image is present
    &:has(img) {
        @apply dlm-border-solid dlm-border dlm-border-gray-200 dlm-rounded;
    }

    &:hover .dlm-image-upload-overlay {
        @apply dlm-opacity-100;
    }
}

.dlm-image-upload-placeholder {
    @apply dlm-flex dlm-flex-col dlm-items-center dlm-justify-center dlm-h-full dlm-gap-3;
    @apply dlm-bg-gray-50 dlm-transition-colors;

    svg {
        @apply dlm-text-gray-400 dlm-transition-colors;
        width: 40px;
        height: 40px;
    }

    span {
        @apply dlm-text-sm dlm-font-medium dlm-text-gray-400 dlm-transition-colors;
    }

    .dlm-image-upload-preview:hover & {
        @apply dlm-bg-primary-50;

        svg {
            @apply dlm-text-primary-400;
        }

        span {
            @apply dlm-text-primary-500;
        }
    }
}

.dlm-image-upload-overlay {
    @apply dlm-absolute dlm-inset-0 dlm-flex dlm-items-center dlm-justify-center dlm-opacity-0;
    background: rgba(0, 0, 0, 0.5);
    transition: opacity 0.2s ease;

    span {
        @apply dlm-text-white dlm-text-sm dlm-font-medium;
    }
}

.dlm-image-upload-actions {
    @apply dlm-mt-2;
}

.dlm-image-upload-link {
    @apply dlm-text-sm dlm-font-medium dlm-text-primary-600 dlm-cursor-pointer;
    background: none;
    border: none;
    outline: none;
    padding: 0;

    &:hover {
        @apply dlm-text-primary-700 dlm-underline;
    }

    &--danger {
        @apply dlm-text-danger-600;
        &:hover {
            @apply dlm-text-danger-700;
        }
    }
}

.dlm-form-error {
    @apply dlm-text-sm dlm-text-danger-600 dlm-mt-1;
}
</style>
