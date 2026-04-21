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

namespace IdeoLogix\DigitalLicenseManager\Tools;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractTool;
use IdeoLogix\DigitalLicenseManager\Database\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Database repair tool.
 *
 * Recreates any missing DLM database tables via dbDelta. Safe to run at any
 * time — existing data is preserved. Extensions (e.g. the Pro plugin) can
 * hook into the `dlm_repair_database_tables` action to repair their own tables.
 */
class DatabaseRepair extends AbstractTool {

	/**
	 * Constructor.
	 */
	public function __construct( $id ) {
		parent::__construct( $id );
		$this->slug        = 'database_repair';
		$this->name        = __( 'Repair database tables', 'digital-license-manager' );
		$this->description = __( 'Recreate any missing Digital License Manager database tables. Safe to run at any time — existing data is preserved.', 'digital-license-manager' );
		$this->is_one_time = false;
	}

	/**
	 * Standard-type tool — rendered by the Vue admin, no PHP view.
	 *
	 * @return string
	 */
	public function getView() {
		return '';
	}

	/**
	 * Single-step, single-page operation.
	 *
	 * @return array
	 */
	public function getSteps() {
		return [
			1 => [
				'name'  => __( 'Repairing database tables', 'digital-license-manager' ),
				'pages' => 1,
			],
		];
	}

	/**
	 * @return bool
	 */
	public function initProcess() {
		return true;
	}

	/**
	 * Recreate the free plugin's tables, then let extensions repair theirs.
	 *
	 * @param int $step
	 * @param int $page
	 *
	 * @return bool
	 */
	public function doStep( $step, $page ) {
		Schema::create();

		/**
		 * Fires when the admin-triggered database repair runs. Extensions
		 * (e.g. DLM Pro) should hook in here and recreate their own tables
		 * using idempotent dbDelta / CREATE TABLE IF NOT EXISTS statements.
		 */
		do_action( 'dlm_repair_database_tables' );

		return true;
	}
}
