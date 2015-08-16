<?php

function wpml_tm_load_element_translations(){
	global $wpml_tm_element_translations;

	if(!isset($wpml_tm_element_translations)){
		require WPML_TM_PATH . '/inc/core/wpml-tm-element-translations.class.php';
		$wpml_tm_element_translations = new WPML_TM_Element_Translations();
	}

	return $wpml_tm_element_translations;
}

function wpml_tm_load_status_display_filter() {
	global $wpml_tm_status_display_filter, $sitepress, $wpdb;

	if ( !isset( $wpml_tm_status_display_filter ) ) {
		$user_id                       = get_current_user_id ();
		$lang_pairs                    = get_user_meta ( $user_id, $wpdb->prefix . 'language_pairs', true );
		$wpml_tm_status_display_filter = new WPML_TM_Translation_Status_Display(
			$user_id,
			current_user_can ( 'manage_options' ),
			$lang_pairs,
			$sitepress->get_current_language (),
			$sitepress->get_active_languages ()
		);
	}

	$wpml_tm_status_display_filter->init ( false );
}

function wpml_tm_init_mail_notifications() {
	global $wpml_tm_mailer;

	if ( !isset( $wpml_tm_mailer ) ) {
		require WPML_TM_PATH . '/inc/local-translation/wpml-tm-mail-notification.class.php';
		$wpml_tm_mailer = new WPML_TM_Mail_Notification();
	}
	$wpml_tm_mailer->init();

	return $wpml_tm_mailer;
}

function wpml_tm_load_tm_dashboard_ajax(){
	global $wpml_tm_dashboard_ajax;

	if(!isset($wpml_tm_dashboard_ajax)){
	require WPML_TM_PATH . '/menu/dashboard/wpml-tm-dashboard-ajax.class.php';
		$wpml_tm_dashboard_ajax = new WPML_Dashboard_Ajax();
	}

	return $wpml_tm_dashboard_ajax;
}

if ( defined( 'DOING_AJAX' ) ) {
    $wpml_tm_dashboard_ajax = wpml_tm_load_tm_dashboard_ajax();
    add_action( 'init', array( $wpml_tm_dashboard_ajax, 'init_ajax_actions' ) );
} elseif ( is_admin() && isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == WPML_TM_FOLDER . '/menu/main.php'
    && ( !isset( $_GET[ 'sm' ] ) || $_GET['sm'] === 'dashboard' ) )
{
    $wpml_tm_dashboard_ajax = wpml_tm_load_tm_dashboard_ajax();
    add_action( 'wpml_tm_scripts_enqueued', array( $wpml_tm_dashboard_ajax, 'enqueue_js' ) );
}
