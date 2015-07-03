<?php

/**
 * Plugin Name: Alphasss Theme Migration
 * Plugin URI:  http://alphasss.com/
 * Description: Plugin that dumps theme settings like customizer settings and menus
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 * Text Domain: alphasss
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require_once BASEPATH . 'wp/wp-includes/class-wp-customize-setting.php';
require_once 'class-option.php';

add_action( 'plugins_loaded', function(){

	// Check is is_updated is empty or 0
	if ( ! @file_get_contents(BASEPATH . 'migrations/theme/is_updated' ) ) {
		
		global $wp_customize;

		$data = @unserialize( file_get_contents( BASEPATH . 'migrations/theme/theme-customize.dat' ) );
		
		// Import custom options.
		if ( isset( $data['options'] ) ) {
			
			foreach ( $data['options'] as $option_key => $option_value ) {
				
				$option = new CEI_Option( $wp_customize, $option_key, array(
					'default'		=> '',
					'type'			=> 'option',
					'capability'	=> 'edit_theme_options'
				) );
				
				$option->import( $option_value );
			}
		}
		
		// Loop through the mods.
		foreach ( $data['mods'] as $key => $val ) {
			
			// Save the mod.
			set_theme_mod( $key, $val );
		}

		file_put_contents( BASEPATH . 'migrations/theme/is_updated', 1 );
	}
});
 
add_action('customize_save_after', function($wp_customize){
	
	$template = get_template();
	$mods     = get_theme_mods();
	$data     = [
		'template'  => $template,
		'mods'	  => $mods ? $mods : array(),
		'options'	  => array()
	];
	
	// Get options from the Customizer API.
	$settings = $wp_customize->settings();

	foreach ( $settings as $key => $setting ) {
		
		if ( 'option' == $setting->type ) {
			
			// Don't save widget data.
			if ( stristr( $key, 'widget_' ) ) {
				continue;
			}
			
			// Don't save sidebar data.
			if ( stristr( $key, 'sidebars_' ) ) {
				continue;
			}
			
			$data['options'][ $key ] = $setting->value();
		}
	}
	// Serialize the export data.
	file_put_contents( BASEPATH . 'migrations/theme/theme-customize.dat', serialize( $data ));
});