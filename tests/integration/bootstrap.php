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

$_tests_dir = getenv( 'WP_TESTS_DIR' );

/*
 * Prefer the wp-phpunit/wp-phpunit Composer package.
 *
 * It ships the WordPress test library as a normal dev dependency, so `composer install` is the
 * only setup step - no svn checkout into a temp directory, and the version is pinned alongside
 * everything else. bin/install-wp-tests.sh still works: set WP_TESTS_DIR to use it instead.
 */
if ( ! $_tests_dir ) {
	$_wp_phpunit_dir = dirname( __DIR__, 2 ) . '/vendor/wp-phpunit/wp-phpunit';

	if ( file_exists( $_wp_phpunit_dir . '/includes/functions.php' ) ) {
		$_tests_dir = $_wp_phpunit_dir;

		// The package loads whichever config this points at.
		if ( ! getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ) {
			putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . dirname( __DIR__ ) . '/wp-tests-config.php' );
		}
	}
}

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo 'Could not find the WordPress test library.' . PHP_EOL // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	     . PHP_EOL
	     . 'Looked in: ' . $_tests_dir . PHP_EOL // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	     . PHP_EOL
	     . 'Fix it with either:' . PHP_EOL
	     . '  composer install                  (installs wp-phpunit/wp-phpunit - recommended)' . PHP_EOL
	     . '  bin/install-wp-tests.sh <db> <user> <pass> [db-host]   (legacy, needs svn)' . PHP_EOL
	     . PHP_EOL
	     . 'From the devenv root, `make test-free` runs this suite inside the container.' . PHP_EOL;
	exit( 1 );
}

if ( PHP_VERSION_ID >= 80000 && file_exists( $_tests_dir . '/includes/phpunit7/MockObject' ) ) {
	// WP Core test library includes patches for PHPUnit 7 to make it compatible with PHP8.
	require_once $_tests_dir . '/includes/phpunit7/MockObject/Builder/NamespaceMatch.php';
	require_once $_tests_dir . '/includes/phpunit7/MockObject/Builder/ParametersMatch.php';
	require_once $_tests_dir . '/includes/phpunit7/MockObject/InvocationMocker.php';
	require_once $_tests_dir . '/includes/phpunit7/MockObject/MockMethod.php';
}


// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Locates a companion plugin needed by the test suite.
 *
 * WP_PLUGIN_DIR is checked first and deliberately: in a development environment the plugin
 * under test is typically symlinked into wp-content/plugins from outside the WordPress tree,
 * so resolving siblings with realpath() lands in the checkout directory rather than beside
 * the other installed plugins.
 *
 * @param string $relative_path e.g. "woocommerce/woocommerce.php".
 *
 * @return string|false Absolute path, or false when not installed.
 */
function dlm_tests_locate_plugin( $relative_path ) {

	$candidates = [];

	if ( defined( 'WP_PLUGIN_DIR' ) ) {
		$candidates[] = WP_PLUGIN_DIR . '/' . $relative_path;
	}

	if ( defined( 'ABSPATH' ) ) {
		$candidates[] = ABSPATH . 'wp-content/plugins/' . $relative_path;
	}

	// Legacy layout: sibling of the plugin under test.
	$sibling = realpath( dirname( __DIR__, 2 ) . '/..' );

	if ( $sibling ) {
		$candidates[] = $sibling . '/' . $relative_path;
	}

	foreach ( $candidates as $candidate ) {
		if ( file_exists( $candidate ) ) {
			return $candidate;
		}
	}

	return false;
}

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {

	$_plugin_dir = dirname( __FILE__ ) . '/../../';

	if ( ! function_exists( 'activate_plugin' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	require_once dirname( __FILE__ ) . '/helpers/class-dlm-helper-settings.php';
	DLM_Helper_Settings::setDefaults();

	tests_add_filter( 'dlm_mock_is_plugin_active', '__return_true' );

	// Companion plugins the suite exercises.
	$required = [
		'woocommerce/woocommerce.php',
	];

	foreach ( $required as $relative_path ) {
		$item = dlm_tests_locate_plugin( $relative_path );

		if ( ! $item ) {
			echo PHP_EOL // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			     . 'Could not find ' . $relative_path . ', which the test suite requires.' . PHP_EOL // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			     . 'Install it into wp-content/plugins, e.g.:' . PHP_EOL
			     . '  make wpcli CMD="plugin install woocommerce"' . PHP_EOL
			     . PHP_EOL;
			exit( 1 );
		}

		require_once $item;
	}

	// Set a default currency to be used for the multi-currency tests because the default
	// is not loaded even though it's set during the tests setup.
	update_option( 'woocommerce_currency', 'USD' );
	require $_plugin_dir . '/digital-license-manager.php';

	IdeoLogix\DigitalLicenseManager\Setup::install( false );

}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Need those polyfills to run tests in CI.
require_once dirname( __FILE__ ) . '/../../vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// We use outdated PHPUnit version, which emits deprecation errors in PHP 7.4 (deprecated reflection APIs).
if ( defined( 'PHP_VERSION_ID' ) && PHP_VERSION_ID >= 70400 ) {
	error_reporting( error_reporting() ^ E_DEPRECATED ); // phpcs:ignore
}
