<?php

namespace FormVibes\Modules\Submissions;

use FormVibes\Classes\FV_Columns;
use FormVibes\Classes\FV_Query;
use FormVibes\Classes\Permissions;
use FormVibes\Classes\Utils;
use FormVibes\Plugin;
use FormVibes\Integrations\Base;

/**
 * The submission class in order to manage plugin submissions
 *
 */

class Module {

	/**
	 * The instance of the class.
	 * @var null|object $instance
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
	 * @access private
	 * @since 1.4.4
	 * @return void
	 */
	private function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ], 10, 1 );
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 9 );

		// ajax
		add_action( 'wp_ajax_fv_get_submissions', [ $this, 'get_submissions' ], 10, 3 );
		add_action( 'wp_ajax_fv_delete_submissions', [ $this, 'delete_submissions' ], 10, 3 );
		add_action( 'wp_ajax_fv_get_columns', [ $this, 'get_columns' ], 10 );

		// Add Script type module
		add_filter('script_loader_tag', [ $this, 'add_type_attribute' ] , 10, 3);
	}

	/**
	 * Get the columns data
	 *
	 * Fired by `wp_ajax_fv_get_columns` action.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array|mixed
	 */
	public function get_columns() {

		if ( ! Permissions::check_permission( Permissions::$CAP_SUBMISSIONS ) ) {
			die( 'Sorry, you are not allowed to do this action!' );
		}

		if ( ! wp_verify_nonce( $_POST['ajaxNonce'], 'fv_ajax_nonce' ) ) {
			die( 'Sorry, your nonce did not verify!' );
		}

		$params  = (array) json_decode( stripslashes( sanitize_text_field( $_POST['params'] ) ) );
		$plugin  = $params['plugin'];
		$form_id = $params['formId'];

		$columns = Utils::get_table_columns( $plugin, $form_id );
		return wp_send_json( $columns );
	}



	/**
	 * Delete the submissions
	 *
	 * Fired by `wp_ajax_fv_delete_submissions` action.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array|mixed
	 */
	public function delete_submissions() {
		
		if (! Permissions::check_permission( Permissions::$CAP_DELETE ) ) {
			die( 'Sorry, you are not allowed to do this action!' );
		}
		if ( ! wp_verify_nonce( $_POST['ajaxNonce'], 'fv_ajax_nonce' ) ) {
			die( 'Sorry, your nonce did not verify!' );
		}

		$params = (array) json_decode( stripslashes( sanitize_text_field( $_POST['params'] ) ) );
		Base::delete_entries( $params['ids'] );
		// Base::delete_entries( ["9) AND (1=1);-- -"] );
	}

	/**
	 * Get the submissions
	 *
	 * Fired by `wp_ajax_fv_get_submissions` action.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array|mixed
	 */
	public function get_submissions( $params ) {

		if ( ! Permissions::check_permission( Permissions::$CAP_SUBMISSIONS ) ) {
			wp_send_json(
				[
					'is_error' => true,
					'message'  => 'Sorry, you are not allowed to do this action!',
				]
			);
			die( 'Sorry, you are not allowed to do this action!' );
		}
		
		if ( ! wp_verify_nonce( $_POST['ajaxNonce'], 'fv_ajax_nonce' ) ) {
			wp_send_json(
				[
					'is_error' => true,
					'message'  => 'Sorry, your nonce did not verify!',
				]
			);
			die( 'Sorry, your nonce did not verify!' );
		}

		

		$params = (array) json_decode( stripslashes( sanitize_text_field( $_POST['params'] ) ) );

		$fv_query          = new FV_Query( $params );
		$result            = $fv_query->get_result();
		$result['columns'] = [];

		if ( count( array_keys( $result['data'] ) ) > 0 || true ) {
			$columns_obj                = new FV_Columns( $params );
			$cols                       = $columns_obj->get_columns();
			$result['columns']          = $cols['columns'];
			$result['original_columns'] = $cols['original_columns'];
		}
		wp_send_json( $result );
	}

	/**
	 * Register the script for the admin area.
	 *
	 * Fired by `admin_enqueue_scripts` action.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_scripts() {
		$screen = get_current_screen();
		if ( $screen->id === 'toplevel_page_fv-leads' ) {
			wp_enqueue_script( 'submissions-js', WPV_FV__URL . 'assets/dist/js/submission.js', [ 'fv-js' ], WPV_FV__VERSION, true );
			wp_enqueue_style( 'fv-submission-css', WPV_FV__URL . 'assets/dist/css/submission.css', '', WPV_FV__VERSION );
			//wp_enqueue_style('date-picker-css', WPV_FV__URL . 'assets/dist/css/date-picker.css', '', WPV_FV__VERSION);
			//wp_enqueue_style( 'fv-main', WPV_FV__URL . 'assets/dist/css/main.css', '', WPV_FV__VERSION );
		}
	}

	/**
	 * Create the admin menu.
	 *
	 * Fired by `admin_menu` action.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu() {
		$caps = Plugin::$capabilities->get_caps();

		add_menu_page( 'Form Vibes Leads', 'Form Vibes', $caps['fv_leads'], 'fv-leads', [ $this, 'render_root' ], 'dashicons-analytics', 30 );
		add_submenu_page( 'fv-leads', 'Form Vibes Submissions', 'Submissions', $caps['fv_leads'], 'fv-leads', [ $this, 'render_root' ], 1 );
	}

	/**
	 * Render the root element
	 *
	 * @access public
	 * @return void
	 */
	public function render_root() {
		$caps = Plugin::$capabilities->get_caps();

		if ( ! Plugin::$capabilities->check( $caps['fv_leads'] ) ) {
			return;
		}
		?>
		<div id="fv-submissions">

		</div>
		<!-- <div id="fv-analytics">

		</div> -->
		<?php
	}

	function add_type_attribute($tag, $handle, $src){
		// if not your script, do nothing and return original $tag
		if ( 'submissions-js' !== $handle ) {
			return $tag;
		}
		// change the script tag by adding type="module" and return it.
		$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
		return $tag;
	}
}
