<?php

namespace FormVibes\Classes;

/**
 * A class to manage the capabilities of the plugin.
 */
class Capabilities {


	/**
	 * The instance of the class.
	 * @var null|object $instance
	 *
	 */
	private static $instance = null;

	/**
	 * The capabilities of the plugin.
	 * @var array $caps
	 */
	private $caps = [];

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
		$this->set_caps();
	}

	/**
	 * Sets the capabilities of the plugin.
	 *
	 * @access private
	 * @since 1.4.4
	 * @return void
	 */
	private function set_caps() {
		$caps = [
			'fv_leads'     => 'publish_posts',
			'fv_analytics' => 'publish_posts',
			'fv_view_logs' => 'publish_posts',
		];

		$this->caps = $caps;
	}

	/**
	 * Gets the capabilities of the plugin.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array @var $this->caps
	 */
	public function get_caps() {
		$this->caps = apply_filters( 'formvibes/capabilities', $this->caps );
		return $this->caps;
	}

	/**
	 * Get a capability by cap key.
	 *
	 * @access public
	 * @param string $cap_key The capability key.
	 * @since 1.4.4
	 * @return string|false
	 */
	public function get_cap( $cap_key ) {
		if ( isset( $this->caps[ $cap_key ] ) ) {
			return $this->caps[ $cap_key ];
		}
		return false;
	}

	/**
	 * Checks if current user has a capability.
	 *
	 * @access public
	 * @param string $cap The capability key.
	 * @since 1.4.4
	 * @return bool
	 */
	public static function check( $cap ) {
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			if ( ! $user->has_cap( $cap ) ) {
				return false;
			}
		}
		return true;
	}
}
