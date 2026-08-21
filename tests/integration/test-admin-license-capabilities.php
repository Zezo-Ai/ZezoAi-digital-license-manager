<?php

use IdeoLogix\DigitalLicenseManager\Admin\Ajax;

class DLM_Admin_License_Capability_Probe_Exception extends RuntimeException {}

class DLM_Admin_License_Capability_Probe extends Ajax {

	public $checked_capability;

	protected function check_access( $capability = 'dlm_read_licenses' ) {
		$this->checked_capability = $capability;

		throw new DLM_Admin_License_Capability_Probe_Exception();
	}
}

class DLM_Admin_License_Capabilities_TestCase extends WP_UnitTestCase {

	private $original_post;

	public function setUp(): void {
		parent::setUp();
		$this->original_post = $_POST;
	}

	public function tearDown(): void {
		$_POST = $this->original_post;
		parent::tearDown();
	}

	public function test_store_requires_create_or_edit_capability() {
		$this->assert_endpoint_capability( 'licenses_store', [], 'dlm_create_licenses' );
		$this->assert_endpoint_capability( 'licenses_store', [ 'id' => 42 ], 'dlm_edit_licenses' );
	}

	public function test_delete_and_import_require_mutation_capabilities() {
		$this->assert_endpoint_capability( 'licenses_delete', [ 'id' => 42 ], 'dlm_delete_licenses' );
		$this->assert_endpoint_capability( 'licenses_import', [], 'dlm_create_licenses' );
	}

	public function test_bulk_actions_require_their_specific_capabilities() {
		$this->assert_endpoint_capability(
			'licenses_bulk_action',
			[ 'bulk_action' => 'activate', 'ids' => [ 42 ] ],
			'dlm_activate_licenses'
		);
		$this->assert_endpoint_capability(
			'licenses_bulk_action',
			[ 'bulk_action' => 'deactivate', 'ids' => [ 42 ] ],
			'dlm_deactivate_licenses'
		);
		$this->assert_endpoint_capability(
			'licenses_bulk_action',
			[ 'bulk_action' => 'delete', 'ids' => [ 42 ] ],
			'dlm_delete_licenses'
		);
	}

	private function assert_endpoint_capability( $method, $post, $expected_capability ) {
		$_POST = $post;
		$probe = ( new ReflectionClass( DLM_Admin_License_Capability_Probe::class ) )->newInstanceWithoutConstructor();

		try {
			$probe->$method();
			$this->fail( 'The capability probe did not stop the endpoint.' );
		} catch ( DLM_Admin_License_Capability_Probe_Exception $exception ) {
			$this->assertSame( $expected_capability, $probe->checked_capability );
		}
	}
}
