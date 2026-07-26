<?php
/**
 * WordPress test suite configuration.
 *
 * Consumed by the wp-phpunit/wp-phpunit Composer package, which is pointed here by the
 * WP_PHPUNIT__TESTS_CONFIG environment variable (set automatically in tests/integration/bootstrap.php).
 *
 * Every value can be overridden by an environment variable, so the same file works inside the
 * devenv container and in CI without editing.
 *
 * WARNING: the test suite DROPS AND RECREATES every table in DB_NAME. It must never point at
 * the development database.
 *
 * @package IdeoLogix\DigitalLicenseManager
 */

/**
 * Reads an environment variable, falling back to a default.
 *
 * @param string $name    Variable name.
 * @param string $default Fallback value.
 *
 * @return string
 */
function dlm_tests_env( $name, $default ) {
	$value = getenv( $name );

	return ( false === $value || '' === $value ) ? $default : $value;
}

// Database. Inside the devenv container the host is "database", not "localhost".
define( 'DB_NAME', dlm_tests_env( 'WP_TESTS_DB_NAME', 'wordpress_test' ) );
define( 'DB_USER', dlm_tests_env( 'WP_TESTS_DB_USER', 'wordpress' ) );
define( 'DB_PASSWORD', dlm_tests_env( 'WP_TESTS_DB_PASSWORD', 'wordpress' ) );
define( 'DB_HOST', dlm_tests_env( 'WP_TESTS_DB_HOST', 'database' ) );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// WordPress core to test against.
define( 'ABSPATH', rtrim( dlm_tests_env( 'WP_TESTS_ABSPATH', '/var/www/html' ), '/' ) . '/' );

define( 'WP_TESTS_DOMAIN', dlm_tests_env( 'WP_TESTS_DOMAIN', 'dlm.test' ) );
define( 'WP_TESTS_EMAIL', dlm_tests_env( 'WP_TESTS_EMAIL', 'admin@dlm.test' ) );
define( 'WP_TESTS_TITLE', 'DLM Test Suite' );

define( 'WP_PHP_BINARY', 'php' );

define( 'WP_DEBUG', true );

// Single site by default; set WP_TESTS_MULTISITE=1 to exercise the multisite paths.
if ( '1' === dlm_tests_env( 'WP_TESTS_MULTISITE', '0' ) ) {
	define( 'WP_TESTS_MULTISITE', true );
	define( 'MULTISITE', true );
	define( 'SUBDOMAIN_INSTALL', true );
}

$table_prefix = dlm_tests_env( 'WP_TESTS_TABLE_PREFIX', 'wptests_' );
