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

use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
use IdeoLogix\DigitalLicenseManager\Enums\LicensePrivateStatus;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Streams license records to a CSV file.
 */
class LicenseCsvExporter {

	/**
	 * Number of filtered records fetched per query.
	 *
	 * @var int
	 */
	const BATCH_SIZE = 500;

	/**
	 * License repository.
	 *
	 * @var Licenses
	 */
	protected $licenses;

	/**
	 * Constructor.
	 *
	 * @param Licenses|null $licenses License repository override.
	 */
	public function __construct( $licenses = null ) {
		$this->licenses = $licenses ?: Licenses::instance();
	}

	/**
	 * Return the columns available to license exports.
	 *
	 * @return array
	 */
	public static function get_columns() {
		$columns = [
			'id'                => [ 'label' => __( 'ID', 'digital-license-manager' ) ],
			'order_id'          => [ 'label' => __( 'Order ID', 'digital-license-manager' ) ],
			'product_id'        => [ 'label' => __( 'Product ID', 'digital-license-manager' ) ],
			'user_id'           => [ 'label' => __( 'User ID', 'digital-license-manager' ) ],
			'license_key'       => [
				'label'     => __( 'License key', 'digital-license-manager' ),
				'sensitive' => true,
			],
			'expires_at'        => [ 'label' => __( 'Expires at', 'digital-license-manager' ) ],
			'valid_for'         => [ 'label' => __( 'Valid for', 'digital-license-manager' ) ],
			'status'            => [ 'label' => __( 'Status', 'digital-license-manager' ) ],
			'activations_limit' => [ 'label' => __( 'Activation limit', 'digital-license-manager' ) ],
			'created_at'        => [ 'label' => __( 'Created at', 'digital-license-manager' ) ],
			'created_by'        => [ 'label' => __( 'Created by', 'digital-license-manager' ) ],
			'updated_at'        => [ 'label' => __( 'Updated at', 'digital-license-manager' ) ],
			'updated_by'        => [ 'label' => __( 'Updated by', 'digital-license-manager' ) ],
		];

		/**
		 * Filter the columns available to license CSV exports.
		 *
		 * Custom columns can supply their values through the
		 * `dlm_license_export_row` filter.
		 *
		 * @param array $columns Export column definitions keyed by column slug.
		 */
		return apply_filters( 'dlm_license_export_columns', $columns );
	}

	/**
	 * Return serializable column choices for the admin UI.
	 *
	 * @return array
	 */
	public static function get_column_choices() {
		$choices = [];

		foreach ( self::get_columns() as $key => $column ) {
			$key = sanitize_key( $key );
			if ( '' === $key || empty( $column['label'] ) ) {
				continue;
			}

			$choices[] = [
				'key'       => $key,
				'label'     => (string) $column['label'],
				'sensitive' => ! empty( $column['sensitive'] ),
			];
		}

		return $choices;
	}

