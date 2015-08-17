<?php
/**
 * Plugin Name: Alphasss Top Navigation
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss Top Navigation
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//Loads the plugin's translated strings
load_textdomain( 'alphasss', plugin_dir_path( __FILE__ ) . '/languages/alphasss-top-navigation-' . get_locale() . '.mo' );

__('Blog', 'alphasss-top-navigation');
__('Register', 'alphasss-top-navigation');
__('Home', 'alphasss-top-navigation');
__('Browse', 'alphasss-top-navigation');
__('Forum', 'alphasss-top-navigation');

add_action( 'plugins_loaded', function(){

	// Add login/logout to the navigation menu
	add_filter('wp_nav_menu_items', function($items, $args) {
		ob_start();
		wp_loginout('/');
		$loginoutlink = str_replace('Log in', __('Login', 'alphasss-top-navigation'), ob_get_contents());
		ob_end_clean();

		// Add login element to navigation menu
		if ( ! is_user_logged_in() ) {
			$items .= '<li>'. $loginoutlink .'</li>';
		}

		return $items;
	}, 10, 2);


	// Top navigation localization
	add_filter('wp_get_nav_menu_items', function($items, $menu){

		foreach ( $items as $key => $item ) {
			$items[$key]->title = __($items[$key]->title, 'alphasss-top-navigation');

			if ($items[$key]->title == 'Register') {
				if (is_user_logged_in()) {
					// Remove register for member
					if (current_user_can('generate_invitation_code')) {
						unset($items[$key]);
					} else {
						$items[$key]->url = get_register_url();

						// Set active class in menu
						if ( $_SERVER['REQUEST_URI'] == get_pre_member_register_uri() ) {
							$items[$key]->classes[] = 'current-menu-item page_item';
						}
					}
				}
			}
	    }

	    return $items;
	}, 10, 2);
	//--
});