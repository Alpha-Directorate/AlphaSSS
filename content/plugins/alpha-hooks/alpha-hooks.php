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

add_filter('alphasss_top_alerts', function(){

	echo '<div id="top-alerts"></div>';

});