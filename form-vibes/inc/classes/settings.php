<?php

namespace FormVibes\Classes;

use FormVibes\Classes\DbManager;
use FormVibes\Classes\Utils;
use Exception;

/**
 * A utility class for managing the plugin settings
 */

class Settings {


	/**
	 * The instance of the class.
	 * @var null|object
	 *
	 */
	private static $instance = null;

	/**
	 * The instaciator of the class.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return @var $instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * The constructor of the class.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return void
	 */
	public function __construct() {
		add_filter( 'formvibes/global/settings', [ $this, 'set_default_settings' ] );
		add_filter( 'formvibes/global/settings', [ $this, 'set_settings' ] );
		add_action( 'wp_ajax_save_settings', [ $this, 'save_settings' ] );
		add_action( 'wp_ajax_reset_settings', [ $this, 'reset_settings' ] );
		add_action( 'wp_ajax_save_columns_settings', [ $this, 'save_columns_settings' ] );
		add_action('init', function(){
			$this->set_initial_settings();
		});
		
	}

	/**
	 * Saves the table columns settings to the database.
	 *
	 * Fired by `wp_ajax_save_columns_settings` action.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array ` [
			'message'  => 'Columns settings saved!',
			'is_error' => false,
		]` | `[
				'message'  => 'Failed to save column settings!',
				'is_error' => true,
			]`
	 */
	public function save_columns_settings() {

		if ( ! Permissions::check_permission( Permissions::$CAP_SUBMISSIONS ) ) {
			die( 'Sorry, you are not allowed to do this action!' );
		}
		
		if ( ! wp_verify_nonce( $_POST['ajaxNonce'], 'fv_ajax_nonce' ) ) {
			die( 'Sorry, your nonce did not verify!' );
		}

		$params = (array) json_decode( stripslashes( sanitize_text_field( $_POST['params'] ) ) );

		$columns = $params['columns'];
		$plugin  = $params['plugin'];
		$form_id = $params['formid'];

		$data                             = get_option( 'fv-keys' );
		$data[ $plugin . '_' . $form_id ] = $columns;
		$is_update                        = update_option( 'fv-keys', $data, false );

		$res = [
			'message'  => 'Columns settings saved!',
			'is_error' => false,
		];

		if ( ! $is_update ) {
			$res = [
				'message'  => 'Failed to save column settings!',
				'is_error' => true,
			];
		}

		wp_send_json( $res );
	}

	/**
	 * Get the settings with default values
	 *
	 *
	 * @access private
	 * @since 1.4.4
	 * @return array
	 */
	private function get_default_settings_value() {
		$settings_default = $this->get_default_settings();
		$save_settings    = [];
		foreach ( $settings_default as $key => $values ) {
			$save_settings[ $key ] = $values['default'];
		}
		return $save_settings;
	}


	/**
	 * Sets the settings when no settings are saved yet.
	 *
	 *
	 * @access private
	 * @since 1.4.4
	 * @return void
	 */
	private function set_initial_settings() {
		$saved_settings   = get_option( 'fvSettings' );
		$default_settings = $this->get_default_settings_value();
		$settings         = [];

		foreach ( $default_settings as $key => $default_setting ) {
			if ( $saved_settings && Utils::key_exists( $key, $saved_settings ) ) {
				$settings[ $key ] = $saved_settings[ $key ];
			} else {
				$settings[ $key ] = $default_setting;
			}
		}

		if ( ! $saved_settings || count( $settings ) > count( $saved_settings ) ) {
			update_option( 'fvSettings', $settings, false );
		}
	}

	/**
	 * Resets the settings
	 *
	 * Fired by `wp_ajax_reset_settings` action.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array `[
				'is_error' => false,
				'message'  => 'Settings reset successfully.',
			]` | `[
					'is_error' => false,
					'message'  => 'Settings reset successfully.',
				]
			`
	 */
	// Check Done
	public function reset_settings() {
		if(!current_user_can('manage_options')){
			die('Sorry, you are not allowed to do this action!');
		}
		
		if ( ! wp_verify_nonce( $_POST['ajaxNonce'], 'fv_ajax_nonce' ) ) {
			die( 'Sorry, your nonce did not verify!' );
		}

		$is_saved = update_option( 'fvSettings', $this->get_default_settings_value(), false );

		wp_send_json(
			[
				'is_error' => false,
				'message'  => 'Settings reset successfully.',
			]
		);

		if ( $is_saved ) {
			wp_send_json(
				[
					'is_error' => false,
					'message'  => 'Settings reset successfully.',
				]
			);
		}

		wp_send_json(
			[
				'is_error' => true,
				'message'  => 'Failed to reset settings.',
			]
		);
	}

