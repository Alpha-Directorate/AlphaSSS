<?php

function wpml_st_load_label_menu() {
	global $wpml_st_label_menu;

	if ( ! isset( $wpml_st_label_menu ) ) {
		require WPML_ST_PATH . '/inc/filters/wpml-st-label-translation-menu.class.php';
		$wpml_st_label_menu = new WPML_ST_Label_Translation();
		$wpml_st_label_menu->init();
	}

	return $wpml_st_label_menu;
}

function wpml_st_setup_label_menu_hooks() {
	if ( is_admin() ) {
		add_action( 'wpml_st_load_label_menu', 'wpml_st_load_label_menu' );
	}
	if ( wpml_is_ajax() ) {
		wpml_st_load_label_menu();
	}
}
