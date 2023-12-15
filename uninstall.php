<?php
/**
 * Storipress
 *
 * @package Storipress
 */

// If uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

require_once __DIR__ . '/autoload.php';

$core = new \Storipress\Storipress\Core();

delete_option( $core->option_key );

$user = wp_get_current_user();

$core->delete_application_password( $user->ID );
