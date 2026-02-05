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

namespace IdeoLogix\DigitalLicenseManager\Admin;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractTool;
use IdeoLogix\DigitalLicenseManager\Controllers\Settings as SettingsController;
use IdeoLogix\DigitalLicenseManager\Core\Services\GeneratorsService;
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\ApiKeys as ApiKeysRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseActivations;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Generators;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseSource;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\RestAPI\Setup as RestAPISetup;
use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Tools\Migration\Migration;
use IdeoLogix\DigitalLicenseManager\Traits\Singleton;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\JsonFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\StringHasher;

defined( 'ABSPATH' ) || exit;

/**
 * Class Ajax
 *
 * Handles AJAX requests for the Vue3 admin interface.
 *
 * @package IdeoLogix\DigitalLicenseManager\Admin
 */
class Ajax {

	use Singleton;

	/**
	 * Initialize AJAX handlers.
	 *
	 * @return void
	 */
	protected function init() {
		// Licenses
		add_action( 'wp_ajax_dlm_admin_licenses_query', [ $this, 'licenses_query' ] );
		add_action( 'wp_ajax_dlm_admin_licenses_find', [ $this, 'licenses_find' ] );
		add_action( 'wp_ajax_dlm_admin_licenses_store', [ $this, 'licenses_store' ] );
		add_action( 'wp_ajax_dlm_admin_licenses_delete', [ $this, 'licenses_delete' ] );
		add_action( 'wp_ajax_dlm_admin_licenses_bulk_action', [ $this, 'licenses_bulk_action' ] );
		add_action( 'wp_ajax_dlm_admin_licenses_show_key', [ $this, 'licenses_show_key' ] );
		add_action( 'wp_ajax_dlm_admin_licenses_generate_key', [ $this, 'licenses_generate_key' ] );
		add_action( 'wp_ajax_dlm_admin_licenses_import', [ $this, 'licenses_import' ] );
		add_action( 'wp_ajax_dlm_admin_licenses_export', [ $this, 'licenses_export' ] );

		// Generators
		add_action( 'wp_ajax_dlm_admin_generators_query', [ $this, 'generators_query' ] );
		add_action( 'wp_ajax_dlm_admin_generators_find', [ $this, 'generators_find' ] );
		add_action( 'wp_ajax_dlm_admin_generators_store', [ $this, 'generators_store' ] );
		add_action( 'wp_ajax_dlm_admin_generators_delete', [ $this, 'generators_delete' ] );
		add_action( 'wp_ajax_dlm_admin_generators_generate', [ $this, 'generators_generate' ] );

		// Activations
		add_action( 'wp_ajax_dlm_admin_activations_query', [ $this, 'activations_query' ] );
		add_action( 'wp_ajax_dlm_admin_activations_find', [ $this, 'activations_find' ] );
		add_action( 'wp_ajax_dlm_admin_activations_delete', [ $this, 'activations_delete' ] );
		add_action( 'wp_ajax_dlm_admin_activations_bulk_action', [ $this, 'activations_bulk_action' ] );

		// Settings
		add_action( 'wp_ajax_dlm_admin_settings_get', [ $this, 'settings_get' ] );
		add_action( 'wp_ajax_dlm_admin_settings_save', [ $this, 'settings_save' ] );
		add_action( 'wp_ajax_dlm_admin_settings_export', [ $this, 'settings_export' ] );
		add_action( 'wp_ajax_dlm_admin_settings_rebuild_db', [ $this, 'settings_rebuild_db' ] );

		// API Keys
		add_action( 'wp_ajax_dlm_admin_api_keys_query', [ $this, 'api_keys_query' ] );
		add_action( 'wp_ajax_dlm_admin_api_keys_store', [ $this, 'api_keys_store' ] );
		add_action( 'wp_ajax_dlm_admin_api_keys_delete', [ $this, 'api_keys_delete' ] );
		add_action( 'wp_ajax_dlm_admin_api_keys_endpoints', [ $this, 'api_keys_endpoints' ] );

		// Tools
		add_action( 'wp_ajax_dlm_admin_tools_list', [ $this, 'tools_list' ] );
		add_action( 'wp_ajax_dlm_admin_tool_init', [ $this, 'tool_init' ] );
		add_action( 'wp_ajax_dlm_admin_tool_process', [ $this, 'tool_process' ] );
		add_action( 'wp_ajax_dlm_admin_tool_status', [ $this, 'tool_status' ] );
		add_action( 'wp_ajax_dlm_admin_tool_undo', [ $this, 'tool_undo' ] );

		// Dropdowns
		add_action( 'wp_ajax_dlm_admin_search_products', [ $this, 'search_products' ] );
		add_action( 'wp_ajax_dlm_admin_search_orders', [ $this, 'search_orders' ] );
		add_action( 'wp_ajax_dlm_admin_search_users', [ $this, 'search_users' ] );
		add_action( 'wp_ajax_dlm_admin_search_generators', [ $this, 'search_generators' ] );
	}

	/**
	 * Verify the request nonce and capability.
	 *
	 * @param string $capability The required capability.
	 *
	 * @return void
	 */
	protected function check_access( $capability = 'dlm_read_licenses' ) {
		if ( ! check_ajax_referer( 'dlm_admin', '_wpnonce', false ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid security token.', 'digital-license-manager' ) ] );
		}

		if ( ! current_user_can( $capability ) ) {
			wp_send_json_error( [ 'message' => __( 'You do not have permission to perform this action.', 'digital-license-manager' ) ] );
		}
	}

	/**
	 * Convert a status slug to its integer value.
	 *
	 * @param string $slug The status slug (e.g. 'active', 'inactive').
	 *
	 * @return int|null
	 */
	protected function status_from_slug( $slug ) {
		$slug = strtolower( $slug );

		return isset( LicenseStatus::$values[ $slug ] ) ? LicenseStatus::$values[ $slug ] : null;
	}

	/**
	 * Convert a status integer value to its slug.
	 *
	 * @param int $status The status integer value.
	 *
	 * @return string
	 */
	protected function status_to_slug( $status ) {
		$slug = array_search( (int) $status, LicenseStatus::$values, true );

		return $slug !== false ? $slug : 'unknown';
	}

	/**
	 * Query licenses with pagination and filters.
	 *
	 * @return void
	 */
	public function licenses_query() {
		$this->check_access();

		$page     = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;
		$per_page = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 25;
		$search   = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
		$status   = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$orderby  = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'id';
		$order    = isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'DESC';

		// Build query
		$query = [];

		if ( ! empty( $status ) ) {
			$status_value = $this->status_from_slug( $status );
			if ( $status_value ) {
				$query['status'] = $status_value;
			}
		}

		// Get licenses
		$licenses_repo = Licenses::instance();
		$offset        = ( $page - 1 ) * $per_page;

		// Get total count and counts by status
		$total_count = $licenses_repo->count( $query );
		$counts      = $this->get_license_status_counts();

		// Fetch records: get($where, $sortBy, $sortDir, $offset, $limit)
		$licenses = $licenses_repo->get(
			$query,
			$orderby,
			$order,
			$offset,
			$per_page
		);

		// Format records for response
		$records = [];
		foreach ( $licenses as $license ) {
			$records[] = $this->format_license( $license );
		}

		wp_send_json_success( [
			'records'    => $records,
			'pagination' => [
				'current_page' => $page,
				'total_pages'  => ceil( $total_count / $per_page ),
				'total'        => $total_count,
				'per_page'     => $per_page,
			],
			'counts'     => $counts,
		] );
	}

	/**
	 * Find a single license.
	 *
	 * @return void
	 */
	public function licenses_find() {
		$this->check_access();

		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid license ID.', 'digital-license-manager' ) ] );
		}

		$license = Licenses::instance()->find( $id );

		if ( ! $license ) {
			wp_send_json_error( [ 'message' => __( 'License not found.', 'digital-license-manager' ) ] );
		}

		$record = $this->format_license( $license, true );

		wp_send_json_success( [ 'record' => $record ] );
	}

