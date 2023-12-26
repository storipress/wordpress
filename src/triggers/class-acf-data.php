<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress\Triggers;

use Storipress;

/**
 * The acf data trigger.
 *
 * @since 0.0.14
 */
final class ACF_Data extends Trigger {
	/**
	 * {@inheritDoc}
	 */
	public function validate(): bool {
		return Storipress::instance()->core->is_connected();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array{}
	 */
	public function run(): array {
		$data = array();

		$posts = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => array( 'acf-field-group', 'acf-field' ),
			)
		);

		foreach ( $posts as $post ) {
			$data[] = array(
				'id'         => (string) $post->ID,
				'post_id'    => empty( $post->post_parent ) ? null : (string) $post->post_parent,
				'type'       => $post->post_type,
				'title'      => $post->post_title,
				'slug'       => $post->post_name,
				'excerpt'    => empty( $post->post_excerpt ) ? null : $post->post_excerpt,
				'content'    => $post->post_content,
				'created_at' => get_the_date( 'U', $post ),
				'updated_at' => get_the_modified_date( 'U', $post ),
			);
		}

		return $data;
	}
}
