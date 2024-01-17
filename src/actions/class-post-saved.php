<?php
/**
 * Storipress
 *
 * @package Storipress
 */

namespace Storipress\Storipress\Actions;

/**
 * Post created/updated/trashed/restored hook action.
 *
 * @since 0.0.14
 */
class Post_Saved extends Action {

	/**
	 * Webhook topic.
	 *
	 * @var string
	 */
	public $topic = 'post_saved';

	/**
	 * The official document link.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/save_post/
	 */
	public function register(): void {
		add_action( 'save_post_post', array( &$this, 'handle' ) );
	}

	/**
	 * Hook action.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function handle( $post_id ): void {
		$this->send(
			array(
				'post_id' => $post_id,
			)
		);
	}
}
