<?php
/**
 * Example: Form Vibes integration.
 *
 * Drop this kind of bootstrap into your plugin's main file (or a dedicated
 * include) to wire up the review reminder.
 *
 * Trigger logic: AND — at least 7 days installed AND at least 25 submissions logged.
 */

// In your main plugin file:
require_once __DIR__ . '/vendor/autoload.php';

use WPVibes\ReviewReminder\ReviewReminder;

add_action( 'plugins_loaded', function () {
	ReviewReminder::register( array(
		'plugin_slug'   => 'form-vibes',
		'plugin_name'   => 'Form Vibes',
		'plugin_file'   => __FILE__,
		'text_domain'   => 'form-vibes',

		'triggers'      => array(
			'time'  => 7 * DAY_IN_SECONDS,
			'usage' => array(
				'option_key' => 'submissions_logged',
				'threshold'  => 25,
			),
		),
		'trigger_logic' => 'AND',

		'screens'       => array( 'dashboard', 'plugins', 'toplevel_page_form-vibes', 'form-vibes_page_*' ),
		'capability'    => 'manage_options',
		'icon_url'      => plugins_url( 'assets/images/fv-logo.svg', __FILE__ ),
	) );
} );

// Wherever Form Vibes records a new submission:
add_action( 'form_vibes_submission_logged', function () {
	ReviewReminder::increment( 'form-vibes', 'submissions_logged' );
} );
