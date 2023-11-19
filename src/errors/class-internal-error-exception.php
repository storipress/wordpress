<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress\Errors;

/**
 * Runtime internal error exception.
 *
 * @since 0.0.12
 */
final class Internal_Error_Exception extends Exception {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( '', 5001001 );
	}
}
