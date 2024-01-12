<?php
/**
 * Storipress
 *
 * @package Storipress
 */

namespace Storipress\Storipress\Actions;

use WP_Error;

/**
 * User created hook action.
 *
 * @since 0.0.14
 */
class User_Created extends Action {

	/**
	 * Webhook topic.
	 *
	 * @var string
	 */
	public $topic = 'user_created';

	/**
	 * The official document link.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/edit_user_created_user/
	 */
	public function register(): void {
		add_action( 'edit_user_created_user', array( &$this, 'handle' ) );
	}

	/**
	 * Hook action.
	 *
	 * @param int|WP_Error $user_id ID of the newly created user or WP_Error on failure.
	 * @return void
	 */
	public function handle( $user_id ): void {
		if ( ! is_int( $user_id ) ) {
			return;
		}

		$this->send(
			array(
				'user_id' => $user_id,
			)
		);
	}
}
