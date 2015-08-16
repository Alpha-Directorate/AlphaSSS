<?php

class WPML_Root_Page_Actions {

	public function delete_root_page_lang() {
		global $wpdb;
		$root_id = $this->get_root_page_id ();

		if ( $root_id ) {
			$wpdb->delete (
				$wpdb->prefix . 'icl_translations',
				array( 'element_id' => $root_id, 'element_type' => 'post_page' )
			);
		}
	}

	public function get_root_page_id() {
		$urls = icl_get_setting ( 'urls' );

		return isset( $urls[ 'root_page' ] )
		       && !empty( $urls[ 'directory_for_default_language' ] )
		       && isset( $urls[ 'show_on_root' ] )
		       && $urls[ 'show_on_root' ] === 'page'
			? $root_id = $urls[ 'root_page' ] : false;
	}

	function wpml_home_url_init() {
		global $pagenow, $sitepress, $sitepress_settings;

		if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {

			if ( isset( $_GET[ 'wpml_root_page' ] ) && $_GET[ 'wpml_root_page' ] && !empty( $sitepress_settings[ 'urls' ][ 'root_page' ] ) ) {
				$rp = get_post ( $sitepress_settings[ 'urls' ][ 'root_page' ] );
				if ( $rp && $rp->post_status != 'trash' ) {
					wp_redirect ( get_edit_post_link ( $sitepress_settings[ 'urls' ][ 'root_page' ], 'no-display' ) );
					exit;
				}
			}

			if ( isset( $_GET[ 'wpml_root_page' ] ) && $_GET[ 'wpml_root_page' ] || ( isset( $_GET[ 'post' ] ) && $_GET[ 'post' ] == $sitepress_settings[ 'urls' ][ 'root_page' ] ) ) {
				remove_action ( 'admin_head', array( $sitepress, 'post_edit_language_options' ) );
				add_action ( 'admin_head', array( $this, 'wpml_home_url_language_box_setup' ) );
				remove_action ( 'page_link', array( $sitepress, 'permalink_filter' ), 1, 2 );
			}
		}
	}

	function wpml_home_url_exclude_root_page_from_menus( $args ) {
		global $sitepress_settings;

		if ( !empty( $args[ 'exclude' ] ) ) {
			$args[ 'exclude' ] .= ',';
		} else {
			$args[ 'exclude' ] = '';
		}
		$args[ 'exclude' ] .= $sitepress_settings[ 'urls' ][ 'root_page' ];

		return $args;

	}

	function wpml_home_url_exclude_root_page( $excludes ) {
		global $sitepress_settings;

		$excludes[ ] = $sitepress_settings[ 'urls' ][ 'root_page' ];

		return $excludes;

	}

	function wpml_home_url_exclude_root_page2( $args ) {
		global $sitepress_settings;

		$args[ 'exclude' ][ ] = $sitepress_settings[ 'urls' ][ 'root_page' ];

		return $args;
	}

	function wpml_home_url_get_pages( $pages ) {
		global $sitepress_settings;

		foreach ( $pages as $k => $page ) {
			if ( $page->ID == $sitepress_settings[ 'urls' ][ 'root_page' ] ) {
				unset( $pages[ $k ] );
				$pages = array_values ( $pages );
				break;
			}
		}

		return $pages;
	}

	function wpml_home_url_language_box_setup() {
		add_meta_box (
			'icl_div',
			__ ( 'Language', 'sitepress' ),
			array( $this, 'wpml_home_url_language_box' ),
			'page',
			'side',
			'high'
		);
	}

	function wpml_home_url_language_box( $post ) {
		global $sitepress_settings;

		if ( isset( $_GET[ 'wpml_root_page' ] )
		     || ( !empty( $sitepress_settings[ 'urls' ][ 'root_page' ] )
		          && $post->ID == $sitepress_settings[ 'urls' ][ 'root_page' ] ) ) {
			_e ( "This page does not have a language since it's the site's root page." );
			echo '<input type="hidden" name="_wpml_root_page" value="1" />';
		}
	}

