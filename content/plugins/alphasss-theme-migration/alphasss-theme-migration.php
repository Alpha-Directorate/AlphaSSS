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