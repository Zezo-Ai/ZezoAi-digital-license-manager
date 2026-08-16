<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-present  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-present  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
 *
 * Licensed under GPLv2 or later.
 */

namespace IdeoLogix\DigitalLicenseManager\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Builds the shared workspace configuration used by every DLM admin app.
 */
class Workspace {

	/**
	 * Build the localized workspace payload.
	 *
	 * Plugins can add or alter navigation entries with the
	 * `dlm_admin_workspace_navigation` filter. Each item accepts id, label,
	 * url, icon, capability, and position values. Entries the current user
	 * cannot access are removed before the payload reaches JavaScript.
	 *
	 * @param string $active_area Active workspace area.
	 *
	 * @return array
	 */
	public static function get_data( $active_area = 'licenses' ) {
		$mark_url = defined( 'DLM_PLUGIN_URL' ) ? DLM_PLUGIN_URL . 'assets/img/logo.svg' : '';
		if ( $mark_url ) {
			$mark_version = defined( 'DLM_PLUGIN_VERSION' ) ? DLM_PLUGIN_VERSION : '';
			$mark_path    = defined( 'DLM_ABSPATH' ) ? DLM_ABSPATH . 'assets/img/logo.svg' : '';
			if ( defined( 'DLM_DEVELOPMENT' ) && DLM_DEVELOPMENT && $mark_path && file_exists( $mark_path ) ) {
				$mark_version = (string) filemtime( $mark_path );
			}

			if ( $mark_version ) {
				$mark_url = add_query_arg( 'ver', $mark_version, $mark_url );
			}
		}

		$default_brand = [
			'name'    => __( 'Digital License Manager', 'digital-license-manager' ),
			'markUrl' => $mark_url,
			'badge'   => '',
		];

		/**
		 * Filters the shared DLM product brand.
		 *
		 * Add-ons can supply a short edition badge without replacing the common
		 * product name or mark.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $brand       Brand name, markUrl, and optional badge.
		 * @param string $active_area Active workspace area.
		 */
		$brand = apply_filters( 'dlm_admin_workspace_brand', $default_brand, $active_area );
		$brand = is_array( $brand ) ? $brand : [];
		$brand = [
			'name'    => sanitize_text_field( $brand['name'] ?? $default_brand['name'] ),
			'markUrl' => esc_url_raw( $brand['markUrl'] ?? $default_brand['markUrl'] ),
			'badge'   => sanitize_text_field( $brand['badge'] ?? '' ),
		];

		$items = [
			[
				'id'         => 'licenses',
				'label'      => __( 'Licensing', 'digital-license-manager' ),
				'url'        => admin_url( 'admin.php?page=' . Boot::PAGE_SLUG . '#/' ),
				'icon'       => 'licenses',
				'capability' => Boot::CAPABILITY,
				'position'   => 10,
			],
			[
				'id'         => 'settings',
				'label'      => __( 'Settings', 'digital-license-manager' ),
				'url'        => admin_url( 'admin.php?page=' . Boot::PAGE_SLUG . '#/settings' ),
				'icon'       => 'settings',
				'capability' => 'dlm_manage_settings',
				'position'   => 40,
			],
		];

		/**
		 * Filters the top-level navigation shared by DLM admin applications.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $items       Workspace navigation items.
		 * @param string $active_area Active workspace area.
		 */
		$items = apply_filters( 'dlm_admin_workspace_navigation', $items, $active_area );
		$items = is_array( $items ) ? $items : [];

		usort(
			$items,
			static function ( $left, $right ) {
				return (int) ( $left['position'] ?? 100 ) <=> (int) ( $right['position'] ?? 100 );
			}
		);

		$navigation = [];
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['id'] ) || empty( $item['label'] ) || empty( $item['url'] ) ) {
				continue;
			}

			$capability = isset( $item['capability'] ) ? (string) $item['capability'] : '';
			if ( $capability && ! current_user_can( $capability ) ) {
				continue;
			}

			$id           = sanitize_key( $item['id'] );
			$navigation[] = [
				'id'      => $id,
				'label'   => sanitize_text_field( $item['label'] ),
				'url'     => esc_url_raw( $item['url'] ),
				'icon'    => sanitize_key( $item['icon'] ?? $id ),
				'current' => $id === $active_area,
			];
		}

		return [
			'brand'      => $brand,
			'activeArea' => sanitize_key( $active_area ),
			'navigation' => $navigation,
			'resources'  => [
				'documentation' => 'https://docs.codeverve.com/digital-license-manager/',
				'support'       => 'https://docs.codeverve.com/digital-license-manager/',
			],
			'labels'     => [
				'workspace'         => __( 'Workspace', 'digital-license-manager' ),
				'navigation'        => __( 'Digital License Manager navigation', 'digital-license-manager' ),
				'sectionNavigation' => __( 'Section navigation', 'digital-license-manager' ),
				'documentation'     => __( 'Documentation', 'digital-license-manager' ),
				'support'           => __( 'Support', 'digital-license-manager' ),
			],
		];
	}
}
