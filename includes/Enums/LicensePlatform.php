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

namespace IdeoLogix\DigitalLicenseManager\Enums;

abstract class LicensePlatform {

	/**
	 * Platform value for WooCommerce orders/products.
	 *
	 * @var string
	 */
	const WOOCOMMERCE = 'woocommerce';

	/**
	 * Platform value for native ecommerce orders/products.
	 *
	 * @var string
	 */
	const NATIVE = 'native';

	/**
	 * Available platform values.
	 *
	 * @var array
	 */
	public static $platforms = array(
		self::WOOCOMMERCE,
		self::NATIVE,
	);

	/**
	 * Returns the string label for a specific platform value.
	 *
	 * @param string $platform Platform value
	 *
	 * @return string|null
	 */
	public static function getLabel( $platform ) {
		$labels = array(
			self::WOOCOMMERCE => 'WooCommerce',
			self::NATIVE      => 'Native',
		);

		return isset( $labels[ $platform ] ) ? $labels[ $platform ] : null;
	}

	/**
	 * Checks if the given platform value is valid.
	 *
	 * @param string $platform Platform value
	 *
	 * @return bool
	 */
	public static function isValid( $platform ) {
		return in_array( $platform, self::$platforms, true );
	}
}