	/**
	 * Create or update a license.
	 *
	 * @return void
	 */
	public function licenses_store() {
		$this->check_access();

		$id          = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$license_key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

		// Generate if empty
		if ( empty( $license_key ) ) {
			$license_key = $this->generate_random_key();
		}

		$data = [
			'license_key'       => $license_key,
			'product_id'        => isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : null,
			'order_id'          => isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : null,
			'user_id'           => isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : null,
			'status'            => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'inactive',
			'activations_limit' => isset( $_POST['activations_limit'] ) && $_POST['activations_limit'] !== '' ? absint( $_POST['activations_limit'] ) : null,
			'expires_at'        => isset( $_POST['expires_at'] ) && ! empty( $_POST['expires_at'] ) ? sanitize_text_field( wp_unslash( $_POST['expires_at'] ) ) : null,
			'valid_for'         => isset( $_POST['valid_for'] ) && $_POST['valid_for'] !== '' ? absint( $_POST['valid_for'] ) : null,
			'source'            => LicenseSource::API,
		];

		// Convert status slug to value
		$status_value = $this->status_from_slug( $data['status'] );
		if ( $status_value ) {
			$data['status'] = $status_value;
		}

		$licenses_service = new LicensesService();

		try {
			if ( $id ) {
				// Update
				$result = $licenses_service->update( $id, $data );
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'message' => $result->get_error_message() ] );
				}
				$message = __( 'License updated successfully.', 'digital-license-manager' );
			} else {
				// Create
				$result = $licenses_service->create( $data );
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'message' => $result->get_error_message() ] );
				}
				$message = __( 'License created successfully.', 'digital-license-manager' );
			}

			wp_send_json_success( [
				'message' => $message,
				'record'  => $this->format_license( $result ),
			] );
		} catch ( \Exception $e ) {
			wp_send_json_error( [ 'message' => $e->getMessage() ] );
		}
	}

	/**
	 * Delete a license.
	 *
	 * @return void
	 */
	public function licenses_delete() {
		$this->check_access();

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid license ID.', 'digital-license-manager' ) ] );
		}

		$result = Licenses::instance()->delete( $id );

		if ( $result ) {
			wp_send_json_success( [ 'message' => __( 'License deleted successfully.', 'digital-license-manager' ) ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to delete license.', 'digital-license-manager' ) ] );
		}
	}

	/**
	 * Perform bulk action on licenses.
	 *
	 * @return void
	 */
	public function licenses_bulk_action() {
		$this->check_access();

		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		$ids    = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : [];

		if ( empty( $action ) || empty( $ids ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid request.', 'digital-license-manager' ) ] );
		}

		$count = 0;

		switch ( $action ) {
			case 'activate':
				foreach ( $ids as $id ) {
					Licenses::instance()->update( $id, [ 'status' => LicenseStatus::ACTIVE ] );
					$count++;
				}
				$message = sprintf( __( '%d license(s) activated.', 'digital-license-manager' ), $count );
				break;

			case 'deactivate':
				foreach ( $ids as $id ) {
					Licenses::instance()->update( $id, [ 'status' => LicenseStatus::INACTIVE ] );
					$count++;
				}
				$message = sprintf( __( '%d license(s) deactivated.', 'digital-license-manager' ), $count );
				break;

			case 'delete':
				foreach ( $ids as $id ) {
					if ( Licenses::instance()->delete( $id ) ) {
						$count++;
					}
				}
				$message = sprintf( __( '%d license(s) deleted.', 'digital-license-manager' ), $count );
				break;

			case 'export':
				$rows = [];
				foreach ( $ids as $id ) {
					$license = Licenses::instance()->find( $id );
					if ( $license ) {
						$rows[] = $this->format_license( $license, true );
						$count++;
					}
				}
				wp_send_json_success( [
					'message' => sprintf( __( '%d license(s) exported.', 'digital-license-manager' ), $count ),
					'records' => $rows,
				] );

			default:
				wp_send_json_error( [ 'message' => __( 'Unknown action.', 'digital-license-manager' ) ] );
		}

		wp_send_json_success( [ 'message' => $message ] );
	}

	/**
	 * Show (decrypt) a license key.
	 *
	 * @return void
	 */
	public function licenses_show_key() {
		$this->check_access();

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid license ID.', 'digital-license-manager' ) ] );
		}

		$license = Licenses::instance()->find( $id );

		if ( ! $license ) {
			wp_send_json_error( [ 'message' => __( 'License not found.', 'digital-license-manager' ) ] );
		}

		$decrypted_key = CryptoHelper::decrypt( $license->getLicenseKey() );

		wp_send_json_success( [ 'license_key' => $decrypted_key ] );
	}

	/**
	 * Generate a new license key.
	 *
	 * @return void
	 */
	public function licenses_generate_key() {
		$this->check_access();

		$license_key = $this->generate_random_key();

		wp_send_json_success( [ 'license_key' => $license_key ] );
	}

	/**
	 * Import licenses.
	 *
	 * @return void
	 */
	public function licenses_import() {
		$this->check_access();

		$license_keys = isset( $_POST['license_keys'] ) ? sanitize_textarea_field( wp_unslash( $_POST['license_keys'] ) ) : '';
		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : null;
		$status       = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'inactive';

		if ( empty( $license_keys ) ) {
			wp_send_json_error( [ 'message' => __( 'No license keys provided.', 'digital-license-manager' ) ] );
		}

		$keys     = array_filter( array_map( 'trim', explode( "\n", $license_keys ) ) );
		$imported = 0;
		$skipped  = 0;
		$failed   = 0;
		$errors   = [];

		$status_value     = $this->status_from_slug( $status );
		$licenses_service = new LicensesService();

		foreach ( $keys as $key ) {
			try {
				$result = $licenses_service->create( [
					'license_key' => $key,
					'product_id'  => $product_id,
					'status'      => $status_value ?: LicenseStatus::INACTIVE,
					'source'      => LicenseSource::IMPORT,
				] );

				if ( is_wp_error( $result ) ) {
					$errors[] = sprintf( '%s: %s', $key, $result->get_error_message() );
					$failed++;
				} else {
					$imported++;
				}
			} catch ( \Exception $e ) {
				$errors[] = sprintf( '%s: %s', $key, $e->getMessage() );
				$failed++;
			}
		}

		wp_send_json_success( [
			'message'  => sprintf( __( 'Import completed. %d imported, %d skipped, %d failed.', 'digital-license-manager' ), $imported, $skipped, $failed ),
			'imported' => $imported,
			'skipped'  => $skipped,
			'failed'   => $failed,
			'errors'   => $errors,
		] );
	}

	/**
	 * Query generators.
	 *
	 * @return void
	 */
	public function generators_query() {
		$this->check_access();

		$page     = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;
		$per_page = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 25;
		$orderby  = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'id';
		$order    = isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'DESC';

		$generators_repo = Generators::instance();
		$offset          = ( $page - 1 ) * $per_page;
		$total_count     = $generators_repo->count();

		// get($where, $sortBy, $sortDir, $offset, $limit)
		$generators = $generators_repo->get( [], $orderby, $order, $offset, $per_page );

		$records = [];
		foreach ( $generators as $generator ) {
			$records[] = $this->format_generator( $generator );
		}

		wp_send_json_success( [
			'records'    => $records,
			'pagination' => [
				'current_page' => $page,
				'total_pages'  => ceil( $total_count / $per_page ),
				'total'        => $total_count,
				'per_page'     => $per_page,
			],
		] );
	}

	/**
	 * Find a single generator.
	 *
	 * @return void
	 */
	public function generators_find() {
		$this->check_access();

		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid generator ID.', 'digital-license-manager' ) ] );
		}

		$generator = Generators::instance()->find( $id );

		if ( ! $generator ) {
			wp_send_json_error( [ 'message' => __( 'Generator not found.', 'digital-license-manager' ) ] );
		}

		$record = $this->format_generator( $generator, true );

		wp_send_json_success( [ 'record' => $record ] );
	}

	/**
	 * Create or update a generator.
	 *
	 * @return void
	 */
	public function generators_store() {
		$this->check_access();

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		$data = [
			'name'                => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
			'charset'             => isset( $_POST['charset'] ) ? sanitize_text_field( wp_unslash( $_POST['charset'] ) ) : '',
			'chunks'              => isset( $_POST['chunks'] ) ? absint( $_POST['chunks'] ) : 4,
			'chunk_length'        => isset( $_POST['chunk_length'] ) ? absint( $_POST['chunk_length'] ) : 4,
			'separator'           => isset( $_POST['separator'] ) ? sanitize_text_field( wp_unslash( $_POST['separator'] ) ) : '-',
			'prefix'              => isset( $_POST['prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['prefix'] ) ) : '',
			'suffix'              => isset( $_POST['suffix'] ) ? sanitize_text_field( wp_unslash( $_POST['suffix'] ) ) : '',
			'times_activated_max' => isset( $_POST['times_activated_max'] ) && $_POST['times_activated_max'] !== '' ? absint( $_POST['times_activated_max'] ) : null,
			'expires_in'          => isset( $_POST['expires_in'] ) && $_POST['expires_in'] !== '' ? absint( $_POST['expires_in'] ) : null,
		];

		if ( empty( $data['name'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Generator name is required.', 'digital-license-manager' ) ] );
		}

		if ( empty( $data['charset'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Character set is required.', 'digital-license-manager' ) ] );
		}

		try {
			$generators_repo = Generators::instance();

			if ( $id ) {
				$result  = $generators_repo->update( $id, $data );
				$message = __( 'Generator updated successfully.', 'digital-license-manager' );
			} else {
				$result  = $generators_repo->insert( $data );
				$message = __( 'Generator created successfully.', 'digital-license-manager' );
			}

			if ( ! $result ) {
				wp_send_json_error( [ 'message' => __( 'Failed to save generator.', 'digital-license-manager' ) ] );
			}

			$generator = $generators_repo->find( $id ?: $result );

			wp_send_json_success( [
				'message' => $message,
				'record'  => $this->format_generator( $generator ),
			] );
		} catch ( \Exception $e ) {
			wp_send_json_error( [ 'message' => $e->getMessage() ] );
		}
	}

	/**
	 * Delete a generator.
	 *
	 * @return void
	 */
	public function generators_delete() {
		$this->check_access();

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid generator ID.', 'digital-license-manager' ) ] );
		}

		$result = Generators::instance()->delete( $id );

		if ( $result ) {
			wp_send_json_success( [ 'message' => __( 'Generator deleted successfully.', 'digital-license-manager' ) ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to delete generator.', 'digital-license-manager' ) ] );
		}
	}

	/**
	 * Generate licenses using a generator.
	 *
	 * @return void
	 */
	public function generators_generate() {
		$this->check_access();

		$generator_id = isset( $_POST['generator_id'] ) ? absint( $_POST['generator_id'] ) : 0;
		$quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 10;
		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : null;
		$order_id     = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : null;
		$status       = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'inactive';
		$valid_for    = isset( $_POST['valid_for'] ) && $_POST['valid_for'] !== '' ? absint( $_POST['valid_for'] ) : null;
		$save         = isset( $_POST['save'] ) && ( $_POST['save'] === 'true' || $_POST['save'] === '1' );

		if ( ! $generator_id ) {
			wp_send_json_error( [ 'message' => __( 'Please select a generator.', 'digital-license-manager' ) ] );
		}

		if ( $quantity < 1 || $quantity > 1000 ) {
			wp_send_json_error( [ 'message' => __( 'Quantity must be between 1 and 1000.', 'digital-license-manager' ) ] );
		}

		$generator = Generators::instance()->find( $generator_id );

		if ( ! $generator ) {
			wp_send_json_error( [ 'message' => __( 'Generator not found.', 'digital-license-manager' ) ] );
		}

		try {
			$generators_service = new GeneratorsService();

			// generateLicenses($amount, $generator, $licenses, $order, $product)
			$licenses = $generators_service->generateLicenses( $quantity, $generator );

			if ( is_wp_error( $licenses ) ) {
				wp_send_json_error( [ 'message' => $licenses->get_error_message() ] );
			}

			$status_value = $this->status_from_slug( $status ) ?: LicenseStatus::INACTIVE;
			$license_keys = [];

			if ( $save ) {
				$licenses_service = new LicensesService();
				$result           = $licenses_service->createMultiple( $licenses, [
					'order_id'          => $order_id,
					'product_id'        => $product_id,
					'status'            => $status_value,
					'source'            => LicenseSource::GENERATOR,
					'valid_for'         => $valid_for ?: $generator->getExpiresIn(),
					'activations_limit' => $generator->getActivationsLimit(),
					'complete'          => true,
				] );

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( [ 'message' => $result->get_error_message() ] );
				}

				$license_keys = $licenses;
			} else {
				$license_keys = $licenses;
			}

			wp_send_json_success( [
				'message'  => sprintf( __( 'Successfully generated %d license(s).', 'digital-license-manager' ), count( $license_keys ) ),
				'licenses' => $license_keys,
			] );
		} catch ( \Exception $e ) {
			wp_send_json_error( [ 'message' => $e->getMessage() ] );
		}
	}

	/**
	 * Query activations.
	 *
	 * @return void
	 */
	public function activations_query() {
		$this->check_access();

		$page     = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;
		$per_page = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 25;
		$orderby  = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'id';
		$order    = isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'DESC';

		$activations_repo = LicenseActivations::instance();
		$offset           = ( $page - 1 ) * $per_page;
		$total_count      = $activations_repo->count();

		// get($where, $sortBy, $sortDir, $offset, $limit)
		$activations = $activations_repo->get( [], $orderby, $order, $offset, $per_page );

		$records = [];
		foreach ( $activations as $activation ) {
			$records[] = $this->format_activation( $activation );
		}

		wp_send_json_success( [
			'records'    => $records,
			'pagination' => [
				'current_page' => $page,
				'total_pages'  => ceil( $total_count / $per_page ),
				'total'        => $total_count,
				'per_page'     => $per_page,
			],
		] );
	}

	/**
	 * Find a single activation.
	 *
	 * @return void
	 */
	public function activations_find() {
		$this->check_access();

		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid activation ID.', 'digital-license-manager' ) ] );
		}

		$activation = LicenseActivations::instance()->find( $id );

		if ( ! $activation ) {
			wp_send_json_error( [ 'message' => __( 'Activation not found.', 'digital-license-manager' ) ] );
		}

		wp_send_json_success( [ 'record' => $this->format_activation( $activation ) ] );
	}

	/**
	 * Delete an activation.
	 *
	 * @return void
	 */
	public function activations_delete() {
		$this->check_access();

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid activation ID.', 'digital-license-manager' ) ] );
		}

		$result = LicenseActivations::instance()->delete( $id );

		if ( $result ) {
			wp_send_json_success( [ 'message' => __( 'Activation deleted successfully.', 'digital-license-manager' ) ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to delete activation.', 'digital-license-manager' ) ] );
		}
	}

	/**
	 * Perform bulk action on activations.
	 *
	 * @return void
	 */
	public function activations_bulk_action() {
		$this->check_access();

		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		$ids    = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : [];

		if ( empty( $action ) || empty( $ids ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid request.', 'digital-license-manager' ) ] );
		}

		$count = 0;

		switch ( $action ) {
			case 'delete':
				foreach ( $ids as $id ) {
					if ( LicenseActivations::instance()->delete( $id ) ) {
						$count++;
					}
				}
				$message = sprintf( __( '%d activation(s) deleted.', 'digital-license-manager' ), $count );
				break;

			default:
				wp_send_json_error( [ 'message' => __( 'Unknown action.', 'digital-license-manager' ) ] );
		}

		wp_send_json_success( [ 'message' => $message ] );
	}

	/**
	 * Get settings.
	 *
	 * Reads the tab/section/field structure from the Settings controller
	 * (which applies the `dlm_settings_fields` filter for extensibility),
	 * then reads current values from the corresponding `dlm_settings_{slug}` option per tab.
	 *
	 * @return void
	 */
	public function settings_get() {
		$this->check_access( 'dlm_manage_settings' );

		$settings_controller = SettingsController::instance();
		$tabs                = $settings_controller->all();

		$result = [];
		foreach ( $tabs as $tab_key => $tab ) {
			$slug = isset( $tab['slug'] ) ? $tab['slug'] : $tab_key;

			// Load stored values for this specific tab.
			$stored = get_option( 'dlm_settings_' . $slug, [] );
			if ( ! is_array( $stored ) ) {
				$stored = [];
			}

			$tab_data = [
				'name'     => isset( $tab['name'] ) ? $tab['name'] : '',
				'slug'     => $slug,
				'priority' => isset( $tab['priority'] ) ? $tab['priority'] : 10,
				'sections' => [],
			];

			if ( isset( $tab['sections'] ) && is_array( $tab['sections'] ) ) {
				foreach ( $tab['sections'] as $section_key => $section ) {
					$section_data = [
						'name'   => isset( $section['name'] ) ? $section['name'] : '',
						'fields' => [],
					];

					if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
						foreach ( $section['fields'] as $field ) {
							$field_id = isset( $field['id'] ) ? $field['id'] : '';
							if ( empty( $field_id ) ) {
								continue;
							}

							$field_type = 'checkbox';
							if ( isset( $field['callback'] ) && is_array( $field['callback'] ) && isset( $field['callback'][1] ) ) {
								$method = $field['callback'][1];
								if ( $method === 'fieldText' ) {
									$field_type = 'text';
								} elseif ( $method === 'fieldImageUpload' ) {
									$field_type = 'image';
								} elseif ( $method === 'fieldSelect' ) {
									$field_type = 'select';
								} elseif ( $method === 'fieldLicenseKeyDeliveryOptions' ) {
									$field_type = 'order_statuses';
								} elseif ( $method === 'fieldManageStock' ) {
									$field_type = 'checkbox';
								}
							}

							$default = isset( $field['default'] ) ? $field['default'] : null;
							$value   = array_key_exists( $field_id, $stored ) ? $stored[ $field_id ] : $default;

							$field_data = [
								'id'       => $field_id,
								'title'    => isset( $field['title'] ) ? $field['title'] : '',
								'type'     => $field_type,
								'value'    => $value,
								'priority' => isset( $field['priority'] ) ? $field['priority'] : 10,
							];

							$args = isset( $field['args'] ) ? $field['args'] : [];
							if ( ! empty( $args['label'] ) ) {
								$field_data['label'] = $args['label'];
							}
							if ( ! empty( $args['explain'] ) ) {
								$field_data['explain'] = $args['explain'];
							}
							if ( ! empty( $args['options'] ) ) {
								$field_data['options'] = $args['options'];
							}
							if ( ! empty( $args['size'] ) ) {
								$field_data['size'] = $args['size'];
							}

							// For order_statuses, supply WC order statuses as options and hardcoded label/explain from the callback.
							if ( $field_type === 'order_statuses' && function_exists( 'wc_get_order_statuses' ) ) {
								$field_data['options'] = wc_get_order_statuses();
							}

							// For fieldManageStock, supply label/explain from the callback's hardcoded strings.
							if ( isset( $field['callback'][1] ) && $field['callback'][1] === 'fieldManageStock' ) {
								$field_data['label']   = __( 'Enable automatic stock management for WooCommerce products.', 'digital-license-manager' );
								$field_data['explain'] = sprintf(
									'%s<br/>1. %s<br/>2. %s<br/>3. %s',
									__( 'To use this feature, you also need to enable the following settings at a product level:', 'digital-license-manager' ),
									__( 'Inventory &rarr; Manage stock?', 'digital-license-manager' ),
									__( 'License Manager &rarr; Sell Licenses', 'digital-license-manager' ),
									__( 'License Manager &rarr; Licenses source &rarr; Provide licenses from stock', 'digital-license-manager' )
								);
							}

							// Resolve attachment URL for image fields so the preview is available on initial load.
							if ( $field_type === 'image' && ! empty( $value ) && is_numeric( $value ) ) {
								$src = wp_get_attachment_image_src( (int) $value, 'medium' );
								if ( $src ) {
									$field_data['image_url'] = $src[0];
								}
							}

							$section_data['fields'][] = $field_data;
						}
					}

					$tab_data['sections'][ $section_key ] = $section_data;
				}
			}

			$result[ $tab_key ] = $tab_data;
		}

		wp_send_json_success( [ 'tabs' => $result ] );
	}

	/**
	 * Save settings.
	 *
	 * Writes back to the `dlm_settings_{section}` option and
	 * fires the `dlm_settings_sanitized` action for compatibility
	 * with Pro and third-party extensions.
	 *
	 * @return void
	 */
	public function settings_save() {
		$this->check_access( 'dlm_manage_settings' );

		$incoming = isset( $_POST['settings'] ) ? (array) $_POST['settings'] : [];
		$section  = isset( $_POST['section'] ) ? sanitize_text_field( $_POST['section'] ) : 'general';

		if ( empty( $incoming ) ) {
			wp_send_json_error( [ 'message' => __( 'No settings provided.', 'digital-license-manager' ) ] );
		}

		$option_name = 'dlm_settings_' . $section;

		// Read the current stored settings for this section.
		$stored = get_option( $option_name, [] );
		if ( ! is_array( $stored ) ) {
			$stored = [];
		}

		// Build a set of valid field IDs from the requested tab only.
		$settings_controller = SettingsController::instance();
		$tabs                = $settings_controller->all();
		$valid_fields        = [];
		$array_fields        = [];

		// Find the matching tab by slug.
		foreach ( $tabs as $tab_key => $tab ) {
			$tab_slug = isset( $tab['slug'] ) ? $tab['slug'] : $tab_key;
			if ( $tab_slug !== $section ) {
				continue;
			}
			if ( ! isset( $tab['sections'] ) || ! is_array( $tab['sections'] ) ) {
				break;
			}
			foreach ( $tab['sections'] as $sec ) {
				if ( ! isset( $sec['fields'] ) || ! is_array( $sec['fields'] ) ) {
					continue;
				}
				foreach ( $sec['fields'] as $field ) {
					if ( ! empty( $field['id'] ) ) {
						$valid_fields[] = $field['id'];

						// Detect fields that store array values (e.g. order_delivery_statuses).
						if ( isset( $field['callback'][1] ) && $field['callback'][1] === 'fieldLicenseKeyDeliveryOptions' ) {
							$array_fields[] = $field['id'];
						}
					}
				}
			}
			break;
		}

		// Merge incoming values into stored settings.
		// Only accept known field IDs.
		foreach ( $incoming as $key => $value ) {
			$key = sanitize_text_field( $key );
			if ( ! in_array( $key, $valid_fields, true ) ) {
				continue;
			}
			if ( in_array( $key, $array_fields, true ) ) {
				// Array field: sanitize each nested value.
				$stored[ $key ] = is_array( $value ) ? array_map( function( $item ) {
					if ( is_array( $item ) ) {
						return array_map( 'sanitize_text_field', $item );
					}
					return sanitize_text_field( $item );
				}, $value ) : [];
			} else {
				$stored[ $key ] = sanitize_text_field( $value );
			}
		}

		update_option( $option_name, $stored );

		// Fire the same action the old settings system fires.
		do_action( 'dlm_settings_sanitized', $stored );

		wp_send_json_success( [ 'message' => __( 'Settings saved successfully.', 'digital-license-manager' ) ] );
	}

	/**
	 * Export data.
	 *
	 * @return void
	 */
	public function settings_export() {
		$this->check_access( 'dlm_manage_settings' );

		$licenses   = Licenses::instance()->findAll();
		$generators = Generators::instance()->findAll();

		$export = [
			'version'    => defined( 'DLM_PLUGIN_VERSION' ) ? DLM_PLUGIN_VERSION : '1.0.0',
			'exported_at' => current_time( 'mysql' ),
			'licenses'   => [],
			'generators' => [],
		];

		foreach ( $licenses as $license ) {
			$export['licenses'][] = $this->format_license( $license, true );
		}

		foreach ( $generators as $generator ) {
			$export['generators'][] = $this->format_generator( $generator, true );
		}

		wp_send_json_success( $export );
	}

	/**
	 * Rebuild database tables.
	 *
	 * @return void
	 */
	public function settings_rebuild_db() {
		$this->check_access( 'dlm_manage_settings' );

		try {
			\IdeoLogix\DigitalLicenseManager\Setup::install();
			wp_send_json_success( [ 'message' => __( 'Database tables rebuilt successfully.', 'digital-license-manager' ) ] );
		} catch ( \Exception $e ) {
			wp_send_json_error( [ 'message' => $e->getMessage() ] );
		}
	}

	/**
	 * Search products.
	 *
	 * @return void
	 */
	public function search_products() {
		$this->check_access();

		$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

		$results = [];

		if ( function_exists( 'wc_get_products' ) ) {
			$products = wc_get_products( [
				'limit'  => 20,
				's'      => $search,
				'status' => 'publish',
			] );

			foreach ( $products as $product ) {
				$results[] = [
					'value' => $product->get_id(),
					'label' => sprintf( '#%d - %s', $product->get_id(), $product->get_name() ),
				];
			}
		}

		wp_send_json_success( [ 'results' => $results ] );
	}

	/**
	 * Search orders.
	 *
	 * @return void
	 */
	public function search_orders() {
		$this->check_access();

		$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

		$results = [];

		if ( function_exists( 'wc_get_orders' ) ) {
			$orders = wc_get_orders( [
				'limit'  => 20,
				's'      => $search,
			] );

			foreach ( $orders as $order ) {
				$results[] = [
					'value' => $order->get_id(),
					'label' => sprintf( '#%d - %s', $order->get_id(), $order->get_billing_email() ),
				];
			}
		}

		wp_send_json_success( [ 'results' => $results ] );
	}

	/**
	 * Search users.
	 *
	 * @return void
	 */
	public function search_users() {
		$this->check_access();

		$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

		$users = get_users( [
			'number'         => 20,
			'search'         => '*' . $search . '*',
			'search_columns' => [ 'user_login', 'user_email', 'display_name' ],
		] );

		$results = [];
		foreach ( $users as $user ) {
			$results[] = [
				'value' => $user->ID,
				'label' => sprintf( '%s (%s)', $user->display_name, $user->user_email ),
			];
		}

		wp_send_json_success( [ 'results' => $results ] );
	}

	/**
	 * Search generators.
	 *
	 * @return void
	 */
	public function search_generators() {
		$this->check_access();

		$generators = Generators::instance()->findAll();

		$results = [];
		foreach ( $generators as $generator ) {
			$results[] = [
				'value' => $generator->getId(),
				'label' => $generator->getName(),
			];
		}

		wp_send_json_success( [ 'results' => $results ] );
	}

	/**
	 * Get registered tools.
	 *
	 * @return array
	 */
	protected function get_tools() {
		$tools = [ 'migration' => Migration::class ];

		return apply_filters( 'dlm_tools', $tools );
	}

	/**
	 * List all available tools.
	 *
	 * @return void
	 */
	public function tools_list() {
		$this->check_access( 'dlm_manage_settings' );

		$registered = $this->get_tools();
		$tools      = [];

		foreach ( $registered as $slug => $class ) {
			/** @var AbstractTool $tool */
			$tool = new $class( time() );

			// Skip one-time tools that are already complete.
			if ( $tool->isOneTime() && $tool->isComplete() ) {
				continue;
			}

			$item = [
				'slug'        => $tool->getSlug(),
				'description' => $tool->getDescription(),
				'is_one_time' => (bool) $tool->isOneTime(),
				'is_complete' => $tool->isComplete(),
			];

			if ( $tool instanceof Migration ) {
				$item['type']    = 'migration';
				$item['plugins'] = [];
				foreach ( $tool->getPlugins() as $plugin ) {
					$item['plugins'][] = [
						'id'   => $plugin->getId(),
						'name' => $plugin->getName(),
					];
				}
			} else {
				$item['type']        = 'standard';
				$item['form_fields'] = $tool->getFormFields();
			}

			$tools[] = $item;
		}

		wp_send_json_success( [ 'tools' => $tools ] );
	}

	/**
	 * Initialize a tool process.
	 *
	 * @return void
	 */
	public function tool_init() {
		$this->check_access( 'dlm_manage_settings' );

		$tool_slug = isset( $_POST['tool'] ) ? sanitize_text_field( wp_unslash( $_POST['tool'] ) ) : '';
		$tool_id   = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';

		$registered = $this->get_tools();
		if ( empty( $tool_slug ) || ! isset( $registered[ $tool_slug ] ) ) {
			wp_send_json_error( [ 'message' => __( 'Unknown tool selected.', 'digital-license-manager' ) ] );
		}

		/** @var AbstractTool $tool */
		$tool    = new $registered[ $tool_slug ]( $tool_id );
		$process = $tool->initProcess();

		if ( ! is_wp_error( $process ) ) {
			wp_send_json_success();
		} elseif ( 'data_warn' === $process->get_error_code() ) {
			wp_send_json_success( [ 'warning' => $process->get_error_message() ] );
		} else {
			wp_send_json_error( [ 'message' => $process->get_error_message() ] );
		}
	}

	/**
	 * Process a tool step.
	 *
	 * @return void
	 */
	public function tool_process() {
		$this->check_access( 'dlm_manage_settings' );

		$tool_slug = isset( $_POST['tool'] ) ? sanitize_text_field( wp_unslash( $_POST['tool'] ) ) : '';
		$tool_id   = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		$step      = isset( $_POST['step'] ) ? intval( $_POST['step'] ) : null;
		$page      = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : null;

		$registered = $this->get_tools();
		if ( empty( $tool_slug ) || ! isset( $registered[ $tool_slug ] ) ) {
			wp_send_json_error( [ 'message' => __( 'Unknown tool selected.', 'digital-license-manager' ) ] );
		}

		/** @var AbstractTool $tool */
		$tool = new $registered[ $tool_slug ]( $tool_id );
		$next = $tool->getNextStep( $step, $page );

		if ( is_wp_error( $next ) ) {
			wp_send_json_error( [ 'message' => $next->get_error_message() ] );
		}

		if ( $next['next_step'] !== -1 ) {
			$result          = $tool->doStep( $step, $page );
			$next['message'] = is_wp_error( $result ) ? $result->get_error_message() : $next['message'];
		} else {
			$tool->markAsComplete();
			if ( $tool_slug === 'migration' ) {
				update_option( 'nc_info_dlm_lmfwc', 'yes' );
			}
		}

		wp_send_json_success( $next );
	}

	/**
	 * Get tool migration status.
	 *
	 * @return void
	 */
	public function tool_status() {
		$this->check_access( 'dlm_manage_settings' );

		$identifier = isset( $_GET['identifier'] ) ? sanitize_text_field( wp_unslash( $_GET['identifier'] ) ) : '';

		$tool  = new Migration( time() );
		$value = $tool->getStatus();

		$status = '';
		if ( $value && ! empty( $value['completed_at'] ) ) {
			$status = sprintf(
				__( 'Migration completed on: %s.', 'digital-license-manager' ),
				DateFormatter::convert( $value['completed_at'], 'Y-m-d H:i:s' )
			);
		}

		wp_send_json_success( [ 'status' => $status ] );
	}

	/**
	 * Undo a tool migration.
	 *
	 * @return void
	 */
	public function tool_undo() {
		$this->check_access( 'dlm_manage_settings' );

		$tool = new Migration( time() );

		set_time_limit( 0 );
		wp_raise_memory_limit( 'image' );

		$plugin = $tool->getPlugin();
		if ( is_wp_error( $plugin ) ) {
			wp_send_json_error( [ 'message' => $plugin->get_error_message() ] );
		}

		if ( $plugin->undo() ) {
			$tool->markAsNotComplete();
			$tool->resetStatus();
			delete_option( 'nc_info_dlm_lmfwc' );
			wp_send_json_success();
		} else {
			wp_send_json_error( [ 'message' => __( 'Operation Error.', 'digital-license-manager' ) ] );
		}
	}

	/**
	 * Format a license for API response.
	 *
	 * @param object $license The license object.
	 * @param bool   $include_key Whether to include the decrypted key.
	 *
	 * @return array
	 */
	protected function format_license( $license, $include_key = false ) {
		$decrypted_key = CryptoHelper::decrypt( $license->getLicenseKey() );
		$partial_key   = substr( $decrypted_key, 0, 4 ) . '****' . substr( $decrypted_key, -4 );

		$source      = $license->getSource();
		$source_label = $this->source_to_label( $source );

		$data = [
			'id'                 => $license->getId(),
			'license_key_partial' => $partial_key,
			'product_id'         => $license->getProductId(),
			'product_name'       => $this->get_product_name( $license->getProductId() ),
			'order_id'           => $license->getOrderId(),
			'user_id'            => $license->getUserId(),
			'user_email'         => $this->get_user_email( $license->getUserId() ),
			'status'             => $this->status_to_slug( $license->getStatus() ),
			'activations_limit'  => $license->getActivationsLimit(),
			'activations_count'  => LicenseActivations::instance()->countBy( [ 'license_id' => $license->getId() ] ),
			'valid_for'          => $license->getValidFor(),
			'expires_at'         => $license->getExpiresAt(),
			'created_at'         => $license->getCreatedAt(),
			'updated_at'         => $license->getUpdatedAt(),
			'source'             => $source,
			'source_label'       => $source_label,
		];

		if ( $include_key ) {
			$data['decrypted_license_key'] = $decrypted_key;
		}

		return $data;
	}

	/**
	 * Format a generator for API response.
	 *
	 * @param object $generator The generator object.
	 * @param bool   $include_full Whether to include full details.
	 *
	 * @return array
	 */
	protected function format_generator( $generator, $include_full = false ) {
		return [
			'id'                  => $generator->getId(),
			'name'                => $generator->getName(),
			'charset'             => $generator->getCharset(),
			'chunks'              => $generator->getChunks(),
			'chunk_length'        => $generator->getChunkLength(),
			'separator'           => $generator->getSeparator(),
			'prefix'              => $generator->getPrefix(),
			'suffix'              => $generator->getSuffix(),
			'times_activated_max' => $generator->getActivationsLimit(),
			'expires_in'          => $generator->getExpiresIn(),
			'created_at'          => $generator->getCreatedAt(),
			'updated_at'          => $generator->getUpdatedAt(),
		];
	}

	/**
	 * Format an activation for API response.
	 *
	 * @param object $activation The activation object.
	 *
	 * @return array
	 */
	protected function format_activation( $activation ) {
		$license     = Licenses::instance()->find( $activation->getLicenseId() );
		$partial_key = '';

		if ( $license ) {
			$decrypted_key = CryptoHelper::decrypt( $license->getLicenseKey() );
			$partial_key   = substr( $decrypted_key, 0, 4 ) . '****' . substr( $decrypted_key, -4 );
		}

		return [
			'id'               => $activation->getId(),
			'license_id'       => $activation->getLicenseId(),
			'license_key_partial' => $partial_key,
			'label'            => $activation->getLabel(),
			'source'           => $activation->getSource(),
			'ip_address'       => $activation->getIpAddress(),
			'user_agent'       => $activation->getUserAgent(),
			'created_at'       => $activation->getCreatedAt(),
			'updated_at'       => $activation->getUpdatedAt(),
		];
	}

	/**
	 * Get product name by ID.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return string|null
	 */
	protected function get_product_name( $product_id ) {
		if ( ! $product_id || ! function_exists( 'wc_get_product' ) ) {
			return null;
		}

		$product = wc_get_product( $product_id );
		return $product ? $product->get_name() : null;
	}

	/**
	 * Get user email by ID.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return string|null
	 */
	protected function get_user_email( $user_id ) {
		if ( ! $user_id ) {
			return null;
		}

		$user = get_user_by( 'ID', $user_id );
		return $user ? $user->user_email : null;
	}

	/**
	 * Get license counts by status.
	 *
	 * @return array
	 */
	protected function get_license_status_counts() {
		$licenses_repo = Licenses::instance();

		return [
			'all'       => $licenses_repo->count(),
			'active'    => $licenses_repo->count( [ 'status' => LicenseStatus::ACTIVE ] ),
			'inactive'  => $licenses_repo->count( [ 'status' => LicenseStatus::INACTIVE ] ),
			'sold'      => $licenses_repo->count( [ 'status' => LicenseStatus::SOLD ] ),
			'delivered' => $licenses_repo->count( [ 'status' => LicenseStatus::DELIVERED ] ),
			'disabled'  => $licenses_repo->count( [ 'status' => LicenseStatus::DISABLED ] ),
		];
	}

	/**
	 * Export licenses as a downloadable CSV.
	 *
	 * @return void
	 */
	public function licenses_export() {
		$this->check_access();

		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : [];

		if ( empty( $ids ) ) {
			wp_send_json_error( [ 'message' => __( 'No licenses selected.', 'digital-license-manager' ) ] );
		}

		$rows = [];
		foreach ( $ids as $id ) {
			$license = Licenses::instance()->find( $id );
			if ( $license ) {
				$rows[] = $this->format_license( $license, true );
			}
		}

		wp_send_json_success( [
			'message' => sprintf( __( '%d license(s) exported.', 'digital-license-manager' ), count( $rows ) ),
			'records' => $rows,
		] );
	}

	/**
	 * Query API keys with pagination.
	 *
	 * @return void
	 */
	public function api_keys_query() {
		$this->check_access( 'dlm_read_api_keys' );

		$page     = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;
		$per_page = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 25;

		$repo        = ApiKeysRepository::instance();
		$offset      = ( $page - 1 ) * $per_page;
		$total_count = $repo->count();
		$keys        = $repo->get( [], 'id', 'DESC', $offset, $per_page );

		$records = [];
		foreach ( $keys as $key ) {
			$records[] = $this->format_api_key( $key );
		}

		wp_send_json_success( [
			'records'    => $records,
			'pagination' => [
				'current_page' => $page,
				'total_pages'  => ceil( $total_count / $per_page ),
				'total'        => $total_count,
				'per_page'     => $per_page,
			],
		] );
	}

	/**
	 * Create or update an API key.
	 *
	 * @return void
	 */
	public function api_keys_store() {
		$this->check_access( 'dlm_create_api_keys' );

		$id          = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$user_id     = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
		$permissions = isset( $_POST['permissions'] ) ? sanitize_text_field( wp_unslash( $_POST['permissions'] ) ) : 'read';
		$endpoints   = isset( $_POST['endpoints'] ) ? array_map( 'sanitize_text_field', (array) $_POST['endpoints'] ) : [];

		if ( empty( $description ) ) {
			wp_send_json_error( [ 'message' => __( 'Description is required.', 'digital-license-manager' ) ] );
		}

		if ( empty( $user_id ) ) {
			wp_send_json_error( [ 'message' => __( 'User is required.', 'digital-license-manager' ) ] );
		}

		if ( ! in_array( $permissions, [ 'read', 'write', 'read_write' ], true ) ) {
			$permissions = 'read';
		}

		if ( empty( $endpoints ) ) {
			wp_send_json_error( [ 'message' => __( 'At least one endpoint must be selected.', 'digital-license-manager' ) ] );
		}

		$repo = ApiKeysRepository::instance();

		if ( $id ) {
			// Update existing key.
			$result = $repo->update( $id, [
				'user_id'     => $user_id,
				'description' => $description,
				'permissions' => $permissions,
				'endpoints'   => JsonFormatter::encode( $endpoints ),
			] );

			if ( $result ) {
				$api_key = $repo->find( $id );
				wp_send_json_success( [
					'message' => __( 'API key updated successfully.', 'digital-license-manager' ),
					'record'  => $this->format_api_key( $api_key ),
				] );
			} else {
				wp_send_json_error( [ 'message' => __( 'Failed to update API key.', 'digital-license-manager' ) ] );
			}
		} else {
			// Create new key.
			$consumer_key    = 'ck_' . StringHasher::random();
			$consumer_secret = 'cs_' . StringHasher::random();

			$result = $repo->insert( [
				'user_id'         => $user_id,
				'description'     => $description,
				'permissions'     => $permissions,
				'endpoints'       => JsonFormatter::encode( $endpoints ),
				'consumer_key'    => StringHasher::make( $consumer_key ),
				'consumer_secret' => $consumer_secret,
				'truncated_key'   => substr( $consumer_key, -7 ),
			] );

			if ( $result ) {
				$api_key = $repo->find( $result );
				wp_send_json_success( [
					'message'         => __( 'API key created successfully. Copy your keys now — the secret will not be shown again.', 'digital-license-manager' ),
					'record'          => $this->format_api_key( $api_key ),
					'consumer_key'    => $consumer_key,
					'consumer_secret' => $consumer_secret,
				] );
			} else {
				wp_send_json_error( [ 'message' => __( 'Failed to create API key.', 'digital-license-manager' ) ] );
			}
		}
	}

	/**
	 * Delete an API key.
	 *
	 * @return void
	 */
	public function api_keys_delete() {
		$this->check_access( 'dlm_delete_api_keys' );

		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid API key ID.', 'digital-license-manager' ) ] );
		}

		$result = ApiKeysRepository::instance()->delete( $id );

		if ( $result ) {
			wp_send_json_success( [ 'message' => __( 'API key deleted successfully.', 'digital-license-manager' ) ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to delete API key.', 'digital-license-manager' ) ] );
		}
	}

	/**
	 * Get available REST API endpoints.
	 *
	 * @return void
	 */
	public function api_keys_endpoints() {
		$this->check_access( 'dlm_read_api_keys' );

		$endpoints = RestAPISetup::getEndpoints();

		wp_send_json_success( [ 'endpoints' => $endpoints ] );
	}

	/**
	 * Format an API key for response.
	 *
	 * @param object $key The API key model.
	 *
	 * @return array
	 */
	protected function format_api_key( $key ) {
		$user       = get_user_by( 'ID', $key->getUserId() );
		$user_label = $user ? sprintf( '%s (%s)', $user->display_name, $user->user_email ) : '';

		return [
			'id'            => $key->getId(),
			'user_id'       => $key->getUserId(),
			'user_label'    => $user_label,
			'description'   => $key->getDescription(),
			'permissions'   => $key->getPermissions(),
			'endpoints'     => $key->getEndpoints(),
			'truncated_key' => $key->getTruncatedKey(),
			'last_access'   => $key->getLastAccess(),
			'created_at'    => $key->getCreatedAt(),
			'updated_at'    => $key->getUpdatedAt(),
		];
	}

	/**
	 * Convert a license source integer to a readable label.
	 *
	 * @param int|string $source The source value.
	 *
	 * @return string
	 */
	protected function source_to_label( $source ) {
		$labels = [
			LicenseSource::GENERATOR => __( 'Generator', 'digital-license-manager' ),
			LicenseSource::IMPORT    => __( 'Import', 'digital-license-manager' ),
			LicenseSource::API       => __( 'API', 'digital-license-manager' ),
			LicenseSource::MIGRATION => __( 'Migration', 'digital-license-manager' ),
		];

		$source = (int) $source;

		return isset( $labels[ $source ] ) ? $labels[ $source ] : __( 'Unknown', 'digital-license-manager' );
	}

	/**
	 * Generate a random license key in XXXX-XXXX-XXXX-XXXX format.
	 *
	 * @return string
	 */
	protected function generate_random_key() {
		$charset = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$chunks  = [];

		for ( $i = 0; $i < 4; $i++ ) {
			$chunk = '';
			for ( $j = 0; $j < 4; $j++ ) {
				$chunk .= $charset[ wp_rand( 0, strlen( $charset ) - 1 ) ];
			}
			$chunks[] = $chunk;
		}

		return implode( '-', $chunks );
	}
}
