<?php
/**
 * Storipress
 *
 * @package           Storipress
 * @author            Storipress
 * @copyright         Albion Media Pty. Ltd.
 * @license           GPL-3.0-or-later
 */

declare(strict_types=1);

namespace Storipress\Storipress\Triggers;

/**
 * The abstract trigger class.
 *
 * @since 0.0.1
 */
abstract class Trigger {
	/**
	 * Validate the trigger request payload.
	 *
	 * @since 0.0.1
	 */
	abstract public function validate(): bool;

	/**
	 * Run the trigger and return response data.
	 *
	 * @return array<array-key, mixed>
	 *
	 * @since 0.0.1
	 */
	abstract public function run(): array;
}
