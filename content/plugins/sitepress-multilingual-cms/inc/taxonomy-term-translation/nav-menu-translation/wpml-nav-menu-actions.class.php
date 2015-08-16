<?php

/**
 * Class WPML_Nav_Menu_Actions
 *
 * @package    wpml-core
 * @subpackage taxonomy-term-translation
 */
class WPML_Nav_Menu_Actions {

	public function __construct() {
		add_action ( 'wp_delete_nav_menu', array( $this, 'wp_delete_nav_menu' ) );
		add_action ( 'wp_create_nav_menu', array( $this, 'wp_update_nav_menu' ), 10, 2 );
		add_action ( 'wp_update_nav_menu', array( $this, 'wp_update_nav_menu' ), 10, 2 );
		add_action ( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item' ), 10, 2 );
		add_action ( 'delete_post', array( $this, 'wp_delete_nav_menu_item' ) );
		add_filter ( 'pre_update_option_theme_mods_' . get_option( 'stylesheet' ), array( $this, 'pre_update_theme_mods_theme' ) );

		if(is_admin()){
			add_filter('theme_mod_nav_menu_locations', array($this, 'theme_mod_nav_menu_locations'));
		}
	}

	public function wp_delete_nav_menu( $id ) {
		global $wpdb;
		$menu_id_tt = $wpdb->get_var (
			$wpdb->prepare (
				"SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id=%d AND taxonomy='nav_menu'",
				$id
			)
		);
		$q          = "DELETE FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type='tax_nav_menu' LIMIT 1";
		$q_prepared = $wpdb->prepare ( $q, $menu_id_tt );
		$wpdb->query ( $q_prepared );
	}

	public function wp_update_nav_menu( $menu_id, $menu_data = null ) {
		/** @var WPML_Term_Translation $wpml_term_translations */
		global $sitepress, $wpdb, $wpml_term_translations;

		if ( $menu_data ) {
			if ( isset( $_POST[ 'icl_translation_of' ] ) && $_POST[ 'icl_translation_of' ] ) {
				$src_term_id = $_POST[ 'icl_translation_of' ];
				if ( $src_term_id != 'none' ) {
					$trid = $sitepress->get_element_trid ( $src_term_id, 'tax_nav_menu' );
				} else {
					$trid = null;
				}
			} else {
				$trid = isset( $_POST[ 'icl_nav_menu_trid' ] ) ? intval ( $_POST[ 'icl_nav_menu_trid' ] ) : null;
			}
			$language_code = $this->get_save_lang($menu_id);

			$menu_id_tt    = $wpdb->get_var (
				$wpdb->prepare (
					"SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id=%d AND taxonomy='nav_menu'",
					$menu_id
				)
			);
			$wpml_term_translations->reload();
			$existing_translations = $wpml_term_translations->get_element_translations ( $menu_id_tt );
			if ( !isset( $existing_translations[ $language_code ] ) ) {
				$sitepress->set_element_language_details ( $menu_id_tt, 'tax_nav_menu', $trid, $language_code );
			}
		}
	}

	private function get_save_lang( $menu_id ) {
		/** @var WPML_Term_Translation $wpml_term_translations */
		global $sitepress, $wpml_term_translations;

		$language_code = isset( $_POST[ 'icl_nav_menu_language' ] )
			? $_POST[ 'icl_nav_menu_language' ] : $wpml_term_translations->lang_code_by_termid ( $menu_id );
		$language_code = $language_code ? $language_code : $sitepress->get_current_language ();

		return $language_code;
	}

	public function wp_update_nav_menu_item( $menu_id, $menu_item_db_id ) {
		global $sitepress;

		$trid          = $sitepress->get_element_trid ( $menu_item_db_id, 'post_nav_menu_item' );
		$language_code = $sitepress->get_current_language ();
		$sitepress->set_element_language_details ( $menu_item_db_id, 'post_nav_menu_item', $trid, $language_code );
	}

	public function wp_delete_nav_menu_item( $menu_item_id ) {
		global $wpdb;
		$post = get_post ( $menu_item_id );
		if ( !empty( $post->post_type ) && $post->post_type == 'nav_menu_item' ) {
			$q          = "DELETE FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type='post_nav_menu_item' LIMIT 1";
			$q_prepared = $wpdb->prepare ( $q, $menu_item_id );
			$wpdb->query ( $q_prepared );
		}
	}

	public function pre_update_theme_mods_theme( $val ) {
		global $sitepress;
		$default_language = $sitepress->get_default_language ();
		$current_language = $sitepress->get_current_language ();

		if ( isset( $val[ 'nav_menu_locations' ] )
		     && filter_input ( INPUT_GET, 'action' ) === 'delete'
		     && $current_language !== $default_language
		) {
			$val[ 'nav_menu_locations' ] = get_theme_mod ( 'nav_menu_locations' );
		}

		if ( isset( $val[ 'nav_menu_locations' ] ) ) {
			foreach ( (array) $val[ 'nav_menu_locations' ] as $k => $v ) {
				if ( !$v && $current_language !== $default_language ) {
					$tl = get_theme_mod ( 'nav_menu_locations' );
					if ( isset( $tl[ $k ] ) ) {
						$val[ 'nav_menu_locations' ][ $k ] = $tl[ $k ];
					}
				} else {
					$val[ 'nav_menu_locations' ][ $k ] = icl_object_id (
						$val[ 'nav_menu_locations' ][ $k ],
						'nav_menu',
						true,
						$default_language
					);
				}
			}
		}

		return $val;
	}

	public function theme_mod_nav_menu_locations( $val ) {
		global /** @var WPML_Term_Translation $wpml_term_translations */
		$sitepress, $wpml_term_translations;

		$current_lang = $sitepress->get_current_language ();
		foreach ( (array) $val as $k => $v ) {
			$val[ $k ] = $wpml_term_translations->term_id_in ( $v, $current_lang );
		}

		return $val;
	}

}