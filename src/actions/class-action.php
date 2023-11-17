<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress\Actions;

/**
 * The abstract action class.
 *
 * @since 0.0.1
 */
abstract class Action {
	/**
	 * Register the hook.
	 *
	 * @return void
	 *
	 * @since 0.0.1
	 */
	public function register() { }

	/**
	 * Unregister the hook.
	 *
	 * @return void
	 *
	 * @since 0.0.1
	 */
	public function unregister() { }

	/**
	 * Response data.
	 *
	 * @return array{
	 *     success: bool,
	 * }
	 *
	 * @since 0.0.1
	 */
	public function response_data(): array {
		return array(
			'success' => true,
		);
	}
}
