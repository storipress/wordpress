<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress;

use Storipress\Storipress\Actions\Action;

/**
 * Action handler.
 *
 * @since 0.0.12
 */
final class Action_Handler {
	/**
	 * The action list.
	 *
	 * @var array<string, Action>
	 *
	 * @since 0.0.12
	 */
	public $actions;

	/**
	 * Constructor.
	 */
	public function __construct() {
		/**
		 * Register list.
		 */
		$this->actions = array();

		$this->register_actions();
	}

	/**
	 * Register the default hooks.
	 *
	 * @return void
	 *
	 * @since 0.0.12
	 */
	public function register_actions() {
		foreach ( $this->actions as $action ) {
			$action->register();
		}
	}

	/**
	 * Unregister the hook.
	 *
	 * @param string $key The key of the action.
	 *
	 * @return void
	 *
	 * @since 0.0.12
	 */
	public function unregister( string $key ) {
		if ( ! isset( $this->actions[ $key ] ) ) {
			return;
		}

		$this->actions[ $key ]->unregister();
	}
}
