<template>
    <div class="dlm-acs">
        <div v-if="steps.length === 0" class="dlm-acs-empty">
            {{ trans('settings.abandoned_checkout.empty') }}
        </div>

        <div
            v-for="(step, index) in steps"
            :key="index"
            class="dlm-acs-row"
            :class="{ 'dlm-acs-row--disabled': !step.enabled, 'dlm-acs-row--open': openBodyIndex === index }"
        >
            <div class="dlm-acs-row-main">
                <div class="dlm-acs-handle">
                    <span class="dlm-acs-index">{{ index + 1 }}</span>
                    <div class="dlm-acs-reorder">
                        <button
                            type="button"
                            class="dlm-acs-arrow"
                            :disabled="index === 0"
                            :aria-label="trans('settings.abandoned_checkout.move_up')"
                            :title="trans('settings.abandoned_checkout.move_up')"
                            @click="move(index, -1)"
                        >&uarr;</button>
                        <button
                            type="button"
                            class="dlm-acs-arrow"
                            :disabled="index === steps.length - 1"
                            :aria-label="trans('settings.abandoned_checkout.move_down')"
                            :title="trans('settings.abandoned_checkout.move_down')"
                            @click="move(index, 1)"
                        >&darr;</button>
                    </div>
                </div>

                <label class="dlm-acs-toggle" :title="trans('settings.abandoned_checkout.enabled')">
                    <span class="dlm-switch">
                        <input v-model="step.enabled" type="checkbox" />
                        <span class="dlm-switch-slider"></span>
                    </span>
                </label>

                <div class="dlm-acs-delay">
                    <input
                        v-model.number="step.delay_value"
                        type="number"
                        min="1"
                        max="9999"
                        class="dlm-acs-delay-value"
                        :aria-label="trans('settings.abandoned_checkout.delay_value')"
                    />
                    <select
                        v-model="step.delay_unit"
                        class="dlm-acs-delay-unit"
                        :aria-label="trans('settings.abandoned_checkout.delay_unit')"
                    >
                        <option value="minute">{{ trans('settings.abandoned_checkout.unit_minutes') }}</option>
                        <option value="hour">{{ trans('settings.abandoned_checkout.unit_hours') }}</option>
                        <option value="day">{{ trans('settings.abandoned_checkout.unit_days') }}</option>
                    </select>
                </div>

                <input
                    v-model="step.subject"
                    type="text"
                    class="dlm-acs-subject"
                    :placeholder="trans('settings.abandoned_checkout.subject_placeholder')"
                    :aria-label="trans('settings.abandoned_checkout.subject')"
                    maxlength="200"
                />

                <div class="dlm-acs-row-actions">
                    <button
                        type="button"
                        class="dlm-acs-btn-secondary"
                        @click="toggleBody(index)"
                    >
                        {{ openBodyIndex === index ? trans('settings.abandoned_checkout.close_body') : trans('settings.abandoned_checkout.edit_body') }}
                    </button>
                    <button
                        type="button"
                        class="dlm-acs-btn-remove"
                        :aria-label="trans('settings.abandoned_checkout.remove')"
                        :title="trans('settings.abandoned_checkout.remove')"
                        @click="removeStep(index)"
                    >&times;</button>
                </div>
            </div>

            <div v-if="openBodyIndex === index" class="dlm-acs-body-panel">
                <div class="dlm-acs-tag-strip">
                    <span class="dlm-acs-tag-strip-label">{{ trans('settings.abandoned_checkout.insert_tag') }}:</span>
                    <button
                        v-for="tag in mergeTags"
                        :key="tag.key"
                        type="button"
                        class="dlm-acs-tag-chip"
                        :title="tag.description"
                        @click="insertTag(index, tag.key)"
                    >{{ tagToken(tag.key) }}</button>
                </div>
                <textarea
                    :ref="el => bodyTextareas[index] = el"
                    v-model="step.body"
                    class="dlm-acs-body"
                    rows="10"
                    :placeholder="trans('settings.abandoned_checkout.body_placeholder')"
                ></textarea>
                <p class="dlm-acs-body-hint">
                    {{ trans('settings.abandoned_checkout.body_hint') }}
                </p>
            </div>
        </div>

        <div class="dlm-acs-footer">
            <button
                v-if="steps.length < maxItems"
                type="button"
                class="dlm-acs-btn-add"
                @click="addStep"
            >+ {{ trans('settings.abandoned_checkout.add_reminder') }}</button>
        </div>

        <details v-if="mergeTags.length" class="dlm-acs-tags-help">
            <summary>{{ trans('settings.abandoned_checkout.merge_tags_help_title') }}</summary>
            <ul>
                <li v-for="tag in mergeTags" :key="tag.key">
                    <code>{{ tagToken(tag.key) }}</code>
                    <span class="dlm-acs-tag-desc">{{ tag.description }}</span>
                </li>
            </ul>
        </details>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { trans } from '@digital-license-manager/ui/utils/useLang'

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    maxItems:   { type: Number, default: 10 },
    mergeTags:  { type: Array, default: () => [] },
})

