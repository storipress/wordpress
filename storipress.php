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
 * Version:           0.0.18
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

require_once __DIR__ . '/autoload.php';

require_once __DIR__ . '/class-storipress.php';

Storipress::instance();
