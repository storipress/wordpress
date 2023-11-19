<?php
/**
 * Storipress
 *
 * @package Storipress
 */

$plugin_name = 'Storipress';

ob_start();

do_action( 'storiress_admin_menu_content', 'home' );

echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
