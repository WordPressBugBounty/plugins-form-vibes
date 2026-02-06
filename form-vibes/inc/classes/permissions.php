<?php

namespace FormVibes\Classes;

/**
 * A utility class for managing the plugin permissions
 */

class Permissions {


	/**
	 * Submission edit permissions.
	 * @var $CAP_EDIT
	 */
	public static $CAP_EDIT = 'edit_fv_submissions';
	/**
	 * Submission delete permissions.
	 * @var $CAP_DELETE
	 */
	public static $CAP_DELETE = 'delete_fv_submissions';
	/**
	 * Submission export permissions.
	 * @var $CAP_EXPORT
	 */
	public static $CAP_EXPORT = 'export_fv_submissions';
	/**
	 * Submission add notes permissions.
	 * @var $CAP_ADD_NOTES
	 */
	public static $CAP_ADD_NOTES = 'add_fv_notes';
	/**
	 * Submission delete notes permissions.
	 * @var $CAP_DELETE_NOTES
	 */
	public static $CAP_DELETE_NOTES = 'delete_fv_note';
	/**
	 * Submission view notes permissions.
	 * @var $CAP_VIEW_NOTES
	 */
	public static $CAP_VIEW_NOTES = 'view_fv_note';
	/**
	 * Submission change notes permissions.
	 * @var $CAP_CHANGE_NOTES
	 */
	public static $CAP_CHANGE_NOTES = 'change_fv_note';
	/**
	 * Submission view status permissions.
	 * @var $CAP_VIEW_STATUS
	 */
	public static $CAP_VIEW_STATUS = 'view_fv_status';
	/**
	 * Submission change status permissions.
	 * @var $CAP_CHANGE_STATUS
	 */
	public static $CAP_CHANGE_STATUS = 'change_fv_status';
	/**
	 * Submission view logs permissions.
	 * @var $CAP_LOGS
	 */
	public static $CAP_LOGS = 'view_fv_logs';
	/**
	 * Submission view permissions.
	 * @var $CAP_SUBMISSIONS
	 */
	public static $CAP_SUBMISSIONS = 'view_fv_submissions';
	/**
	 * Analytics view permissions.
	 * @var $CAP_ANALYTICS
	 */
	public static $CAP_ANALYTICS = 'view_fv_analytics';
	/**
	 * Manage data profile view permissions.
	 * @var $CAP_DATA_PROFILE
	 */
	public static $CAP_DATA_PROFILE = 'manage_fv_data_profiles';
	/**
	 * Manage export profile edit permissions.
	 * @var $CAP_EXPORT_PROFILE
	 */
	public static $CAP_EXPORT_PROFILE = 'manage_fv_export_profiles';

	/**
	 * Check if the user has the permission
	 * @param  string $permission The permission to check
	 * @param  int $user_id The user ID to check
	 * @access public
	 * @since 1.4.4
	 * @return bool
	 */
	public static function check_permission($permission, $user_id = null) {
		$user = wp_get_current_user();
		
		// NOt USE Furter
		// if (!$user_id) {
		// 	$user_id = $user->ID;
		// }
	
		// Use current_user_can() to check for capabilities instead of user_can()
		if (current_user_can('manage_options')) { // Check a suitable capability for admin users
			return true;
		}
	
		if (Utils::is_pro()) {
			if (current_user_can($permission)) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Gets all the permissions
	 * @access public
	 * @since 1.4.4
	 * @return array
	 */
	public static function get_permissions() {
		return apply_filters(
			'formvibes/permissions',
			[
				'edit_fv_submissions',
				'delete_fv_submissions',
				'export_fv_submissions',
				'add_fv_notes',
				'delete_fv_note',
				'view_fv_note',
				'change_fv_note',
				'view_fv_status',
				'change_fv_status',
				'view_fv_logs',
				'view_fv_submissions',
				'view_fv_analytics',
				'manage_fv_data_profiles',
				'manage_fv_export_profiles',
			]
		);
	}

	/**
	 * Checks if current user is admin
	 * @access public
	 * @since 1.4.4
	 * @return bool
	 */
	public static function is_admin() {
		if ( current_user_can( 'manage_options' ) ) {
			// user is admin
			return true;
		}
		return false;
	}
}
