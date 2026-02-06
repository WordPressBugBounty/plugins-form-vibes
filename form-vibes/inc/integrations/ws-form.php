<?php

namespace FormVibes\Integrations;

use FormVibes\Classes\Utils;
use FormVibes\Integrations\Base;
use WS_Form_Common;
use WS_Form_Form;

/**
 * WS Form plugin class
 *
 * Register the WS Form plugin
 */

class WsForm extends Base {

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
		$this->plugin_name = 'ws-form';

		$this->set_skip_fields();

		add_filter( 'fv_forms', [ $this, 'register_plugin' ] );
		// calls after wp forms submit the form.
		add_action( 'wsf_submit_create', [ $this, 'ws_form_insert' ], 10, 4 );
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
		$forms[ $this->plugin_name ] = 'WS Form';
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
		$this->skip_fields = [ 'wsf_meta_key_hidden' ];
	}

	/**
	 * Run when the form is submitted
	 *
	 * @access public
	 * @return string|mixed
	 */
	public function ws_form_insert( $form ) {
		$form_name = $form->form_object->label;
		$form_id   = $form->form_id;

		// die();
		// check if user wants to store/save the entry to db.
		$save_entry = true;

		$save_entry = apply_filters( 'formvibes/wsforms/save_record', $save_entry, $form );

		if ( ! $save_entry ) {
			return;
		}

		$data['plugin_name']  = $this->plugin_name;
		$data['id']           = $form_id;
		$data['captured']     = current_time( 'mysql', 0 );
		$data['captured_gmt'] = current_time( 'mysql', 1 );
		$data['title']        = $form_name;
		$data['url']          = $_SERVER['HTTP_REFERER'];
		$posted_data          = $this->prepare_posted_data( $form->meta, $form );

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
	private function prepare_posted_data( $fields, $form ) {
		$posted_data = [];
		$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'form-vibes/ws-form';
		if ( ! file_exists( $uploads_dir ) ) {
			wp_mkdir_p( $uploads_dir );
		}

		$wsupload       = wp_upload_dir();
		$fv_dirname     = $wsupload['baseurl'] . '/form-vibes/ws-form';
		$uploaded_files = [];

		foreach ( $fields as $key => $values ) {
			if ( $key === 'wsf_meta_key_hidden' || ! isset( $values['type'] ) ) {
				continue;
			}
			if ( $values['type'] === 'file' ) {
				if ( $values['value'] ) {
					foreach ( $values['value'] as $fileKey => $fileValue ) {
						$filetype = strrpos( $fileValue['name'], '.' );
						$filetype = substr( $fileValue['name'], $filetype );
						$filename = wp_rand( 1111111111, 9999999999 );
						$time_now = time();

						array_push( $uploaded_files, $fv_dirname . '/' . $time_now . '-' . $filename . $filetype );
						copy( wp_upload_dir()['basedir'] . '/' . $fileValue['path'], $uploads_dir . '/' . $time_now . '-' . $filename . $filetype );
					}
				}
				$values['value'] = $uploaded_files;
			}
			$posted_data[ $key ] = is_array( $values['value'] ) ? implode( ', ', $values['value'] ) : $values['value'];
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
		$ws_form_form     = new WS_Form_Form();
		$ws_form_form->id = $form_id;
		$form_object      = $ws_form_form->db_read( true, true, false, true );
		$ws_forms_fields  = WS_Form_Common::get_fields_from_form( $form_object );

		if ( ! $ws_forms_fields ) {
			return $cols;
		}

		foreach ( $ws_forms_fields as $values ) {
			if ( $values->type === 'submit' ) {
				continue;
			}
			$label = $values->label;
			$id    = $values->id;
			$key   = 'field_' . $id;
			$label = Utils::key_exists( $key, $cols ) ? $values->label : false;

			if ( $label ) {
				// if alias is as same as key
				if ( $cols[ $key ]['alias'] === $key ) {
					$cols[ $key ]['alias'] = $label;
				}
			}
		}

		return $cols;
	}
}
