<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress;

use Storipress;
use Storipress\Storipress\Errors\Internal_Error_Exception;
use WP_Application_Passwords;
use WP_Error;

/**
 * The core class.
 *
 * @since 0.0.12
 */
final class Core {

	/**
	 * The option key.
	 *
	 * @var string
	 *
	 * @since 0.0.12
	 */
	public $option_key = 'storipress';

	/**
	 * The option data.
	 *
	 * @var array{
	 *     hash_key?: string,
	 *     client?: string,
	 *     dev?: bool,
	 * }
	 *
	 * @since 0.0.12
	 */
	public $options;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$options = get_option( $this->option_key );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		$this->options = $options;
	}

	/**
	 * Generate the API Key.
	 *
	 * @return string
	 *
	 * @throws Internal_Error_Exception Something went wrong.
	 *
	 * @since 0.0.12
	 */
	public function generate_auth(): string {
		$user = wp_get_current_user();

		$user_id = $user->ID;

		$this->delete_application_password( $user_id );

		$password = WP_Application_Passwords::create_new_application_password(
			$user_id,
			array(
				'name' => 'Storipress',
			)
		);

		if ( $password instanceof WP_Error ) {
			throw new Internal_Error_Exception();
		}

		$this->set_options( array( 'hash_key' => $password[1]['password'] ) );

		$data = wp_json_encode(
			array(
				'version'             => Storipress::instance()->version,
				'token'               => $password[0],
				'hash_key'            => $password[1]['password'],
				'email'               => $user->user_email,
				'username'            => $user->user_login,
				'user_id'             => $user_id,
				'site_name'           => get_bloginfo( 'name' ),
				'url'                 => get_bloginfo( 'url' ),
				'rest_prefix'         => rest_get_url_prefix(),
				'permalink_structure' => get_option( 'permalink_structure' ),
			)
		);

		if ( false === $data ) {
			throw new Internal_Error_Exception();
		}

		return base64_encode( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Delete Storipress application password.
	 *
	 * @param int $user_id User ID.
	 *
	 * @since 0.0.12
	 */
	public function delete_application_password( int $user_id ): bool {
		$passwords = WP_Application_Passwords::get_user_application_passwords( $user_id );

		foreach ( $passwords as $password ) {
			if ( 'Storipress' === $password['name'] ) {
				$deleted = WP_Application_Passwords::delete_application_password( $user_id, $password['uuid'] );

				if ( $deleted instanceof WP_Error ) {
					return false;
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Update the options.
	 *
	 * @param array{ hash_key?: string, client?: string } $value The option value.
	 *
	 * @since 0.0.12
	 */
	public function set_options( array $value ): bool {
		$this->options = array_merge( $this->options, $value );

		return update_option( $this->option_key, $this->options );
	}

	/**
	 * Get the installed URL.
	 *
	 * @since 0.0.12
	 */
	public function get_install_url(): string {
		return add_query_arg(
			array(
				'to'          => 'choose-publication',
				'integration' => 'wordpress',
				'client_id'   => '_',
				'code'        => rawurlencode( $this->generate_auth() ),
			),
			'https://stori.press/redirect'
		);
	}

	/**
	 * Get the app URL.
	 *
	 * @throws Internal_Error_Exception Runtime error.
	 *
	 * @since 0.0.12
	 */
	public function get_app_url(): string {
		if ( ! isset( $this->options['client'] ) ) {
			throw new Internal_Error_Exception();
		}

		return sprintf( 'https://stori.press/%s/', $this->options['client'] );
	}

	/**
	 * Connected or not
	 *
	 * @since 0.0.12
	 */
	public function is_connected(): bool {
		return isset( $this->options['client'] ) && ! empty( $this->options['client'] );
	}
}
