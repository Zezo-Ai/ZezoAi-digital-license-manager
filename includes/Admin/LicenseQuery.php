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
 * Digital License Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

namespace IdeoLogix\DigitalLicenseManager\Admin;

use IdeoLogix\DigitalLicenseManager\Enums\LicensePrivateStatus;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Builds repository conditions shared by the license list and CSV exporter.
 */
class LicenseQuery {

	/**
	 * Build repository conditions from normalized filter arguments.
	 *
	 * @param array $args Filter arguments.
	 *
	 * @return array
	 */
	public static function build( $args ) {
		$query  = [];
		$status = isset( $args['status'] ) ? sanitize_key( $args['status'] ) : '';
		$search = isset( $args['search'] ) ? sanitize_text_field( $args['search'] ) : '';

		if ( '' !== $status && isset( LicensePrivateStatus::$values[ $status ] ) ) {
			$query['status'] = LicensePrivateStatus::$values[ $status ];
		}

		if ( '' !== $search ) {
			$query['hash'] = CryptoHelper::hash( $search );
		}

		foreach ( [ 'product_id', 'order_id', 'user_id' ] as $field ) {
			$value = isset( $args[ $field ] ) ? absint( $args[ $field ] ) : 0;
			if ( $value ) {
				$query[ $field ] = $value;
			}
		}

		return $query;
	}
}
