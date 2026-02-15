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

namespace IdeoLogix\DigitalLicenseManager\Controllers;

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses as LicensesRepository;
use IdeoLogix\DigitalLicenseManager\Utils\HttpHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Class Licenses
 * Handles license-related AJAX actions for WooCommerce order pages.
 *
 * @package IdeoLogix\DigitalLicenseManager\Controllers
 */
class Licenses {

	/**
	 * Licenses constructor.
	 */
	public function __construct() {
		// AJAX calls (used on WooCommerce order pages)
		add_action( 'wp_ajax_dlm_show_license_key', array( $this, 'showLicenseKey' ), 10 );
		add_action( 'wp_ajax_dlm_show_all_license_keys', array( $this, 'showAllLicenseKeys' ), 10 );
	}

	/**
	 * Show a single license key.
	 */
	public function showLicenseKey() {
		// Validate request.
		check_ajax_referer( 'dlm_show_license_key', 'show' );

		if ( ! current_user_can( 'dlm_read_licenses' ) ) {
			wp_send_json( 'ERROR' );
			wp_die();
		}

		if ( 'POST' !== HttpHelper::requestMethod() ) {
			wp_die( __( 'Invalid request.', 'digital-license-manager' ) );
		}

		/** @var License $license */
		$license = LicensesRepository::instance()->findBy( array( 'id' => isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0 ) );

		$decrypted = $license->getDecryptedLicenseKey();
		if ( is_wp_error( $decrypted ) ) {
			wp_send_json( 'ERROR' );
		}

		wp_send_json( $decrypted );

		wp_die();
	}

	/**
	 * Shows all visible license keys.
	 */
	public function showAllLicenseKeys() {
		// Validate request.
		check_ajax_referer( 'dlm_show_all_license_keys', 'show_all' );

		if ( ! current_user_can( 'dlm_read_licenses' ) ) {
			wp_send_json( 'ERROR' );
			wp_die();
		}

		if ( 'POST' != HttpHelper::requestMethod() ) {
			wp_die( __( 'Invalid request.', 'digital-license-manager' ) );
		}

		$licenseKeysIds     = array();
		$licenseKeyIdsInput = ! empty( $_POST['ids'] ) ? array_map( 'absint', json_decode( wp_unslash( $_POST['ids'] ), true ) ) : [];

		foreach ( $licenseKeyIdsInput as $licenseKeyId ) {
			$licenseKeyId = intval( $licenseKeyId );
			/** @var License $license */
			$license = LicensesRepository::instance()->find( $licenseKeyId );

			$licenseKey = $license->getDecryptedLicenseKey();
			if ( ! is_wp_error( $license ) ) {
				$licenseKeysIds[ $licenseKeyId ] = $licenseKey;
			} else {
				$licenseKeysIds[ $licenseKeyId ] = 'ERROR';
			}
		}

		wp_send_json( $licenseKeysIds );
	}
}
