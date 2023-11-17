<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress;

use Storipress\Storipress\Errors\Exception;
use Storipress\Storipress\Errors\Internal_Error_Exception;
use Storipress\Storipress\Errors\Invalid_Payload_Exception;
use Storipress\Storipress\Triggers\Connect;
use Storipress\Storipress\Triggers\Disconnect;
use Storipress\Storipress\Triggers\Trigger;
use Throwable;
use WP_REST_Request;
use WP_REST_Server;

/**
 * The trigger class.
 *
 * @since 0.0.1
 */
final class Trigger_Handler {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// register custom rest route when rest_api_init.
		add_action(
			'rest_api_init',
			array( &$this, 'register_routes' )
		);
	}

	/**
	 * Register custom rest routes.
	 *
	 * @return void
	 *
	 * @since 0.0.1
	 */
	public function register_routes() {
		$routes = array(
			array(
				'path'     => '/connect',
				'callback' => 'connect',
			),
			array(
				'path'     => '/disconnect',
				'callback' => 'disconnect',
			),
		);

		foreach ( $routes as $route ) {
			register_rest_route(
				'storipress',
				$route['path'],
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( &$this, $route['callback'] ),
					'permission_callback' => 'is_user_logged_in',
				)
			);
		}
	}

	/**
	 * Connect the client.
	 *
	 * @param WP_REST_Request<array{}> $request The request instance.
	 * @return void
	 *
	 * @since 0.0.1
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function connect( $request ) {
		$client = $request->get_param( 'storipress_client' );

		if ( ! is_string( $client ) ) {
			$this->error( new Invalid_Payload_Exception() );

			return;
		}

		$this->handle( new Connect( $client ) );
	}

	/**
	 * Disconnect the client.
	 *
	 * @return void
	 *
	 * @since 0.0.1
	 */
	public function disconnect() {
		$this->handle( new Disconnect() );
	}

	/**
	 * Trigger handler.
	 *
	 * @param Trigger $trigger The trigger instance.
	 * @return void
	 *
	 * @since 0.0.1
	 */
	public function handle( Trigger $trigger ) {
		if ( ! $trigger->validate() ) {
			$this->error( new Invalid_Payload_Exception() );

			return;
		}

		try {
			$this->response(
				array(
					'ok'   => true,
					'data' => $trigger->run(),
				)
			);
		} catch ( Exception $e ) {
			$this->error( $e );
		} catch ( Throwable $e ) {
			$this->error( new Internal_Error_Exception() );
		}
	}

	/**
	 * Error response.
	 *
	 * @param Exception $exception Exception.
	 * @return void
	 *
	 * @since 0.0.1
	 */
	public function error( Exception $exception ) {
		$this->response(
			array(
				'ok'   => false,
				'code' => $exception->getCode(),
			),
			intdiv( $exception->getCode(), 10000 )
		);
	}

	/**
	 * Response the trigger request.
	 *
	 * @param array<string, mixed> $data The response data.
	 * @param int                  $status The response status.
	 * @return void
	 *
	 * @since 0.0.1
	 */
	public function response( array $data, int $status = 200 ) {
		status_header( $status );

		nocache_headers();

		header( 'Content-Type: application/json' );

		echo wp_json_encode( $data );

		exit;
	}
}
