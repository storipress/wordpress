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
use Storipress\Storipress\Errors\Non_Activated_Trigger_Exception;
use Storipress\Storipress\Triggers\ACF_Data;
use Storipress\Storipress\Triggers\Connect;
use Storipress\Storipress\Triggers\Disconnect;
use Storipress\Storipress\Triggers\Trigger;
use Storipress\Storipress\Triggers\Update_Yoast_Seo_Metadata;
use Throwable;
use WP_REST_Request;
use WP_REST_Server;

/**
 * The trigger class.
 *
 * @since 0.0.12
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
	 * @since 0.0.12
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
			array(
				'path'     => '/acf-data',
				'callback' => 'acf_data',
			),
			array(
				'path'     => '/update-yoast-seo-metadata',
				'callback' => 'update_yoast_seo_metadata',
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
	 * @since 0.0.12
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
	 * @since 0.0.12
	 */
	public function disconnect() {
		$this->handle( new Disconnect() );
	}

	/**
	 * Retrieve ACF fields
	 *
	 * @return void
	 *
	 * @since 0.0.14
	 */
	public function acf_data() {
		$this->handle( new ACF_Data() );
	}

	/**
	 * Update Yoast seo metadata
	 *
	 * @param WP_REST_Request<array{}> $request The request instance.
	 * @return void
	 *
	 * @since 0.0.14
	 */
	public function update_yoast_seo_metadata( $request ) {
		$id = $request->get_param( 'id' );

		$options = $request->get_param( 'options' );

		// Post id is required and must be int.
		if ( ! is_int( $id ) ) {
			$this->error( new Invalid_Payload_Exception() );

			return;
		}

		if ( empty( $options ) || ! is_array( $options ) ) {
			$this->error( new Invalid_Payload_Exception() );

			return;
		}

		// Ensure the value is of the correct type.
		if ( ( isset( $options['seo_title'] ) && ! is_string( $options['seo_title'] ) )
			|| ( isset( $options['seo_description'] ) && ! is_string( $options['seo_description'] ) )
			|| ( isset( $options['og_title'] ) && ! is_string( $options['og_title'] ) )
			|| ( isset( $options['og_description'] ) && ! is_string( $options['og_description'] ) )
			|| ( isset( $options['og_image_id'] ) && ! is_int( $options['og_image_id'] ) )
		) {
			$this->error( new Invalid_Payload_Exception() );

			return;
		}

		$this->handle( new Update_Yoast_Seo_Metadata( $id, $options ) );
	}

	/**
	 * Trigger handler.
	 *
	 * @param Trigger $trigger The trigger instance.
	 * @return void
	 *
	 * @since 0.0.12
	 */
	public function handle( Trigger $trigger ) {
		if ( ! $trigger->is_activated() ) {
			$this->error( new Non_Activated_Trigger_Exception() );

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
	 * @since 0.0.12
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
	 * @since 0.0.12
	 */
	public function response( array $data, int $status = 200 ) {
		status_header( $status );

		nocache_headers();

		header( 'Content-Type: application/json' );

		echo wp_json_encode( $data );

		exit;
	}
}
