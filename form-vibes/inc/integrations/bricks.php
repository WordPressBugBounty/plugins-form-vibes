<?php

namespace FormVibes\Integrations;

use FormVibes\Classes\Utils;
use FormVibes\Integrations\Base;

/**
 * Bricks Builder plugin class
 *
 * Register the Bricks Builder theme
 */

class Bricks extends Base {

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
	 * Error Array.
	 * @var array
	 *
	 */
	public static $errors = [];

	/**
	 * The submission id
	 * @var string $submission_id
	 *
	 */
	public static $submission_id = '';

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
		$this->plugin_name = 'bricks-builder';

		add_filter( 'fv_forms', [ $this, 'register_plugin' ] );

		// Add Form Name control to form
		add_filter( 'bricks/elements/form/controls', [ $this, 'add_form_control' ], 10 );

		// add_action( 'wp_ajax_bricks_form_submit', [ $this, 'form_submit' ] );
		// add_action( 'wp_ajax_nopriv_bricks_form_submit', [ $this, 'form_submit' ] );

		add_filter( 'bricks/form/response', [$this,'form_submit'], 10, 2 );
	}
	/**
	 * Register the form plugin
	 *
	 * @param array $forms
	 * @access public
	 * @return array
	 */
	public function register_plugin( $forms ) {
		$forms[ $this->plugin_name ] = 'Bricks';
		return $forms;
	}

	/**
	 * Add form name control to Bricks Form
	 *
	 * Fire on `bricks/elements/form/controls` hook
	 *
	 * @param array $controls form controls
	 * @access public
	 * @return array update controls array
	 */

	public function add_form_control( $controls ) {
		$controls['fvFormName'] = [
			'tab'   => 'content',
			'label' => esc_html__( 'Form Name', 'wpv-fv' ),
			'type'  => 'text',
		];

		return $controls;
	}

	/**
	 * Runs on Bricks Form Submit
	 *
	 * Bricks form submit handling by execute our function on same ajax function of bricks builder.
	 *
	 * @access public
	 * @return void
	 */
	public function form_submit($response, $form) {
		
		\Bricks\Ajax::verify_nonce('bricks-nonce-form');

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$form_settings = \Bricks\Helpers::get_element_settings( $_POST['postId'], $_POST['formId'] );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$form_id = $this->is_form_global( $_POST['postId'], $_POST['formId'] );

		$file_data = $this->handle_files( $form_settings );

		if ( self::$errors ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$form_fields = stripslashes_deep( $_POST );

		$data                 = [];
		$id                   = $form_id;
		$data['plugin_name']  = $this->plugin_name;
		$data['id']           = $id;
		$data['captured']     = current_time( 'mysql', 0 );
		$data['captured_gmt'] = current_time( 'mysql', 1 );

		$data['title'] = isset( $form_settings['fvFormName'] ) ? $form_settings['fvFormName'] : 'Bricks Form';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data['url']              = $_POST['referrer'];
		$posted_data              = array_merge( $form_fields, $file_data );
		$posted_data['fv_plugin'] = $this->plugin_name;
		$posted_data              = $this->prepare_form_data( $posted_data );

		$settings = get_option( 'fvSettings' );

		if ( Utils::key_exists( 'save_ip_address', $settings ) && true === $settings['save_ip_address'] ) {
			$posted_data['IP'] = $this->get_user_ip();
		}

		$posted_data['fv_form_id'] = $id;
		$data['posted_data']       = $posted_data;

		self::$submission_id = $this->insert_entries( $data );
		$this->save_columns_alias( $id, $form_settings );

		return $response;
	}

	/**
	 * Check Form is global
	 *
	 * Check form is global and if it global return the value of global
	 *
	 * @param string $post_id
	 * @param string $element_id form id
	 * @access public
	 * @return string updated $element_id if it is global
	 */

	public function is_form_global( $post_id, $element_id ) {
		$post_meta = get_post_meta( $post_id, '_bricks_page_content_2' )[0];

		foreach ( $post_meta as $key => $value ) {
			if ( $element_id === $value['id'] && $value['name'] === 'form' && isset( $value['global'] ) ) {
				$element_id = $value['global'];
			}
		}
		return $element_id;
	}
	/**
	 * Prepare Form data to save
	 *
	 * @param array $data original form data
	 * @access public
	 * @return array
	 */
	private function prepare_form_data( $data ) {
		$posted_data = [];

		foreach ( $data as $key => $value ) {
			if ( ! str_contains( $key, 'form-field-' ) ) {
				continue;
			}
			$key                 = substr( $key, 11 );
			$posted_data[ $key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
		}

		return $posted_data;
	}

	/**
	 * Save Column data to databse
	 *
	 * Runs after insert data to Save columns label properly to show proper label.
	 *
	 * @param array $form_id form id
	 * @param array $form_settings form settings
	 * @access public
	 * @return void
	 */

	public function save_columns_alias( $form_id, $form_settings ) {
		$saved_columns_option = Utils::get_fv_keys();
		$saved_columns_key    = $this->plugin_name . '_' . $form_id;

		$form_columns = $this->get_col_key_val( $form_settings );
		if ( $saved_columns_option && Utils::key_exists( $saved_columns_key, $saved_columns_option ) && $saved_columns_option[ $saved_columns_key ] ) {

			$saved_columns = $saved_columns_option[ $saved_columns_key ];
			// check all fields are saved if not new field add
			foreach ( array_keys( (array) $form_columns ) as $key => $value ) {
				$keyFind = array_search( $value, array_column( $saved_columns, 'colKey' ), true );
				if ( false === $keyFind ) {
					$saved_columns[ $value ] = $form_columns[ $value ];
				}
			}
			$saved_columns_option[ $saved_columns_key ] = $saved_columns;
		} else {
			$saved_columns_option[ $saved_columns_key ] = $form_columns;
		}
		update_option( 'fv-keys', $saved_columns_option );
	}

	/**
	 * Get Form Column Data in key value pair
	 *
	 * @param array $form_settings form settings
	 * @access public
	 * @return array column in key data pair
	 */

	public function get_col_key_val( $form_settings ) {
		$columns       = [];
		$columns['id'] = (object) [
			'colKey'  => 'id',
			'alias'   => 'ID',
			'visible' => true,
		];

		$fields = $form_settings['fields'];

		foreach ( $fields as $field ) {
			$columns[ $field['id'] ] = (object) [
				'colKey'  => $field['id'],
				'alias'   => $field['label'],
				'visible' => true,
			];
		}

		return $columns;
	}

	public function handle_files( $form_settings ) {
		$file_data   = [];
		$fileUpload  = [];
		$uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'form-vibes/bricks';
		if ( ! file_exists( $uploads_dir ) ) {
			wp_mkdir_p( $uploads_dir );
		}
		$bricksupload = wp_upload_dir();
		$fv_dirname   = $bricksupload['baseurl'] . '/form-vibes/bricks';

		$fields = $form_settings['fields'];

		foreach ( $fields as $field ) {
			if ( $field['type'] === 'file' ) {
				$fileUpload[ $field['id'] ] = [
					'fileUploadSize'         => $field['fileUploadSize'] ?? wp_max_upload_size(),
					'fileUploadLimit'        => $field['fileUploadLimit'] ?? '',
					'fileUploadAllowedTypes' => $field['fileUploadAllowedTypes'] ?? '',
				];
			}
		}
		foreach ( $_FILES as $fieldKey => $files ) {
			$key = substr( $fieldKey, 11 );
			if ( empty( $files['name'] ) ) {
				continue;
			}
			if ( $fileUpload[ $key ]['fileUploadLimit'] !== '' && count( $files['name'] ) > $fileUpload[ $key ]['fileUploadLimit'] ) {
				self::$errors[] = 'File upload limit exceed';
				return; // have to uncomment
			}
			foreach ( $files['name'] as $inputKey => $value ) {
				$fileType = strtolower( explode( '/', $files['type'][ $inputKey ] )[1] );
				$fileName = $files['name'][ $inputKey ];
				$tmpName  = $files['tmp_name'][ $inputKey ];
				$fileSize = $files['size'][ $inputKey ];

				$allowedTypes = $fileUpload[ $key ]['fileUploadAllowedTypes'];

				$allowedTypes = str_replace( '.', '', strtolower( $fileUpload[ $key ]['fileUploadAllowedTypes'] ) );
				$allowedTypes = array_map( 'trim', explode( ',', $allowedTypes ) );

				// check file type allowed
				if ( count( $allowedTypes ) ) {
					if ( $fileType === 'jpeg' || $fileType === 'jpg' ) {
						// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						if ( ! in_array( 'jpg', $allowedTypes ) && ! in_array( 'jpeg', $allowedTypes ) ) {
							self::$errors[] = 'File Type not allowed';
							return; // have to uncomment
						}
					} else {
						// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						if ( ! in_array( $fileType, $allowedTypes ) ) {
							self::$errors[] = 'File Type not allowed';
							return; // have to uncomment
						}
					}
				}
				// check file size limit
				if ( $fileUpload[ $key ]['fileUploadSize'] * 1000000 < $fileSize ) {
					self::$errors[] = 'File size exceed';
					return;
				}

				$filename                            = wp_rand( 1111111111, 9999999999 );
				$time_now                            = time();
				$file_data[ 'form-field-' . $key ][] = $fv_dirname . '/' . $time_now . '-' . $filename . '.' . $fileType;
				copy( $tmpName, $uploads_dir . '/' . $time_now . '-' . $filename . '.' . $fileType );
			}
		}

		return $file_data;
	}

}
