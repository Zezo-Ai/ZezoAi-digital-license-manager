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

use IdeoLogix\DigitalLicenseManager\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Class Boot
 *
 * Handles the Vue3 admin interface registration and rendering.
 *
 * @package IdeoLogix\DigitalLicenseManager\Admin
 */
class Boot {

	use Singleton;

	/**
	 * The main page slug
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'dlm-licenses';

	/**
	 * The required capability
	 *
	 * @var string
	 */
	const CAPABILITY = 'dlm_read_licenses';

	/**
	 * Assets handler
	 *
	 * @var Assets
	 */
	protected $assets;

	/**
	 * Ajax handler
	 *
	 * @var Ajax
	 */
	protected $ajax;

	/**
	 * Initialize the admin boot.
	 *
	 * @return void
	 */
	protected function init() {
		$this->assets = Assets::instance();
		$this->ajax   = Ajax::instance();

		add_action( 'admin_menu', [ $this, 'register_menu' ], 9 );
	}

	/**
	 * Register the admin menu and submenus.
	 *
	 * @return void
	 */
	public function register_menu() {
		global $submenu;

		// Get the icon
		$icon = $this->get_menu_icon();
		$slug = self::PAGE_SLUG;
		$capability = self::CAPABILITY;

		// Add main menu page
		add_menu_page(
			__( 'License Manager', 'digital-license-manager' ),
			__( 'License Manager', 'digital-license-manager' ),
			$capability,
			$slug,
			[ $this, 'render_page' ],
			$icon,
			58
		);

		// Add submenus directly to $submenu global (required for hash-based routing)
		$submenu[ $slug ][] = [ __( 'Licenses', 'digital-license-manager' ), $capability, 'admin.php?page=' . $slug . '#/' ];
		$submenu[ $slug ][] = [ __( 'Generators', 'digital-license-manager' ), $capability, 'admin.php?page=' . $slug . '#/generators' ];
		$submenu[ $slug ][] = [ __( 'Activations', 'digital-license-manager' ), $capability, 'admin.php?page=' . $slug . '#/activations' ];

		// Allow other plugins (e.g. Pro) to register submenu pages at this position.
		do_action( 'dlm_admin_menu_pages', $slug );

		$submenu[ $slug ][] = [ __( 'Settings', 'digital-license-manager' ), 'dlm_manage_settings', 'admin.php?page=' . $slug . '#/settings' ];
	}

	/**
	 * Render the Vue app mount point.
	 *
	 * @return void
	 */
	public function render_page() {
		echo '<div class="wrap"><div id="dlm-admin"></div></div>';
	}

	/**
	 * Get the menu icon SVG.
	 *
	 * @return string
	 */
	protected function get_menu_icon() {
		$icon_path = DLM_ABSPATH . 'assets/img/logo.svg';
		if ( ! is_readable( $icon_path ) ) {
			return 'dashicons-admin-network';
		}

		$svg = file_get_contents( $icon_path );
		if ( false === $svg ) {
			return 'dashicons-admin-network';
		}

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Check if we're on the DLM admin page.
	 *
	 * @return bool
	 */
	public static function is_dlm_page() {
		if ( ! is_admin() ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		return strpos( $screen->id, self::PAGE_SLUG ) !== false;
	}
}
