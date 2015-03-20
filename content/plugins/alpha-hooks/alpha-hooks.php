<?php
/*
Plugin Name: Alpha Hooks
Plugin URI:
Description: All filter and action hooks used on the site, gathered together as one happy family.
Version: 1.0.0
Author: Andrew Voyticky
Author URI:
Text Domain: alpha-hooks
*/

// Require helper functions
require_once('includes/alphasss-hooks-functions.php');

load_plugin_textdomain('alpha-hooks', false, basename(dirname( __FILE__ )) . '/languages');

wp_enqueue_script( 'bootstrap-js', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js', array('jquery') );
wp_enqueue_style( 'bootstrap-css', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css' );

// Add login/logout to the navigation menu
add_filter('wp_nav_menu_items', function($items, $args) {
	ob_start();
	wp_loginout('/');
	$loginoutlink = str_replace('Log in', __('Login', 'alpha-hooks'), ob_get_contents());
	ob_end_clean();

	// Add login element to navigation menu
	if ( ! is_user_logged_in() ) {
		$items .= '<li>'. $loginoutlink .'</li>';
	}

	return $items;
}, 10, 2);

// Redirect after user looged in
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
//--

add_action( 'xprofile_data_before_save', function($field){

	switch ($field->field_id) {
		case 45:
			if ( ! preg_match( '/^[a-z0-9\'_.-]+$/i', $field->value ) ) {

				$field->field_id = 0;
				bp_core_add_message(__("You may use only the following characters: letters (a-z), numbers (0-9), dashes (-), underscores (_), apostrophes ('), and periods (.). Try again please.", 'alpha-hooks'), 'error');
			}

			$display_name = bp_get_profile_field_data( [
				'user_id' => bp_loggedin_user_id(),
				'field'   => 45
			] );

			if ( $display_name != $field->value ) {
				// User exists? Show validation error
				if ( username_exists( $field->value ) ) {

					$field->field_id = 0;
					bp_core_add_message(__('This nickname is already taken. Please choose another one.', 'alpha-hooks'), 'error');
				}
			}
		break;
	}

}, 1, 1 );

add_filter('alphasss_top_alerts', function(){

	echo '<div id="top-alerts"></div>';

});