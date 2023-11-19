<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress\Errors;

/**
 * Invalid request payload exception.
 *
 * @since 0.0.12
 */
final class Invalid_Payload_Exception extends Exception {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( '', 4221001 );
	}
}
