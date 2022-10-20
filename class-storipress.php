<?php
/**
 * Storipress Exporter
 *
 * @package           Storipress Exporter
 * @author            Storipress
 * @copyright         2021 Albion Media Pty. Ltd.
 * @license           GPL-3.0-or-later
 */

/**
 * Plugin class.
 *
 * @package Storipress Exporter
 * @author  Storipress
 */
final class Storipress {
	/**
	 * Plugin version.
	 *
	 * @since 0.0.2
	 *
	 * @var string
	 */
	protected $version = '0.0.7';

	/**
	 * Instance of this class.
	 *
	 * @since 0.0.1
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
	 * @since 0.0.1
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

		if (
			! isset( $_GET['type'], $_GET['sp_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_GET['sp_nonce'] ), 'storipress' )
		) {
			return;
		}

		if ( 'storipress' !== $_GET['type'] ) {
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
		$nonce = wp_create_nonce( 'storipress' );

		add_management_page(
			'Export to Storipress',
			'Export to Storipress',
			'manage_options',
			sprintf( 'export.php?type=storipress&sp_nonce=%s', $nonce )
		);
	}

	/**
	 * Export site content.
	 *
	 * @return void
	 */
	protected function export() {
		if ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		$filename = sprintf( 'storipress-exports-%d-%03d.ndjson', time(), wp_rand( 0, 999 ) );

		header( 'Content-Encoding: identity' );

		header( 'Content-Type: application/jsonlines+json; charset=utf-8' );

		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$this->export_site_config();

		$this->export_users();

		$this->export_categories();

		$this->export_tags();

		$this->export_posts();
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

		$data = array(
			'uploads_url' => wp_get_upload_dir()['baseurl'],
		);

		foreach ( $fields as $field ) {
			$data[ $field ] = get_bloginfo( $field );
		}

		$this->flush( 'site', $data, false );

		return $this;
	}

	/**
	 * Export users.
	 *
	 * @return self
	 */
	protected function export_users(): Storipress {
		$result = count_users();

		$per_page = 50;

		$total = intval( ceil( $result['total_users'] / $per_page ) );

		for ( $page = 1; $page <= $total; ++$page ) {
			/**
			 * Array of WP_User object.
			 *
			 * @var WP_User[] $users
			 */

			$users = get_users(
				array(
					'fields'  => 'all_with_meta',
					'number'  => $per_page,
					'paged'   => $page,
					'orderby' => 'ID',
				)
			);

			foreach ( $users as $user ) {
				$this->flush(
					'user',
					array_diff_key(
						$user->to_array(),
						array_flip( array( 'user_pass' ) )
					)
				);
			}
		}

		return $this;
	}

	/**
	 * Export categories.
	 *
	 * @return self
	 */
	protected function export_categories(): Storipress {
		$categories = get_terms(
			array(
				'taxonomy'   => 'category',
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
			)
		);

		uasort(
			$categories,
			function ( $left, $right ) {
				return $left->parent <=> $right->parent;
			}
		);

		foreach ( $categories as $category ) {
			$this->flush( 'category', $category->to_array() );
		}

		return $this;
	}

	/**
	 * Export tags.
	 *
	 * @return self
	 */
	protected function export_tags(): Storipress {
		$tags = get_tags(
			array(
				'taxonomy'   => 'post_tag',
				'orderby'    => 'id',
				'hide_empty' => false,
			)
		);

		foreach ( $tags as $tag ) {
			$this->flush( 'tag', $tag->to_array() );
		}

		return $this;
	}

	/**
	 * Export posts.
	 *
	 * @return self
	 */
	protected function export_posts(): Storipress {
		foreach ( $this->get_post_ids() as $idx => $post_id ) {
			$post = get_post( $post_id );

			setup_postdata( $post );

			$this->flush(
				$post->post_type,
				array_merge(
					$this->get_post_data( $post ),
					$this->get_post_taxonomies( $post )
				)
			);

			if ( 0 === $idx % 500 ) {
				wp_cache_flush();
			}
		}

		return $this;
	}

	/**
	 * Get all post IDs.
	 *
	 * Because there may be thousands of posts, call get_posts may run out of memory.
	 *
	 * @return Generator
	 */
	protected function get_post_ids(): Generator {
		global $wpdb;

		$result = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} ORDER BY ID DESC LIMIT 0, 1" ); /* db call ok; no-cache ok */

		$max_id = (int) $result[0] ?? 0;

		$estimate_time = intval( ceil( $max_id / 400 ) );

		set_time_limit(
			min( max( $estimate_time, 30 ), 900 )
		);

		$step = 100;

		for ( $i = 1; $i <= $max_id; $i += $step ) {
			$lower_bound = $i;

			$upper_bound = $i + $step;

			$post_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE ID >= %d AND ID < %d ORDER BY ID ASC",
					$lower_bound,
					$upper_bound
				)
			); /* db call ok; no-cache ok */

			foreach ( $post_ids as $post_id ) {
				yield (int) $post_id;
			}
		}
	}

	/**
	 * Get post data for export.
	 *
	 * @param WP_Post $post WP_Post object.
	 *
	 * @return mixed[]
	 */
	protected function get_post_data( WP_Post $post ): array {
		return array(
			'id'          => (string) $post->ID,
			'post_id'     => empty( $post->post_parent ) ? null : (string) $post->post_parent,
			'type'        => $post->post_type,
			'author_id'   => $post->post_author,
			'title'       => $post->post_title, /* do not use get_the_title */
			'slug'        => $post->post_name,
			'excerpt'     => empty( $post->post_excerpt ) ? null : $post->post_excerpt,
			'content'     => wpautop( $post->post_content ),
			'status'      => get_post_status( $post ),
			'commentable' => $post->comment_status,
			'password'    => empty( $post->post_password ) ? null : $post->post_password,
			'mime_type'   => empty( $post->post_mime_type ) ? null : $post->post_mime_type,
			'created_at'  => get_the_date( 'U', $post ),
			'updated_at'  => get_the_modified_date( 'U', $post ),
			'permalink'   => str_replace( home_url(), '', get_permalink( $post ) ),
			'metadata'    => get_post_custom( $post->ID ),
		);
	}

	/**
	 * Get post taxonomies for export.
	 *
	 * @param WP_Post $post WP_Post object.
	 *
	 * @return mixed[]
	 */
	protected function get_post_taxonomies( WP_Post $post ): array {
		$taxonomies = get_taxonomies(
			array(
				'object_type' => array( get_post_type( $post ) ),
			)
		);

		return array_map(
			function ( $taxonomy ) use ( $post ) {
				switch ( $taxonomy ) {
					case 'category':
					case 'post_tag':
						$terms = get_the_terms( $post, $taxonomy );

						return false === $terms ? null : wp_list_pluck( $terms, 'term_id' );

					case 'post_format':
						$format = get_post_format( $post );

						if ( false === $format ) {
							return null;
						}

						return $format;

					default:
						return $taxonomy;
				}
			},
			$taxonomies
		);
	}

	/**
	 * Immediately flush the buffer.
	 *
	 * @param string  $type The type of the data, e.g. post, attachment.
	 * @param mixed[] $data The data.
	 * @param bool    $prepend_newline Insert new line symbol before each line.
	 *
	 * @return void
	 */
	protected function flush( string $type, array $data, bool $prepend_newline = true ) {
		$payload = array(
			'version' => $this->version,
			'type'    => $type,
			'data'    => $data,
		);

		if ( $prepend_newline ) {
			echo PHP_EOL;
		}

		echo wp_json_encode( $payload );

		flush();
	}
}
