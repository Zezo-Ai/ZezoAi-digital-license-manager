<template>
    <div>
        <!-- Page header -->
        <div class="dlm-page-header">
            <h1>{{ trans('settings.title') }}</h1>
        </div>

        <div class="dlm-settings-layout">
            <!-- Vertical Nav Sidebar -->
            <nav class="dlm-settings-nav">
                <ul class="dlm-settings-nav-list">
                    <li v-for="tab in computedTabs" :key="tab.id">
                        <button
                            :class="['dlm-settings-nav-item', { 'dlm-settings-nav-item--active': activeTab === tab.id }]"
                            @click="changeTab(tab.id)"
                        >
                            <span class="dlm-settings-nav-icon" v-html="tabIcon(tab.id)"></span>
                            <span>{{ tab.label }}</span>
                        </button>
                    </li>
                </ul>
            </nav>

            <!-- Content Panel -->
            <div class="dlm-settings-content">
                <div class="dlm-settings-panel-header">
                    <h2>{{ currentTabLabel }}</h2>
                </div>

                <div class="dlm-card">
                    <div class="dlm-card-body">
                        <!-- Dynamic settings tabs (any tab that has sections/fields from PHP) -->
                        <div v-show="isSettingsTab(activeTab)">
                            <form class="dlm-settings-form" @submit.prevent="saveSettings">
                                <template v-if="activeTabData">
                                    <div
                                        v-for="(section, sectionKey) in activeTabData.sections"
                                        :key="sectionKey"
                                        class="dlm-settings-section"
                                    >
                                        <h3 v-if="section.name && sectionCount(activeTabData) > 1">
                                            {{ section.name }}
                                        </h3>

                                        <div
                                            v-for="field in section.fields"
                                            :key="field.id"
                                            class="dlm-form-group"
                                        >
                                            <!-- Checkbox (toggle switch) -->
                                            <template v-if="field.type === 'checkbox'">
                                                <label :for="field.id">{{ field.title }}</label>
                                                <div>
                                                    <label :for="field.id" class="dlm-switch-label">
                                                        <span class="dlm-switch">
                                                            <input
                                                                :id="field.id"
                                                                v-model="settingsValues[field.id]"
                                                                type="checkbox"
                                                                true-value="1"
                                                                false-value=""
                                                            />
                                                            <span class="dlm-switch-slider"></span>
                                                        </span>
                                                        <span>{{ field.label || field.title }}</span>
                                                    </label>
                                                    <p v-if="field.explain" class="dlm-form-hint" v-html="field.explain"></p>
                                                </div>
                                            </template>

                                            <!-- Text -->
                                            <template v-else-if="field.type === 'text'">
                                                <label :for="field.id">{{ field.title }}</label>
                                                <input
                                                    :id="field.id"
                                                    v-model="settingsValues[field.id]"
                                                    type="text"
                                                    class="dlm-input"
                                                    :size="field.size || 20"
                                                />
                                                <p v-if="field.explain" class="dlm-form-hint" v-html="field.explain"></p>
                                            </template>

                                            <!-- Select -->
                                            <template v-else-if="field.type === 'select'">
                                                <label :for="field.id">{{ field.title }}</label>
                                                <select
                                                    :id="field.id"
                                                    v-model="settingsValues[field.id]"
                                                    class="dlm-input"
                                                >
                                                    <option
                                                        v-for="(optLabel, optVal) in field.options"
                                                        :key="optVal"
                                                        :value="optVal"
                                                    >{{ optLabel }}</option>
                                                </select>
                                                <p v-if="field.explain" class="dlm-form-hint" v-html="field.explain"></p>
                                            </template>

                                            <!-- Page Select -->
                                            <template v-else-if="field.type === 'page_select'">
                                                <label :for="field.id">{{ field.title }}</label>
                                                <select
                                                    :id="field.id"
                                                    v-model="settingsValues[field.id]"
                                                    class="dlm-input"
                                                >
                                                    <option value="">{{ field.allow_empty ? trans('settings.none') : trans('settings.select_page') }}</option>
                                                    <option
                                                        v-for="(pageTitle, pageId) in field.options"
                                                        :key="pageId"
                                                        :value="pageId"
                                                    >{{ pageTitle }}</option>
                                                </select>
                                                <p v-if="field.explain" class="dlm-form-hint" v-html="field.explain"></p>
                                            </template>

                                            <!-- Password -->
                                            <template v-else-if="field.type === 'password'">
                                                <label :for="field.id">{{ field.title }}</label>
                                                <input
                                                    :id="field.id"
                                                    v-model="settingsValues[field.id]"
                                                    type="password"
                                                    class="dlm-input"
                                                    :placeholder="field.placeholder || ''"
                                                />
                                                <p v-if="field.explain" class="dlm-form-hint" v-html="field.explain"></p>
                                            </template>

                                            <!-- Textarea -->
                                            <template v-else-if="field.type === 'textarea'">
                                                <label :for="field.id">{{ field.title }}</label>
                                                <textarea
                                                    :id="field.id"
                                                    v-model="settingsValues[field.id]"
                                                    class="dlm-input dlm-textarea"
                                                    :rows="field.rows || 5"
                                                ></textarea>
                                                <p v-if="field.explain" class="dlm-form-hint" v-html="field.explain"></p>
                                            </template>

                                            <!-- Color picker -->
                                            <template v-else-if="field.type === 'color'">
                                                <label :for="field.id">{{ field.title }}</label>
                                                <div class="dlm-color-field">
                                                    <input
                                                        :id="field.id"
                                                        v-model="settingsValues[field.id]"
                                                        type="color"
                                                        class="dlm-color-input"
                                                    />
                                                    <input
                                                        v-model="settingsValues[field.id]"
                                                        type="text"
                                                        class="dlm-input dlm-color-text"
                                                        maxlength="7"
                                                        placeholder="#000000"
                                                    />
                                                    <button
                                                        v-if="field.default_value && settingsValues[field.id] !== field.default_value"
                                                        type="button"
                                                        class="dlm-btn dlm-btn-secondary dlm-btn-sm"
                                                        @click="settingsValues[field.id] = field.default_value"
                                                    >Reset</button>
                                                </div>
                                                <p v-if="field.explain" class="dlm-form-hint" v-html="field.explain"></p>
                                            </template>

                                            <!-- Image upload -->
                                            <template v-else-if="field.type === 'image'">
                                                <ImageUpload
                                                    :id="field.id"
                                                    v-model="settingsValues[field.id]"
                                                    :label="field.title"
                                                    :hint="field.explain"
                                                    :image-url="field.image_url"
                                                />
                                            </template>

                                            <!-- Order statuses (multi-checkbox table) -->
                                            <template v-else-if="field.type === 'order_statuses'">
                                                <label>{{ field.title }}</label>
                                                <table class="dlm-order-statuses-table">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('global.labels.status') }}</th>
                                                            <th>{{ trans('global.labels.send') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(statusLabel, statusSlug) in field.options" :key="statusSlug">
                                                            <td>{{ statusLabel }}</td>
                                                            <td>
                                                                <input
                                                                    type="checkbox"
                                                                    class="dlm-checkbox"
                                                                    :checked="isOrderStatusChecked(field.id, statusSlug)"
                                                                    @change="toggleOrderStatus(field.id, statusSlug, $event)"
                                                                />
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <p v-if="field.explain" class="dlm-form-hint" v-html="field.explain"></p>
                                            </template>

                                            <!-- Items Table -->
                                            <template v-else-if="field.type === 'items_table'">
                                                <table class="dlm-gateways-table">
                                                    <thead>
                                                        <tr>
                                                            <th v-for="col in field.columns" :key="col.key">{{ col.label }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(item, itemId) in field.items" :key="itemId">
                                                            <td v-for="col in field.columns" :key="col.key">
                                                                <template v-if="col.key === 'name'">
                                                                    <div class="dlm-font-medium">{{ item.title }}</div>
                                                                    <div v-if="item.description" class="dlm-gateways-description">{{ item.description }}</div>
                                                                </template>
                                                                <template v-else-if="col.key === 'info'">
                                                                    <span class="dlm-text-sm dlm-text-gray-600 dlm-italic">{{ item.info || '\u2014' }}</span>
                                                                </template>
                                                                <template v-else-if="col.key === 'status'">
                                                                    <span
                                                                        class="dlm-badge"
                                                                        :class="settingsValues[item.status_key] === '1' ? 'dlm-badge-success' : 'dlm-badge-gray'"
                                                                    >
                                                                        {{ settingsValues[item.status_key] === '1' ? trans('settings.items_table.enabled') : trans('settings.items_table.disabled') }}
                                                                    </span>
                                                                </template>
                                                                <template v-else-if="col.key === 'actions'">
                                                                    <button
                                                                        type="button"
                                                                        class="dlm-btn dlm-btn-secondary dlm-btn-sm"
                                                                        @click="openItemModal(field, item)"
                                                                    >
                                                                        {{ trans('settings.items_table.configure') }}
                                                                    </button>
                                                                </template>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <!-- Item Settings Modal -->
                                                <Modal
                                                    :show="itemModal.show && itemModal.fieldId === field.id"
                                                    :title="itemModal.item?.title || ''"
                                                    size="lg"
                                                    @close="closeItemModal"
                                                >
                                                    <div v-if="itemModal.item" class="dlm-gateway-modal-fields">
                                                        <div
                                                            v-for="subField in itemModal.item.fields"
                                                            :key="subField.id"
                                                            class="dlm-form-group"
                                                        >
                                                            <template v-if="subField.type === 'checkbox'">
                                                                <label :for="subField.id">{{ subField.title }}</label>
                                                                <div>
                                                                    <label :for="subField.id" class="dlm-switch-label">
                                                                        <span class="dlm-switch">
                                                                            <input
                                                                                :id="subField.id"
                                                                                v-model="settingsValues[subField.id]"
                                                                                type="checkbox"
                                                                                true-value="1"
                                                                                false-value=""
                                                                            />
                                                                            <span class="dlm-switch-slider"></span>
                                                                        </span>
                                                                        <span>{{ subField.label || subField.title }}</span>
                                                                    </label>
                                                                    <p v-if="subField.explain" class="dlm-form-hint" v-html="subField.explain"></p>
                                                                </div>
                                                            </template>
                                                            <template v-else-if="subField.type === 'text'">
                                                                <label :for="subField.id">{{ subField.title }}</label>
                                                                <input
                                                                    :id="subField.id"
                                                                    v-model="settingsValues[subField.id]"
                                                                    type="text"
                                                                    class="dlm-input"
                                                                    :placeholder="subField.placeholder || ''"
                                                                />
                                                                <p v-if="subField.explain" class="dlm-form-hint" v-html="subField.explain"></p>
                                                            </template>
                                                            <template v-else-if="subField.type === 'select'">
                                                                <label :for="subField.id">{{ subField.title }}</label>
                                                                <select
                                                                    :id="subField.id"
                                                                    v-model="settingsValues[subField.id]"
                                                                    class="dlm-input"
                                                                >
                                                                    <option
                                                                        v-for="(optLabel, optVal) in subField.options"
                                                                        :key="optVal"
                                                                        :value="optVal"
                                                                    >{{ optLabel }}</option>
                                                                </select>
                                                                <p v-if="subField.explain" class="dlm-form-hint" v-html="subField.explain"></p>
                                                            </template>
                                                            <template v-else-if="subField.type === 'password'">
                                                                <label :for="subField.id">{{ subField.title }}</label>
                                                                <input
                                                                    :id="subField.id"
                                                                    v-model="settingsValues[subField.id]"
                                                                    type="password"
                                                                    class="dlm-input"
                                                                    :placeholder="subField.placeholder || ''"
                                                                />
                                                                <p v-if="subField.explain" class="dlm-form-hint" v-html="subField.explain"></p>
                                                            </template>
                                                            <template v-else-if="subField.type === 'textarea'">
                                                                <label :for="subField.id">{{ subField.title }}</label>
                                                                <textarea
                                                                    :id="subField.id"
                                                                    v-model="settingsValues[subField.id]"
                                                                    class="dlm-input dlm-textarea"
                                                                    :rows="subField.rows || 5"
                                                                ></textarea>
                                                                <p v-if="subField.explain" class="dlm-form-hint" v-html="subField.explain"></p>
                                                            </template>
                                                            <template v-else-if="subField.type === 'repeater'">
                                                                <label>{{ subField.title }}</label>
                                                                <p v-if="subField.explain" class="dlm-form-hint dlm-mb-2" v-html="subField.explain"></p>

                                                                <div
                                                                    v-for="(entry, index) in getRepeaterEntries(subField.id)"
                                                                    :key="index"
                                                                    class="dlm-notification-reminder-row"
                                                                >
                                                                    <template v-for="sf in subField.sub_fields" :key="sf.id">
                                                                        <input
                                                                            v-if="sf.type === 'number'"
                                                                            :value="entry[sf.id]"
                                                                            @input="updateRepeaterField(subField.id, index, sf.id, $event)"
                                                                            type="number"
                                                                            :min="sf.min || ''"
                                                                            class="dlm-input dlm-input-sm"
                                                                            style="width: 80px;"
                                                                        />
                                                                    </template>
                                                                    <span v-if="subField.sub_fields.length === 1" class="dlm-text-sm dlm-text-gray-600">{{ subField.sub_fields[0].label }}</span>
                                                                    <button
                                                                        type="button"
                                                                        class="dlm-btn dlm-btn-danger dlm-btn-sm"
                                                                        @click="removeRepeaterEntry(subField.id, index)"
                                                                    >
                                                                        {{ trans('global.actions.remove') }}
                                                                    </button>
                                                                </div>

                                                                <button
                                                                    v-if="getRepeaterEntries(subField.id).length < subField.max_items"
                                                                    type="button"
                                                                    class="dlm-btn dlm-btn-secondary dlm-btn-sm dlm-mt-2"
                                                                    @click="addRepeaterEntry(subField)"
                                                                >
                                                                    {{ subField.add_label }}
                                                                </button>
                                                                <p v-else class="dlm-form-hint dlm-mt-2">{{ subField.max_label }}</p>
                                                            </template>
                                                        </div>
                                                    </div>

                                                    <template #footer>
                                                        <button
                                                            type="button"
                                                            class="dlm-btn dlm-btn-primary"
                                                            :disabled="saving"
                                                            @click="saveAndCloseItemModal"
                                                        >
                                                            {{ saving ? trans('global.buttons.saving') : trans('settings.items_table.done') }}
                                                        </button>
                                                    </template>
                                                </Modal>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <div class="dlm-settings-footer">
                                    <button type="submit" class="dlm-btn dlm-btn-primary" :disabled="saving">
                                        {{ saving ? trans('global.buttons.saving') : trans('global.buttons.save_changes') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- REST API tab -->
                        <div v-show="activeTab === 'rest_api'">
                            <!-- API Keys Management -->
                            <div class="dlm-api-keys-section">
                                <div class="dlm-flex dlm-items-center dlm-justify-between dlm-mb-4">
                                    <h3>{{ trans('settings.rest_api.api_keys_title') }}</h3>
                                    <button
                                        class="dlm-btn dlm-btn-primary dlm-btn-sm"
                                        @click="showApiKeyForm = true; editingApiKey = null; resetApiKeyForm()"
                                    >
                                        {{ trans('global.buttons.add_new') }}
                                    </button>
                                </div>

                                <!-- Newly created key credentials -->
                                <div v-if="newCredentials" class="dlm-credentials-box dlm-mb-4">
                                    <div class="dlm-credentials-header">
                                        <svg class="dlm-credentials-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                        <div>
                                            <p class="dlm-credentials-title">{{ trans('settings.rest_api.credentials_notice') }}</p>
                                        </div>
                                    </div>
                                    <div class="dlm-credentials-keys">
                                        <div class="dlm-credentials-key-row">
                                            <label>{{ trans('settings.rest_api.consumer_key') }}</label>
                                            <div class="dlm-credentials-key-value">
                                                <code>{{ newCredentials.consumer_key }}</code>
                                                <button type="button" class="dlm-credentials-copy" @click="copyToClipboard(newCredentials.consumer_key)" title="Copy">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="dlm-credentials-key-row">
                                            <label>{{ trans('settings.rest_api.consumer_secret') }}</label>
                                            <div class="dlm-credentials-key-value">
                                                <code>{{ newCredentials.consumer_secret }}</code>
                                                <button type="button" class="dlm-credentials-copy" @click="copyToClipboard(newCredentials.consumer_secret)" title="Copy">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="dlm-btn dlm-btn-secondary dlm-btn-sm" @click="newCredentials = null">
                                        {{ trans('settings.rest_api.dismiss_credentials') }}
                                    </button>
                                </div>

                                <!-- API Key Form Modal -->
                                <Modal
                                    :show="showApiKeyForm"
                                    :title="editingApiKey ? trans('settings.rest_api.edit_key') : trans('settings.rest_api.add_key')"
                                    size="xl"
                                    @close="showApiKeyForm = false"
                                >
                                    <form id="api-key-form" @submit.prevent="saveApiKey">
                                        <div class="dlm-api-key-form-fields">
                                            <TextInput
                                                id="api_key_description"
                                                v-model="apiKeyForm.description"
                                                :label="trans('settings.rest_api.fields.description')"
                                                :required="true"
                                            />

                                            <div class="dlm-form-group">
                                                <AsyncSelect
                                                    id="api_key_user"
                                                    v-model="apiKeyForm.user_id"
                                                    search-type="user"
                                                    :label="trans('settings.rest_api.fields.user')"
                                                    :required="true"
                                                    :placeholder="trans('settings.rest_api.fields.user_placeholder')"
                                                    :initial-option="editingApiKeyUserOption"
                                                />
                                            </div>

                                            <Dropdown
                                                id="api_key_permissions"
                                                v-model="apiKeyForm.permissions"
                                                :label="trans('settings.rest_api.fields.permissions')"
                                                :options="permissionOptions"
                                            />
                                        </div>

                                        <div class="dlm-form-group dlm-mt-4">
                                            <label>{{ trans('settings.rest_api.fields.endpoints') }} *</label>

                                            <div class="dlm-endpoint-groups-grid">
                                                <div v-for="(group, groupKey) in groupedEndpoints" :key="groupKey" class="dlm-endpoint-group" :data-group="groupKey">
                                                <div class="dlm-endpoint-group-header">
                                                    <span class="dlm-endpoint-group-title">
                                                        <svg v-if="groupKey === 'licenses'" class="dlm-endpoint-group-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg>
                                                        <svg v-else-if="groupKey === 'generators'" class="dlm-endpoint-group-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                        {{ endpointGroupLabels[groupKey] || groupKey }}
                                                    </span>
                                                    <div class="dlm-endpoint-group-actions">
                                                        <button type="button" class="dlm-btn-link" @click="selectAllInGroup(groupKey)">
                                                            {{ trans('global.buttons.select_all') }}
                                                        </button>
                                                        <span class="dlm-text-gray-400">|</span>
                                                        <button type="button" class="dlm-btn-link" @click="deselectAllInGroup(groupKey)">
                                                            {{ trans('global.buttons.deselect_all') }}
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="dlm-endpoint-group-items">
                                                    <label v-for="ep in group.endpoints" :key="ep.id" class="dlm-endpoint-item">
                                                        <input type="checkbox" :value="ep.id" v-model="apiKeyForm.endpoints" class="dlm-checkbox" />
                                                        <span class="dlm-endpoint-method" :class="'dlm-method-' + ep.method.toLowerCase()">{{ ep.method }}</span>
                                                        <span class="dlm-endpoint-route">{{ ep.name }}</span>
                                                    </label>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    <template #footer>
                                        <button type="submit" form="api-key-form" class="dlm-btn dlm-btn-primary" :disabled="savingApiKey">
                                            {{ savingApiKey ? trans('global.buttons.saving') : trans('global.buttons.save') }}
                                        </button>
                                        <button type="button" class="dlm-btn dlm-btn-secondary" @click="showApiKeyForm = false">
                                            {{ trans('global.buttons.cancel') }}
                                        </button>
                                    </template>
                                </Modal>

                                <!-- API Keys Table -->
                                <div v-if="apiKeys.length > 0" class="dlm-table-responsive">
                                    <table class="dlm-table">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('settings.rest_api.columns.description') }}</th>
                                                <th>{{ trans('settings.rest_api.columns.user') }}</th>
                                                <th>{{ trans('settings.rest_api.columns.permissions') }}</th>
                                                <th>{{ trans('settings.rest_api.columns.truncated_key') }}</th>
                                                <th>{{ trans('settings.rest_api.columns.last_access') }}</th>
                                                <th>{{ trans('global.labels.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="key in apiKeys" :key="key.id">
                                                <td>{{ key.description }}</td>
                                                <td>{{ key.user_label }}</td>
                                                <td>
                                                    <span class="dlm-badge">{{ key.permissions }}</span>
                                                </td>
                                                <td><code>...{{ key.truncated_key }}</code></td>
                                                <td>{{ key.last_access || trans('settings.rest_api.never') }}</td>
                                                <td>
                                                    <div class="dlm-flex dlm-gap-2">
                                                        <button class="dlm-btn dlm-btn-secondary dlm-btn-sm" @click="editApiKey(key)">
                                                            {{ trans('global.actions.edit') }}
                                                        </button>
                                                        <button class="dlm-btn dlm-btn-danger dlm-btn-sm" @click="deleteApiKey(key.id)">
                                                            {{ trans('global.actions.delete') }}
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-else-if="!loadingApiKeys" class="dlm-text-gray-500">
                                    {{ trans('global.messages.no_records') }}
                                </p>
                            </div>
                        </div>

                        <!-- Tools -->
                        <div v-show="activeTab === 'tools'">
                            <!-- Dynamic tools -->
                            <template v-for="tool in tools" :key="tool.slug">
                                <!-- Migration type tool -->
                                <div v-if="tool.type === 'migration'" class="dlm-tools-card">
                                    <div class="dlm-tools-card-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                                    </div>
                                    <div class="dlm-tools-card-content">
                                        <h3>{{ trans('settings.tools.dynamic.migration_title') }}</h3>
                                        <p>{{ tool.description }}</p>

                                        <!-- Plugin select -->
                                        <div class="dlm-form-group dlm-mb-3">
                                            <label>{{ trans('settings.tools.dynamic.select_plugin_label') }}</label>
                                            <select
                                                v-model="migrationForm.identifier"
                                                class="dlm-input"
                                                @change="loadMigrationStatus"
                                            >
                                                <option value="none">{{ trans('settings.tools.dynamic.select_plugin') }}</option>
                                                <option
                                                    v-for="plugin in tool.plugins"
                                                    :key="plugin.id"
                                                    :value="plugin.id"
                                                >{{ plugin.name }}</option>
                                            </select>
                                        </div>

                                        <!-- Preserve IDs checkbox -->
                                        <div class="dlm-form-group dlm-mt-4 dlm-mb-3">
                                            <label>
                                                <input
                                                    v-model="migrationForm.preserve_ids"
                                                    type="checkbox"
                                                    class="dlm-checkbox"
                                                />
                                                {{ trans('settings.tools.dynamic.preserve_ids_warning') }}
                                            </label>
                                        </div>

                                        <!-- Progress bar -->
                                        <div v-if="toolProgress[tool.slug]?.running || toolProgress[tool.slug]?.finished" class="dlm-tool-progress">
                                            <div class="dlm-tool-progress-bar">
                                                <div
                                                    class="dlm-tool-progress-bar-inner"
                                                    :style="{ width: (toolProgress[tool.slug]?.percent || 0) + '%' }"
                                                ></div>
                                            </div>
                                            <div class="dlm-tool-progress-info">
                                                {{ toolProgress[tool.slug]?.message || '' }}
                                                <template v-if="toolProgress[tool.slug]?.finished">
                                                    {{ trans('settings.tools.dynamic.finished') }}
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Migration status + undo -->
                                        <div v-if="migrationStatus" class="dlm-tool-status">
                                            <span>{{ migrationStatus }}</span>
                                            <a @click.prevent="undoMigration">{{ trans('settings.tools.dynamic.undo') }}</a>
                                        </div>

                                        <!-- Migrate button -->
                                        <button
                                            class="dlm-btn dlm-btn-secondary"
                                            :disabled="toolProgress[tool.slug]?.running || migrationForm.identifier === 'none' || (!!migrationStatus && !toolProgress[tool.slug]?.finished)"
                                            @click="runTool(tool)"
                                        >
                                            {{ trans('settings.tools.dynamic.migrate_button') }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Standard type tool -->
                                <div v-else class="dlm-tools-card">
                                    <div class="dlm-tools-card-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.193-.14 1.743" /></svg>
                                    </div>
                                    <div class="dlm-tools-card-content">
                                        <h3>{{ tool.name }}</h3>
                                        <p>{{ tool.description }}</p>

                                        <!-- Dynamic form fields -->
                                        <template v-if="tool.form_fields && tool.form_fields.length">
                                            <div
                                                v-for="field in tool.form_fields"
                                                :key="field.name"
                                                class="dlm-form-group dlm-mb-3"
                                            >
                                                <template v-if="field.type === 'ajax_select' || field.type === 'ajax_multiselect'">
                                                    <AsyncSelect
                                                        v-model="toolForms[tool.slug][field.name]"
                                                        :search-type="field.search_type"
                                                        :placeholder="field.placeholder || ''"
                                                        :label="field.label || ''"
                                                        :required="field.required || false"
                                                        :multiple="field.type === 'ajax_multiselect'"
                                                    />
                                                </template>
                                                <template v-else-if="field.type === 'select'">
                                                    <label v-if="field.label" :for="field.name">{{ field.label }}</label>
                                                    <select
                                                        v-model="toolForms[tool.slug][field.name]"
                                                        :id="field.name"
                                                        class="dlm-input"
                                                    >
                                                        <option v-if="field.placeholder" value="">{{ field.placeholder }}</option>
                                                        <option
                                                            v-for="(optLabel, optVal) in field.options"
                                                            :key="optVal"
                                                            :value="optVal"
                                                        >{{ optLabel }}</option>
                                                    </select>
                                                </template>
                                                <template v-else-if="field.type === 'checkbox'">
                                                    <label>
                                                        <input
                                                            v-model="toolForms[tool.slug][field.name]"
                                                            type="checkbox"
                                                            class="dlm-checkbox"
                                                        />
                                                        {{ field.label }}
                                                    </label>
                                                </template>
                                            </div>
                                        </template>

                                        <!-- Progress bar -->
                                        <div v-if="toolProgress[tool.slug]?.running || toolProgress[tool.slug]?.finished" class="dlm-tool-progress">
                                            <div class="dlm-tool-progress-bar">
                                                <div
                                                    class="dlm-tool-progress-bar-inner"
                                                    :style="{ width: (toolProgress[tool.slug]?.percent || 0) + '%' }"
                                                ></div>
                                            </div>
                                            <div class="dlm-tool-progress-info">
                                                {{ toolProgress[tool.slug]?.message || '' }}
                                                <template v-if="toolProgress[tool.slug]?.finished">
                                                    {{ trans('settings.tools.dynamic.finished') }}
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Run button -->
                                        <button
                                            class="dlm-btn dlm-btn-secondary"
                                            :disabled="toolProgress[tool.slug]?.running"
                                            @click="runTool(tool)"
                                        >
                                            {{ trans('settings.tools.dynamic.run_button') }}
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- External tabs (registered by extensions) -->
                        <div
                            v-for="tab in externalTabs"
                            :key="tab.id"
                            v-show="activeTab === tab.id"
                            :id="'dlm-settings-tab-' + tab.id"
                        ></div>

                        <!-- Help -->
                        <div v-show="activeTab === 'help'">
                            <div class="dlm-tools-card">
                                <div class="dlm-tools-card-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                                </div>
                                <div class="dlm-tools-card-content">
                                    <h3>{{ trans('settings.help.documentation.title') }}</h3>
                                    <p>{{ trans('settings.help.documentation.description') }}</p>
                                    <a
                                        href="https://docs.codeverve.com/digital-license-manager/"
                                        target="_blank"
                                        class="dlm-btn dlm-btn-secondary"
                                    >
                                        {{ trans('settings.help.documentation.button') }}
                                    </a>
                                </div>
                            </div>

                            <div class="dlm-tools-card">
                                <div class="dlm-tools-card-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.712 4.33a9.027 9.027 0 0 1 1.652 1.306c.51.51.944 1.064 1.306 1.652M16.712 4.33l-3.448 4.138m3.448-4.138a9.014 9.014 0 0 0-9.424 0M19.67 7.288l-4.138 3.448m4.138-3.448a9.014 9.014 0 0 1 0 9.424m-4.138-5.976a3.736 3.736 0 0 0-.88-1.388 3.737 3.737 0 0 0-1.388-.88m2.268 2.268a3.765 3.765 0 0 1 0 2.528m-2.268-4.796a3.765 3.765 0 0 0-2.528 0m4.796 2.268c-.181.506-.475.982-.88 1.388a3.736 3.736 0 0 1-1.388.88m2.268-2.268 4.138 3.448m0 0a9.027 9.027 0 0 1-1.306 1.652 9.027 9.027 0 0 1-1.652 1.306m2.958-2.958a9.014 9.014 0 0 1-9.424 0m5.976-4.138-3.448 4.138m0 0a3.765 3.765 0 0 1-2.528 0m2.528 0 3.448 4.138m-5.976-4.138-4.138 3.448m4.138-3.448a3.736 3.736 0 0 1-1.388.88 3.737 3.737 0 0 1-.88-.88m0 0-3.448 4.138m3.448-4.138a3.765 3.765 0 0 1 0-2.528m0 2.528-4.138-3.448m4.138 3.448a3.736 3.736 0 0 0 .88 1.388 3.737 3.737 0 0 0 1.388.88m-2.268-2.268L4.33 16.712m0 0a9.027 9.027 0 0 1-1.652-1.306 9.027 9.027 0 0 1-1.306-1.652m2.958 2.958a9.014 9.014 0 0 1 0-9.424m4.138 5.976-3.448-4.138m0 0a9.027 9.027 0 0 1 1.306-1.652A9.014 9.014 0 0 1 7.288 4.33m-2.958 2.958a9.014 9.014 0 0 1 9.424 0" /></svg>
                                </div>
                                <div class="dlm-tools-card-content">
                                    <h3>{{ trans('settings.help.support.title') }}</h3>
                                    <p>{{ trans('settings.help.support.description') }}</p>
                                    <a
                                        href="https://docs.codeverve.com/digital-license-manager/"
                                        target="_blank"
                                        class="dlm-btn dlm-btn-secondary"
                                    >
                                        {{ trans('settings.help.support.button') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { trans } from '@digital-license-manager/ui/utils/useLang'
import { useAlertStore } from '@digital-license-manager/ui/stores/alert'
import * as settingsService from '../services/settings'
import Dropdown from '@digital-license-manager/ui/components/input/Dropdown.vue'
import AsyncSelect from '@digital-license-manager/ui/components/input/AsyncSelect.vue'
import ImageUpload from '@digital-license-manager/ui/components/input/ImageUpload.vue'
import TextInput from '@digital-license-manager/ui/components/input/TextInput.vue'
import Modal from '@digital-license-manager/ui/components/Modal.vue'

const props = defineProps({
    tab: {
        type: String,
        default: 'general',
    },
})

const route = useRoute()
const router = useRouter()
const alertStore = useAlertStore()

const activeTab = ref(props.tab || route.params.tab || 'general')
const loading = ref(true)
const saving = ref(false)

// Settings data from PHP
const tabData = ref({})
const settingsValues = reactive({})

// Dynamic tools state
const tools = ref([])
const loadingTools = ref(false)
const toolProgress = reactive({})
const toolForms = reactive({})
const migrationForm = reactive({ identifier: 'none', preserve_ids: false })
const migrationStatus = ref('')

// Item modal state (generic for items_table fields)
const itemModal = reactive({ show: false, fieldId: null, item: null })

function openItemModal(field, item) {
    itemModal.fieldId = field.id
    itemModal.item = item
    itemModal.show = true
}

function closeItemModal() {
    itemModal.show = false
    itemModal.fieldId = null
    itemModal.item = null
}

async function saveAndCloseItemModal() {
    await saveSettings()
    closeItemModal()
}

function getRepeaterEntries(key) {
    if (!settingsValues[key] || !Array.isArray(settingsValues[key])) {
        return []
    }
    return settingsValues[key]
}

function addRepeaterEntry(subField) {
    const key = subField.id
    if (!settingsValues[key] || !Array.isArray(settingsValues[key])) {
        settingsValues[key] = []
    }
    if (settingsValues[key].length < subField.max_items) {
        const entry = {}
        for (const sf of subField.sub_fields) {
            entry[sf.id] = ''
        }
        settingsValues[key].push(entry)
    }
}

function removeRepeaterEntry(key, index) {
    if (Array.isArray(settingsValues[key])) {
        settingsValues[key].splice(index, 1)
    }
}

function updateRepeaterField(key, index, fieldId, event) {
    if (Array.isArray(settingsValues[key]) && settingsValues[key][index]) {
        settingsValues[key][index][fieldId] = event.target.value
    }
}

// Tab icons (Heroicons outline SVGs)
const tabIcons = {
    general: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>',
    rest_api: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg>',
    tools: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.049.58.025 1.193-.14 1.743" /></svg>',
    help: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg>',
    woocommerce: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>',
}

const defaultIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>'

function tabIcon(tabId) {
    const tab = findTabData(tabId)
    if (tab && tab.icon) return tab.icon
    return tabIcons[tabId] || defaultIcon
}

// External tabs: tabs registered by extensions with no sections and not built-in custom tabs
const externalTabs = computed(() => {
    return computedTabs.value.filter(tab => {
        if (customTabs.has(tab.id)) return false
        const data = findTabData(tab.id)
        return data && (!data.sections || Object.keys(data.sections).length === 0)
    })
})

function dispatchTabEvent(tabId) {
    nextTick(() => {
        document.dispatchEvent(new CustomEvent('dlm-settings-tab-activated', {
            detail: { tab: tabId }
        }))
    })
}

const currentTabLabel = computed(() => {
    const tab = computedTabs.value.find(t => t.id === activeTab.value)
    return tab ? tab.label : ''
})

// Compute tabs from PHP structure
const computedTabs = computed(() => {
    const tabs = []
    for (const [key, tab] of Object.entries(tabData.value)) {
        tabs.push({
            id: tab.slug || key,
            label: tab.name,
            priority: tab.priority || 10,
        })
    }
    tabs.sort((a, b) => a.priority - b.priority)
    return tabs
})

// Custom tabs that have hardcoded UI (not dynamic settings fields)
const customTabs = new Set(['rest_api', 'tools', 'help'])

// Find tab data by slug (PHP keys may differ from slugs)
function findTabData(tabId) {
    if (tabData.value[tabId]) return tabData.value[tabId]
    for (const tab of Object.values(tabData.value)) {
        if (tab.slug === tabId) return tab
    }
    return null
}

// Check if a tab is a dynamic settings tab (has sections/fields from PHP)
function isSettingsTab(tabId) {
    if (customTabs.has(tabId)) return false
    const tab = findTabData(tabId)
    return tab && tab.sections && Object.keys(tab.sections).length > 0
}

// Get the active tab's data
const activeTabData = computed(() => {
    return findTabData(activeTab.value)
})

// Count non-empty sections in a tab
function sectionCount(tab) {
    if (!tab || !tab.sections) return 0
    return Object.keys(tab.sections).length
}

// Order statuses helpers
function isOrderStatusChecked(fieldId, statusSlug) {
    const val = settingsValues[fieldId]
    if (!val || typeof val !== 'object') return false
    return !!(val[statusSlug] && val[statusSlug].send)
}

function toggleOrderStatus(fieldId, statusSlug, event) {
    if (!settingsValues[fieldId] || typeof settingsValues[fieldId] !== 'object') {
        settingsValues[fieldId] = {}
    }
    if (event.target.checked) {
        settingsValues[fieldId][statusSlug] = { send: '1' }
    } else {
        delete settingsValues[fieldId][statusSlug]
    }
}

// API Keys state
const apiKeys = ref([])
const loadingApiKeys = ref(false)
const showApiKeyForm = ref(false)
const savingApiKey = ref(false)
const editingApiKey = ref(null)
const newCredentials = ref(null)
const availableEndpoints = ref([])

const apiKeyForm = reactive({
    description: '',
    user_id: null,
    permissions: 'read',
    endpoints: [],
})

const permissionOptions = [
    { value: 'read', label: trans('settings.rest_api.permissions.read') },
    { value: 'write', label: trans('settings.rest_api.permissions.write') },
    { value: 'read_write', label: trans('settings.rest_api.permissions.read_write') },
]

const editingApiKeyUserOption = computed(() => {
    if (editingApiKey.value && apiKeyForm.user_id) {
        return {
            id: editingApiKey.value.user_id,
            text: editingApiKey.value.user_label,
        }
    }
    return null
})

// Group endpoints by their group field
const groupedEndpoints = computed(() => {
    const groups = {}
    for (const ep of availableEndpoints.value) {
        const group = ep.group || 'other'
        if (!groups[group]) {
            groups[group] = { name: group, endpoints: [] }
        }
        groups[group].endpoints.push(ep)
    }
    return groups
})

// Labels for endpoint groups
const endpointGroupLabels = {
    licenses: trans('settings.rest_api.groups.licenses'),
    generators: trans('settings.rest_api.groups.generators'),
}

// Select/deselect helpers for endpoint groups
function selectAllInGroup(group) {
    const ids = groupedEndpoints.value[group].endpoints.map(ep => String(ep.id))
    apiKeyForm.endpoints = [...new Set([...apiKeyForm.endpoints, ...ids])]
}

function deselectAllInGroup(group) {
    const ids = groupedEndpoints.value[group].endpoints.map(ep => String(ep.id))
    apiKeyForm.endpoints = apiKeyForm.endpoints.filter(id => !ids.includes(String(id)))
}

function changeTab(tabId) {
    activeTab.value = tabId
    router.push(`/settings/${tabId}`)

    if (tabId === 'rest_api') {
        loadApiKeys()
        loadEndpoints()
    }
    if (tabId === 'tools') {
        loadTools()
    }
    dispatchTabEvent(tabId)
}

async function loadSettings() {
    loading.value = true

    try {
        const response = await settingsService.get()
        const json = await response.json()

        if (json.success) {
            tabData.value = json.data.tabs

            // Extract field values into settingsValues
            for (const [, tab] of Object.entries(json.data.tabs)) {
                if (tab.sections) {
                    for (const [, section] of Object.entries(tab.sections)) {
                        if (section.fields) {
                            for (const field of section.fields) {
                                const defaultVal = field.type === 'order_statuses' ? {} : ''
                                settingsValues[field.id] = field.value != null ? field.value : defaultVal

                                // For items_table, populate all item sub-field values
                                if (field.type === 'items_table' && field.items) {
                                    for (const item of Object.values(field.items)) {
                                        if (item.fields) {
                                            for (const subField of item.fields) {
                                                if (subField.type === 'repeater') {
                                                    settingsValues[subField.id] = Array.isArray(subField.value) ? subField.value : []
                                                } else {
                                                    settingsValues[subField.id] = subField.value != null ? subField.value : ''
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            alertStore.error(json.data?.message || trans('global.errors.network'))
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        loading.value = false
    }
}

async function saveSettings() {
    saving.value = true

    try {
        // Collect only the field values for the active tab.
        const tab = tabData.value[activeTab.value]
        const tabValues = {}
        if (tab && tab.sections) {
            for (const section of Object.values(tab.sections)) {
                if (section.fields) {
                    for (const field of section.fields) {
                        if (field.id in settingsValues) {
                            tabValues[field.id] = settingsValues[field.id]
                        }

                        // For items_table, include all item sub-field values
                        if (field.type === 'items_table' && field.items) {
                            for (const item of Object.values(field.items)) {
                                if (item.fields) {
                                    for (const subField of item.fields) {
                                        if (subField.id in settingsValues) {
                                            tabValues[subField.id] = settingsValues[subField.id]
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        const response = await settingsService.save(activeTab.value, tabValues)
        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
        } else {
            alertStore.error(json.data?.message || trans('global.errors.network'))
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        saving.value = false
    }
}

// API Keys
async function loadApiKeys() {
    loadingApiKeys.value = true
    try {
        const response = await settingsService.getApiKeys()
        const json = await response.json()
        if (json.success) {
            apiKeys.value = json.data.records
        }
    } catch (error) {
        console.error('Failed to load API keys:', error)
    } finally {
        loadingApiKeys.value = false
    }
}

async function loadEndpoints() {
    if (availableEndpoints.value.length > 0) return
    try {
        const response = await settingsService.getEndpoints()
        const json = await response.json()
        if (json.success) {
            availableEndpoints.value = json.data.endpoints
        }
    } catch (error) {
        console.error('Failed to load endpoints:', error)
    }
}

function resetApiKeyForm() {
    apiKeyForm.description = ''
    apiKeyForm.user_id = null
    apiKeyForm.permissions = 'read'
    apiKeyForm.endpoints = []
}

function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            alertStore.success(trans('global.messages.copied') || 'Copied to clipboard!')
        })
    } else {
        const textarea = document.createElement('textarea')
        textarea.value = text
        textarea.style.position = 'fixed'
        textarea.style.opacity = '0'
        document.body.appendChild(textarea)
        textarea.select()
        document.execCommand('copy')
        document.body.removeChild(textarea)
        alertStore.success(trans('global.messages.copied') || 'Copied to clipboard!')
    }
}

function editApiKey(key) {
    editingApiKey.value = key
    apiKeyForm.description = key.description
    apiKeyForm.user_id = key.user_id
    apiKeyForm.permissions = key.permissions
    // Handle both indexed arrays (old format) and associative objects (new format)
    // Vue checkboxes expect an array of values: ["011", "015"]
    if (Array.isArray(key.endpoints)) {
        apiKeyForm.endpoints = key.endpoints.map(String)
    } else {
        // Convert associative object {"011": true, "015": true} to array ["011", "015"]
        apiKeyForm.endpoints = Object.keys(key.endpoints || {})
    }
    showApiKeyForm.value = true
}

async function saveApiKey() {
    savingApiKey.value = true
    try {
        const data = { ...apiKeyForm }
        let response

        if (editingApiKey.value) {
            response = await settingsService.updateApiKey(editingApiKey.value.id, data)
        } else {
            response = await settingsService.createApiKey(data)
        }

        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            showApiKeyForm.value = false

            // Show credentials for newly created keys
            if (!editingApiKey.value && json.data.consumer_key) {
                newCredentials.value = {
                    consumer_key: json.data.consumer_key,
                    consumer_secret: json.data.consumer_secret,
                }
            }

            editingApiKey.value = null
            await loadApiKeys()
        } else {
            alertStore.error(json.data?.message || trans('global.errors.network'))
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    } finally {
        savingApiKey.value = false
    }
}

async function deleteApiKey(id) {
    if (!confirm(trans('global.messages.confirm'))) return

    try {
        const response = await settingsService.deleteApiKey(id)
        const json = await response.json()

        if (json.success) {
            alertStore.success(json.data.message)
            await loadApiKeys()
        } else {
            alertStore.error(json.data?.message || trans('global.errors.network'))
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    }
}

// Dynamic tools
async function loadTools() {
    loadingTools.value = true
    try {
        const response = await settingsService.getTools()
        const json = await response.json()
        if (json.success) {
            // Initialize form data BEFORE setting tools to avoid template access errors
            for (const tool of json.data.tools) {
                if (tool.type === 'standard' && tool.form_fields && tool.form_fields.length) {
                    if (!toolForms[tool.slug]) {
                        toolForms[tool.slug] = {}
                        for (const field of tool.form_fields) {
                            toolForms[tool.slug][field.name] = field.type === 'checkbox' ? false : null
                        }
                    }
                }
            }
            tools.value = json.data.tools
        }
    } catch (error) {
        console.error('Failed to load tools:', error)
    } finally {
        loadingTools.value = false
    }
}

async function loadMigrationStatus() {
    if (migrationForm.identifier === 'none') {
        migrationStatus.value = ''
        return
    }
    try {
        const response = await settingsService.getToolStatus(migrationForm.identifier)
        const json = await response.json()
        if (json.success) {
            migrationStatus.value = json.data.status || ''
        }
    } catch (error) {
        console.error('Failed to load migration status:', error)
    }
}

async function runTool(tool) {
    if (!confirm(trans('settings.tools.dynamic.confirm_warning'))) return

    // Build payload
    const payload = { tool: tool.slug, id: Date.now() }

    if (tool.type === 'migration') {
        payload.identifier = migrationForm.identifier
        payload.preserve_ids = migrationForm.preserve_ids ? 1 : 0
    } else if (toolForms[tool.slug]) {
        Object.assign(payload, toolForms[tool.slug])
    }

    // Initialize progress
    toolProgress[tool.slug] = { running: true, finished: false, percent: 0, message: '' }
    window.onbeforeunload = () => ''

    try {
        // Init phase
        const initResponse = await settingsService.initTool(payload)
        const initJson = await initResponse.json()

        if (!initJson.success) {
            toolProgress[tool.slug] = { running: false, finished: false, percent: 0, message: '' }
            window.onbeforeunload = null
            alertStore.error(initJson.data?.message || trans('global.errors.network'))
            return
        }

        // Handle warning from init
        if (initJson.data?.warning) {
            if (!confirm(initJson.data.warning)) {
                toolProgress[tool.slug] = { running: false, finished: false, percent: 0, message: '' }
                window.onbeforeunload = null
                return
            }
        }

        // Start step processing
        await processToolStep(tool, payload, 1, 1)
    } catch (error) {
        toolProgress[tool.slug] = { running: false, finished: false, percent: 0, message: '' }
        window.onbeforeunload = null
        alertStore.error(trans('global.errors.network'))
    }
}

async function processToolStep(tool, payload, step, page) {
    try {
        const response = await settingsService.processTool({
            ...payload,
            step,
            page,
        })
        const json = await response.json()

        if (!json.success) {
            toolProgress[tool.slug] = { running: false, finished: false, percent: toolProgress[tool.slug]?.percent || 0, message: json.data?.message || '' }
            window.onbeforeunload = null
            alertStore.error(json.data?.message || trans('global.errors.network'))
            return
        }

        const data = json.data
        toolProgress[tool.slug] = {
            running: data.next_step !== -1,
            finished: data.next_step === -1,
            percent: data.percent || 0,
            message: data.message || '',
        }

        if (data.next_step !== -1) {
            setTimeout(() => {
                processToolStep(tool, payload, data.next_step, data.next_page)
            }, 200)
        } else {
            window.onbeforeunload = null
            if (tool.type === 'migration') {
                loadMigrationStatus()
            }
        }
    } catch (error) {
        toolProgress[tool.slug] = { running: false, finished: false, percent: 0, message: '' }
        window.onbeforeunload = null
        alertStore.error(trans('global.errors.network'))
    }
}

async function undoMigration() {
    if (!confirm(trans('settings.tools.dynamic.undo_confirm'))) return

    try {
        const response = await settingsService.undoTool(migrationForm.identifier)
        const json = await response.json()

        if (json.success) {
            migrationStatus.value = ''
            toolProgress['migration'] = { running: false, finished: false, percent: 0, message: '' }
            alertStore.success(trans('settings.tools.dynamic.undo_success'))
            await loadTools()
        } else {
            alertStore.error(json.data?.message || trans('global.errors.network'))
        }
    } catch (error) {
        alertStore.error(trans('global.errors.network'))
    }
}

watch(() => route.params.tab, (newTab) => {
    if (newTab) {
        activeTab.value = newTab
        if (newTab === 'rest_api') {
            loadApiKeys()
            loadEndpoints()
        }
        if (newTab === 'tools') {
            loadTools()
        }
        dispatchTabEvent(newTab)
    }
})

onMounted(() => {
    loadSettings()
    if (activeTab.value === 'rest_api') {
        loadApiKeys()
        loadEndpoints()
    }
    if (activeTab.value === 'tools') {
        loadTools()
    }
    dispatchTabEvent(activeTab.value)
})

</script>

<style lang="scss" scoped>
// Layout: sidebar + content
.dlm-settings-layout {
    @apply dlm-flex dlm-gap-6;
    min-height: 500px;
}

.dlm-settings-nav {
    @apply dlm-flex-shrink-0;
    width: 240px;
}

.dlm-settings-nav-list {
    @apply dlm-list-none dlm-m-0 dlm-p-0;
    position: sticky;
    top: 46px;

    li {
        @apply dlm-m-0 dlm-p-0;
    }
}

.dlm-settings-nav-item {
    @apply dlm-flex dlm-items-center dlm-gap-3 dlm-w-full dlm-px-4 dlm-py-3;
    @apply dlm-text-sm dlm-font-medium dlm-text-gray-600;
    @apply dlm-bg-transparent dlm-border-0 dlm-rounded-lg dlm-cursor-pointer;
    transition: background-color 0.15s ease, color 0.15s ease;

    &:hover {
        @apply dlm-bg-gray-100 dlm-text-gray-900;
    }

    &--active {
        @apply dlm-bg-primary-50 dlm-text-primary-700;

        .dlm-settings-nav-icon {
            @apply dlm-text-primary-600;
        }
    }
}

.dlm-settings-nav-icon {
    @apply dlm-flex-shrink-0;
    width: 20px;
    height: 20px;

    :deep(svg) {
        width: 20px;
        height: 20px;
    }
}

.dlm-settings-content {
    @apply dlm-flex-1;
    min-width: 0;

    // Neutralize the outer .dlm-card wrapper so sections render against page background
    // Use child combinator to protect nested .dlm-card (e.g. REST API key form)
    > .dlm-card {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    > .dlm-card > .dlm-card-body {
        padding: 0 !important;
    }
}

.dlm-settings-panel-header {
    @apply dlm-mb-6;

    h2 {
        @apply dlm-text-lg dlm-font-semibold dlm-text-gray-900 dlm-m-0;
    }
}

// General tab form
.dlm-settings-form {
    max-width: none;
}

.dlm-settings-section {
    @apply dlm-mb-5 dlm-bg-white dlm-rounded-lg dlm-border dlm-border-gray-200;
    padding: 24px 28px;

    h3 {
        @apply dlm-font-semibold dlm-text-gray-900 dlm-uppercase dlm-tracking-wide dlm-mb-5 dlm-pb-3;
        font-size: 0.65rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .dlm-form-group {
        @apply dlm-mb-0;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;

        // Horizontal grid for text/select/switch inputs: label left, input right
        &:has(.dlm-input), &:has(.dlm-switch) {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 0 24px;
            align-items: start;

            > label {
                padding-top: 7px; // vertical-align with input
            }

            .dlm-input {
                max-width: 560px;
            }

            .dlm-form-hint {
                grid-column: 2;
            }
        }

    }

    .dlm-form-group:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    h3 + .dlm-form-group {
        padding-top: 0;
    }
}

// REST API tab card
.dlm-api-keys-section {
    @apply dlm-bg-white dlm-rounded-lg dlm-border dlm-border-gray-200;
    padding: 24px 28px;
}

// Order statuses table
.dlm-order-statuses-table {
    @apply dlm-w-full dlm-text-sm;
    max-width: 560px;

    th {
        @apply dlm-text-left dlm-p-2 dlm-border-b dlm-border-gray-200 dlm-font-medium dlm-text-gray-600;
    }

    td {
        @apply dlm-p-2 dlm-border-b dlm-border-gray-100;
    }

    tr:nth-child(even) {
        @apply dlm-bg-gray-50;
    }
}

// Color picker field
.dlm-color-field {
    display: flex;
    align-items: center;
    gap: 8px;
}

.dlm-color-input {
    width: 40px;
    height: 40px;
    padding: 2px;
    border: 1px solid #d0d5dd;
    border-radius: 6px;
    cursor: pointer;
    background: none;
}

.dlm-color-text {
    width: 100px !important;
    font-family: monospace;
}

// Textarea styling
.dlm-textarea {
    resize: vertical;
    min-height: 60px;
    max-width: 560px;
    font-family: inherit;
}

// Footer for save button
.dlm-settings-footer {
    @apply dlm-mt-6 dlm-pt-4;
}

.dlm-mt-4 {
    margin-top: 1rem;
}

.dlm-mb-4 {
    margin-bottom: 1rem;
}

// Tools & Help cards
.dlm-tools-card {
    @apply dlm-flex dlm-gap-4 dlm-p-5 dlm-rounded-lg dlm-border dlm-border-gray-200 dlm-bg-white;

    & + & {
        @apply dlm-mt-4;
    }
}

.dlm-tools-card-icon {
    @apply dlm-flex-shrink-0 dlm-rounded-lg dlm-bg-gray-100 dlm-flex dlm-items-center dlm-justify-center dlm-text-gray-500;
    width: 40px;
    height: 40px;

    svg {
        width: 20px;
        height: 20px;
    }
}

.dlm-tools-card-content {
    @apply dlm-flex-1;

    h3 {
        @apply dlm-text-base dlm-font-semibold dlm-mb-2 dlm-mt-0;
    }

    p {
        @apply dlm-text-sm dlm-text-gray-600 dlm-mb-4;
    }

    .dlm-btn {
        @apply dlm-mt-4;
    }
}

// API key form layout
.dlm-api-key-form-fields {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
}

.dlm-api-key-form-actions {
    @apply dlm-flex dlm-gap-2 dlm-mt-5 dlm-pt-4;
    border-top: 1px solid #f3f4f6;
}

.dlm-credentials-box {
    @apply dlm-rounded-lg dlm-overflow-hidden;
    border: 1px solid #fbbf24;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
}

.dlm-credentials-header {
    @apply dlm-flex dlm-items-start dlm-gap-3 dlm-p-4;
    background: rgba(251, 191, 36, 0.15);
    border-bottom: 1px solid rgba(251, 191, 36, 0.3);
}

.dlm-credentials-icon {
    width: 24px;
    height: 24px;
    color: #d97706;
    flex-shrink: 0;
    margin-top: 2px;
}

.dlm-credentials-title {
    @apply dlm-font-semibold dlm-m-0;
    color: #92400e;
    font-size: 0.9rem;
}

.dlm-credentials-keys {
    @apply dlm-p-4;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.dlm-credentials-key-row {
    label {
        @apply dlm-block dlm-text-xs dlm-font-medium dlm-mb-1;
        color: #92400e;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
}

.dlm-credentials-key-value {
    @apply dlm-flex dlm-items-center dlm-gap-2;

    code {
        @apply dlm-flex-1 dlm-p-3 dlm-bg-white dlm-rounded-md dlm-font-mono dlm-text-sm;
        border: 1px solid #e5e7eb;
        word-break: break-all;
        color: #1f2937;
    }
}

.dlm-credentials-copy {
    @apply dlm-flex dlm-items-center dlm-justify-center dlm-p-2 dlm-rounded-md dlm-cursor-pointer;
    background: white;
    border: 1px solid #e5e7eb;
    color: #6b7280;
    transition: all 0.15s ease;

    &:hover {
        background: #f9fafb;
        color: #374151;
        border-color: #d1d5db;
    }

    svg {
        width: 18px;
        height: 18px;
    }
}

.dlm-credentials-box > .dlm-btn {
    margin: 0 16px 16px 16px;
}

// Responsive table wrapper
.dlm-table-responsive {
    @apply dlm-w-full;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;

    .dlm-table {
        min-width: 700px;
    }
}

.dlm-table {
    @apply dlm-w-full dlm-text-sm;

    th {
        @apply dlm-text-left dlm-p-2 dlm-border-b dlm-border-gray-200 dlm-font-medium dlm-text-gray-600;
    }

    td {
        @apply dlm-p-2 dlm-border-b dlm-border-gray-100;
    }
}

.dlm-badge {
    @apply dlm-inline-block dlm-px-2 dlm-py-0.5 dlm-text-xs dlm-font-medium dlm-rounded dlm-bg-gray-100 dlm-text-gray-700;
}

.dlm-badge-success {
    @apply dlm-bg-green-100 dlm-text-green-700;
}

.dlm-badge-gray {
    @apply dlm-bg-gray-100 dlm-text-gray-500;
}

// Payment Gateways table
.dlm-gateways-table {
    @apply dlm-w-full dlm-text-sm;

    th {
        @apply dlm-text-left dlm-p-3 dlm-border-b dlm-border-gray-200 dlm-font-medium dlm-text-gray-600;
    }

    td {
        @apply dlm-p-3 dlm-border-b dlm-border-gray-100;
        vertical-align: middle;
    }

}

.dlm-gateways-description {
    @apply dlm-text-xs dlm-text-gray-500 dlm-mt-0.5;
}

// Notification reminder rows
.dlm-notification-reminder-row {
    @apply dlm-flex dlm-items-center dlm-gap-2 dlm-mb-2;
}

.dlm-font-medium {
    font-weight: 500;
}

// Gateway modal fields
.dlm-gateway-modal-fields {
    .dlm-form-group {
        @apply dlm-mb-0;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;

        &:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        &:first-child {
            padding-top: 0;
        }

        &:has(.dlm-input), &:has(.dlm-switch) {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 0 16px;
            align-items: start;

            > label {
                padding-top: 7px;
            }

            .dlm-form-hint {
                grid-column: 2;
            }
        }

    }
}

// Endpoint groups - 2-column grid layout
.dlm-endpoint-groups-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;

    @media (max-width: 900px) {
        grid-template-columns: 1fr;
    }
}

.dlm-endpoint-group {
    @apply dlm-rounded-lg dlm-overflow-hidden;
    border: 1px solid #e5e7eb;
    border-top: 3px solid;

    // Licenses group - green accent
    &[data-group="licenses"] {
        border-top-color: #10b981;

        .dlm-endpoint-group-header {
            background: rgba(16, 185, 129, 0.06);
        }

        .dlm-endpoint-group-icon {
            color: #10b981;
        }
    }

    // Generators group - purple accent
    &[data-group="generators"] {
        border-top-color: #8b5cf6;

        .dlm-endpoint-group-header {
            background: rgba(139, 92, 246, 0.06);
        }

        .dlm-endpoint-group-icon {
            color: #8b5cf6;
        }
    }
}

.dlm-endpoint-group-header {
    @apply dlm-flex dlm-items-center dlm-justify-between dlm-px-3 dlm-py-2;
    border-bottom: 1px solid #e5e7eb;
}

.dlm-endpoint-group-title {
    @apply dlm-flex dlm-items-center dlm-gap-2 dlm-font-semibold dlm-text-gray-700;
    font-size: 0.8rem;
}

.dlm-endpoint-group-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.dlm-endpoint-group-actions {
    @apply dlm-flex dlm-items-center dlm-gap-1;
}

.dlm-btn-link {
    @apply dlm-bg-transparent dlm-border-0 dlm-p-0 dlm-cursor-pointer;
    color: #6b7280;
    font-size: 0.7rem;

    &:hover {
        color: #374151;
        text-decoration: underline;
    }
}

.dlm-endpoint-group-items {
    padding: 6px;
}

.dlm-endpoint-item {
    @apply dlm-flex dlm-items-center dlm-gap-3 dlm-rounded;
    padding: 4px 6px;
    font-size: 0.75rem;
    transition: background-color 0.15s ease;

    &:hover {
        background: rgba(0, 0, 0, 0.03);
    }

    .dlm-checkbox {
        flex-shrink: 0;
        width: 14px;
        height: 14px;
    }
}

.dlm-endpoint-route {
    @apply dlm-font-mono;
    font-size: 0.7rem;
    color: #374151;
}

.dlm-endpoint-method {
    @apply dlm-inline-block dlm-text-center dlm-font-bold dlm-rounded dlm-text-white;
    width: 38px;
    padding: 1px 4px;
    font-size: 0.6rem;
    flex-shrink: 0;

    &.dlm-method-get {
        @apply dlm-bg-green-500;
    }

    &.dlm-method-post {
        @apply dlm-bg-blue-500;
    }

    &.dlm-method-put {
        background: #f59e0b;
    }

    &.dlm-method-delete {
        @apply dlm-bg-red-500;
    }
}

.dlm-btn-danger {
    @apply dlm-bg-red-600 dlm-text-white;

    &:hover {
        @apply dlm-bg-red-700;
    }
}

// Tool progress bar
.dlm-tool-progress {
    @apply dlm-mt-3 dlm-mb-3;
}

.dlm-tool-progress-bar {
    @apply dlm-w-full dlm-bg-gray-200 dlm-rounded-full dlm-overflow-hidden;
    height: 8px;
}

.dlm-tool-progress-bar-inner {
    @apply dlm-bg-primary-600 dlm-rounded-full;
    height: 100%;
    transition: width 0.3s ease;
}

.dlm-tool-progress-info {
    @apply dlm-text-sm dlm-text-gray-600 dlm-mt-1;
}

.dlm-tool-status {
    @apply dlm-mb-3;

    a {
        @apply dlm-text-primary-600 dlm-cursor-pointer dlm-ml-2;

        &:hover {
            @apply dlm-underline;
        }
    }
}

.dlm-mb-3 {
    margin-bottom: 0.75rem;
}

// Responsive: stack vertically on narrow screens
@media (max-width: 768px) {
    .dlm-settings-layout {
        @apply dlm-flex-col;
    }

    .dlm-settings-nav {
        width: 100%;
    }

    .dlm-settings-nav-list {
        @apply dlm-flex dlm-overflow-x-auto dlm-gap-1 dlm-pb-2;
        position: static;

        li {
            @apply dlm-flex-shrink-0;
        }
    }

    .dlm-settings-section {
        padding: 16px 20px;

        .dlm-form-group {
            &:has(.dlm-input), &:has(.dlm-image-upload-field), &:has(.dlm-switch) {
                grid-template-columns: 1fr;
                gap: 4px 0;
            }
        }
    }

    .dlm-api-key-form-fields {
        grid-template-columns: 1fr;
    }
}

// Loading state
.dlm-loading-container {
    @apply dlm-flex dlm-items-center dlm-justify-center;
    min-height: 300px;
}

.dlm-loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #e5e7eb;
    border-top-color: #6366f1;
    border-radius: 50%;
    animation: dlm-spin 0.8s linear infinite;
}

@keyframes dlm-spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
