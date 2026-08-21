<?php

use IdeoLogix\DigitalLicenseManager\Admin\LicenseCsvExporter;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
use IdeoLogix\DigitalLicenseManager\Enums\LicensePrivateStatus;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseSource;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;

class DLM_License_Csv_Export_TestCase extends WP_UnitTestCase {

	private $license_ids = [];

	public function tearDown(): void {
		if ( ! empty( $this->license_ids ) ) {
			Licenses::instance()->delete( $this->license_ids );
		}

		remove_filter( 'dlm_license_export_row', [ $this, 'replace_license_key_with_formula' ] );
		parent::tearDown();
	}

	public function test_selected_export_uses_requested_columns_and_full_license_key() {
		$first  = $this->create_license( 'EXPORT-KEY-0001', LicensePrivateStatus::DELIVERED, 41 );
		$second = $this->create_license( 'EXPORT-KEY-0002', LicensePrivateStatus::ACTIVE, 42 );

		$exporter = new LicenseCsvExporter();
		$args     = $exporter->prepare_args(
			[
				'scope'   => 'selected',
				'ids'     => [ $first->getId() ],
				'columns' => [ 'license_key', 'id', 'status', 'created_by' ],
				'orderby' => 'id',
				'order'   => 'ASC',
			]
		);

		$this->assertIsArray( $args );
		$this->assertSame( 1, $exporter->count_records( $args ) );

		$rows = $this->export_rows( $exporter, $args );

		$this->assertSame( [ 'license_key', 'id', 'status', 'created_by' ], $rows[0] );
		$this->assertSame( 'EXPORT-KEY-0001', $rows[1][0] );
		$this->assertSame( (string) $first->getId(), $rows[1][1] );
		$this->assertSame( 'DELIVERED', $rows[1][2] );
		$this->assertSame( '41', $rows[1][3] );
		$this->assertCount( 2, $rows );
		$this->assertNotSame( $first->getId(), $second->getId() );
	}

	public function test_filtered_export_includes_every_matching_record() {
		$delivered_one = $this->create_license( 'FILTER-KEY-0001', LicensePrivateStatus::DELIVERED );
		$delivered_two = $this->create_license( 'FILTER-KEY-0002', LicensePrivateStatus::DELIVERED );
		$this->create_license( 'FILTER-KEY-0003', LicensePrivateStatus::DISABLED );

		$exporter = new LicenseCsvExporter();
		$args     = $exporter->prepare_args(
			[
				'scope'   => 'filtered',
				'status'  => 'delivered',
				'columns' => [ 'id', 'status' ],
				'orderby' => 'id',
				'order'   => 'ASC',
			]
		);

		$this->assertSame( 2, $exporter->count_records( $args ) );

		$rows = $this->export_rows( $exporter, $args );
		$ids  = [ (string) $delivered_one->getId(), (string) $delivered_two->getId() ];

		$this->assertSame( [ 'id', 'status' ], $rows[0] );
		$this->assertSame( $ids, array_column( array_slice( $rows, 1 ), 0 ) );
		$this->assertSame( [ 'DELIVERED', 'DELIVERED' ], array_column( array_slice( $rows, 1 ), 1 ) );
	}

	public function test_filtered_export_uses_exact_license_key_search() {
		$match = $this->create_license( 'SEARCH-EXPORT-KEY', LicensePrivateStatus::ACTIVE );
		$this->create_license( 'OTHER-EXPORT-KEY', LicensePrivateStatus::ACTIVE );

		$exporter = new LicenseCsvExporter();
		$args     = $exporter->prepare_args(
			[
				'scope'   => 'filtered',
				'search'  => 'SEARCH-EXPORT-KEY',
				'columns' => [ 'id', 'license_key' ],
			]
		);

		$this->assertSame( 1, $exporter->count_records( $args ) );

		$rows = $this->export_rows( $exporter, $args );
		$this->assertSame( (string) $match->getId(), $rows[1][0] );
		$this->assertSame( 'SEARCH-EXPORT-KEY', $rows[1][1] );
	}

