<?php
// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
namespace FormVibes\Integrations;

use FormVibes\Classes\Utils;
use FormVibes\Integrations\Base;

/**
 * Ninja Forms plugin class
 *
 * Register the Ninja Forms plugin
 */
class NinjaForms extends Base {

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
		$this->plugin_name = 'Ninja-Forms';

		$this->set_skip_fields();

		add_filter( 'fv_forms', [ $this, 'register_plugin' ] );
		// calls after ninja forms submit the form.
		add_action( 'ninja_forms_after_submission', [ $this, 'ninja_forms_after_submission' ] );
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
		$forms[ $this->plugin_name ] = 'Ninja Forms';
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
	public function ninja_forms_after_submission( $data ) {
		// echo '<pre>';  print_r($data); echo '</pre>';
		// die('daf');
		$form_id   = $data['form_id'];
		// echo '<pre>';  print_r($form_id); echo '</pre>';
		// die('daf');
		$form_name = $data['settings']['title'];
		// check if user wants to store/save the entry to db.
		$save_entry = true;

		$save_entry = apply_filters( 'formvibes/ninjaforms/save_record', $save_entry, $data );

		if ( ! $save_entry ) {
			return;
		}

		$form_data['plugin_name']  = $this->plugin_name;
		$form_data['id']           = $form_id;
		$form_data['captured']     = current_time( 'mysql', 0 );
		$form_data['captured_gmt'] = current_time( 'mysql', 1 );
		$form_data['title']        = $form_name;
		$form_data['url']          = $_SERVER['HTTP_REFERER'];
		$posted_data               = $this->prepare_posted_data( $data['fields'] );

		$settings = get_option( 'fvSettings' );

		if ( Utils::key_exists( 'save_ip_address', $settings ) && true === $settings['save_ip_address'] ) {
			$posted_data['IP'] = $this->get_user_ip();
		}

		$form_data['fv_form_id']  = $form_id;
		$form_data['posted_data'] = $posted_data;
		self::$submission_id      = $this->insert_entries( $form_data );
	}

	/**
	 * Prepare the saved data
	 *
	 * @access public
	 * @return array
	 */
	private function prepare_posted_data( $data ) {
		$posted_data = [];

		foreach ( $data as $key => $values ) {
			$value_key = $values['key'];
			$value     = $values['value'];
			$type      = $values['type'];

			$posted_data[ $value_key ] = $value;

			if ( $type === 'listcheckbox' || $type === 'listimage' || $type === 'listmultiselect' || $type === 'file_upload' ) {
				if ( $value ) {
					$posted_data[ $value_key ] = implode( ', ', $value );
				}
			}
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
		// get fields data from Ninja Form.
		$fields = Ninja_Forms()->form( $form_id )->get_fields();

		foreach ( $fields as $field ) {
			$settings = ( is_object( $field ) ) ? $field->get_settings() : $field['settings'];
			$label    = ( is_object( $settings ) ) ? $settings->label : $settings['label'];
			$key      = ( is_object( $settings ) ) ? $settings->key : $settings['key'];
			// if alias is as same as key
			if ( $cols[ $key ]['alias'] === $key ) {
				$cols[ $key ]['alias'] = $label;
			}
		}

		return $cols;
	}
}
