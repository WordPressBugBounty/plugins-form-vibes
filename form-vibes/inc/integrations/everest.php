<?php

namespace FormVibes\Integrations;

use FormVibes\Classes\Utils;
use FormVibes\Integrations\Base;

/**
 * WS Form plugin class
 *
 * Register the WS Form plugin
 */

class Everest extends Base {

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
		$this->plugin_name = 'everest-forms';

		$this->set_skip_fields();

		add_filter( 'fv_forms', [ $this, 'register_plugin' ] );
        add_action('everest_forms_process', [ $this, 'everest_form_insert' ], 10, 3 );
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
		$forms[ $this->plugin_name ] = 'Everest Forms';
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
		$this->skip_fields = [
             'private-note',
             'hidden',
             'privacy-policy',
        ];
	}


	/**
	 * Run when the form is submitted
	 *
	 * @access public
	 * @return string|mixed
	 */
	public function everest_form_insert( $fields, $entry, $form_data) {     
        // echo '<pre>';  print_r($fields); echo '</pre>';
        // echo '<pre>';  print_r($form_data); echo '</pre>';
        // die('dfaf');
        $data     = [];
        $form_id   = $form_data['id'];
        $form_name = $form_data['settings']['form_title'];
        $save_entry = true;

		$save_entry = apply_filters( 'formvibes/everest-forms/save_record', $save_entry, $fields );

		if ( ! $save_entry ) {
			return;
		}

		$data['plugin_name']  = $this->plugin_name;
		$data['id']           = $form_id;
		$data['captured']     = current_time( 'mysql', 0 );
		$data['captured_gmt'] = current_time( 'mysql', 1 );
		$data['title']        = $form_name;
		$data['url']          = $_SERVER['HTTP_REFERER'];
		$posted_data          = $this->prepare_posted_data( $fields, $form_data );
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
    $uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'form-vibes/everest-form';
    if ( ! file_exists( $uploads_dir ) ) {
        wp_mkdir_p( $uploads_dir );
    }

    $everestupload = wp_upload_dir();
    $fv_dirname    = $everestupload['baseurl'] . '/form-vibes/everest-form';
    
    $posted_data = [];
    
    foreach ( $fields as $key => $values ) {
        if ( in_array( $values['type'], $this->skip_fields, true ) || ! isset( $values['type'] ) ) {
            continue;
        }
        
        if ( $values['type'] === 'file-upload' || $values['type'] === 'image-upload' ) {
            $uploaded_files = [];
            
            // Check if value_raw exists and is an array (multiple files)
            if ( isset( $values['value_raw'] ) && is_array( $values['value_raw'] ) ) {
                foreach ( $values['value_raw'] as $fileValue ) {
                    // Get file extension
                    $filetype = '.' . $fileValue['ext'];
                    
                    // Generate random filename with timestamp
                    $filename = wp_rand( 1111111111, 9999999999 );
                    $time_now = time();
                    
                    $new_filename = $time_now . '-' . $filename . $filetype;
                    
                    // Add new file URL to uploaded files array
                    array_push( $uploaded_files, $fv_dirname . '/' . $new_filename );
                    
                    // Get the source file path from the URL
                    $source_file = str_replace( $everestupload['baseurl'], $everestupload['basedir'], $fileValue['value'] );
                    
                    // echo $uploads_dir . '/' . $new_filename;
                    // echo "<br>";
                    
                    // Copy file from Everest Forms upload location to Form Vibes storage
                    if ( file_exists( $source_file ) ) {
                        copy( $source_file, $uploads_dir . '/' . $new_filename );
                    }
                    
                }
            }
            // Store files as comma-separated string
            $posted_data[ $values['meta_key'] ] = implode( ', ', $uploaded_files );
            
        } elseif ( $values['type'] === 'radio' ) {
            $posted_data[ $values['meta_key'] ] = isset( $values['value_raw'] ) ? $values['value_raw'] : '';
        }elseif($values['type'] === 'dropdown' || $values['type'] === 'checkbox') {
            $posted_data[ $values['meta_key'] ] = isset( $values['value_raw'] ) ? (is_array( $values['value_raw'] ) ? implode( ', ', $values['value_raw'] ) : $values['value_raw']) : '';

        }elseif($values['type'] === 'country'){
            $all_contries = evf_get_countries();
            $value = isset( $values['value'] ) ? (is_array( $values['value'] ) ? $values['value']['country_code'] : '' ) : '';
            if($value !== ''){
                $country_name = isset($all_contries[$value]) ? $all_contries[$value] : $value;
                $posted_data[ $values['meta_key'] ] = $country_name;
            }
        } elseif($values['type'] == 'rating'){
            $posted_data[ $values['meta_key'] ] = isset( $values['value'] ) ? (is_array( $values['value'] ) ? $values['value']['value'] : '' ) : '';
        }elseif($values['type'] === 'address'){
            $address_value = '';
            if(isset($values['address1'])){
                $address_value .= $values['address1'] . "\n"; 
            }
            if(isset($values['address2'])){
                $address_value .= $values['address2'] . "\n"; 
            }
            
            if(isset($values['city']) && isset($values['state'])){
                $address_value .= $values['city'] . ', ' . $values['state'] . "\n"; 
            }elseif(isset($values['city']) ){
                $address_value .= $values['city']  . "\n"; 
            }elseif( isset($values['state'])){
                $address_value .= $values['state'] . "\n"; 
            }
            if(isset($values['postal'])){
                $address_value .= $values['postal'] . "\n"; 
            }
            if(isset($values['country'])){
                $address_value .= $values['country'] . "\n"; 
            }
            $posted_data[ $values['meta_key'] ] = $address_value;
        }
        else {
            $posted_data[ $values['meta_key'] ] = is_array( $values['value'] ) ? implode( ', ', $values['value'] ) : $values['value'];
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
        // Check if Everest Forms classes exist
        if ( ! class_exists( 'EVF_Form_Handler' ) ) {
            return $cols;
        }
    
        // Get form handler instance
        $form_handler = new \EVF_Form_Handler();
    
        // Fetch form data with content only
        $form_data = $form_handler->get( 
            $form_id, 
            array( 'content_only' => true ) 
        );
    
        // Check if form data exists and has form fields
        if ( ! $form_data || ! isset( $form_data['form_fields'] ) ) {
            return $cols;
        }
    
        // Loop through form fields
        foreach ( $form_data['form_fields'] as $field_id => $field ) {
            // Skip if no meta_key or label
            if ( ! isset( $field['meta-key'] ) || ! isset( $field['label'] ) ) {
                continue;
            }
            
            $meta_key = $field['meta-key'];
            $label = $field['label'];
            
            // Update column alias if it exists and matches the meta key
            if ( isset( $cols[ $meta_key ] ) && $cols[ $meta_key ]['alias'] === $meta_key ) {
                $cols[ $meta_key ]['alias'] = $label;
            }
        }
    
        return $cols;
    }
}
