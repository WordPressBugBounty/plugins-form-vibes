<?php
// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
namespace FormVibes\Integrations;

use FormVibes\Classes\Utils;

/**
 * An abstract class for all form plugin integrations.
 *
 * This contains the logic for saving the form data into the database.
 */
abstract class Base {

	/**
	 * Get the IP address of the user and return it.
	 *
	 * @access protected
	 * @since 1.4.4
	 * @return string
	 */
	public function get_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			// check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// to check ip is pass from proxy
			$temp_ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );

			$ip = $temp_ip[0];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

	/**
	 * Inserts the form data into the database.
	 *
	 * This method is called when the form is submitted.
	 * It takes the form data and inserts it into the database.
	 * Call this method after preparing the form data.
	 *
	 * @param array $entries {
	 *      The form data.
	 *      @type string $plugin_name The name of the plugin.
	 *      @type string plugin_name The name of the plugin.
	 *      @type string id The form ID.
	 *      @type string captured The date and time the form was submitted.
	 *      @type string captured_gmt The date and time the form was submitted in GMT.
	 *      @type string title The title of the form.
	 *      @type string url The URL of the page the form was submitted from.
	 *      @type array posted_data The meta data.
	 * }
	 * @access public
	 * @since 1.4.4
	 * @return string The Entry ID or null.
	 *
	 */

	public function insert_entries( $entries ) {
		// TODO :: Check exclude form

		$inserted_forms = get_option( 'fv_forms' );

		if ( false === $inserted_forms ) {
			$inserted_forms = [];
		}
		$forms = [];

		if ( Utils::key_exists( $entries['plugin_name'], $inserted_forms ) ) {
			$forms = $inserted_forms[ $entries['plugin_name'] ];

			$forms[ $entries['id'] ] = [
				'id'   => $entries['id'],
				'name' => $entries['title'],
			];
		} else {
			$forms[ $entries['id'] ] = [
				'id'   => $entries['id'],
				'name' => $entries['title'],
			];
		}
		$inserted_forms[ $entries['plugin_name'] ] = $forms;

		update_option( 'fv_forms', $inserted_forms );

		global $wpdb;
		$entry_data = [
			'form_plugin'  => $entries['plugin_name'],
			'form_id'      => $entries['id'],
			'captured'     => $entries['captured'],
			'captured_gmt' => $entries['captured_gmt'],
			'url'          => $entries['url'],
		];

		$settings = get_option( 'fvSettings' );
		$save_ua  = false;

		if ( $settings && Utils::key_exists( 'save_user_agent', $settings ) ) {
			$save_ua = $settings['save_user_agent'];
		}

		if ( $save_ua ) {
			$entry_data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			$entries['user_agent']    = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$entry_data['user_agent'] = '';
		}

		$wpdb->insert(
			$wpdb->prefix . 'fv_enteries',
			$entry_data
		);
		$insert_id = $wpdb->insert_id;

		if ( $insert_id !== 0 ) {
			$this->insert_entry_meta( $insert_id, $entries['posted_data'], $entries['plugin_name'], $entries['id'], $entry_data );
			return $insert_id;
		}
	}

	/**
	 * Inserts the form meta data into the database.
	 *
	 * @param int   $insert_id The inserted Entry ID.
	 * @param array $entries The form meta data that will be inserted into the entry meta table.
	 * @param string $plugin_name The plugin name.
	 * @param string $form_id The form ID.
	 * @param array $entry_data The entry data that was inserted into the entry table.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return void
	 */
	public function insert_entry_meta( $insert_id, $entires, $plugin_name, $form_id, $entry_data ) {
		global $wpdb;

		foreach ( $entires as $key => $value ) {
			$wpdb->insert(
				$wpdb->prefix . 'fv_entry_meta',
				[
					'data_id'    => $insert_id,
					'meta_key'   => $key,
					'meta_value' => $value,
				]
			);
		}
		$insert_id_meta = $wpdb->insert_id;

		if ( $insert_id_meta < 1 ) {
			do_action(
				'fv_after_entry_meta_failed',
				[
					'insert_id'   => $insert_id,
					'plugin_name' => $plugin_name,
					'form_id'     => $form_id,
					'entry_data'  => $entry_data,
					'entries'     => $entires,
				]
			);
		} else {
			do_action(
				'fv_after_entry_meta_success',
				[
					'insert_id'   => $insert_id,
					'plugin_name' => $plugin_name,
					'form_id'     => $form_id,
					'entry_data'  => $entry_data,
					'entires'     => $entires,
				]
			);
		}
	}

	/**
	 * Delete the entry from database
	 *
	 * @param array<string> $ids The entry ids to be deleted
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array $message {
	 *      @type string $status The status of the request. **passed | failed**.
	 *      @type string $message The message.
	 * }
	 */
	public static function delete_entries( $ids ) {
		global $wpdb;
		$message = [];
		// $delete_row_query1 = "Delete from {$wpdb->prefix}fv_enteries where id IN (" . implode( ',', $ids ) . ')';
		// $delete_row_query2 = "Delete from {$wpdb->prefix}fv_entry_meta where data_id IN (" . implode( ',', $ids ) . ')';
		$idsPlaceholder = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );

		// PHPCS:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$delete_row_query1 = $wpdb->prepare( "Delete from {$wpdb->prefix}fv_enteries where id IN ( $idsPlaceholder )", $ids );
		// PHPCS:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$delete_row_query2 = $wpdb->prepare( "Delete from {$wpdb->prefix}fv_entry_meta where data_id IN ( $idsPlaceholder )", $ids );

		$dl1 = $wpdb->query( $delete_row_query1 );

		$dl2 = $wpdb->query( $delete_row_query2 );

		if ( 0 === $dl1 || 0 === $dl2 ) {
			$message['status']  = 'failed';
			$message['message'] = 'Could not able to delete Entries';
		} else {
			$message['status']  = 'passed';
			$message['message'] = 'Entries Deleted';
		}

		wp_send_json( $message );
	}
}
