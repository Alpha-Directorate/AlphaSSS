<?php namespace AlphaSSS\Repositories;

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
				if ( $role == $role_key ) return true;
			}
		}

		return false;
	}
}