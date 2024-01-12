<?php
/**
 * Storipress
 *
 * @package Storipress
 */

namespace Storipress\Storipress\Actions;

/**
 * Tag created hook action.
 *
 * @since 0.0.14
 */
class Tag_Created extends Action {

	/**
	 * Webhook topic.
	 *
	 * @var string
	 */
	public $topic = 'tag_created';

	/**
	 * The official document link.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/create_taxonomy/
	 */
	public function register(): void {
		add_action( 'create_post_tag', array( &$this, 'handle' ), 10, 2 );
	}

	/**
	 * Hook action.
	 *
	 * @param int $term_id Term ID.
	 * @param int $taxonomy_id Term taxonomy ID.
	 * @return void
	 */
	public function handle( $term_id, $taxonomy_id ): void {
		$this->send(
			array(
				'termId'     => $term_id,
				'taxonomyId' => $taxonomy_id,
			)
		);
	}
}
