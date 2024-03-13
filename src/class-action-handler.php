<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress;

use Storipress\Storipress\Actions\Action;
use Storipress\Storipress\Actions\Category_Created;
use Storipress\Storipress\Actions\Category_Deleted;
use Storipress\Storipress\Actions\Category_Edited;
use Storipress\Storipress\Actions\Plugin_Upgraded;
use Storipress\Storipress\Actions\Post_Deleted;
use Storipress\Storipress\Actions\Post_Saved;
use Storipress\Storipress\Actions\Tag_Created;
use Storipress\Storipress\Actions\Tag_Deleted;
use Storipress\Storipress\Actions\Tag_Edited;
use Storipress\Storipress\Actions\User_Created;
use Storipress\Storipress\Actions\User_Deleted;
use Storipress\Storipress\Actions\User_Edited;

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
		// Prevent webhook firing when calling REST Api.
		if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );

			if ( stripos( $user_agent, 'Storipress' ) !== false ) {
				return;
			}
		}

		$this->register_actions(
			array(
				new Category_Created(),
				new Category_Edited(),
				new Category_Deleted(),
				new Tag_Created(),
				new Tag_Edited(),
				new Tag_Deleted(),
				new User_Created(),
				new User_Edited(),
				new User_Deleted(),
				new Post_Saved(),
				new Post_Deleted(),
				new Plugin_Upgraded(),
			)
		);
	}

	/**
	 * Register the default hooks.
	 *
	 * @param Action[] $actions The action list.
	 * @return void
	 *
	 * @since 0.0.12
	 */
	public function register_actions( array $actions ) {
		foreach ( $actions as $action ) {
			$this->actions[ $action->topic ] = $action;

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
