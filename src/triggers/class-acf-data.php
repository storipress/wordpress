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
	 * Plugin files.
	 *
	 * @var array<int, string>
	 */
	public $files = array(
		'advanced-custom-fields/acf.php',
		'advanced-custom-fields-pro/acf.php',
	);

	/**
	 * {@inheritDoc}
	 */
	public function is_activated(): bool {
		if ( ! Storipress::instance()->core->is_connected() ) {
			return false;
		}

		// Needs to include the plugin function on a non-admin page.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $this->files as $path ) {
			if ( Storipress::instance()->core->is_plugin_activate( $path ) ) {
				return true;
			}
		}

		return false;
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
				'post_type'   => array( 'acf-field' ),
			)
		);

		$posts = array_filter(
			$posts,
			function ( $post ) {
				if ( empty( $post->post_title ) || empty( $post->post_excerpt ) ) {
					return false;
				}

				if ( preg_match( '/^[a-z_][a-z0-9_]{2,31}$/', $post->post_excerpt ) === 0 ) {
					return false;
				}

				return true;
			}
		);

		return array_map(
			function ( $post ) {
				/**
				 * The post_content value.
				 *
				 * @var array{
				 *  location?: array<array-key, array<array-key, object{
				 *    param: string,
				 *    operator: string,
				 *    value: string,
				 *  }>>,
				 *  instructions?: string,
				 *  placeholder?: string,
				 *  field_type?: string,
				 *  taxonomy?: string,
				 *  type?: string,
				 *  required?: int,
				 *  choices?: array<string, string>,
				 *  multiple?: int,
				 *  min?: int|string,
				 *  max?: int|string,
				 * } $attributes
				 */
                // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
				$attributes = unserialize( $post->post_content );

				return array(
					'id'         => (string) $post->ID,
					// Acf field group ID, which will be null if it's a field group.
					'group_id'   => empty( $post->post_parent ) ? null : (string) $post->post_parent,
					// This is an ACF type, which will be either 'acf-field' or 'acf-field-group'.
					'acf_type'   => $post->post_type,
					// Field label.
					'label'      => $post->post_title,
					// Acf field name.
					'name'       => $post->post_excerpt,
					// The detailed settings, including types, validation, etc.
					'attributes' => array(
						// Group location rules setting.
						'location'    => $attributes['location'] ?? null,
						// Instructions for authors. Shown when submitting data.
						'description' => $attributes['instructions'] ?? null,
						// Appears within the input.
						'placeholder' => $attributes['placeholder'] ?? null,
						// The appearance of this field. (for taxonomy).
						'field_type'  => $attributes['field_type'] ?? null,
						// The taxonomy to be displayed. (for taxonomy).
						'taxonomy'    => $attributes['taxonomy'] ?? null,
						// The field type.
						'type'        => $attributes['type'] ?? null,
						'required'    => $attributes['required'] ?? null,
						// Options for select and checkbox. (for select or checkbox).
						'choices'     => $attributes['choices'] ?? null,
						// Allow content editors to select multiple values. (for select).
						'multiple'    => $attributes['multiple'] ?? null,
						// Minimum number. (for number).
						'min'         => $attributes['min'] ?? null,
						// Maximum number. (for number).
						'max'         => $attributes['max'] ?? null,
					),
					'created_at' => get_the_date( 'U', $post ),
					'updated_at' => get_the_modified_date( 'U', $post ),
				);
			},
			array_values( $posts )
		);
	}
}
