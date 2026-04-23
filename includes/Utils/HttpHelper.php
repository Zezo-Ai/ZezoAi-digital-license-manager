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

defined( 'ABSPATH' ) || exit;

/**
 * Class HttpHelper
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class HttpHelper {

	/**
	 * Return the real client IP address.
	 *
	 * Reads proxy-forwarded headers from `$_SERVER` (not `getenv()`, which
	 * under PHP-FPM doesn't receive HTTP_* headers by default), prefers the
	 * first public IP in a forwarded chain (so private proxy hops like k8s
	 * pod IPs are skipped), and validates every candidate.
	 *
	 * Short-circuit with the `dlm_pre_client_ip` filter; post-process with
	 * `dlm_client_ip` (receives the resolved IP and the header it came from).
	 *
	 * @return string Empty string when no valid IP is found.
	 */
	public static function clientIp() {

		$pre = apply_filters( 'dlm_pre_client_ip', null );
		if ( is_string( $pre ) && '' !== $pre ) {
			return $pre;
		}

		$headers = [
			'HTTP_CF_CONNECTING_IP', // Cloudflare
			'HTTP_TRUE_CLIENT_IP',   // Cloudflare Enterprise / Akamai
			'HTTP_X_REAL_IP',        // Traefik / nginx — explicit client IP
			'HTTP_X_FORWARDED_FOR',  // Standard, may be comma-separated chain
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'HTTP_CLIENT_IP',
			'REMOTE_ADDR',
		];

		$private_fallback = '';

		foreach ( $headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}
			$raw = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );

			// Leftmost entry in a forwarded chain is the original client.
			foreach ( array_map( 'trim', explode( ',', $raw ) ) as $candidate ) {
				if ( '' === $candidate ) {
					continue;
				}
				// Prefer the first public IP — skips k8s pod IPs, LAN hops, etc.
				if ( filter_var( $candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return apply_filters( 'dlm_client_ip', $candidate, $header );
				}
				// Remember the first private-but-valid IP in case no public IP is found.
				if ( '' === $private_fallback && filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
					$private_fallback = $candidate;
				}
			}
		}

		return apply_filters( 'dlm_client_ip', $private_fallback, 'fallback' );
	}

	/**
	 * Return the client user agent
	 * @return string|null
	 */
	public static function userAgent() {
		return isset( $_SERVER["HTTP_USER_AGENT"] ) ? sanitize_text_field( wp_unslash( $_SERVER["HTTP_USER_AGENT"] ) ) : null;
	}

	/**
	 * Return's the request method
	 * @return string|null
	 */
	public static function requestMethod() {
		return isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : null;
	}

	/**
	 * Returns the request uri
	 * @return string|null
	 */
	public static function requestUri() {
		return isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : null;
	}

	/**
	 * Redirects to specific url
	 * @return void
	 */
	public static function redirect( $url ) {

		if ( ! $url ) {
			return;
		}

		wp_redirect( $url );
		exit;

	}

}
