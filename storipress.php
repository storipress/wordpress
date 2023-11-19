<?php
/**
 * Storipress
 *
 * @package           Storipress
 * @author            Storipress
 * @copyright         Albion Media Pty. Ltd.
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Storipress
 * Plugin URI:        https://github.com/storipress/wp-storipress-exporter
 * Description:       Experience the full power of Storipress on WordPress.
 * Version:           0.0.12
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Storipress
 * Author URI:        https://storipress.com
 * Text Domain:       storipress
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

// Abort if calling this file directly.
if ( ! defined( 'WPINC' ) ) {
	die();
}

if ( version_compare( PHP_VERSION, '7.2.0', '<' ) ) {
	wp_die( 'Storipress Exporter requires PHP 7.2 or later.' );
}

spl_autoload_register(
	function ( string $class_name ) {
		if ( ! str_contains( $class_name, 'Storipress' ) ) {
			return;
		}

		$path = str_replace(
			array( 'storipress', '_', '\\' ),
			array( '', '-', DIRECTORY_SEPARATOR ),
			strtolower( $class_name )
		);

		$file = sprintf(
			'%s/src/%s/class-%s.php',
			__DIR__,
			dirname( $path ),
			basename( $path )
		);

		$realpath = realpath( $file );

		if ( ! is_string( $realpath ) ) {
			return;
		}

		require_once $realpath;
	}
);

require_once __DIR__ . '/class-storipress.php';

Storipress::instance();
