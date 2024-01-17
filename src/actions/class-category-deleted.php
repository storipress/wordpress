<?php
/**
 * Storipress
 *
 * @package Storipress
 */

namespace Storipress\Storipress\Actions;

/**
 * Category deleted hook action.
 *
 * @since 0.0.14
 */
class Category_Deleted extends Action {

	/**
	 * Webhook topic.
	 *
	 * @var string
	 */
	public $topic = 'category_deleted';

	/**
	 * The official document link.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/delete_taxonomy/
	 */
	public function register(): void {
		add_action( 'delete_category', array( &$this, 'handle' ) );
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