	/**
	 * Validate and normalize export arguments.
	 *
	 * @param array $args Raw export arguments.
	 *
	 * @return array|WP_Error
	 */
	public function prepare_args( $args ) {
		$args = wp_parse_args(
			$args,
			[
				'scope'   => 'filtered',
				'ids'     => [],
				'columns' => array_keys( self::get_columns() ),
				'search'  => '',
				'status'  => '',
				'orderby' => 'id',
				'order'   => 'DESC',
			]
		);

		$scope = sanitize_key( $args['scope'] );
		if ( ! in_array( $scope, [ 'selected', 'filtered' ], true ) ) {
			return new WP_Error( 'invalid_scope', __( 'Invalid export scope.', 'digital-license-manager' ) );
		}

		$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $args['ids'] ) ) ) );
		if ( 'selected' === $scope && empty( $ids ) ) {
			return new WP_Error( 'empty_selection', __( 'No licenses were selected.', 'digital-license-manager' ) );
		}

		$allowed_columns = array_keys( self::get_columns() );
		$columns         = array_values(
			array_unique(
				array_intersect(
					array_map( 'sanitize_key', (array) $args['columns'] ),
					$allowed_columns
				)
			)
		);

		if ( empty( $columns ) ) {
			return new WP_Error( 'empty_columns', __( 'Select at least one column to export.', 'digital-license-manager' ) );
		}

		$status = sanitize_key( $args['status'] );
		if ( '' !== $status && ! isset( LicensePrivateStatus::$values[ $status ] ) ) {
			return new WP_Error( 'invalid_status', __( 'Invalid license status.', 'digital-license-manager' ) );
		}

		$allowed_orderby = [ 'id', 'order_id', 'product_id', 'user_id', 'status', 'expires_at', 'created_at', 'updated_at' ];
		$orderby         = sanitize_key( $args['orderby'] );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'id';
		}

		$order = strtoupper( sanitize_text_field( $args['order'] ) );
		if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
			$order = 'DESC';
		}

		return [
			'scope'   => $scope,
			'ids'     => $ids,
			'columns' => $columns,
			'search'  => sanitize_text_field( $args['search'] ),
			'status'  => $status,
			'orderby' => $orderby,
			'order'   => $order,
		];
	}

	/**
	 * Count the records represented by prepared export arguments.
	 *
	 * @param array $args Prepared export arguments.
	 *
	 * @return int
	 */
	public function count_records( $args ) {
		if ( 'selected' === $args['scope'] ) {
			return (int) $this->licenses->count( $args['ids'] );
		}

		return (int) $this->licenses->count( $this->build_query( $args ) );
	}

	/**
	 * Write a prepared export to an open stream.
	 *
	 * @param resource $stream Open writable stream.
	 * @param array    $args   Prepared export arguments.
	 *
	 * @return int|WP_Error Number of exported records on success.
	 */
	public function write( $stream, $args ) {
		if ( ! is_resource( $stream ) ) {
			return new WP_Error( 'invalid_stream', __( 'Unable to create the export file.', 'digital-license-manager' ) );
		}

		$args = $this->prepare_args( $args );
		if ( is_wp_error( $args ) ) {
			return $args;
		}

		fwrite( $stream, "\xEF\xBB\xBF" );
		fputcsv( $stream, $args['columns'] );

		$count = 0;

		if ( 'selected' === $args['scope'] ) {
			$records = $this->licenses->get( $args['ids'], $args['orderby'], $args['order'] );
			$count   = $this->write_records( $stream, $records, $args['columns'] );
		} else {
			$query  = $this->build_query( $args );
			$offset = 0;

			do {
				$records = $this->licenses->get(
					$query,
					$args['orderby'],
					$args['order'],
					$offset,
					self::BATCH_SIZE
				);

				$batch_count = count( $records );
				$count      += $this->write_records( $stream, $records, $args['columns'] );
				$offset     += $batch_count;
			} while ( self::BATCH_SIZE === $batch_count );
		}

		fflush( $stream );

		return $count;
	}

	/**
	 * Build the repository query for a filtered export.
	 *
	 * @param array $args Prepared export arguments.
	 *
	 * @return array
	 */
	protected function build_query( $args ) {
		$query = [];

		if ( '' !== $args['status'] ) {
			$query['status'] = LicensePrivateStatus::$values[ $args['status'] ];
		}

		if ( '' !== $args['search'] ) {
			$query['hash'] = CryptoHelper::hash( $args['search'] );
		}

		return $query;
	}

	/**
	 * Write a list of license records.
	 *
	 * @param resource $stream  Open writable stream.
	 * @param array    $records License records.
	 * @param array    $columns Selected column slugs.
	 *
	 * @return int
	 */
	protected function write_records( $stream, $records, $columns ) {
		$count = 0;

		foreach ( $records as $license ) {
			$row    = $this->format_row( $license );
			$output = [];

			foreach ( $columns as $column ) {
				$output[] = $this->protect_csv_value( isset( $row[ $column ] ) ? $row[ $column ] : '' );
			}

			fputcsv( $stream, $output );
			$count++;
		}

		return $count;
	}

	/**
	 * Format a license as an export row.
	 *
	 * @param object $license License model.
	 *
	 * @return array
	 */
	protected function format_row( $license ) {
		$license_key = $license->getDecryptedLicenseKey();
		if ( is_wp_error( $license_key ) ) {
			$license_key = '';
		}

		$status = $license->getStatus();
		if ( in_array( (int) $status, LicensePrivateStatus::$status, true ) ) {
			$status = LicensePrivateStatus::getLabel( (int) $status );
		}

		$row = [
			'id'                => $license->getId(),
			'order_id'          => $license->getOrderId(),
			'product_id'        => $license->getProductId(),
			'user_id'           => $license->getUserId(),
			'license_key'       => $license_key,
			'expires_at'        => $license->getExpiresAt(),
			'valid_for'         => $license->getValidFor(),
			'status'            => $status,
			'activations_limit' => $license->getActivationsLimit(),
			'created_at'        => $license->getCreatedAt(),
			'created_by'        => $license->getCreatedBy(),
			'updated_at'        => $license->getUpdatedAt(),
			'updated_by'        => $license->getUpdatedBy(),
		];

		/**
		 * Filter a formatted license CSV row before selected columns are written.
		 *
		 * @param array  $row     Formatted export values keyed by column slug.
		 * @param object $license License model.
		 */
		return apply_filters( 'dlm_license_export_row', $row, $license );
	}

	/**
	 * Prevent spreadsheet applications from evaluating untrusted cells.
	 *
	 * @param mixed $value Cell value.
	 *
	 * @return mixed
	 */
	protected function protect_csv_value( $value ) {
		if ( ! is_string( $value ) ) {
			return $value;
		}

		$trimmed = ltrim( $value );
		if ( '' !== $trimmed && in_array( $trimmed[0], [ '=', '+', '-', '@' ], true ) ) {
			return "'" . $value;
		}

		return $value;
	}
}