	/**
	 * Set a new setting in the settings array.
	 *
	 * Fired by `formvibes/global/settings` filter.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public function set_settings( $args ) {

		$settings               = get_option( 'fvSettings' );
		$settings               = apply_filters( 'formvibes/settings/saved', $settings );
		$args['settings_saved'] = $settings;
		return $args;
	}

	/**
	 * Sets the default settings.
	 *
	 * Fired by `formvibes/global/settings` filter.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public function set_default_settings( $args ) {

		$settings                 = $this->get_default_settings();
		$args['settings_default'] = $settings;

		return $args;
	}


	/**
	 * Save the settings to the database.
	 *
	 * Fired by `wp_ajax_save_settings` action.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array `[
						'is_error' => false,
						'message'  => 'Settings have been saved successfully.',
					]` | `[
					'is_error' => true,
					'message'  => 'Failed to save settings in database.',
				]`
	 */
	public function save_settings() {
		
		if(! current_user_can('manage_options')){
			die('Sorry, you are not allowed to do this action!');
		}

		if ( ! wp_verify_nonce( $_POST['ajaxNonce'], 'fv_ajax_nonce' ) ) {
			die( 'Sorry, your nonce did not verify!' );
		}
		
		// echo '<pre>';  print_r($_POST['params']); echo '</pre>';
		// die('dfaf');
		$settings = (array) json_decode( stripslashes( sanitize_text_field( $_POST['params'] ) ) );

		try {
			// save settings to db.
			$is_saved = update_option( 'fvSettings', $settings, false );
			if ( $is_saved ) {
				wp_send_json(
					[
						'is_error' => false,
						'message'  => 'Settings have been saved successfully.',
					]
				);
			}

			throw new Exception( 'error' );
		} catch ( Exception $e ) {
			wp_send_json(
				[
					'is_error' => true,
					'message'  => 'Failed to save settings in database.',
				]
			);
		}
	}

	/**
	 * Prepare and return the settings.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public function get_default_settings() {
		$settings = [
			'dashboard_widget'       => [
				'label'   => __( 'Dashboard Widgets', 'wpv-fv' ),
				'type'    => 'toggle',
				'default' => true,
			],
			'save_ip_address'        => [
				'label'   => __( 'Save IP Address', 'wpv-fv' ),
				'type'    => 'toggle',
				'default' => true,
			],
			'save_user_agent'        => [
				'label'   => __( 'Save User Agent', 'wpv-fv' ),
				'type'    => 'toggle',
				'default' => true,
			],
			'debug_mode'             => [
				'label'   => __( 'Debug Mode', 'wpv-fv' ),
				'type'    => 'toggle',
				'default' => false,
			],
			'csv_export_reason'      => [
				'label'   => __( 'CSV Export Reason', 'wpv-fv' ),
				'type'    => 'toggle',
				'default' => false,
			],
			'auto_refresh'           => [
				'label'   => __( 'Auto Refresh', 'wpv-fv' ),
				'type'    => 'toggle',
				'default' => false,
			],
			'auto_refresh_frequency' => [
				'label'      => __( 'Auto Refresh Frequency', 'wpv-fv' ),
				'type'       => 'input',
				'input_type' => 'number',
				'default'    => 30,
			],
			'persist_filter'         => [
				'label'   => __( 'Persist Filter', 'wpv-fv' ),
				'type'    => 'toggle',
				'default' => true,
			],
		];

		return apply_filters( 'formvibes/settings/default', $settings );
	}

	/**
	 * Get saved settings from the database.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public function get_settings() {
		$settings = get_option( 'fvSettings' );

		return apply_filters( 'formvibes/settings', $settings );
	}

	/**
	 * Get a setting by key.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return bool|string
	 */
	public function get_setting_value_by_key( $key ) {
		$settings = $this->get_settings();

		if ( $settings && Utils::key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		return false;
	}
}
