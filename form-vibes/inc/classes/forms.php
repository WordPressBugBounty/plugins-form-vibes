<?php

namespace FormVibes\Classes;

/**
 * A utility class for managing the forms
 */
class Forms {

	/**
	 * The instance of the class.
	 * @var null|object
	 *
	 */
	private static $instance = null;

	/**
	 * The forms of the plugin.
	 * @var array
	 */
	public static $forms = [];

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

	private function __construct() {
		self::$forms = $this->get_all_forms();
	}


	/**
	 * Gets all the forms from the database.
	 *
	 * @access private
	 * @since 1.4.4
	 * @return array
	 */
	public function get_all_forms() {
		// get forms saved in options
		$forms = get_option( 'fv_forms' );

		$forms = apply_filters( 'formvibes/forms', $forms );

		return $forms;
	}
}
