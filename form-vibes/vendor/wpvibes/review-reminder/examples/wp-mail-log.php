<?php
/**
 * Example: WP Mail Log integration.
 *
 * Trigger logic: TIME_ONLY — 14 days after install. No usage event needed
 * because the value moment for a logger is "user came back to look at logs",
 * which is naturally captured by them being on the plugin's screen.
 */

require_once __DIR__ . '/vendor/autoload.php';

use WPVibes\ReviewReminder\ReviewReminder;

add_action( 'plugins_loaded', function () {
	ReviewReminder::register( array(
		'plugin_slug'   => 'wp-mail-log',
		'plugin_name'   => 'WP Mail Log',
		'plugin_file'   => __FILE__,
		'text_domain'   => 'wp-mail-log',

		'triggers'      => array(
			'time' => 14 * DAY_IN_SECONDS,
		),
		'trigger_logic' => 'TIME_ONLY',

		// Only show on the plugin's own screens — user is engaged when they're here.
		'screens'       => array( 'tools_page_wp-mail-log', 'wp-mail-log_page_*' ),
		'capability'    => 'manage_options',
	) );
} );