	public function test_filtered_export_combines_product_order_and_customer_filters() {
		$match = $this->create_license(
			'ASSIGNMENT-FILTER-0001',
			LicensePrivateStatus::DELIVERED,
			1,
			[ 'product_id' => 501, 'order_id' => 601, 'user_id' => 701 ]
		);
		$this->create_license(
			'ASSIGNMENT-FILTER-0002',
			LicensePrivateStatus::DELIVERED,
			1,
			[ 'product_id' => 501, 'order_id' => 602, 'user_id' => 702 ]
		);
		$this->create_license(
			'ASSIGNMENT-FILTER-0003',
			LicensePrivateStatus::DELIVERED,
			1,
			[ 'product_id' => 502, 'order_id' => 601, 'user_id' => 702 ]
		);
		$this->create_license(
			'ASSIGNMENT-FILTER-0004',
			LicensePrivateStatus::DELIVERED,
			1,
			[ 'product_id' => 502, 'order_id' => 602, 'user_id' => 701 ]
		);

		$exporter = new LicenseCsvExporter();

		$product_args = $exporter->prepare_args( [ 'product_id' => 501 ] );
		$order_args   = $exporter->prepare_args( [ 'order_id' => 601 ] );
		$user_args    = $exporter->prepare_args( [ 'user_id' => 701 ] );

		$this->assertSame( 2, $exporter->count_records( $product_args ) );
		$this->assertSame( 2, $exporter->count_records( $order_args ) );
		$this->assertSame( 2, $exporter->count_records( $user_args ) );

		$combined_args = $exporter->prepare_args(
			[
				'scope'      => 'filtered',
				'status'     => 'delivered',
				'product_id' => 501,
				'order_id'   => 601,
				'user_id'    => 701,
				'columns'    => [ 'id', 'product_id', 'order_id', 'user_id' ],
			]
		);

		$this->assertSame( 1, $exporter->count_records( $combined_args ) );

		$rows = $this->export_rows( $exporter, $combined_args );
		$this->assertSame( [ 'id', 'product_id', 'order_id', 'user_id' ], $rows[0] );
		$this->assertSame( [ (string) $match->getId(), '501', '601', '701' ], $rows[1] );
		$this->assertCount( 2, $rows );
	}

	public function test_export_rejects_invalid_scope_and_empty_columns() {
		$exporter = new LicenseCsvExporter();

		$invalid_scope = $exporter->prepare_args( [ 'scope' => 'everything' ] );
		$empty_columns = $exporter->prepare_args( [ 'scope' => 'filtered', 'columns' => [ 'not_allowed' ] ] );

		$this->assertWPError( $invalid_scope );
		$this->assertSame( 'invalid_scope', $invalid_scope->get_error_code() );
		$this->assertWPError( $empty_columns );
		$this->assertSame( 'empty_columns', $empty_columns->get_error_code() );
	}

	public function test_export_protects_spreadsheet_formula_values() {
		$license = $this->create_license( 'SAFE-EXPORT-KEY', LicensePrivateStatus::ACTIVE );
		add_filter( 'dlm_license_export_row', [ $this, 'replace_license_key_with_formula' ] );

		$exporter = new LicenseCsvExporter();
		$args     = $exporter->prepare_args(
			[
				'scope'   => 'selected',
				'ids'     => [ $license->getId() ],
				'columns' => [ 'license_key' ],
			]
		);

		$rows = $this->export_rows( $exporter, $args );
		$this->assertSame( "'=1+1", $rows[1][0] );
	}

	public function replace_license_key_with_formula( $row ) {
		$row['license_key'] = '=1+1';

		return $row;
	}

	private function create_license( $key, $status, $created_by = 1, $assignments = [] ) {
		$license = Licenses::instance()->create(
			array_merge(
				[
					'license_key'       => CryptoHelper::encrypt( $key ),
					'hash'              => CryptoHelper::hash( $key ),
					'status'            => $status,
					'source'            => LicenseSource::API,
					'activations_limit' => 5,
					'valid_for'         => 365,
					'created_by'        => $created_by,
				],
				$assignments
			)
		);

		$this->assertNotFalse( $license );
		$this->license_ids[] = $license->getId();

		return $license;
	}

	private function export_rows( $exporter, $args ) {
		$stream = fopen( 'php://temp', 'w+' );
		$count  = $exporter->write( $stream, $args );

		$this->assertIsInt( $count );
		rewind( $stream );
		$contents = stream_get_contents( $stream );
		fclose( $stream );

		$this->assertStringStartsWith( "\xEF\xBB\xBF", $contents );
		$contents = substr( $contents, 3 );

		$stream = fopen( 'php://temp', 'w+' );
		fwrite( $stream, $contents );
		rewind( $stream );

		$rows = [];
		while ( false !== ( $row = fgetcsv( $stream ) ) ) {
			$rows[] = $row;
		}
		fclose( $stream );

		return $rows;
	}
}
