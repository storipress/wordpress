<?php
/**
 * Storipress
 *
 * @package Storipress
 */

namespace Storipress\Storipress\Actions;

/**
 * Tag edited hook action.
 *
 * @since 0.0.14
 */
class Tag_Edited extends Action {

	/**
	 * Webhook topic.
	 *
	 * @var string
	 */
	public $topic = 'tag_edited';

	/**
	 * The official document link.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/edited_taxonomy/
	 */
	public function register(): void {
		add_action( 'edited_post_tag', array( &$this, 'handle' ) );
	}

	/**
	 * Hook action.
	 *
	 * @param int $term_id Term ID.
	 * @return void
	 */
	public function handle( $term_id ): void {
		$this->send(
			array(
				'term_id' => $term_id,
			)
		);
	}
}
