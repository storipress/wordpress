<?php
/**
 * Storipress
 *
 * @package Storipress
 */

use Storipress\Storipress\Action_Handler;
use Storipress\Storipress\Core;
use Storipress\Storipress\Trigger_Handler;

/**
 * Plugin class.
 */
final class Storipress {
	/**
	 * Plugin version.
	 *
	 * @var string
	 *
	 * @since 0.0.2
	 */
	public $version = '0.0.18';

	/**
	 * Plugin build version.
	 *
	 * @var integer
	 *
	 * @since 0.0.10
	 */
	protected $build = 18;

	/**
	 * Instance of this class.
	 *
	 * @var Storipress|null
	 *
	 * @since 0.0.1
	 */
	protected static $instance = null;

	/**
	 * Helper class.
	 *
	 * @var Core
	 *
	 * @since 0.0.12
	 */
	public $core;

	/**
	 * Trigger class.
	 *
	 * @var Trigger_Handler
	 *
	 * @since 0.0.12
	 */
	public $trigger;

	/**
	 * Action class.
	 *
	 * @var Action_Handler
	 *
	 * @since 0.0.12
	 */
	public $action;

	/**
	 * Hook into WP Core.
	 */
	public function __construct() {
		$this->core = new Core();

		$this->trigger = new Trigger_Handler();

		$this->action = new Action_Handler();

		add_action( 'admin_menu', array( &$this, 'register_menu' ) );

		add_action( 'rest_api_init', array( &$this, 'register_routes' ) );

		add_action( 'current_screen', array( &$this, 'callback' ) );

		add_filter( 'wp_kses_allowed_html', array( &$this, 'register_allowed_html' ), 10, 2 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return Storipress
	 *
	 * @since 0.0.1
	 */
	public static function instance(): Storipress {
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
	 *
	 * @since 0.0.1
	 */
	public function callback() {
		$screen = get_current_screen();

		if ( null === $screen || 'export' !== $screen->id ) {
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
	 *
	 * @since 0.0.1
	 */
	public function register_menu() {
		$nonce = wp_create_nonce( 'storipress' );

		add_management_page(
			'Export to Storipress',
			'Export to Storipress',
			'manage_options',
			sprintf( 'export.php?type=storipress&sp_nonce=%s', $nonce )
		);

		add_management_page(
			'Connect to Storipress',
			'Connect to Storipress',
			'manage_options',
			'connect_to_storipress',
			array( &$this, 'render_page' )
		);
	}

	/**
	 * Register custom rest routes.
	 *
	 * @return void
	 *
	 * @since 0.0.18
	 */
	public function register_routes() {
		foreach ( get_taxonomies( array( 'name' => 'post_tag' ), 'objects' ) as $taxonomy ) {
			$taxonomy->show_in_rest = true;

			$controller = $taxonomy->get_rest_controller();

			if ( ! $controller ) {
				continue;
			}

			$controller->register_routes();
		}
	}

	/**
	 * Register custom allowed html attributes.
	 *
	 * @param array<string, array<string, bool>> $allowed Allowed HTML tags.
	 * @param array<mixed>|string                $context Context name.
	 * @return array<string, array<string, bool>>
	 *
	 * @since 0.0.16
	 */
	public function register_allowed_html( $allowed, $context ) {
		if ( is_array( $context ) ) {
			return $allowed;
		}

		if ( 'post' === $context ) {
			$tags = array( 'div', 'p', 'h2', 'h3', 'ul', 'ol' );

			foreach ( $tags as $tag ) {
				$allowed[ $tag ]['data-sp-article'] = true;
			}
		}

		return $allowed;
	}

	/**
	 * Export site content.
	 *
	 * @return void
	 *
	 * @since 0.0.1
	 */
	protected function export() {
		if ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		$filename = sprintf( 'storipress-exports-%04d-%d-%03d.ndjson', $this->build, time(), wp_rand( 0, 999 ) );

		header( 'Content-Encoding: identity' );

		header( 'Content-Type: application/jsonlines+json; charset=utf-8' );

		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$this->export_site_config();

		$this->export_plugins();

		$this->export_users();

		$this->export_categories();

		$this->export_tags();

		$this->export_posts();
	}

	/**
	 * Export site config.
	 *
	 * @return self
	 *
	 * @since 0.0.1
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
	 *
	 * @since 0.0.1
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
						array_merge( $user->to_array(), array( 'caps' => $user->allcaps ) ),
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
	 *
	 * @since 0.0.1
	 */
	protected function export_categories(): Storipress {
		$parents = array( 0 );

		$categories = array();

		do {
			$temp = array();

			foreach ( $parents as $parent ) {
				$items = get_terms(
					array(
						'taxonomy'   => 'category',
						'orderby'    => 'name',
						'order'      => 'ASC',
						'hide_empty' => false,
						'parent'     => $parent,
					)
				);

				if ( ! is_array( $items ) || empty( $items ) ) {
					continue;
				}

				array_push( $categories, ...$items );

				array_push( $temp, ...$items );
			}

			$parents = array_map(
				function ( WP_Term $item ) {
					return $item->term_id;
				},
				$temp
			);

			$parents = array_values( array_unique( $parents ) );
		} while ( ! empty( $parents ) );

		foreach ( $categories as $category ) {
			$this->flush( 'category', $category->to_array() );
		}

		return $this;
	}

	/**
	 * Export tags.
	 *
	 * @return self
	 *
	 * @since 0.0.1
	 */
	protected function export_tags(): Storipress {
		$tags = get_tags(
			array(
				'taxonomy'   => 'post_tag',
				'orderby'    => 'id',
				'hide_empty' => false,
			)
		);

		if ( $tags instanceof WP_Error ) {
			return $this;
		}

		foreach ( $tags as $tag ) {
			$this->flush( 'tag', $tag->to_array() );
		}

		return $this;
	}

	/**
	 * Export posts.
	 *
	 * @return self
	 *
	 * @since 0.0.1
	 */
	protected function export_posts(): Storipress {
		foreach ( $this->get_post_ids() as $idx => $post_id ) {
			$post = get_post( $post_id );

			if ( null === $post ) {
				continue;
			}

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
	 * Export plugins.
	 *
	 * @return self
	 *
	 * @since 0.0.11
	 */
	protected function export_plugins(): Storipress {
		$plugins = get_plugins();

		foreach ( $plugins as $file => $plugin ) {
			$plugin = array_merge(
				$plugin,
				array(
					'IsActive'   => is_plugin_active( $file ),
					'IsPaused'   => is_plugin_paused( $file ),
					'IsInActive' => is_plugin_inactive( $file ),
				)
			);

			$this->flush( 'plugin', $plugin );
		}

		return $this;
	}

	/**
	 * Get all post IDs.
	 *
	 * Because there may be thousands of posts, call get_posts may run out of memory.
	 *
	 * @return Generator<int, int>
	 *
	 * @since 0.0.1
	 */
	protected function get_post_ids(): Generator {
		global $wpdb;

		$result = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} ORDER BY ID DESC LIMIT 0, 1" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		$max_id = (int) ( $result[0] ?? 0 );

		$estimate_time = intval( ceil( $max_id / 400 ) );

		set_time_limit(
			min( max( $estimate_time, 30 ), 900 )
		);

		$step = 100;

		for ( $i = 1; $i <= $max_id; $i += $step ) {
			$lower_bound = $i;

			$upper_bound = $i + $step;

			$post_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT ID FROM {$wpdb->posts} WHERE ID >= %d AND ID < %d ORDER BY ID ASC",
					$lower_bound,
					$upper_bound
				)
			);

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
	 *
	 * @since 0.0.1
	 */
	protected function get_post_data( WP_Post $post ): array {
		$tags = get_the_tags( $post->ID );

		return array(
			'id'          => (string) $post->ID,
			'post_id'     => empty( $post->post_parent ) ? null : (string) $post->post_parent,
			'type'        => $post->post_type,
			'author_id'   => $post->post_author,
			'title'       => $post->post_title, /* do not use get_the_title */
			'slug'        => $post->post_name,
			'excerpt'     => empty( $post->post_excerpt ) ? null : $post->post_excerpt,
			'content'     => wpautop( $post->post_content ),
			'categories'  => wp_list_pluck( get_the_category( $post->ID ), 'term_id' ),
			'tags'        => is_array( $tags ) ? wp_list_pluck( $tags, 'term_id' ) : array(),
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
	 *
	 * @since 0.0.1
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

						return ( false === $terms || $terms instanceof WP_Error ) ? null : wp_list_pluck( $terms, 'term_id' );

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
	 *
	 * @since 0.0.1
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

	/**
	 * Render the admin submenu page.
	 *
	 * @return void
	 *
	 * @since 0.0.12
	 */
	public function render_page() {
		add_action( 'storiress_admin_menu_content', array( &$this, 'add_menu_content' ) );

		require_once __DIR__ . '/templates/page.php';
	}

	/**
	 * Callback for the current screen.
	 *
	 * @param string $menu The current menu.
	 * @return void
	 *
	 * @since 0.0.12
	 */
	public function add_menu_content( $menu ) {
		switch ( $menu ) {
			case 'home':
				require_once __DIR__ . '/templates/menu/home.php';
				break;
		}
	}
}
