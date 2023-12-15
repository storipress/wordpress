<?php
/**
 * Storipress
 *
 * @package Storipress
 */

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
