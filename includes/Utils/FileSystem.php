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

namespace IdeoLogix\DigitalLicenseManager\Utils;

use IdeoLogix\DigitalLicenseManager\Traits\Singleton;
use WP_Filesystem_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Class FileSystem
 *
 * Wraps WP_Filesystem for all file operations, providing compatibility
 * with stream wrappers (e.g. S3-Uploads) and consistent error handling.
 *
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class FileSystem {

	use Singleton;

	/**
	 * The WP_Filesystem instance
	 * @var WP_Filesystem_Base|null
	 */
	private $fs = null;

	/**
	 * Initialize the filesystem.
	 */
	protected function init() {
		$this->loadFilesystem();
	}

	/**
	 * Lazy-load the WP_Filesystem global.
	 *
	 * @return WP_Filesystem_Base|null
	 */
	private function loadFilesystem() {
		if ( $this->fs !== null ) {
			return $this->fs;
		}

		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$this->fs = $wp_filesystem;

		return $this->fs;
	}

	/**
	 * Check if a file or directory exists.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function exists( $path ) {
		return $this->fs->exists( $path );
	}

	/**
	 * Check if path is a directory.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isDir( $path ) {
		return $this->fs->is_dir( $path );
	}

	/**
	 * Check if path is a file.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isFile( $path ) {
		return $this->fs->is_file( $path );
	}

	/**
	 * Check if a path is writable.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public function isWritable( $path ) {
		return $this->fs->is_writable( $path );
	}

	/**
	 * Get the file size.
	 *
	 * @param string $path
	 *
	 * @return int|false
	 */
	public function size( $path ) {
		return $this->fs->size( $path );
	}

	/**
	 * Create a directory.
	 *
	 * @param string    $path
	 * @param int|false $chmod
	 * @param string    $chown
	 * @param string    $chgrp
	 *
	 * @return bool
	 */
	public function mkdir( $path, $chmod = false, $chown = false, $chgrp = false ) {
		return $this->fs->mkdir( $path, $chmod, $chown, $chgrp );
	}

	/**
	 * Delete a file or directory.
	 *
	 * @param string $path
	 * @param bool   $recursive
	 * @param string $type
	 *
	 * @return bool
	 */
	public function delete( $path, $recursive = false, $type = false ) {
		return $this->fs->delete( $path, $recursive, $type );
	}

	/**
	 * Write contents to a file.
	 *
	 * @param string    $path
	 * @param string    $content
	 * @param int|false $mode
	 *
	 * @return bool
	 */
	public function putContents( $path, $content, $mode = false ) {
		return $this->fs->put_contents( $path, $content, $mode );
	}

	/**
	 * Read file contents.
	 *
	 * @param string $path
	 *
	 * @return string|false
	 */
	public function getContents( $path ) {
		return $this->fs->get_contents( $path );
	}

	/**
	 * Move a file from source to destination.
	 *
	 * Uses copy + delete as fallback when rename fails (e.g. cross-stream-wrapper moves).
	 *
	 * @param string $source
	 * @param string $dest
	 * @param bool   $overwrite
	 *
	 * @return bool
	 */
	public function move( $source, $dest, $overwrite = false ) {
		return $this->fs->move( $source, $dest, $overwrite );
	}

	/**
	 * Copy a file.
	 *
	 * @param string $source
	 * @param string $dest
	 * @param bool   $overwrite
	 * @param int    $mode
	 *
	 * @return bool
	 */
	public function copy( $source, $dest, $overwrite = false, $mode = false ) {
		return $this->fs->copy( $source, $dest, $overwrite, $mode );
	}

	/**
	 * Set file permissions.
	 *
	 * @param string    $path
	 * @param int|false $mode
	 * @param bool      $recursive
	 *
	 * @return bool
	 */
	public function chmod( $path, $mode = false, $recursive = false ) {
		return $this->fs->chmod( $path, $mode, $recursive );
	}

	/**
	 * Stream a file to the output buffer in chunks.
	 *
	 * Uses fopen/fread/fclose for compatibility with stream wrappers (e.g. S3).
	 *
	 * @param string $path
	 * @param int    $chunkSize Bytes per chunk (default 8KB).
	 *
	 * @return bool
	 */
	public function stream( $path, $chunkSize = 8192 ) {
		$handle = @fopen( $path, 'rb' );
		if ( ! $handle ) {
			return false;
		}

		while ( ! feof( $handle ) ) {
			echo fread( $handle, $chunkSize );
			flush();
		}

		fclose( $handle );

		return true;
	}
}
