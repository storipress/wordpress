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
		$posts = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => array( 'acf-field-group', 'acf-field' ),
			)
		);

		$posts = array_filter(
			$posts,
			function ( $post ) {
				return ! empty( $post->post_title ) && ! empty( $post->post_excerpt );
			}
		);

		return array_map(
			function ( $post ) {
                // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
				$content = unserialize( $post->post_content );

				return array(
					'id'         => (string) $post->ID,
					// Acf field group ID, which will be null if it's a field group.
					'group_id'   => empty( $post->post_parent ) ? null : (string) $post->post_parent,
					// This is an ACF type, which will be either 'acf-field' or 'acf-field-group'.
					'acf_type'   => $post->post_type,
					// Field label.
					'label'      => $post->post_title,
					// Field name.
					'name'       => empty( $post->post_excerpt ) ? null : $post->post_excerpt,
					// The detailed settings, including types, validation, etc.
					'attributes' => array(
						// Group location rules setting.
						'location'    => $content['location'] ?? null,
						// Instructions for authors. Shown when submitting data.
						'description' => $content['instructions'] ?? null,
						// Appears within the input.
						'placeholder' => $content['placeholder'] ?? null,
						// The appearance of this field. (for taxonomy).
						'field_type'  => $content['field_type'] ?? null,
						// The taxonomy to be displayed. (for taxonomy).
						'taxonomy'    => $content['taxonomy'] ?? null,
						// The field type.
						'type'        => $content['type'] ?? null,
						'required'    => $content['required'] ?? null,
						// Options for select and checkbox. (for select or checkbox).
						'choices'     => $content['choices'] ?? null,
						// Allow content editors to select multiple values. (for select).
						'multiple'    => $content['multiple'] ?? null,
						// Minimum number. (for number).
						'min'         => $content['min'] ?? null,
						// Maximum number. (for number).
						'max'         => $content['max'] ?? null,
					),
					'created_at' => get_the_date( 'U', $post ),
					'updated_at' => get_the_modified_date( 'U', $post ),
				);
			},
			$posts
		);
	}
}
