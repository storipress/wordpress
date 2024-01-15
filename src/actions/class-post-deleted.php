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
		add_action( 'deleted_post', array( &$this, 'handle' ) );
	}

	/**
	 * Hook action.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function handle( $post_id ): void {
		// Compatible with versions below 5.4. There will only be one parameter: post id.
		$post = get_post( $post_id );

		if ( ! ( $post instanceof WP_POST ) || 'post' !== $post->post_type ) {
			return;
		}

		$this->send(
			array(
				'post_id' => $post_id,
			)
		);
	}
}
