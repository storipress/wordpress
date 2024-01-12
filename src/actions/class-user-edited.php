<?php
/**
 * Storipress
 *
 * @package Storipress
 */

namespace Storipress\Storipress\Actions;

/**
 * User edited hook action.
 *
 * @since 0.0.14
 */
class User_Edited extends Action {

	/**
	 * Webhook topic.
	 *
	 * @var string
	 */
	public $topic = 'user_edited';

	/**
	 * The official document link.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/wp_update_user/
	 */
	public function register(): void {
		add_action( 'wp_update_user', array( &$this, 'handle' ) );
	}

	/**
	 * Hook action.
	 *
	 * @param int $user_id The user id.
	 * @return void
	 */
	public function handle( $user_id ): void {
		$this->send(
			array(
				'user_id' => $user_id,
			)
		);
	}
}
