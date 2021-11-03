<?php
/**
 * Storipress Exporter
 *
 * @package           Storipress Exporter
 * @author            Storipress
 * @copyright         2021 Albion Media Pty. Ltd.
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Storipress Exporter
 * Plugin URI:        https://storipress.com
 * Description:       Migrate your WordPress to Storipress just by one-click.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.3
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

if ( version_compare( PHP_VERSION, '7.3.0', '<' ) ) {
	wp_die( 'Storipress Exporter requires PHP 7.3 or later.' );
}

require_once __DIR__ . '/class-storipress.php';

Storipress::get_instance();