const emit = defineEmits(['update:modelValue'])

const steps = ref(normalize(props.modelValue))

watch(() => props.modelValue, (val) => {
    steps.value = normalize(val)
}, { deep: false })

watch(steps, (val) => {
    emit('update:modelValue', val)
}, { deep: true })

const openBodyIndex = ref(null)
const bodyTextareas = ref({})

function normalize(input) {
    if (!Array.isArray(input)) return []
    return input.map((s) => ({
        enabled:     !!s?.enabled,
        delay_value: Number.isFinite(Number(s?.delay_value)) ? Number(s.delay_value) : 1,
        delay_unit:  ['minute', 'hour', 'day'].includes(s?.delay_unit) ? s.delay_unit : 'hour',
        subject:     typeof s?.subject === 'string' ? s.subject : '',
        body:        typeof s?.body === 'string' ? s.body : '',
    }))
}

function tagToken(key) {
    return '{' + '{' + key + '}' + '}'
}

function move(index, delta) {
    const target = index + delta
    if (target < 0 || target >= steps.value.length) return
    const next = steps.value.slice()
    const [row] = next.splice(index, 1)
    next.splice(target, 0, row)
    steps.value = next
    if (openBodyIndex.value === index) openBodyIndex.value = target
    else if (openBodyIndex.value === target) openBodyIndex.value = index
}

function addStep() {
    if (steps.value.length >= props.maxItems) return
    steps.value = steps.value.concat({
        enabled:     true,
        delay_value: 24,
        delay_unit:  'hour',
        subject:     '',
        body:        '',
    })
}

function removeStep(index) {
    const next = steps.value.slice()
    next.splice(index, 1)
    steps.value = next
    if (openBodyIndex.value === index) openBodyIndex.value = null
    else if (openBodyIndex.value !== null && openBodyIndex.value > index) openBodyIndex.value -= 1
}

function toggleBody(index) {
    openBodyIndex.value = openBodyIndex.value === index ? null : index
}

function insertTag(index, key) {
    const token = tagToken(key)
    const ta = bodyTextareas.value[index]
    if (!ta) {
        steps.value[index].body = (steps.value[index].body || '') + token
        return
    }
    const start = ta.selectionStart ?? ta.value.length
    const end   = ta.selectionEnd   ?? ta.value.length
    const before = ta.value.slice(0, start)
    const after  = ta.value.slice(end)
    steps.value[index].body = before + token + after
    requestAnimationFrame(() => {
        const cursor = before.length + token.length
        ta.focus()
        ta.setSelectionRange(cursor, cursor)
    })
}
</script>

<style>
.dlm-acs {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
    max-width: 960px;
}
.dlm-acs-cell {
    min-width: 0; /* let the grid cell shrink so children can size correctly */
}
.dlm-acs-empty {
    padding: 18px;
    border: 1px dashed #d0d5dd;
    border-radius: 8px;
    color: #6b7280;
    font-style: italic;
    text-align: center;
    background: #fff;
}

.dlm-acs-row {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
    transition: border-color .15s ease, box-shadow .15s ease, opacity .15s ease;
}
.dlm-acs-row:hover {
    border-color: #cbd5e1;
}
.dlm-acs-row--open {
    border-color: #2563eb;
    box-shadow: 0 0 0 1px #2563eb inset;
}
.dlm-acs-row--disabled .dlm-acs-row-main {
    opacity: 0.55;
}
.dlm-acs-row--disabled.dlm-acs-row--open {
    opacity: 1;
}

.dlm-acs-row-main {
    display: grid;
    grid-template-columns: auto auto auto 1fr auto;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
}

