<?php
/**
 * Plugin Name: AlphaSSS Login
 * Plugin URI:  http://alphasss.com/
 * Description: Login/Logout hooks and actions
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

add_filter( 'login_redirect', function($redirect_to, $request, $user){

	global $user;

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			//check for pre_member role
			if ( in_array( 'pre_member', $user->roles ) ) {
				// Return registre pre-member url
				return get_pre_member_register_uri();
			} else {
				return '/browse/' . $user->user_login . '/activity/';
			}
		}
	}
}, 10, 3);