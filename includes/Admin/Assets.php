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

use IdeoLogix\DigitalLicenseManager\Enums\ActivationSource;
use IdeoLogix\DigitalLicenseManager\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Class Assets
 *
 * Handles script and style enqueuing for the Vue3 admin.
 *
 * @package IdeoLogix\DigitalLicenseManager\Admin
 */
class Assets {

	use Singleton;

	/**
	 * Script handle
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'dlm-admin-vue';

	/**
	 * Style handle
	 *
	 * @var string
	 */
	const STYLE_HANDLE = 'dlm-admin-vue';

	/**
	 * Initialize assets.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 10 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ], 11 );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @return void
	 */
	public function register() {
		$version   = defined( 'DLM_PLUGIN_VERSION' ) ? DLM_PLUGIN_VERSION : '1.0.0';
		$asset_url = defined( 'DLM_PLUGIN_URL' ) ? DLM_PLUGIN_URL . 'assets/admin/' : '';

		// Register Vue admin script
		wp_register_script(
			self::SCRIPT_HANDLE,
			$asset_url . 'scripts.js',
			[],
			$version,
			true
		);

		// Register Vue admin styles
		wp_register_style(
			self::STYLE_HANDLE,
			$asset_url . 'styles.css',
			[],
			$version
		);
	}

	/**
	 * Enqueue scripts and styles on DLM pages.
	 *
	 * @param string $hook The current admin page hook.
	 *
	 * @return void
	 */
	public function enqueue( $hook ) {
		if ( ! $this->should_enqueue( $hook ) ) {
			return;
		}

		// Enqueue WordPress media library (needed for image upload fields)
		wp_enqueue_media();

		// Enqueue styles
		wp_enqueue_style( self::STYLE_HANDLE );

		// Enqueue script
		wp_enqueue_script( self::SCRIPT_HANDLE );

		// Localize script with config
		wp_localize_script( self::SCRIPT_HANDLE, 'DLMAdmin', $this->get_localized_data() );
	}

	/**
	 * Check if we should enqueue assets.
	 *
	 * @param string $hook The current admin page hook.
	 *
	 * @return bool
	 */
	protected function should_enqueue( $hook ) {
		return strpos( $hook, Boot::PAGE_SLUG ) !== false;
	}

	/**
	 * Get the localized script data.
	 *
	 * @return array
	 */
	protected function get_localized_data() {
		$strings = include __DIR__ . '/Strings.php';

		return apply_filters( 'dlm_admin_localized_data', [
			'i18n'          => $strings,
			'nonce'         => wp_create_nonce( 'dlm_admin' ),
			'dropdownNonce' => wp_create_nonce( 'dlm_dropdown_search' ),
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'restUrl'   => rest_url( 'dlm/v1/' ),
			'adminUrl'  => admin_url(),
			'pluginUrl' => defined( 'DLM_PLUGIN_URL' ) ? DLM_PLUGIN_URL : '',
			'version'   => defined( 'DLM_PLUGIN_VERSION' ) ? DLM_PLUGIN_VERSION : '1.0.0',
			'config'    => [
				'dateFormat'        => get_option( 'date_format', 'Y-m-d' ),
				'timeFormat'        => get_option( 'time_format', 'H:i' ),
				'activationSources' => ActivationSource::all(),
			],
		] );
	}
}
