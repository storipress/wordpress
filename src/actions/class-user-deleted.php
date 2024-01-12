<?php
/**
 * Storipress
 *
 * @package Storipress
 */

namespace Storipress\Storipress\Actions;

/**
 * User deleted hook action.
 *
 * @since 0.0.14
 */
class User_Deleted extends Action {

	/**
	 * Webhook topic.
	 *
	 * @var string
	 */
	public $topic = 'user_deleted';

	/**
	 * The official document link.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/deleted_user/
	 */
	public function register(): void {
		add_action( 'deleted_user', array( &$this, 'handle' ), 10, 2 );
	}

	/**
	 * Hook action.
	 *
	 * @param int      $user_id ID of the deleted user.
	 * @param int|null $reassign ID of the user to reassign posts and links to. Default null, for no reassignment.
	 * @return void
	 */
	public function handle( $user_id, $reassign ): void {
		$this->send(
			array(
				'user_id'  => $user_id,
				'reassign' => $reassign,
			)
		);
	}
}
