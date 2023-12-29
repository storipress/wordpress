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
final class Update_Yoast_Seo_Metadata extends Trigger {

	/**
	 * Plugin file.
	 *
	 * @var string
	 */
	public $file = 'wordpress-seo/wp-seo.php';

	/**
	 * The post id.
	 *
	 * @var int
	 */
	public $post_id;

	/**
	 * The seo title.
	 *
	 * @var string|null
	 */
	public $title;

	/**
	 * The seo description.
	 *
	 * @var string|null
	 */
	public $description;

	/**
	 * Constructor.
	 *
	 * @param int         $post_id The post id.
	 * @param string|null $title The seo title.
	 * @param string|null $description The seo description.
	 */
	public function __construct( int $post_id, string $title = null, string $description = null ) {
		$this->title = $title;

		$this->description = $description;

		$this->post_id = $post_id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(): bool {
		if ( ! Storipress::instance()->core->is_connected() ) {
			return false;
		}

		// Needs to include the plugin function on a non-admin page.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Ensure yoast seo is installed.
		if ( ! in_array( $this->file, array_keys( get_plugins() ), true ) ) {
			return false;
		}

		// Ensure yoast seo is active.
		if ( ! is_plugin_active( $this->file ) ) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function run(): array {
		if ( ! empty( $this->title ) ) {
			update_metadata( 'post', $this->post_id, '_yoast_wpseo_title', $this->title );
		}

		if ( ! empty( $this->description ) ) {
			update_metadata( 'post', $this->post_id, '_yoast_wpseo_metadesc', $this->description );
		}

		return array();
	}
}
