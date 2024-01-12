<?php
/**
 * Storipress
 *
 * @package Storipress
 */

namespace Storipress\Storipress\Actions;

use WP_Post;

/**
 * Post deleted hook action.
 *
 * @since 0.0.14
 */
class Post_Deleted extends Action {

	/**
	 * Webhook topic.
	 *
	 * @var string
	 */
	public $topic = 'post_deleted';

	/**
	 * The official document link.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/deleted_post/
	 */
	public function register(): void {
		add_action( 'deleted_post', array( &$this, 'handle' ), 10, 2 );
	}

	/**
	 * Hook action.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_POST $post Post object.
	 * @return void
	 */
	public function handle( $post_id, $post ): void {
		if ( 'post' !== $post->post_type ) {
			return;
		}

		$this->send(
			array(
				'post_id' => $post_id,
			)
		);
	}
}
