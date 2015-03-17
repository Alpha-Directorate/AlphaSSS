<?php
/**
 * Plugin Name: Alphasss Top Bar
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss Top Bar
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//Loads the plugin's translated strings
load_plugin_textdomain('alphasss-top-bar', false, basename(dirname( __FILE__ )) . '/languages');

add_action( 'plugins_loaded', function(){
	// Top navigation localization
	add_filter('wp_get_nav_menu_items', function($items, $menu){

	    return $items;
	}, 10, 2);
	//--
});