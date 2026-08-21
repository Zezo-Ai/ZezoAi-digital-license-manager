<?php

use IdeoLogix\DigitalLicenseManager\Admin\Assets;
use IdeoLogix\DigitalLicenseManager\Admin\Workspace;

class DLM_Admin_Workspace_TestCase extends WP_UnitTestCase {

	private $user_id;

	public function setUp(): void {
		parent::setUp();

		$this->user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$user          = new WP_User( $this->user_id );
		$user->add_cap( 'dlm_read_licenses' );
		$user->add_cap( 'dlm_manage_settings' );
		wp_set_current_user( $this->user_id );
	}

	public function tearDown(): void {
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	public function test_workspace_navigation_is_extensible_sorted_and_capability_aware() {
		$filter = static function ( $items ) {
			$items[] = [
				'id'         => 'reports',
				'label'      => 'Reports',
				'url'        => admin_url( 'admin.php?page=dlm-reports' ),
				'icon'       => 'chart',
				'capability' => 'dlm_read_licenses',
				'position'   => 5,
			];
			$items[] = [
				'id'         => 'private',
				'label'      => 'Private',
				'url'        => admin_url( 'admin.php?page=dlm-private' ),
				'capability' => 'dlm_private_capability',
				'position'   => 6,
			];
			$items[] = [ 'id' => 'invalid' ];

			return $items;
		};

		add_filter( 'dlm_admin_workspace_navigation', $filter );
		$data = Workspace::get_data( 'licenses' );
		remove_filter( 'dlm_admin_workspace_navigation', $filter );

		$this->assertSame( 'licenses', $data['activeArea'] );
		$this->assertSame( [ 'reports', 'licenses', 'settings' ], wp_list_pluck( $data['navigation'], 'id' ) );
		$this->assertTrue( $data['navigation'][1]['current'] );
		$this->assertSame( 'Licensing', $data['navigation'][1]['label'] );
		$this->assertNotEmpty( $data['brand']['markUrl'] );
		$this->assertArrayHasKey( 'documentation', $data['resources'] );
		$this->assertSame( 'Workspace', $data['labels']['workspace'] );
		$this->assertArrayNotHasKey( 'description', $data['labels'] );
	}

	public function test_development_admin_assets_use_file_modification_times() {
		if ( ! defined( 'DLM_DEVELOPMENT' ) ) {
			define( 'DLM_DEVELOPMENT', true );
		}

		$this->assertTrue( DLM_DEVELOPMENT );

		wp_deregister_script( Assets::SCRIPT_HANDLE );
		wp_deregister_style( Assets::STYLE_HANDLE );
		Assets::instance()->register();

		$this->assertSame(
			(string) filemtime( DLM_ABSPATH . 'assets/admin/scripts.js' ),
			(string) wp_scripts()->registered[ Assets::SCRIPT_HANDLE ]->ver
		);
		$this->assertSame(
			(string) filemtime( DLM_ABSPATH . 'assets/admin/styles.css' ),
			(string) wp_styles()->registered[ Assets::STYLE_HANDLE ]->ver
		);
	}

	public function test_license_export_config_is_capability_aware() {
		$user = wp_get_current_user();
		$user->set_role( 'subscriber' );
		$user->add_cap( 'dlm_read_licenses' );
		$user->add_cap( 'dlm_export_licenses' );

		$method = new ReflectionMethod( Assets::class, 'get_localized_data' );
		$method->setAccessible( true );
		$data = $method->invoke( Assets::instance() );

		$this->assertTrue( $data['config']['licenseExport']['enabled'] );
		$this->assertSame( admin_url( 'admin-post.php' ), $data['config']['licenseExport']['url'] );
		$this->assertNotEmpty( $data['config']['licenseExport']['nonce'] );
		$this->assertCount( 13, $data['config']['licenseExport']['columns'] );
		$this->assertSame(
			[
				'create'     => false,
				'edit'       => false,
				'delete'     => false,
				'activate'   => false,
				'deactivate' => false,
				'export'     => true,
			],
			$data['config']['licenseCapabilities']
		);

		foreach ( [ 'create', 'edit', 'delete', 'activate', 'deactivate' ] as $action ) {
			$user->add_cap( 'dlm_' . $action . '_licenses' );
		}
		$data = $method->invoke( Assets::instance() );

		$this->assertNotContains( false, $data['config']['licenseCapabilities'], true );

		$user->remove_cap( 'dlm_export_licenses' );
		$data = $method->invoke( Assets::instance() );

		$this->assertFalse( $data['config']['licenseExport']['enabled'] );
		$this->assertSame( '', $data['config']['licenseExport']['url'] );
		$this->assertSame( '', $data['config']['licenseExport']['nonce'] );
		$this->assertSame( [], $data['config']['licenseExport']['columns'] );
		$this->assertFalse( $data['config']['licenseCapabilities']['export'] );
	}

	public function test_workspace_brand_is_filterable_and_sanitized() {
		$filter = static function ( $brand, $active_area ) {
			$brand['name']    = '<strong>DLM</strong>';
			$brand['markUrl'] = 'javascript:alert(1)';
			$brand['badge']   = '<em>Pro</em>';

			return $brand;
		};

		add_filter( 'dlm_admin_workspace_brand', $filter, 10, 2 );
		$data = Workspace::get_data( 'commerce' );
		remove_filter( 'dlm_admin_workspace_brand', $filter, 10 );

		$this->assertSame( 'DLM', $data['brand']['name'] );
		$this->assertSame( '', $data['brand']['markUrl'] );
		$this->assertSame( 'Pro', $data['brand']['badge'] );
	}
}
