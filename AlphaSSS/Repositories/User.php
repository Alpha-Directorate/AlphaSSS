<?php namespace AlphaSSS\Repositories;

if ( ! defined( 'ABSPATH' ) ) exit;

class User {

	/**
	 * Method checks is email already exists in database
	 * 
	 * @param string $email
	 * @return boolean
	 */
	public static function isEmailExists($email)
	{
		global $wpdb;

		$hashed_email = hash('sha512', $email);

		$found_email = $wpdb->get_row(sprintf("SELECT * FROM `wp_usermeta` WHERE `meta_key` = 'hashed_email' AND `meta_value` = '%s'", $hashed_email), ARRAY_A);

		return $found_email != NULL;
	}

	/**
	 * Method checks is current user have specific role
	 * 
	 * @param string $role_key
	 * @return boolean
	 */
	public static function hasRole($role_key)
	{
		// Detect current user
		$current_user = wp_get_current_user();

		if ( $current_user instanceof \WP_User ) {

			foreach ( $current_user->roles as $role ) {
				// Returns true if user has requested or administrator role
				if ( $role == $role_key || $role == 'administrator' ) return true;
			}
		}

		return false;
	}

	/**
	 * Method checks that current user has group(s) where he/she is admin
	 * 
	 * @return boolean
	 */
	public static function isAdminOfGroup()
	{
		// Detect current user
		$current_user = wp_get_current_user();

		if ( $current_user instanceof \WP_User ) {

			// Gets all groups where current user is admin
			$user_group = \BP_Groups_Member::get_is_admin_of($current_user->ID);
			
			return (boolean) $user_group['groups'];
		}

		return false;
	}
}