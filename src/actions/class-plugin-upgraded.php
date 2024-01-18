<?php
/**
 * Storipress
 *
 * @package Storipress
 */

namespace Storipress\Storipress\Actions;

use Storipress;
use WP_Upgrader;

/**
 * Plugin upgrade hook action.
 *
 * @since 0.0.14
 */
class Plugin_Upgraded extends Action {

	/**
	 * Webhook topic.
	 *
	 * @var string
	 */
	public $topic = 'plugin_upgraded';

	/**
	 * The official document link.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/upgrader_process_complete/
	 */
	public function register(): void {
		add_action( 'upgrader_process_complete', array( &$this, 'handle' ), 10, 2 );
	}

	/**
	 * Hook action.
	 *
	 * @param WP_Upgrader                                                        $upgrader Instance.
	 * @param array{action: string, type: string, bulk: bool, plugins: string[]} $hook_extra Array of bulk item update data.
	 * @return void
	 */
	public function handle( $upgrader, $hook_extra ): void {
		if ( 'update' === $hook_extra['action'] && 'plugin' === $hook_extra['type'] ) {
			foreach ( $hook_extra['plugins'] as $plugin_file ) {
				if ( 'storipress/storipress.php' === $plugin_file ) {
					$this->send(
						Storipress::instance()->core->get_site_data()
					);
				}
			}
		}
	}
}
