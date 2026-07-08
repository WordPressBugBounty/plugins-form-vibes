<?php

/**
 * Plugin Name: Form Vibes
 * Plugin URI: https://formvibes.com
 * Description: Lead Management and Graphical Reports for Elementor Pro, Contact Form 7 & Caldera form submissions.
 * Author: WPVibes
 * Version: 1.5.3
 * Author URI: https://wpvibes.com/
 * Text Domain: wpv-fv
 * License: GPLv2
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 8.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}
if ( !defined( 'WPV_FV__PATH' ) ) {
    define( 'WPV_FV__VERSION', '1.5.3' );
    // recommended pro version for free
    // define( 'WPV_FV__PRO_RECOMMENDED_VERSION', '0.5' );
    define( 'WPV_FV__URL', plugins_url( '/', __FILE__ ) );
    define( 'WPV_FV__PATH', plugin_dir_path( __FILE__ ) );
    define( 'WPV_FV_PLUGIN_BASE', plugin_basename( __FILE__ ) );
    if ( !defined( 'WPV_PRO_FV_VERSION' ) ) {
        // maintain
        define( 'WPV_PRO_FV_VERSION', '1.5.3' );
        define( 'WPV_FV_MIN_VERSION', '1.3.6' );
    }
}
// @fv_pro_freemius_code_start
// Only needed in pro zip not required in free zip
if ( !function_exists( 'wpv_fv' ) ) {
    // Create a helper function for easy SDK access.
    function wpv_fv() {
        global $wpv_fv;
        if ( !isset( $wpv_fv ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $wpv_fv = fs_dynamic_init( [
                'id'               => '4666',
                'slug'             => 'form-vibes',
                'premium_slug'     => 'form-vibes-pro',
                'type'             => 'plugin',
                'public_key'       => 'pk_321780b7f1d1ee45009cf6da38431',
                'is_premium'       => false,
                'premium_suffix'   => 'Pro',
                'has_addons'       => false,
                'has_paid_plans'   => true,
                'menu'             => [
                    'slug'       => 'fv-leads',
                    'first-path' => 'admin.php?page=fv-db-settings',
                    'support'    => false,
                ],
                'is_live'          => true,
                'is_org_compliant' => true,
            ] );
        }
        return $wpv_fv;
    }

    // Init Freemius.
    wpv_fv();
    // Signal that SDK was initiated.
    do_action( 'wpv_fv_loaded' );
}
// @fv_pro_freemius_code_end
// Load the plugin text domain for translations.
add_action( 'init', function () {
    load_plugin_textdomain( 'wpv-fv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );
add_action( 'plugins_loaded', function () {
    require_once WPV_FV__PATH . '/vendor/autoload.php';
    //if strauss.phar is not present, run the following command to download it before running composer install or update
    // curl -L "https://github.com/BrianHenryIE/strauss/releases/download/0.19.5/strauss.phar" -o bin/strauss.phar
    require_once __DIR__ . '/vendor-prefixed/autoload.php';
    require_once WPV_FV__PATH . '/inc/bootstrap.php';
    FormVibes\Classes\DbTables::fv_plugin_activated();
    \WPVibes\FormVibes\Vendor\WPVibes\ReviewReminder\ReviewReminder::register( array(
        'plugin_slug'     => 'form-vibes',
        'plugin_name'     => 'Form Vibes',
        'plugin_file'     => __FILE__,
        'text_domain'     => 'wpv-fv',
        'triggers'        => array(
            'time'  => 7 * DAY_IN_SECONDS,
            'usage' => array(
                'option_key' => 'submissions_logged',
                'threshold'  => 20,
            ),
        ),
        'trigger_logic'   => 'OR',
        'screens'         => array(
            'dashboard',
            'plugins',
            'toplevel_page_fv-leads',
            'form-vibes_page_fv-leads'
        ),
        'capability'      => 'manage_options',
        'icon_url'        => WPV_FV__URL . 'assets/images/fv-logo.svg',
        'message_heading' => 'Enjoying Form Vibes?',
        'message_body'    => "Glad it's helping you capture more leads! If it's been useful, a quick review on WordPress.org would mean a lot to our team and helps other folks discover the plugin.",
    ) );
} );