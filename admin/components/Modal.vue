<template>
    <Teleport to="body">
        <transition name="modal">
            <div v-if="show" class="dlm-modal" @click.self="closeOnOverlay && close()">
                <div class="dlm-modal-overlay" @click="closeOnOverlay && close()"></div>
                <div class="dlm-modal-container">
                    <div class="dlm-modal-content" :class="sizeClass" @click.stop>
                        <div class="dlm-modal-header">
                            <h3>{{ title }}</h3>
                            <button
                                v-if="showClose"
                                type="button"
                                class="dlm-modal-close"
                                @click="close"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg>
                            </button>
                        </div>

                        <div class="dlm-modal-body">
                            <slot></slot>
                        </div>

                        <div v-if="$slots.footer" class="dlm-modal-footer">
                            <slot name="footer"></slot>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<script setup>
import { computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
    },
    showClose: {
        type: Boolean,
        default: true,
    },
    closeOnOverlay: {
        type: Boolean,
        default: true,
    },
    closeOnEscape: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits(['close'])

const sizeClass = computed(() => `dlm-modal-${props.size}`)

function close() {
    emit('close')
}

function handleKeydown(e) {
    if (e.key === 'Escape' && props.show && props.closeOnEscape) {
        close()
    }
}

watch(() => props.show, (show) => {
    if (show) {
        document.body.style.overflow = 'hidden'
    } else {
        document.body.style.overflow = ''
    }
})

onMounted(() => {
    document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
    document.body.style.overflow = ''
})
</script>

<style lang="scss" scoped>
.dlm-modal-close {
    @apply dlm-p-1 dlm-rounded dlm-text-gray-400 hover:dlm-text-gray-600 dlm-bg-transparent dlm-border-0 dlm-cursor-pointer;

    svg {
        @apply dlm-w-5 dlm-h-5;
    }
}

.dlm-modal-sm .dlm-modal-content { max-width: 400px; }
.dlm-modal-md .dlm-modal-content { max-width: 500px; }
.dlm-modal-lg .dlm-modal-content { max-width: 700px; }
.dlm-modal-xl .dlm-modal-content { max-width: 900px; }

.dlm-modal-sm,
.dlm-modal-md,
.dlm-modal-lg,
.dlm-modal-xl {
    @apply dlm-w-full;
}

// Transitions
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.2s ease;

    .dlm-modal-content {
        transition: transform 0.2s ease, opacity 0.2s ease;
    }
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;

    .dlm-modal-content {
        transform: scale(0.95);
        opacity: 0;
    }
}
</style>
