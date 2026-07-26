<?php
/**
 * Tests for REST API authentication routing.
 *
 * isRequestToRestApi() decides whether DLM authenticates a request at all. Webhook routes are
 * deliberately excluded because they carry their own signature verification, so the exclusion
 * must be decided from the resolved route and nothing else - matching against the raw
 * REQUEST_URI lets a caller put a webhook path in the query string and opt out of authentication.
 *
 * @package IdeoLogix\DigitalLicenseManager
 */

use IdeoLogix\DigitalLicenseManager\RestAPI\Authentication;

class DLM_RestAuthentication_TestCase extends WP_UnitTestCase {

	/**
	 * @var Authentication
	 */
	private $auth;

	/**
	 * @var ReflectionMethod
	 */
	private $isRequestToRestApi;

	/**
	 * @var string|null
	 */
	private $originalRequestUri;

	protected function setUp(): void {
		parent::setUp();

		// Built without the constructor: it registers filters we do not want duplicated.
		$this->auth = ( new ReflectionClass( Authentication::class ) )->newInstanceWithoutConstructor();

		$this->isRequestToRestApi = new ReflectionMethod( Authentication::class, 'isRequestToRestApi' );
		$this->isRequestToRestApi->setAccessible( true );

		$this->originalRequestUri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;
	}

	protected function tearDown(): void {

		if ( null === $this->originalRequestUri ) {
			unset( $_SERVER['REQUEST_URI'] );
		} else {
			$_SERVER['REQUEST_URI'] = $this->originalRequestUri;
		}

		unset( $_GET['rest_route'] );

		parent::tearDown();
	}

	/**
	 * Runs the classification for a given request.
	 *
	 * @param string      $request_uri The REQUEST_URI to simulate.
	 * @param string|null $rest_route  Optional ?rest_route= value (plain permalinks).
	 *
	 * @return bool Whether DLM would authenticate this request.
	 */
	private function wouldAuthenticate( $request_uri, $rest_route = null ) {

		$_SERVER['REQUEST_URI'] = $request_uri;

		if ( null === $rest_route ) {
			unset( $_GET['rest_route'] );
		} else {
			$_GET['rest_route'] = $rest_route;
		}

		return (bool) $this->isRequestToRestApi->invoke( $this->auth );
	}

	// =========================================================================
	// Normal routing
	// =========================================================================

	public function test_dlm_route_is_authenticated() {
		$this->assertTrue( $this->wouldAuthenticate( '/wp-json/dlm/v1/licenses' ) );
	}

	public function test_genuine_webhook_route_skips_authentication() {
		$this->assertFalse( $this->wouldAuthenticate( '/wp-json/dlm/v1/webhooks/stripe' ) );
		$this->assertFalse( $this->wouldAuthenticate( '/wp-json/dlm/v1/webhooks/paddle?foo=1' ) );
	}

	public function test_other_plugins_and_core_routes_are_left_alone() {
		$this->assertFalse( $this->wouldAuthenticate( '/wp-json/wc/v3/orders' ) );
		$this->assertFalse( $this->wouldAuthenticate( '/wp-json/wp/v2/posts' ) );
		$this->assertFalse( $this->wouldAuthenticate( '/wp-admin/admin.php?page=dlm-licenses' ) );
	}

	public function test_subdirectory_installs_are_recognised() {
		$this->assertTrue( $this->wouldAuthenticate( '/wp/wp-json/dlm/v1/licenses' ) );
	}

	// =========================================================================
	// Authentication must not be skippable from the request
	// =========================================================================

	/**
	 * A webhook path in the query string must not disable authentication for a real endpoint.
	 */
	public function test_webhook_path_in_query_string_does_not_skip_authentication() {
		$this->assertTrue(
			$this->wouldAuthenticate( '/wp-json/dlm/v1/licenses?x=/wp-json/dlm/v1/webhooks/stripe' ),
			'A webhook path in the query string must not opt the request out of authentication.'
		);
	}

	public function test_webhook_path_as_a_parameter_value_does_not_skip_authentication() {
		$this->assertTrue(
			$this->wouldAuthenticate( '/wp-json/dlm/v1/licenses?redirect=wp-json/dlm/v1/webhooks/' )
		);
	}

	/**
	 * The exclusion is anchored to the start of the route, so a webhook path appearing later in
	 * the path is not the route being requested.
	 */
	public function test_webhook_path_later_in_the_path_does_not_skip_authentication() {
		$this->assertTrue(
			$this->wouldAuthenticate( '/wp-json/dlm/v1/licenses/wp-json/dlm/v1/webhooks/stripe' )
		);
	}

	// =========================================================================
	// Plain permalinks
	// =========================================================================

	/**
	 * With plain permalinks the REQUEST_URI contains no /wp-json/ segment at all. Without
	 * handling ?rest_route= these sites could not authenticate against the DLM API.
	 */
	public function test_rest_route_form_is_authenticated() {
		$this->assertTrue(
			$this->wouldAuthenticate( '/?rest_route=/dlm/v1/licenses', '/dlm/v1/licenses' ),
			'Plain-permalink sites must still authenticate against the DLM API.'
		);
	}

	public function test_rest_route_form_still_excludes_webhooks() {
		$this->assertFalse(
			$this->wouldAuthenticate( '/?rest_route=/dlm/v1/webhooks/stripe', '/dlm/v1/webhooks/stripe' )
		);
	}

	public function test_rest_route_form_ignores_other_namespaces() {
		$this->assertFalse( $this->wouldAuthenticate( '/?rest_route=/wp/v2/posts', '/wp/v2/posts' ) );
	}

	// =========================================================================
	// End to end
	// =========================================================================

	/**
	 * An anonymous caller must not reach a licenses endpoint, with or without the bypass attempt.
	 */
	public function test_anonymous_request_to_licenses_is_refused() {

		wp_set_current_user( 0 );

		foreach (
			[
				'/wp-json/dlm/v1/licenses',
				'/wp-json/dlm/v1/licenses?x=/wp-json/dlm/v1/webhooks/stripe',
			] as $request_uri
		) {
			$_SERVER['REQUEST_URI'] = $request_uri;

			$response = rest_do_request( new WP_REST_Request( 'GET', '/dlm/v1/licenses' ) );

			$this->assertTrue(
				$response->is_error(),
				sprintf( 'Anonymous GET must be refused for %s', $request_uri )
			);
			$this->assertNotEquals(
				200,
				$response->get_status(),
				sprintf( 'Anonymous GET must not return 200 for %s', $request_uri )
			);
		}
	}
}
