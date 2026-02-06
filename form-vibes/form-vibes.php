<?php

/**
 * Plugin Name: Form Vibes
 * Plugin URI: https://formvibes.com
 * Description: Lead Management and Graphical Reports for Elementor Pro, Contact Form 7 & Caldera form submissions.
 * Author: WPVibes
 * Version: 1.5.1
 * Author URI: https://wpvibes.com/
 * Text Domain: wpv-fv
 * License: GPLv2
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * 
 * @fs_premium_only inc/pro, assets/css/pro, assets/script/pro, assets/dist/pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! defined( 'WPV_FV__PATH' ) ) {
	define( 'WPV_FV__VERSION', '1.5.1' );
	// recommended pro version for free
	// define( 'WPV_FV__PRO_RECOMMENDED_VERSION', '0.5' );
	define( 'WPV_FV__URL', plugins_url( '/', __FILE__ ) );
	define( 'WPV_FV__PATH', plugin_dir_path( __FILE__ ) );
	define( 'WPV_FV_PLUGIN_BASE', plugin_basename( __FILE__ ) );

	if ( ! defined( 'WPV_PRO_FV_VERSION' ) ) {
		// maintain
		define( 'WPV_PRO_FV_VERSION', '1.5.0' );
		define( 'WPV_FV_MIN_VERSION', '1.3.6' );
	}
}

// Load the plugin text domain for translations.
add_action( 'init', function () {
	load_plugin_textdomain( 'wpv-fv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

add_action('plugins_loaded', function(){

	require_once WPV_FV__PATH . '/vendor/autoload.php';
	require_once WPV_FV__PATH . '/inc/bootstrap.php';
	FormVibes\Classes\DbTables::fv_plugin_activated();
	//load_plugin_textdomain( 'wpv-fv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
});