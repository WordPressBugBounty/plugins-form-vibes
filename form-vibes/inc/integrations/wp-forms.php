<?php

namespace FormVibes\Integrations;

use FormVibes\Classes\Utils;
use FormVibes\Integrations\Base;

/**
 * WP Forms plugin class
 *
 * Register the WP Forms plugin
 */

class WpForms extends Base {

	private $plugin_name = '';
	/**
	 * The instance of the class.
	 * @var null|object $instance
	 *
	 */
	private static $instance = null;
	/**
	 * The forms.
	 * @var array
	 *
	 */
	public static $forms = [];
	/**
	 * The submission id
	 * @var string $submission_id
	 *
	 */
	public static $submission_id = '';

	/**
	 * Array for skipping fields or unwanted data from the form data..
	 * @var array $skip_fields
	 *
	 */
	protected $skip_fields = [];

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
	 * @access private
	 * @since 1.4.4
	 * @return void
	 */
 

	public function __construct() {
		$this->plugin_name = 'wp-forms';

		$this->set_skip_fields();

		add_filter( 'fv_forms', [ $this, 'register_plugin' ] );
		// calls after wp forms submit the form.
		add_action( 'wpforms_process_entry_save', [ $this, 'wp_form_insert' ], 10, 4 );
		add_filter( "formvibes/submissions/{$this->plugin_name}/columns", [ $this, 'prepare_columns' ], 10, 3 );
	}

	/**
	 * Register the form plugin
	 *
	 * @param array $forms
	 * @access public
	 * @return array
	 */
	public function register_plugin( $forms ) {
		$forms[ $this->plugin_name ] = 'WP Forms';
		return $forms;
	}

	/**
	 * Set the skip fields
	 *
	 * @access protected
	 * @return void
	 */
	protected function set_skip_fields() {
		// name of all fields which should not be stored in our database.
		$this->skip_fields = [];
	}

	/**
	 * Run when the form is submitted
	 *
	 * @access public
	 * @return string|mixed
	 */
	public function wp_form_insert( $fields, $entry, $form_id, $form_data ) {
		$form_name = $form_data['settings']['form_title'];
		// check if user wants to store/save the entry to db.
		$save_entry = true;

		$save_entry = apply_filters( 'formvibes/ninjaforms/save_record', $save_entry, $fields );

		if ( ! $save_entry ) {
			return;
		}

		$data['plugin_name']  = $this->plugin_name;
		$data['id']           = $form_id;
		$data['captured']     = current_time( 'mysql', 0 );
		$data['captured_gmt'] = current_time( 'mysql', 1 );
		$data['title']        = $form_name;
		$data['url']          = $_SERVER['HTTP_REFERER'];
		$posted_data          = $this->prepare_posted_data( $fields );

		$settings = get_option( 'fvSettings' );

		if ( Utils::key_exists( 'save_ip_address', $settings ) && true === $settings['save_ip_address'] ) {
			$posted_data['IP'] = $this->get_user_ip();
		}

		$data['fv_form_id']  = $form_id;
		$data['posted_data'] = $posted_data;
		self::$submission_id = $this->insert_entries( $data );
	}

	/**
	 * Prepare the saved data
	 *
	 * @access public
	 * @return array
	 */
	private function prepare_posted_data( $fields ) {
		$posted_data = [];

		foreach ( $fields as $values ) {
			$name  = $values['name'];
			$value = $values['value'];
			$id    = $values['id'];
			$posted_data[ str_replace( ' ', '_', $name ) . '_' . $id ] = $value;
		}

		return $posted_data;
	}

	/**
	 * Prepare the table columns
	 *
	 * @access public
	 * @return array
	 */
	public function prepare_columns( $cols, $columns, $form_id ) {
		$wp_forms_data   = wpforms()->form->get( $form_id );
		$wp_forms_fields = $wp_forms_data ? wpforms_decode( $wp_forms_data->post_content )['fields'] : false;

		if ( ! $wp_forms_fields ) {
			return $cols;
		}

		foreach ( $wp_forms_fields as $values ) {
			$label = Utils::key_exists( 'label', $values ) ? $values['label'] : false;
			if ( $label ) {
				$id  = $values['id'];
				$key = str_replace( ' ', '_', $label . '_' . $id );

				// if alias is as same as key
				if ( $cols[ $key ]['alias'] === $key ) {
					$cols[ $key ]['alias'] = $label;
				}
			}
		}

		return $cols;
	}
}