	function wpml_home_url_save_post_actions( $pidd, $post ) {
		global $sitepress, $wpdb, $iclTranslationManagement;

		if ( (bool) filter_input ( INPUT_POST, '_wpml_root_page' ) === true ) {

			if ( isset( $_POST[ 'autosave' ] ) || ( isset( $post->post_type ) && $post->post_type == 'revision' ) ) {
				return;
			}

			$iclsettings[ 'urls' ][ 'root_page' ] = $post->ID;
			$sitepress->save_settings ( $iclsettings );

			remove_action ( 'save_post', array( $sitepress, 'save_post_actions' ), 10, 2 );

			if ( !is_null ( $iclTranslationManagement ) ) {
				remove_action ( 'save_post', array( $iclTranslationManagement, 'save_post_actions' ), 11, 2 );
			}

			$wpdb->query (
				$wpdb->prepare (
					"DELETE FROM {$wpdb->prefix}icl_translations WHERE element_type='post_page' AND element_id=%d",
					$post->ID
				)
			);
		}
	}

	function wpml_home_url_setup_root_page() {
		global $sitepress, $wpml_query_filter, $sitepress_settings;

		remove_action ( 'template_redirect', 'redirect_canonical' );
		add_action ( 'parse_query', array( $this, 'wpml_home_url_parse_query' ) );

		remove_filter ( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
		remove_filter ( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );

		$rp = get_post ( $sitepress_settings[ 'urls' ][ 'root_page' ] );
		if ( $rp && $rp->post_status != 'trash' ) {
			$sitepress->ROOT_URL_PAGE_ID = $sitepress_settings[ 'urls' ][ 'root_page' ];
		}
	}

	/**
	 * @param WP_Query $q
	 *
	 * @return mixed
	 */
	function wpml_home_url_parse_query( $q ) {
		if ( !$q->is_main_query () ) {
			return $q;
		}
		global $sitepress_settings;

		if ( !WPML_Root_Page::is_current_request_root () ) {
			return $q;
		} else {
			remove_action ( 'parse_query', array( $this, 'wpml_home_url_parse_query' ) );

			$request_array = explode ( '/', $_SERVER[ "REQUEST_URI" ] );

			$sanitized_query = array_pop ( $request_array );

			$potential_pagination_parameter = array_pop ( $request_array );

			if ( is_numeric ( $potential_pagination_parameter ) ) {
				if ( $sanitized_query ) {
					$sanitized_query .= '&';
				}
				$sanitized_query .= 'page=' . $potential_pagination_parameter;
			}

			$sanitized_query = str_replace ( '?', '', $sanitized_query );
			$q->parse_query ( $sanitized_query );
			add_action ( 'parse_query', array( $this, 'wpml_home_url_parse_query' ) );

			$q->query_vars[ 'page_id' ] = $sitepress_settings[ "urls" ][ "root_page" ];
			$q->query[ 'page_id' ]      = $sitepress_settings[ "urls" ][ "root_page" ];
			$q->is_page                 = 1;
			$q->queried_object          = new WP_Post( get_post ( $sitepress_settings[ "urls" ][ "root_page" ] ) );
			$q->queried_object_id       = $sitepress_settings[ "urls" ][ "root_page" ];
			$q->query_vars[ 'error' ]   = "";
			$q->is_404                  = false;
			$q->query[ 'error' ]        = null;
		}

		return $q;
	}
}

function wpml_home_url_ls_hide_check() {
	global $sitepress_settings, $sitepress;

	$hide = $sitepress_settings[ 'language_negotiation_type' ] == 1 && $sitepress_settings[ 'urls' ][ 'directory_for_default_language' ] && $sitepress_settings[ 'urls' ][ 'show_on_root' ] == 'page' && $sitepress_settings[ 'urls' ][ 'hide_language_switchers' ] && isset( $sitepress->ROOT_URL_PAGE_ID ) && $sitepress->ROOT_URL_PAGE_ID == $sitepress_settings[ 'urls' ][ 'root_page' ];

	return $hide;

}