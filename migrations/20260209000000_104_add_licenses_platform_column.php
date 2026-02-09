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

/* @var int $migrationMode */

use IdeoLogix\DigitalLicenseManager\Database\Migrator;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

defined( 'ABSPATH' ) || exit;

/**
 * Upgrade script - Add platform column to licenses table
 */
if ( $migrationMode === Migrator::MODE_UP ) {
	global $wpdb;
	$table = $wpdb->prefix . DatabaseTable::LICENSES;

	// Add the platform column
	$wpdb->query(
		"ALTER TABLE `{$table}`
	        ADD `platform` VARCHAR(50) NULL DEFAULT NULL
        	AFTER `product_id`;"
	);

	// Backfill existing licenses that have an order_id as WooCommerce
	// (native ecommerce is new, so all existing order-linked licenses are WooCommerce)
	$wpdb->query(
		"UPDATE `{$table}` SET `platform` = 'woocommerce' WHERE `order_id` IS NOT NULL;"
	);

	// Add index for platform column
	$wpdb->query(
		"ALTER TABLE `{$table}` ADD INDEX `platform` (`platform`);"
	);

	return true;
}

return false;
