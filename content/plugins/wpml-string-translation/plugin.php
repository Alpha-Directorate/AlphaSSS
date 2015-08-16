<?php
/*
Plugin Name: WPML String Translation
Plugin URI: https://wpml.org/
Description: Adds theme and plugins localization capabilities to WPML | <a href="https://wpml.org">Documentation</a> | <a href="https://wpml.org/version/wpml-3-2/">WPML 3.2 release notes</a>
Author: OnTheGoSystems
Author URI: http://www.onthegosystems.com/
Version: 2.2.2
Plugin Slug: wpml-string-translation
*/

if ( defined( 'WPML_ST_VERSION' ) ) {
	return;
}

define( 'WPML_ST_VERSION', '2.2.2' );
//define( 'WPML_PT_VERSION_DEV', '2.2.2' );
define( 'WPML_ST_PATH', dirname( __FILE__ ) );
require WPML_ST_PATH . '/inc/wpml-dependencies-check/wpml-bundle-check.class.php';
require WPML_ST_PATH . '/inc/functions-load.php';
require WPML_ST_PATH . '/inc/wpml-string-translation.class.php';
require WPML_ST_PATH . '/inc/constants.php';

global $WPML_String_Translation;
$WPML_String_Translation = new WPML_String_Translation();

require WPML_ST_PATH . '/inc/package-translation/wpml-package-translation.php';

add_action( 'wpml_loaded', 'wpml_st_setup_label_menu_hooks', 10, 0 );