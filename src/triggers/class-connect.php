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
 * The connect trigger.
 *
 * @since 0.0.12
 */
final class Connect extends Trigger {
	/**
	 * The client.
	 *
	 * @var string
	 *
	 * @since 0.0.12
	 */
	public $client;

	/**
	 * Constructor.
	 *
	 * @param string $client The client.
	 */
	public function __construct( string $client ) {
		$this->client = $client;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_activated(): bool {
		// not allow to connect more than one client.
		return ! Storipress::instance()->core->is_connected();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return array{}
	 *
	 * @throws Internal_Error_Exception The option wasn't saved successfully.
	 */
	public function run(): array {
		$updated = Storipress::instance()->core->set_options( array( 'client' => $this->client ) );

		if ( ! $updated ) {
			throw new Internal_Error_Exception();
		}

		return array();
	}
}
