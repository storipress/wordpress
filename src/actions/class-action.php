<?php
/**
 * Storipress
 *
 * @package Storipress
 */

declare(strict_types=1);

namespace Storipress\Storipress\Actions;

use Storipress;

/**
 * The abstract action class.
 *
 * @since 0.0.12
 */
abstract class Action {

	/**
	 * Webhook topic.
	 *
	 * @var string
	 *
	 * @since 0.0.14
	 */
	public $topic;

	/**
	 * Register the hook.
	 *
	 * @return void
	 *
	 * @since 0.0.12
	 */
	public function register() { }

	/**
	 * Unregister the hook.
	 *
	 * @return void
	 *
	 * @since 0.0.12
	 */
	public function unregister() { }

	/**
	 * Send events to Storipress.
	 *
	 * @param array<mixed> $args The request arguments.
	 * @return void
	 *
	 * @since 0.0.14
	 */
	public function send( $args ): void {
		$core = Storipress::instance()->core;

		if ( ! $core->is_connected() ) {
			return;
		}

		if ( ! isset( $core->options['client'] ) || ! isset( $core->options['hash_key'] ) ) {
			return;
		}

		$args['topic'] = $this->topic;

		$args['client'] = $core->options['client'];

		$signature = $this->sign( $args );

		if ( null === $signature ) {
			return;
		}

		$domain = null;

		switch ( substr( $args['client'], 0, 1 ) ) {
			case 'D':
				$domain = 'https://api.storipress.dev';
				break;
			case 'S':
				$domain = 'https://api.storipress.pro';
				break;
			case 'P':
				$domain = 'https://api.stori.press';
				break;
			case 'L':
			case 'T':
				$domain = 'http://localhost:8000';
				break;
			default:
				break;
		}

		if ( null === $domain ) {
			return;
		}

		$body = wp_json_encode( $args );

		if ( false === $body ) {
			return;
		}

		wp_remote_post(
			sprintf( '%s/partners/wordpress/events', $domain ),
			array(
				'headers' => array(
					'Content-Type'           => 'application/json',
					'X-Storipress-Signature' => $signature,
					'X-Storipress-Timestamp' => time(),
				),
				'body'    => $body,
			)
		);
	}


	/**
	 * Generate the signature.
	 *
	 * @param array<string, mixed> $payload The payload.
	 * @return string|null
	 *
	 * @since 0.0.12
	 */
	public function sign( $payload ) {
		$options = Storipress::instance()->core->options;

		if ( empty( $options['hash_key'] ) ) {
			return null;
		}

		$key = $options['hash_key'];

		ksort( $payload );

		$data = wp_json_encode( $payload );

		if ( ! $data ) {
			return null;
		}

		return hash_hmac( 'sha256', $data, $key );
	}
}