.dlm-acs-handle {
    display: flex;
    align-items: center;
    gap: 8px;
}
.dlm-acs-index {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #eef2ff;
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
}
.dlm-acs-reorder {
    display: flex;
    flex-direction: column;
    gap: 1px;
}
.dlm-acs-arrow {
    width: 20px;
    height: 16px;
    padding: 0;
    line-height: 14px;
    font-size: 11px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    color: #4b5563;
    border-radius: 3px;
    cursor: pointer;
}
.dlm-acs-arrow:hover:not(:disabled) {
    background: #eef2ff;
    border-color: #c7d2fe;
    color: #1d4ed8;
}
.dlm-acs-arrow:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.dlm-acs-toggle {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    margin: 0;
}

.dlm-acs-delay {
    display: flex;
    gap: 4px;
    align-items: center;
}
.dlm-acs-delay-value {
    width: 64px;
    height: 32px;
    padding: 4px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 13px;
    font-variant-numeric: tabular-nums;
    text-align: right;
    box-sizing: border-box;
}
.dlm-acs-delay-unit {
    height: 32px;
    padding: 0 24px 0 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 13px;
    background-color: #fff;
    box-sizing: border-box;
    min-width: 88px;
}

.dlm-acs-subject {
    height: 32px;
    padding: 4px 10px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 13px;
    min-width: 0;
    width: 100%;
    box-sizing: border-box;
}
.dlm-acs-subject:focus,
.dlm-acs-delay-value:focus,
.dlm-acs-delay-unit:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.18);
}

.dlm-acs-row-actions {
    display: flex;
    gap: 6px;
    align-items: center;
}
.dlm-acs-btn-secondary {
    height: 32px;
    padding: 0 12px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #374151;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
}
.dlm-acs-btn-secondary:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}
.dlm-acs-btn-remove {
    width: 32px;
    height: 32px;
    padding: 0;
    border: 1px solid transparent;
    background: transparent;
    color: #9ca3af;
    border-radius: 4px;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
}
.dlm-acs-btn-remove:hover {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

.dlm-acs-body-panel {
    border-top: 1px solid #eef2f7;
    padding: 10px 12px 12px;
    background: #fafbfc;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}
.dlm-acs-tag-strip {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    align-items: center;
    margin-bottom: 6px;
}
.dlm-acs-tag-strip-label {
    font-size: 11px;
    color: #6b7280;
    margin-right: 4px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.dlm-acs-tag-chip {
    background: #eef2ff;
    color: #1d4ed8;
    border: 1px solid #c7d2fe;
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 11px;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    cursor: pointer;
    line-height: 1.4;
}
.dlm-acs-tag-chip:hover {
    background: #e0e7ff;
}
.dlm-acs-body {
    width: 100%;
    min-height: 180px;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 13px;
    box-sizing: border-box;
    background: #fff;
    resize: vertical;
}
.dlm-acs-body:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.18);
}
.dlm-acs-body-hint {
    margin: 6px 0 0;
    font-size: 11px;
    color: #6b7280;
}

.dlm-acs-footer {
    display: flex;
    align-items: center;
}
.dlm-acs-btn-add {
    height: 32px;
    padding: 0 14px;
    border: 1px dashed #9ca3af;
    background: #fff;
    color: #374151;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
}
.dlm-acs-btn-add:hover {
    border-color: #2563eb;
    color: #1d4ed8;
    background: #eef2ff;
}

.dlm-acs-tags-help {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 10px 14px;
    background: #fafbfc;
    font-size: 12px;
    margin-top: 4px;
}
.dlm-acs-tags-help summary {
    cursor: pointer;
    font-weight: 600;
    color: #374151;
    user-select: none;
}
.dlm-acs-tags-help ul {
    margin: 8px 0 0;
    padding: 0;
    list-style: none;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 6px 16px;
}
.dlm-acs-tags-help li {
    display: flex;
    gap: 8px;
    align-items: baseline;
}
.dlm-acs-tags-help code {
    background: #eef2ff;
    color: #1d4ed8;
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 11px;
    white-space: nowrap;
}
.dlm-acs-tag-desc {
    color: #6b7280;
}

@media (max-width: 860px) {
    .dlm-acs-row-main {
        grid-template-columns: auto 1fr auto;
        grid-auto-rows: auto;
    }
    .dlm-acs-subject {
        grid-column: 1 / -1;
    }
    .dlm-acs-delay {
        grid-column: 2;
    }
    .dlm-acs-row-actions {
        grid-column: 3;
    }
}
</style>
