<?php

class WPML_Settings_Helper {

	public function set_post_type_translatable( $post_type ) {
		global $sitepress, $wpml_post_translations;

		$sync_settings               = $sitepress->get_setting( 'custom_posts_sync_option', array() );
		$sync_settings[ $post_type ] = 1;
		$sitepress->set_setting( 'custom_posts_sync_option', $sync_settings, true );
		$sitepress->verify_post_translations( $post_type );
		$wpml_post_translations->reload();
	}

	public function set_post_type_not_translatable( $post_type ) {
		global $sitepress;

		$sync_settings = $sitepress->get_setting( 'custom_posts_sync_option', array() );
		if ( isset( $sync_settings[ $post_type ] ) ) {
			unset( $sync_settings[ $post_type ] );
		}

		$sitepress->set_setting( 'custom_posts_sync_option', $sync_settings, true );
	}

	public function set_taxonomy_translatable( $taxonomy ) {
		global $sitepress;

		$sync_settings              = $sitepress->get_setting( 'taxonomies_sync_option', array() );
		$sync_settings[ $taxonomy ] = 1;
		$sitepress->set_setting( 'taxonomies_sync_option', $sync_settings, true );
		$sitepress->verify_taxonomy_translations( $taxonomy );
	}

	public function set_taxonomy_not_translatable( $taxonomy ) {
		global $sitepress;

		$sync_settings = $sitepress->get_setting( 'taxonomies_sync_option', array() );
		if ( isset( $sync_settings[ $taxonomy ] ) ) {
			unset( $sync_settings[ $taxonomy ] );
		}

		$sitepress->set_setting( 'taxonomies_sync_option', $sync_settings, true );
	}

	public function activate_slug_translation( $post_type ) {
		global $sitepress;

		$slug_settings                          = $sitepress->get_setting( 'posts_slug_translation', array() );
		$slug_settings[ 'types' ]               = isset( $slug_settings[ 'types' ] )
			? $slug_settings[ 'types' ] : array();
		$slug_settings[ 'types' ][ $post_type ] = 1;
		$slug_settings[ 'on' ]                  = 1;

		$sitepress->set_setting( 'posts_slug_translation', $slug_settings, true );
	}

	public function deactivate_slug_translation( $post_type ) {
		global $sitepress;

		$slug_settings = $sitepress->get_setting( 'posts_slug_translation', array() );
		if ( isset( $slug_settings[ 'types' ][ $post_type ] ) ) {
			unset( $slug_settings[ 'types' ][ $post_type ] );
		}

		$sitepress->set_setting( 'posts_slug_translation', $slug_settings, true );
	}

	/**
	 * @param array[] $taxs_obj_type
	 *
	 * @see \WPML_Config::maybe_add_filter
	 *
	 * @return array
	 */
	function _override_get_translatable_taxonomies( $taxs_obj_type ) {
		global $wp_taxonomies, $sitepress, $iclTranslationManagement;

		$taxs = $taxs_obj_type[ 'taxs' ];

		$object_type = $taxs_obj_type[ 'object_type' ];
		foreach ( $taxs as $k => $tax ) {
			if ( ! $sitepress->is_translated_taxonomy( $tax ) ) {
				unset( $taxs[ $k ] );
			}
		}

		foreach ( $iclTranslationManagement->settings[ 'taxonomies_readonly_config' ] as $tx => $translate ) {
			if ( $translate
				 && ! in_array( $tx, $taxs )
				 && isset( $wp_taxonomies[ $tx ] )
				 && in_array( $object_type, $wp_taxonomies[ $tx ]->object_type )
			) {
				$taxs[ ] = $tx;
			}
		}

		$ret = array( 'taxs' => $taxs, 'object_type' => $taxs_obj_type[ 'object_type' ] );

		return $ret;
	}

	/**
	 * @param array[] $types
	 *
	 * @see \WPML_Config::maybe_add_filter
	 *
	 * @return array
	 */
	function _override_get_translatable_documents( $types ) {
		global $wp_post_types, $iclTranslationManagement;

		foreach ( $types as $k => $type ) {
			if ( isset( $iclTranslationManagement->settings[ 'custom-types_readonly_config' ][ $k ] )
				 && ! $iclTranslationManagement->settings[ 'custom-types_readonly_config' ][ $k ]
			) {
				unset( $types[ $k ] );
			}
		}
		foreach ( $iclTranslationManagement->settings[ 'custom-types_readonly_config' ] as $cp => $translate ) {
			if ( $translate && ! isset( $types[ $cp ] ) && isset( $wp_post_types[ $cp ] ) ) {
				$types[ $cp ] = $wp_post_types[ $cp ];
			}
		}

		return $types;
	}
}