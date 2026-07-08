<?php
/**
 * AJAX handler.
 *
 * Single endpoint that dispatches to the right state transition based on the
 * action parameter.
 *
 * @package WPVibes\ReviewReminder
 */

namespace WPVibes\ReviewReminder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AjaxHandler {

	/**
	 * Handle a state transition request.
	 *
	 * @return void
	 */
	public static function handle() {
		$slug   = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
		$action = isset( $_POST['reminder_action'] ) ? sanitize_key( wp_unslash( $_POST['reminder_action'] ) ) : '';

		if ( '' === $slug || '' === $action ) {
			wp_send_json_error( array( 'message' => 'missing_params' ), 400 );
		}

		$instances = ReviewReminder::get_instances();
		if ( ! isset( $instances[ $slug ] ) ) {
			wp_send_json_error( array( 'message' => 'unknown_plugin' ), 404 );
		}
		$config = $instances[ $slug ];

		if ( ! current_user_can( $config->capability ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}

		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wpvibes_review_reminder_' . $slug ) ) {
			wp_send_json_error( array( 'message' => 'bad_nonce' ), 403 );
		}

		switch ( $action ) {
			case 'rate':
			case 'rated':
				State::mark_rated( $slug );
				break;
			case 'snooze':
				State::snooze( $slug );
				break;
			case 'dismiss':
				State::dismiss( $slug );
				break;
			default:
				wp_send_json_error( array( 'message' => 'unknown_action' ), 400 );
		}

		wp_send_json_success( array( 'state' => State::get( $slug )['state'] ) );
	}
}
