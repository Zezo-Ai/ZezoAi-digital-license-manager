<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-present  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-present  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

defined( 'ABSPATH' ) || exit;

/**
 * Translation strings for the Vue3 admin interface.
 * Accessed via trans() helper in JavaScript.
 */
return [
	'global' => [
		'nav' => [
			'documentation' => __( 'Documentation', 'digital-license-manager' ),
			'support'       => __( 'Support', 'digital-license-manager' ),
		],
		'buttons' => [
			'add_new'      => __( 'Add New', 'digital-license-manager' ),
			'save'         => __( 'Save', 'digital-license-manager' ),
			'save_changes' => __( 'Save Changes', 'digital-license-manager' ),
			'saving'       => __( 'Saving...', 'digital-license-manager' ),
			'create'       => __( 'Create', 'digital-license-manager' ),
			'update'       => __( 'Update', 'digital-license-manager' ),
			'delete'       => __( 'Delete', 'digital-license-manager' ),
			'cancel'       => __( 'Cancel', 'digital-license-manager' ),
			'apply'        => __( 'Apply', 'digital-license-manager' ),
			'search'       => __( 'Search', 'digital-license-manager' ),
			'filter'       => __( 'Filter', 'digital-license-manager' ),
			'reset'        => __( 'Reset', 'digital-license-manager' ),
			'close'        => __( 'Close', 'digital-license-manager' ),
			'select_all'   => __( 'Select All', 'digital-license-manager' ),
			'deselect_all' => __( 'Deselect All', 'digital-license-manager' ),
		],
		'actions' => [
			'edit'   => __( 'Edit', 'digital-license-manager' ),
			'delete' => __( 'Delete', 'digital-license-manager' ),
			'view'   => __( 'View', 'digital-license-manager' ),
			'remove' => __( 'Remove', 'digital-license-manager' ),
			'change' => __( 'Change', 'digital-license-manager' ),
		],
		'labels' => [
			'bulk_actions' => __( 'Bulk Actions', 'digital-license-manager' ),
			'id'           => __( 'ID', 'digital-license-manager' ),
			'actions'      => __( 'Actions', 'digital-license-manager' ),
			'status'       => __( 'Status', 'digital-license-manager' ),
			'send'         => __( 'Send', 'digital-license-manager' ),
		],
		'placeholders' => [
			'search' => __( 'Search...', 'digital-license-manager' ),
			'select' => __( 'Select...', 'digital-license-manager' ),
		],
		'messages' => [
			'no_records'    => __( 'No records found.', 'digital-license-manager' ),
			'loading'       => __( 'Loading...', 'digital-license-manager' ),
			'confirm'       => __( 'Are you sure?', 'digital-license-manager' ),
			'success'       => __( 'Success!', 'digital-license-manager' ),
			'error'         => __( 'An error occurred.', 'digital-license-manager' ),
			'saved'         => __( 'Changes saved successfully.', 'digital-license-manager' ),
			'deleted'       => __( 'Item deleted successfully.', 'digital-license-manager' ),
			'copied'        => __( 'Copied to clipboard!', 'digital-license-manager' ),
		],
		'errors' => [
			'network'    => __( 'Network error. Please try again.', 'digital-license-manager' ),
			'permission' => __( 'You do not have permission to perform this action.', 'digital-license-manager' ),
			'invalid'    => __( 'Invalid request.', 'digital-license-manager' ),
		],
		'pagination' => [
			'showing'  => __( 'Showing :from to :to of :total results', 'digital-license-manager' ),
			'first'    => __( 'First', 'digital-license-manager' ),
			'previous' => __( 'Previous', 'digital-license-manager' ),
			'next'     => __( 'Next', 'digital-license-manager' ),
			'last'     => __( 'Last', 'digital-license-manager' ),
		],
	],
	'licenses' => [
		'title'  => __( 'Licenses', 'digital-license-manager' ),
		'titles' => [
			'add'    => __( 'Add License', 'digital-license-manager' ),
			'edit'   => __( 'Edit License', 'digital-license-manager' ),
			'import' => __( 'Import Licenses', 'digital-license-manager' ),
		],
		'columns' => [
			'id'           => __( 'ID', 'digital-license-manager' ),
			'license_key'  => __( 'License Key', 'digital-license-manager' ),
			'product'      => __( 'Product', 'digital-license-manager' ),
			'user'         => __( 'User', 'digital-license-manager' ),
			'order'        => __( 'Order', 'digital-license-manager' ),
			'status'       => __( 'Status', 'digital-license-manager' ),
			'activations'  => __( 'Activations', 'digital-license-manager' ),
			'expires_at'   => __( 'Expires', 'digital-license-manager' ),
			'created_at'   => __( 'Created', 'digital-license-manager' ),
		],
		'fields' => [
			'license_key'       => __( 'License Key', 'digital-license-manager' ),
			'product'           => __( 'Product', 'digital-license-manager' ),
			'order'             => __( 'Order', 'digital-license-manager' ),
			'user'              => __( 'User', 'digital-license-manager' ),
			'status'            => __( 'Status', 'digital-license-manager' ),
			'valid_for'         => __( 'Valid For', 'digital-license-manager' ),
			'expires_at'        => __( 'Expires At', 'digital-license-manager' ),
			'activations_limit' => __( 'Activations Limit', 'digital-license-manager' ),
			'source'            => __( 'Source', 'digital-license-manager' ),
		],
		'placeholders' => [
			'license_key'       => __( 'Enter or generate a license key', 'digital-license-manager' ),
			'product'           => __( 'Search for a product...', 'digital-license-manager' ),
			'order'             => __( 'Search for an order...', 'digital-license-manager' ),
			'user'              => __( 'Search for a user...', 'digital-license-manager' ),
			'expires_at'        => __( 'Select expiration date', 'digital-license-manager' ),
			'activations_limit' => __( 'Leave empty for unlimited', 'digital-license-manager' ),
			'valid_for_days'    => __( 'Number of days', 'digital-license-manager' ),
		],
		'hints' => [
			'license_key'       => __( 'Leave empty to auto-generate, or enter your own key.', 'digital-license-manager' ),
			'status'            => __( 'Define the initial license status. Set "Active" to make this license available for stock purchases.', 'digital-license-manager' ),
			'product'           => __( 'Optional. The product to which the license will be assigned.', 'digital-license-manager' ),
			'order'             => __( 'Optional. The order to which the license will be assigned.', 'digital-license-manager' ),
			'user'              => __( 'Optional. The user to which the license will be assigned.', 'digital-license-manager' ),
			'expires_at'        => __( 'Optional. Leave blank if the license does not expire.', 'digital-license-manager' ),
			'valid_for'         => __( 'Optional. Expiration period added after the license is purchased from stock.', 'digital-license-manager' ),
			'activations_limit' => __( 'Optional. Leave empty for unlimited activations.', 'digital-license-manager' ),
		],
		'statuses' => [
			'all'       => __( 'All', 'digital-license-manager' ),
			'active'    => __( 'Active', 'digital-license-manager' ),
			'inactive'  => __( 'Inactive', 'digital-license-manager' ),
			'sold'      => __( 'Sold', 'digital-license-manager' ),
			'delivered' => __( 'Delivered', 'digital-license-manager' ),
			'disabled'  => __( 'Disabled', 'digital-license-manager' ),
		],
		'units' => [
			'days'   => __( 'Days', 'digital-license-manager' ),
			'weeks'  => __( 'Weeks', 'digital-license-manager' ),
			'months' => __( 'Months', 'digital-license-manager' ),
			'years'  => __( 'Years', 'digital-license-manager' ),
		],
		'sources' => [
			'import'    => __( 'Import', 'digital-license-manager' ),
			'generator' => __( 'Generator', 'digital-license-manager' ),
			'migration' => __( 'Migration', 'digital-license-manager' ),
		],
		'labels' => [
			'never' => __( 'Never', 'digital-license-manager' ),
		],
		'actions' => [
			'show_key'   => __( 'Show License Key', 'digital-license-manager' ),
			'hide_key'   => __( 'Hide License Key', 'digital-license-manager' ),
			'copy_key'   => __( 'Copy to Clipboard', 'digital-license-manager' ),
			'activate'   => __( 'Activate', 'digital-license-manager' ),
			'deactivate' => __( 'Deactivate', 'digital-license-manager' ),
			'delete'     => __( 'Delete', 'digital-license-manager' ),
			'export'     => __( 'Export', 'digital-license-manager' ),
		],
		'buttons' => [
			'generate' => __( 'Generate', 'digital-license-manager' ),
			'import'   => __( 'Import', 'digital-license-manager' ),
			'export'   => __( 'Export', 'digital-license-manager' ),
		],
		'modals' => [
			'delete' => [
				'title'   => __( 'Delete License', 'digital-license-manager' ),
				'message' => __( 'Are you sure you want to delete this license? This action cannot be undone.', 'digital-license-manager' ),
			],
			'export' => [
				'title'                => __( 'Export licenses', 'digital-license-manager' ),
				'scope'                => __( 'Export scope', 'digital-license-manager' ),
				'selected'             => __( 'Selected licenses (%d)', 'digital-license-manager' ),
				'selected_description' => __( 'Only the licenses currently selected in the table.', 'digital-license-manager' ),
				'filtered'             => __( 'All matching licenses (%d)', 'digital-license-manager' ),
				'filtered_description' => __( 'Every license matching the applied status and search filters across all pages.', 'digital-license-manager' ),
				'columns'              => __( 'Columns', 'digital-license-manager' ),
				'select_all'           => __( 'Select all', 'digital-license-manager' ),
				'clear_all'            => __( 'Clear all', 'digital-license-manager' ),
				'no_columns'           => __( 'Select at least one column to export.', 'digital-license-manager' ),
				'sensitive_notice'     => __( 'This CSV contains full license keys. Store and share it securely.', 'digital-license-manager' ),
				'download'             => __( 'Export CSV', 'digital-license-manager' ),
				'preparing'            => __( 'Preparing export...', 'digital-license-manager' ),
			],
		],
		'import' => [
			'fields' => [
				'license_keys' => __( 'License Keys', 'digital-license-manager' ),
			],
			'placeholders' => [
				'license_keys' => __( 'Enter license keys, one per line...', 'digital-license-manager' ),
			],
			'hints' => [
				'license_keys' => __( 'Enter one license key per line. Duplicates will be skipped.', 'digital-license-manager' ),
			],
			'buttons' => [
				'import'    => __( 'Import Licenses', 'digital-license-manager' ),
				'importing' => __( 'Importing...', 'digital-license-manager' ),
			],
			'errors' => [
				'empty' => __( 'Please enter at least one license key.', 'digital-license-manager' ),
			],
			'results' => [
				'title'    => __( 'Import Results', 'digital-license-manager' ),
				'imported' => __( 'Imported', 'digital-license-manager' ),
				'skipped'  => __( 'Skipped', 'digital-license-manager' ),
				'failed'   => __( 'Failed', 'digital-license-manager' ),
				'errors'   => __( 'Errors', 'digital-license-manager' ),
			],
		],
	],
	'generators' => [
		'title'  => __( 'Generators', 'digital-license-manager' ),
		'titles' => [
			'add'  => __( 'Add Generator', 'digital-license-manager' ),
			'edit' => __( 'Edit Generator', 'digital-license-manager' ),
		],
		'columns' => [
			'id'              => __( 'ID', 'digital-license-manager' ),
			'name'            => __( 'Name', 'digital-license-manager' ),
			'products'        => __( 'Products', 'digital-license-manager' ),
			'charset'         => __( 'Charset', 'digital-license-manager' ),
			'chunks'          => __( 'Chunks', 'digital-license-manager' ),
			'chunk_length'    => __( 'Chunk Length', 'digital-license-manager' ),
			'max_activations' => __( 'Max Activations', 'digital-license-manager' ),
		],
		'fields' => [
			'name'            => __( 'Name', 'digital-license-manager' ),
			'products'        => __( 'Products', 'digital-license-manager' ),
			'charset'         => __( 'Character Set', 'digital-license-manager' ),
			'chunks'          => __( 'Number of Chunks', 'digital-license-manager' ),
			'chunk_length'    => __( 'Chunk Length', 'digital-license-manager' ),
			'separator'       => __( 'Separator', 'digital-license-manager' ),
			'prefix'          => __( 'Prefix', 'digital-license-manager' ),
			'suffix'          => __( 'Suffix', 'digital-license-manager' ),
			'max_activations' => __( 'Max Activations', 'digital-license-manager' ),
			'expires_in'      => __( 'Expires In (days)', 'digital-license-manager' ),
			'preview'         => __( 'Preview', 'digital-license-manager' ),
		],
		'placeholders' => [
			'name'            => __( 'Enter generator name', 'digital-license-manager' ),
			'products'        => __( 'Select products...', 'digital-license-manager' ),
			'charset'         => __( 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 'digital-license-manager' ),
			'max_activations' => __( 'Leave empty for unlimited', 'digital-license-manager' ),
			'expires_in'      => __( 'Leave empty for no expiration', 'digital-license-manager' ),
		],
		'hints' => [
			'products'        => __( 'Generated licenses will be assigned to these products.', 'digital-license-manager' ),
			'charset'         => __( 'Characters used for key generation. Avoid confusing characters like 0/O, 1/I/l.', 'digital-license-manager' ),
			'chunks'          => __( 'Number of character groups in the key (e.g., 4 chunks = XXXX-XXXX-XXXX-XXXX).', 'digital-license-manager' ),
			'chunk_length'    => __( 'Characters per chunk.', 'digital-license-manager' ),
			'separator'       => __( 'Character between chunks. Usually a dash (-).', 'digital-license-manager' ),
			'prefix'          => __( 'Text prepended to every key.', 'digital-license-manager' ),
			'suffix'          => __( 'Text appended to every key.', 'digital-license-manager' ),
			'max_activations' => __( 'Maximum activations for generated licenses.', 'digital-license-manager' ),
			'expires_in'      => __( 'Days until generated licenses expire.', 'digital-license-manager' ),
		],
		'buttons' => [
			'generate' => __( 'Generate Licenses', 'digital-license-manager' ),
		],
		'modals' => [
			'delete' => [
				'title'   => __( 'Delete Generator', 'digital-license-manager' ),
				'message' => __( 'Are you sure you want to delete this generator? This will not affect already generated licenses.', 'digital-license-manager' ),
			],
		],
		'generate' => [
			'title'  => __( 'Generate Licenses', 'digital-license-manager' ),
			'fields' => [
				'generator' => __( 'Generator', 'digital-license-manager' ),
				'quantity'  => __( 'Quantity', 'digital-license-manager' ),
				'product'   => __( 'Product', 'digital-license-manager' ),
				'order'     => __( 'Order', 'digital-license-manager' ),
				'status'    => __( 'Status', 'digital-license-manager' ),
				'valid_for' => __( 'Valid For (days)', 'digital-license-manager' ),
				'save'      => __( 'Save to Database', 'digital-license-manager' ),
			],
			'placeholders' => [
				'generator' => __( 'Select a generator...', 'digital-license-manager' ),
				'product'   => __( 'Optional: Assign to product', 'digital-license-manager' ),
				'order'     => __( 'Optional: Assign to order', 'digital-license-manager' ),
				'valid_for' => __( 'Leave empty to use generator default', 'digital-license-manager' ),
			],
			'hints' => [
				'quantity'  => __( 'Maximum 1000 licenses per batch.', 'digital-license-manager' ),
				'product'   => __( 'Override the generator\'s default product assignment.', 'digital-license-manager' ),
				'valid_for' => __( 'Override the generator\'s default expiration period (in days).', 'digital-license-manager' ),
			],
			'buttons' => [
				'generate'   => __( 'Generate', 'digital-license-manager' ),
				'generating' => __( 'Generating...', 'digital-license-manager' ),
				'copy'       => __( 'Copy All', 'digital-license-manager' ),
				'download'   => __( 'Download CSV', 'digital-license-manager' ),
			],
			'errors' => [
				'no_generator' => __( 'Please select a generator.', 'digital-license-manager' ),
			],
			'results' => [
				'title' => __( 'Generated Licenses', 'digital-license-manager' ),
			],
			'messages' => [
				'copied' => __( 'Licenses copied to clipboard!', 'digital-license-manager' ),
			],
		],
	],
	'activations' => [
		'title' => __( 'Activations', 'digital-license-manager' ),
		'columns' => [
			'id'          => __( 'ID', 'digital-license-manager' ),
			'license_key' => __( 'License Key', 'digital-license-manager' ),
			'label'       => __( 'Label', 'digital-license-manager' ),
			'source'      => __( 'Source', 'digital-license-manager' ),
			'ip_address'  => __( 'IP Address', 'digital-license-manager' ),
			'user_agent'  => __( 'User Agent', 'digital-license-manager' ),
			'status'      => __( 'Status', 'digital-license-manager' ),
			'created_at'  => __( 'Created At', 'digital-license-manager' ),
		],
		'actions' => [
			'enable'  => __( 'Enable', 'digital-license-manager' ),
			'disable' => __( 'Disable', 'digital-license-manager' ),
			'delete'  => __( 'Delete', 'digital-license-manager' ),
		],
		'labels' => [
			'enabled'  => __( 'Enabled', 'digital-license-manager' ),
			'disabled' => __( 'Disabled', 'digital-license-manager' ),
		],
		'filters' => [
			'license_key' => __( 'License Key', 'digital-license-manager' ),
			'all_sources' => __( 'All Sources', 'digital-license-manager' ),
			'filter'      => __( 'Filter', 'digital-license-manager' ),
		],
		'modals' => [
			'delete' => [
				'title'   => __( 'Delete Activation', 'digital-license-manager' ),
				'message' => __( 'Are you sure you want to delete this activation? The license will have one more available activation slot.', 'digital-license-manager' ),
			],
		],
	],
	'settings' => [
		'title'       => __( 'Settings', 'digital-license-manager' ),
		'select_page' => __( '— Select a page —', 'digital-license-manager' ),
		'none'        => __( '— None —', 'digital-license-manager' ),
		'items_table' => [
			'enabled'   => __( 'Enabled', 'digital-license-manager' ),
			'disabled'  => __( 'Disabled', 'digital-license-manager' ),
			'configure' => __( 'Configure', 'digital-license-manager' ),
			'done'      => __( 'Done', 'digital-license-manager' ),
		],
		'appearance_preset' => [
			'eyebrow'       => __( 'DLM 2.0', 'digital-license-manager' ),
			'title'         => __( 'A calmer, more focused storefront', 'digital-license-manager' ),
			'description'   => __( 'Preview the new teal palette here. Your storefront changes only after you save.', 'digital-license-manager' ),
			'apply'         => __( 'Preview DLM 2.0 palette', 'digital-license-manager' ),
			'restore'       => __( 'Restore previous colors', 'digital-license-manager' ),
			'selected'      => __( 'DLM 2.0 palette selected', 'digital-license-manager' ),
			'preview_label' => __( 'Checkout preview', 'digital-license-manager' ),
			'preview_title' => __( 'Complete your purchase', 'digital-license-manager' ),
			'preview_body'  => __( 'Secure checkout with immediate license delivery.', 'digital-license-manager' ),
			'preview_cta'   => __( 'Continue', 'digital-license-manager' ),
		],
		'tabs'  => [
			'general'  => __( 'General', 'digital-license-manager' ),
			'rest_api' => __( 'REST API', 'digital-license-manager' ),
			'tools'    => __( 'Tools', 'digital-license-manager' ),
			'help'     => __( 'Help', 'digital-license-manager' ),
		],
		'general' => [
			'hide_license_keys'      => __( 'Hide License Keys', 'digital-license-manager' ),
			'hide_license_keys_hint' => __( 'Mask license keys in the admin list table until clicked.', 'digital-license-manager' ),
			'allow_duplicates'       => __( 'Allow Duplicate License Keys', 'digital-license-manager' ),
			'allow_duplicates_hint'  => __( 'Allow the same license key to exist multiple times in the database.', 'digital-license-manager' ),
			'stock_management'       => __( 'Stock Management', 'digital-license-manager' ),
			'stock_management_hint'  => __( 'Automatically manage product stock based on available licenses.', 'digital-license-manager' ),
			'stock_options'          => [
				'none' => __( 'Disabled', 'digital-license-manager' ),
				'auto' => __( 'Automatic', 'digital-license-manager' ),
			],
			'upload'                 => __( 'Upload', 'digital-license-manager' ),
			'image_placeholder'      => __( 'Media ID', 'digital-license-manager' ),
		],
		'rest_api' => [
			'api_keys_title'       => __( 'API Keys', 'digital-license-manager' ),
			'add_key'              => __( 'Add API Key', 'digital-license-manager' ),
			'edit_key'             => __( 'Edit API Key', 'digital-license-manager' ),
			'credentials_notice'   => __( 'Copy your new keys now. The secret key will not be shown again.', 'digital-license-manager' ),
			'consumer_key'         => __( 'Consumer Key', 'digital-license-manager' ),
			'consumer_secret'      => __( 'Consumer Secret', 'digital-license-manager' ),
			'dismiss_credentials'  => __( 'Done', 'digital-license-manager' ),
			'never'                => __( 'Never', 'digital-license-manager' ),
			'columns'              => [
				'description'   => __( 'Description', 'digital-license-manager' ),
				'user'          => __( 'User', 'digital-license-manager' ),
				'permissions'   => __( 'Permissions', 'digital-license-manager' ),
				'truncated_key' => __( 'Key', 'digital-license-manager' ),
				'last_access'   => __( 'Last Access', 'digital-license-manager' ),
			],
			'fields'               => [
				'description'      => __( 'Description', 'digital-license-manager' ),
				'user'             => __( 'User', 'digital-license-manager' ),
				'user_placeholder' => __( 'Search for a user...', 'digital-license-manager' ),
				'permissions'      => __( 'Permissions', 'digital-license-manager' ),
				'endpoints'        => __( 'Endpoints', 'digital-license-manager' ),
			],
			'permissions'          => [
				'read'       => __( 'Read', 'digital-license-manager' ),
				'write'      => __( 'Write', 'digital-license-manager' ),
				'read_write' => __( 'Read/Write', 'digital-license-manager' ),
			],
			'groups' => [
				'licenses'   => __( 'Licenses', 'digital-license-manager' ),
				'generators' => __( 'Generators', 'digital-license-manager' ),
			],
		],
		'tools' => [
			'database' => [
				'title'       => __( 'Rebuild Database Tables', 'digital-license-manager' ),
				'description' => __( 'Recreate database tables. Use this if you experience database-related issues.', 'digital-license-manager' ),
				'button'      => __( 'Rebuild', 'digital-license-manager' ),
				'confirm'     => __( 'Are you sure? This will recreate all database tables. Your data will be preserved.', 'digital-license-manager' ),
			],
			'dynamic' => [
				'migration_title'      => __( 'Database Migration', 'digital-license-manager' ),
				'select_plugin_label'  => __( 'Select plugin', 'digital-license-manager' ),
				'select_plugin'        => __( 'Please select a plugin to migrate from.', 'digital-license-manager' ),
				'preserve_ids_warning' => __( 'Preserve old IDs. If checked, your existing Digital License Manager database will be wiped to remove/free used IDs. Use this ONLY if you are absolutely sure what you are doing and if your app depends on the existing license/generator IDs.', 'digital-license-manager' ),
				'confirm_warning'      => __( 'WARNING - Please take a database backup before proceeding. Are you sure you want to continue?', 'digital-license-manager' ),
				'migrate_button'       => __( 'Migrate', 'digital-license-manager' ),
				'run_button'           => __( 'Run', 'digital-license-manager' ),
				'finished'             => __( 'Process finished.', 'digital-license-manager' ),
				'undo'                 => __( 'Undo', 'digital-license-manager' ),
				'undo_confirm'         => __( 'Are you sure you want to undo this migration? This will remove all migrated data.', 'digital-license-manager' ),
				'undo_success'         => __( 'Migration has been undone successfully.', 'digital-license-manager' ),
			],
		],
		'help' => [
			'documentation' => [
				'title'       => __( 'Documentation', 'digital-license-manager' ),
				'description' => __( 'Read the documentation to learn how to use Digital License Manager.', 'digital-license-manager' ),
				'button'      => __( 'View Documentation', 'digital-license-manager' ),
			],
			'support' => [
				'title'       => __( 'Support', 'digital-license-manager' ),
				'description' => __( 'Need help? Contact our support team.', 'digital-license-manager' ),
				'button'      => __( 'Get Support', 'digital-license-manager' ),
			],
		],
		'abandoned_checkout' => [
			'empty'                 => __( 'No recovery emails configured. Click "Add reminder" to create the first step.', 'digital-license-manager' ),
			'add_reminder'          => __( 'Add reminder', 'digital-license-manager' ),
			'move_up'               => __( 'Move up', 'digital-license-manager' ),
			'move_down'             => __( 'Move down', 'digital-license-manager' ),
			'enabled'               => __( 'Enabled', 'digital-license-manager' ),
			'delay_value'           => __( 'Delay value', 'digital-license-manager' ),
			'delay_unit'            => __( 'Delay unit', 'digital-license-manager' ),
			'unit_minutes'          => __( 'minutes', 'digital-license-manager' ),
			'unit_hours'            => __( 'hours', 'digital-license-manager' ),
			'unit_days'             => __( 'days', 'digital-license-manager' ),
			'subject_placeholder'   => __( 'Email subject — supports merge tags', 'digital-license-manager' ),
			'edit_body'             => __( 'Edit body', 'digital-license-manager' ),
			'close_body'            => __( 'Close body', 'digital-license-manager' ),
			'body_placeholder'      => __( 'Email body (HTML) — supports merge tags', 'digital-license-manager' ),
			'body_hint'             => __( 'Write HTML. Common tags like <p>, <strong>, <em>, <a>, <br>, <img>, <ul>, <ol>, <li>, <h1>–<h6> are supported.', 'digital-license-manager' ),
			'insert_tag'            => __( 'Insert tag', 'digital-license-manager' ),
			'remove'                => __( 'Remove reminder', 'digital-license-manager' ),
			'merge_tags_help_title' => __( 'Available merge tags', 'digital-license-manager' ),
			'step_label'            => __( 'Step %d', 'digital-license-manager' ),
		],
	],
];
