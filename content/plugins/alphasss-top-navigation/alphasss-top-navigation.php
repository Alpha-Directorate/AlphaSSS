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
load_plugin_textdomain('alphasss-top-navigation', false, basename(dirname( __FILE__ )) . '/languages');

__('Blog', 'alphasss-top-navigation');
__('Register', 'alphasss-top-navigation');
__('Home', 'alphasss-top-navigation');
__('Browse', 'alphasss-top-navigation');
__('Forum', 'alphasss-top-navigation');

add_action( 'plugins_loaded', function(){
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