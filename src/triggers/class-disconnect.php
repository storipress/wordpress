<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress\Triggers;

use Storipress;
use Storipress\Storipress\Errors\Internal_Error_Exception;

/**
 * The disconnect trigger.
 *
 * @since 0.0.12
 */
final class Disconnect extends Trigger {
	/**
	 * {@inheritDoc}
	 */
	public function is_activated(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array{}
	 *
	 * @throws Internal_Error_Exception The option wasn't deleted successfully.
	 */
	public function run(): array {
		$deleted = delete_option( Storipress::instance()->core->option_key );

		if ( ! $deleted ) {
			throw new Internal_Error_Exception();
		}

		Storipress::instance()->core->delete_application_password( get_current_user_id() );

		return array();
	}
}
