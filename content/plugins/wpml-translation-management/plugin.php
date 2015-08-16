<?php
/*
Plugin Name: WPML Translation Management
Plugin URI: https://wpml.org/
Description: Add a complete translation process for WPML | <a href="https://wpml.org">Documentation</a> | <a href="https://wpml.org/version/wpml-3-2/">WPML 3.2 release notes</a>
Author: OnTheGoSystems
Author URI: http://www.onthegosystems.com/
Version: 2.0.2
Plugin Slug: wpml-translation-management
*/

if ( defined( 'WPML_TM_VERSION' ) ) {
	return;
}

define( 'WPML_TM_VERSION', '2.0.2' );
//define( 'WPML_TM_DEV_VERSION', '2.0.1' );
define( 'WPML_TM_PATH', dirname( __FILE__ ) );

require WPML_TM_PATH . '/inc/wpml-dependencies-check/wpml-bundle-check.class.php';
require WPML_TM_PATH . '/inc/constants.php';
require WPML_TM_PATH . '/inc/translation-proxy/interfaces/TranslationProxy_Interface.php';

global $wpml_translation_job_factory;
$wpml_translation_job_factory = new WPML_Translation_Job_Factory();

require WPML_TM_PATH . '/inc/translation-proxy/wpml-pro-translation.class.php';
require WPML_TM_PATH . '/inc/ajax.php';
require WPML_TM_PATH . '/inc/wpml-translation-management.class.php';
require WPML_TM_PATH . '/inc/wpml-translation-management-xliff.class.php';
require WPML_TM_PATH . '/inc/translation-proxy/translationproxy.class.php';
require WPML_TM_PATH . '/inc/functions-load.php';
wpml_tm_init_mail_notifications();
wpml_tm_load_element_translations();

global $WPML_Translation_Management, $wpml_tm_translation_status;
$WPML_Translation_Management = new WPML_Translation_Management();
require WPML_TM_PATH . '/inc/filters/wpml-tm-translation-status.class.php';
require WPML_TM_PATH . '/inc/filters/wpml-tm-translation-status-display.class.php';
$wpml_tm_translation_status = new WPML_TM_Translation_Status();
add_action( 'wpml_loaded', array( $wpml_tm_translation_status, 'init' ) );
add_action( 'wpml_pre_status_icon_display', 'wpml_tm_load_status_display_filter' );
