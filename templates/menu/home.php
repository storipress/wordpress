<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

$core = \Storipress::instance()->core;

try {
	$url = $core->is_connected()
		? $core->get_app_url()
		: $core->get_install_url();

	require __DIR__ . '/../dist/home.html';
} catch ( Exception $e ) {
	printf( 'Something went wrong, error code: %d', esc_html( $e->getCode() ) );
}
