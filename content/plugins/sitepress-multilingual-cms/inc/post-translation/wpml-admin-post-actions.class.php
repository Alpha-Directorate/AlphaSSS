<?php

/**
 * Class WPML_Admin_Post_Actions
 *
 * @package    wpml-core
 * @subpackage post-translation
 */

class WPML_Admin_Post_Actions extends  WPML_Post_Translation{

	public function init() {
		parent::init ();
		if ( $this->is_setup_complete() ) {
			add_action ( 'delete_post', array( $this, 'delete_post_actions' ) );
			add_action ( 'wp_trash_post', array( $this, 'trashed_post_actions' ) );
			add_action ( 'untrashed_post', array( $this, 'untrashed_post_actions' ) );
		}
	}

	public function save_post_actions( $pidd, $post ) {
		global $sitepress, $wpdb;

		wp_defer_term_counting ( true );
		$post = isset( $post ) ? $post : get_post ( $pidd );
		// exceptions
		if ( !$this->has_save_post_action ( $post ) ) {
			wp_defer_term_counting ( false );
			return;
		}

		if ( WPML_WordPress_Actions::is_bulk_trash ( $pidd ) || WPML_WordPress_Actions::is_bulk_untrash ( $pidd ) ) {
			return;
		}

		$default_language = $sitepress->get_default_language ();

		// allow post arguments to be passed via wp_insert_post directly and not be expected on $_POST exclusively
		$post_vars = (array) $_POST;
		foreach ( (array) $post as $k => $v ) {
			$post_vars[ $k ] = $v;
		}

		$post_vars[ 'post_type' ] = isset( $post_vars[ 'post_type' ] ) ? $post_vars[ 'post_type' ] : $post->post_type;
		$post_id                  = $pidd;
		if ( isset( $post_vars[ 'action' ] ) && $post_vars[ 'action' ] === 'post-quickpress-publish' ) {
			$language_code = $default_language;
		} else {
			$post_id = isset( $post_vars[ 'post_ID' ] ) ? $post_vars[ 'post_ID' ]
				: $pidd; //latter case for XML-RPC publishing
			$language_code = $this->get_save_post_lang ( $post_id, $sitepress );
		}

		if ( $this->is_inline_action ( $post_vars ) && !( $language_code = $this->get_element_lang_code (
				$post_id
			) )
		) {
			return;
		}

		if ( isset( $post_vars[ 'icl_translation_of' ] ) && is_numeric ( $post_vars[ 'icl_translation_of' ] ) ) {
			$translation_of_data_prepared = $wpdb->prepare (
				"SELECT trid, language_code
				 FROM {$wpdb->prefix}icl_translations
				 WHERE element_id=%d
				  AND element_type=%s",
				$post_vars[ 'icl_translation_of' ],
				'post_' . $post->post_type
			);
			list( $trid, $source_language ) = $wpdb->get_row ( $translation_of_data_prepared, 'ARRAY_N' );
		}
		$trid = isset( $trid ) && $trid ? $trid : $this->get_save_post_trid ( $post_id, $post->post_status );
		// after getting the right trid set the source language from it by referring to the root translation
		// of this trid, in case no proper source language has been set yet
		$source_language = isset( $source_language )
			? $source_language : $this->get_save_post_source_lang ( $trid, $language_code, $default_language );

		$this->maybe_set_elid ( $trid, $post->post_type, $language_code, $post_id, $source_language );

		$translation_sync = $this->get_sync_helper ();
		$original_id = $this->get_original_element ( $post_id );
		if ( $original_id ) {
			$translation_sync->sync_with_translations ( $original_id, $post_vars );
		}
		if ( isset( $post_vars[ 'icl_tn_note' ] ) ) {
			update_post_meta ( $post_id, '_icl_translator_note', $post_vars[ 'icl_tn_note' ] );
		}

		require_once ICL_PLUGIN_PATH . '/inc/cache.php';
		icl_cache_clear ( $post_vars[ 'post_type' ] . 's_per_language', true );
		wp_defer_term_counting ( false );
	}

	/**
	 * @param integer   $post_id
	 * @param SitePress $sitepress
	 *
	 * @return null|string
	 */
	protected function get_save_post_lang( $post_id, $sitepress ) {
		$language_code = isset( $post_vars[ 'icl_post_language' ] ) ? $post_vars[ 'icl_post_language' ] : null;
		$language_code = $language_code
			? $language_code
			: filter_input (
				INPUT_GET,
				'lang',
				FILTER_SANITIZE_FULL_SPECIAL_CHARS
			);
		$language_code = $language_code ? $language_code : parent::get_save_post_lang ( $post_id, $sitepress );

		return $language_code;
	}

	/**
	 * @param array $post_vars
	 * @return bool
	 */
	private function is_inline_action( $post_vars ) {

		return isset( $post_vars[ 'action' ] )
		       && $post_vars[ 'action' ] == 'inline-save'
		       || isset( $_GET[ 'bulk_edit' ] )
		       || isset( $_GET[ 'doing_wp_cron' ] )
		       || ( isset( $_GET[ 'action' ] )
		            && $_GET[ 'action' ] == 'untrash' );
	}

	/**
	 * @param int    $trid
	 * @param string $language_code
	 * @param string $default_language
	 *
	 * @uses \WPML_Backend_Request::get_source_language_from_referer to retrieve the source_language when saving via ajax
	 *
	 * @return null|string
	 */
	protected function get_save_post_source_lang( $trid, $language_code, $default_language ) {
		/** @var WPML_Backend_Request $wpml_request_handler */
		global $wpml_request_handler;

		$source_language = filter_input ( INPUT_GET, 'source_lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$source_language = $source_language ? $source_language
			: $wpml_request_handler->get_source_language_from_referer ();
		$source_language = $source_language ? $source_language : SitePress::get_source_language_by_trid ( $trid );
		$source_language = $source_language === 'all' ? $default_language : $source_language;
		$source_language = $source_language !== $language_code ? $source_language : null;

		return $source_language;
	}

	private function get_trid_from_referer() {
		if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) ) {
			$query = parse_url ( $_SERVER[ 'HTTP_REFERER' ], PHP_URL_QUERY );
			parse_str ( $query, $vars );
		}

		return isset( $vars[ 'trid' ] ) ? filter_var ( $vars[ 'trid' ], FILTER_SANITIZE_NUMBER_INT ) : false;
	}

	/**
	 * @param Integer $post_id
	 * @param String $post_status
	 * @return null|int
	 */
	protected function get_save_post_trid( $post_id, $post_status ) {
		$trid = $this->get_element_trid ( $post_id );
		$trid = !$trid && isset( $post_vars[ 'icl_trid' ] ) ? $post_vars[ 'icl_trid' ] : $trid;
		$trid = $trid ? $trid : filter_input ( INPUT_GET, 'trid' );
		$trid = $trid ? $trid : $this->get_trid_from_referer ();
		$trid = apply_filters ( 'wpml_save_post_trid_value', $trid, $post_status );

		return $trid;
	}
}