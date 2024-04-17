<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress\Triggers;

use Storipress;
use WP_Post;

/**
 * The yoast seo data trigger.
 *
 * @since 0.0.14
 */
final class Update_Yoast_Seo_Metadata extends Trigger {

	/**
	 * Plugin files.
	 *
	 * @var array<int, string>
	 */
	public $files = array(
		'wordpress-seo/wp-seo.php',
	);

	/**
	 * The post id.
	 *
	 * @var int
	 */
	public $post_id;

	/**
	 * The seo title.
	 *
	 * @var array{
	 *    seo_title?: string,
	 *    seo_description?: string,
	 *    og_title?: string,
	 *    og_description?: string,
	 *    og_image_id?: int
	 * }
	 */
	public $options;

	/**
	 * The seo description.
	 *
	 * @var string|null
	 */
	public $description;

	/**
	 * Constructor.
	 *
	 * @param int                                                                                                                 $post_id The post id.
	 * @param array{seo_title?: string, seo_description?: string, og_title?: string, og_description?: string, og_image_id?: int } $options The seo options.
	 */
	public function __construct( int $post_id, array $options ) {
		$this->post_id = $post_id;

		$this->options = $options;
	}

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
		if ( isset( $this->options['seo_title'] ) ) {
			update_metadata( 'post', $this->post_id, '_yoast_wpseo_title', $this->options['seo_title'] );
		}

		if ( isset( $this->options['seo_description'] ) ) {
			update_metadata( 'post', $this->post_id, '_yoast_wpseo_metadesc', $this->options['seo_description'] );
		}

		if ( isset( $this->options['og_title'] ) ) {
			update_metadata( 'post', $this->post_id, '_yoast_wpseo_opengraph-title', $this->options['og_title'] );
		}

		if ( isset( $this->options['og_description'] ) ) {
			update_metadata( 'post', $this->post_id, '_yoast_wpseo_opengraph-description', $this->options['og_description'] );
		}

		if ( isset( $this->options['og_image_id'] ) ) {
			if ( -1 === $this->options['og_image_id'] ) {
				delete_metadata( 'post', $this->post_id, '_yoast_wpseo_opengraph-image-id' );

				delete_metadata( 'post', $this->post_id, '_yoast_wpseo_opengraph-image' );
			} else {
				$post = get_post( $this->options['og_image_id'] );

				if ( $post instanceof WP_Post && 'attachment' === $post->post_type ) {
					update_metadata( 'post', $this->post_id, '_yoast_wpseo_opengraph-image-id', (string) $this->options['og_image_id'] );

					update_metadata( 'post', $this->post_id, '_yoast_wpseo_opengraph-image', $post->guid );
				}
			}
		}

		return array();
	}
}
