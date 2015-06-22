<?php
/**
 * Plugin Name: AlphaSSS Login
 * Plugin URI:  http://alphasss.com/
 * Description: Login/Logout hooks and actions
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 * Text Domain: alphasss
 */

load_textdomain( 'alphasss', WP_LANG_DIR . '/plugins/alphasss/alphasss-' . get_locale() . '.mo' );

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

add_filter('wp_authenticate_user', function($user) {
	global $wpdb;

	$activation_link = $wpdb->get_row(sprintf("SELECT * FROM `wp_dmec` WHERE `user_login` = '%s'", $user->data->user_login), ARRAY_A);

	return $activation_link != NULL
		? new WP_Error('account_not_confirmed',__('Your account isn\'t active. Please click the link in your email to confirm it.', 'alphasss'))
		: $user;

}, 10, 2);