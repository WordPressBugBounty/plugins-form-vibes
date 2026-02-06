<?php

namespace FormVibes\Modules\DashboardWidgets;

use FormVibes\Plugin;
use FormVibes\Classes\Settings;
use FormVibes\Classes\Capabilities;

/**
 * The dashboard widget class in order to manage the form submissions analytics on dashboard.
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
		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widgets' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ], 10, 1 );

		add_filter('script_loader_tag', [ $this, 'add_type_attribute' ] , 10, 3);
	}

	/**
	 * Add the dashboard widgets.
	 *
	 * Fired by `wp_dashboard_setup` action.
	 *
	 * @access public
	 * @return void
	 */
	public function add_dashboard_widgets() {
		$settings         = new Settings();
		$dashboard_widget = $settings->get_setting_value_by_key( 'dashboard_widget' );
		
		if ( ! $dashboard_widget ) {
			return;
		}

		$caps = Capabilities::instance()->get_caps();

		if ( ! Capabilities::check( $caps['fv_analytics'] ) ) {
			return;
		}

		add_meta_box( 'form_vibes_widget-0', 'Form Vibes Analytics', [ $this, 'dashboard_widget' ], null, 'normal', 'high', 0 );
	}


	/**
	 * Render the dashboard widget.
	 *
	 * Fired by `form_vibes_widget-0` action.
	 *
	 * @access public
	 * @return void
	 */
	public function dashboard_widget( $vars, $i ) {
		echo '<div name="dashboard-widget" id="fv-dashboard-widgets-' . esc_html( $i['args'] ) . '">
				</div>';
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

		if ( 'dashboard' === $screen->id ) {
			wp_enqueue_script( 'dashboard-select-form-js', WPV_FV__URL . 'assets/script/add-dashboard-widget-gear-icon.js', [], WPV_FV__VERSION, true );
			wp_enqueue_script( 'dashboard-js', WPV_FV__URL . 'assets/dist/js/dashBoard.js', [ 'wp-components' ], '1.0.0', true );
			wp_enqueue_script( 'script-js', WPV_FV__URL . 'assets/script/index.js', '', WPV_FV__VERSION, true );
			wp_enqueue_style( 'dashboard-css', WPV_FV__URL . 'assets/dist/css/dashboard.css', '', WPV_FV__VERSION );
		}
	}

	function add_type_attribute($tag, $handle, $src){
		// if not your script, do nothing and return original $tag
		if ( 'dashboard-js' !== $handle ) {
			return $tag;
		}
		// change the script tag by adding type="module" and return it.
		$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
		return $tag;
	}
}
