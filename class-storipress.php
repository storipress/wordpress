<?php
/**
 * WordPress to Storipress exporter
 *
 * @package   Storipress
 * @author    Kevin(kevin@storipress.com)
 * @license   proprietary
 * @link      https://storipress.com
 * @copyright 2021 Albion Media Pty. Ltd.
 */

/**
 * Plugin class.
 *
 * @package Storipress
 * @author  Kevin(kevin@storipress.com)
 */
final class Storipress {
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $version = '1.0.0';

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var Storipress
	 */
	protected static $instance = null;

	/**
	 * Hook into WP Core.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'register_menu' ) );
		add_action( 'current_screen', array( &$this, 'callback' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return Storipress
	 *
	 * @since 1.0.0
	 */
	public static function get_instance(): Storipress {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Listens for page callback, intercepts and runs export.
	 *
	 * @return void
	 */
	public function callback() {
		if ( get_current_screen()->id !== 'export' ) {
			return;
		}

		if ( ! isset( $_GET['type'] ) || 'storipress' !== $_GET['type'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->export();

		exit();
	}

	/**
	 * Add menu option to Tools list.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_management_page( 'Export to Storipress', 'Export to Storipress', 'manage_options', 'export.php?type=storipress' );
	}

	/**
	 * Export site content.
	 *
	 * @return void
	 */
	protected function export() {
		do_action( 'storipress_export' );

		ob_clean();

		$filename = sprintf( 'storipress-export-%s.ndjson', date( 'Y-m-d-H-i-s' ) );

		header( 'Content-Type: application/jsonlines+json; charset=utf-8' );

		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$this->export_site_config()->export_posts();
	}

	/**
	 * Export site config.
	 *
	 * @return self
	 */
	protected function export_site_config(): Storipress {
		$fields = array(
			'name',
			'description',
			'url',
		);

		$data = array();

		foreach ( $fields as $field ) {
			$data[ $field ] = get_bloginfo( $field );
		}

		$this->flush( 'site', $data );

		return $this;
	}

	/**
	 * Export posts.
	 *
	 * @return self
	 */
	protected function export_posts(): Storipress {
		global $post;

		foreach ( $this->get_post_ids() as $post_id ) {
			$post = get_post( $post_id );

			setup_postdata( $post );

			$this->flush(
				$post->post_type,
				array_merge(
					$this->get_post_data( $post ),
					$this->get_post_taxonomies( $post )
				)
			);
		}

		return $this;
	}

	/**
	 * Get all post IDs.
	 *
	 * Because there may be thousands of posts, call get_posts may run out of memory.
	 *
	 * @return string[]
	 */
	protected function get_post_ids(): array {
		global $wpdb;

		return $wpdb->get_col( "SELECT ID FROM $wpdb->posts ORDER BY ID ASC" );
	}

	/**
	 * Get post data for export.
	 *
	 * @param WP_Post $post
	 *
	 * @return mixed[]
	 */
	protected function get_post_data( WP_Post $post ): array {
		return array(
			'id'          => (string) $post->ID,
			'post_id'     => empty( $post->post_parent ) ? null : (string) $post->post_parent,
			'type'        => $post->post_type,
			'author_id'   => $post->post_author,
			'title'       => $post->post_title, // do not use get_the_title
			'slug'        => $post->post_name,
			'excerpt'     => apply_filters( 'get_the_excerpt', $post->post_excerpt ),
			'content'     => apply_filters( 'the_content', $post->post_content ),
			'status'      => get_post_status( $post ),
			'commentable' => $post->comment_status,
			'password'    => $post->post_password ?: null,
			'mime_type'   => get_post_mime_type( $post ) ?: null,
			'created_at'  => get_the_date( 'U', $post ),
			'updated_at'  => get_the_modified_date( 'U', $post ),
			'permalink'   => str_replace( home_url(), '', get_permalink( $post ) ),
			'metadata'    => get_post_custom( $post->ID ),
		);
	}

	/**
	 * Get post taxonomies for export.
	 *
	 * @param WP_Post $post
	 *
	 * @return mixed[]
	 */
	protected function get_post_taxonomies( WP_Post $post ): array {
		$taxonomies = get_taxonomies(
			array(
				'object_type' => array( get_post_type( $post ) ),
			)
		);

		return array_map( function ( $taxonomy ) use ( $post ) {
			switch ( $taxonomy ) {
				case 'category':
				case 'post_tag':
					$terms = get_the_terms( $post, $taxonomy );

					return $terms === false ? null : wp_list_pluck( $terms, 'name' );

				case 'post_format':
					return get_post_format( $post ) ?: null;

				default:
					return $taxonomy;
			}
		}, $taxonomies );
	}

	/**
	 * Immediately  flush the buffer.
	 *
	 * @param string  $type
	 * @param mixed[] $data
	 *
	 * @return void
	 */
	protected function flush( string $type, array $data ) {
		$payload = array(
			'type' => $type,
			'data' => $data,
		);

		echo json_encode( $payload ) . PHP_EOL;

		ob_flush();

		flush();
	}
}
