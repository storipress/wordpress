<?php
/**
 * WordPress to Storipress exporter
 *
 * @package   Storipress
 * @author    Kevin(kevin@storipress.com)
 * @license   proprietary
 * @link      https://storipress.com
 * @copyright 2021 Albion Media Pty. Ltd.
 *
 * @storipress
 * Plugin Name: Storipress
 * Plugin URI:  https://storipress.com
 * Description: Plugin to export your WordPress blog so you can import it into your Storipress publication
 * Version:     1.0.0
 * Author:      Kevin(kevin@storipress.com)
 * License:     proprietary
 */

// Abort if calling this file directly.
if ( ! defined( 'WPINC' ) ) {
	die();
}

if ( version_compare( PHP_VERSION, '7.0.0', '<' ) ) {
	wp_die( 'Storipress Export requires PHP 7.0 or later.' );
}

require_once __DIR__ . '/class-storipress.php';

Storipress::get_instance();
