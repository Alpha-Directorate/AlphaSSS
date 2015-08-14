<?php

class WPML_Page_Name_Query_Filter{

	private $current_language;
	private $active_languages;
	private $al_regexp;

	public function __construct( $active_languages ) {
		global $sitepress;

		$this->current_language    = $sitepress->get_current_language();
		$this->active_languages[ ] = $this->current_language;
		unset( $active_languages[ $this->current_language ] );
		$this->active_languages = array_merge ( $this->active_languages, array_keys ( $active_languages ) );
		$this->al_regexp        = $this->generate_al_regexp ( $this->active_languages );
	}

	public function set_current_lang() {
		global $sitepress;

		$this->active_languages = array_unique (
			array_merge ( array( $sitepress->get_current_language () ), $this->active_languages )
		);
	}

	private function generate_al_regexp( $active_language_codes ) {

		return '/^(' . implode ( '|', $active_language_codes ) . ')\//';
	}

	public function filter_page_name( $page_query ) {

		if ( empty( $page_query->query_vars[ 'pagename' ] ) ) {
			return $page_query;
		}
		$this->set_current_lang();
		// find the page with the page name in the current language.
		$page_name_for_query = preg_replace ( $this->al_regexp, '', $page_query->query_vars[ 'pagename' ] );

		if ( strpos ( $page_name_for_query, '/' ) === false ) {
			$pages_with_name = $this->get_single_slug_adjusted_IDs ( $page_name_for_query );
		} else {
			$pages_with_name = $this->get_multiple_slug_adjusted_IDs ( explode ( '/', $page_name_for_query ) );
		}

		global $wpml_post_translations;

		foreach ( $this->active_languages as $lang_code ) {
			foreach ( $pages_with_name as $p_with_name ) {
				if ( $wpml_post_translations->get_element_lang_code ( $p_with_name ) === $lang_code ) {
					$pid = $p_with_name;
					break;
				}
			}
			if ( isset( $pid ) ) {
				break;
			}
		}

		if ( isset( $pid ) ) {
			$page_query->query_vars[ 'page_id' ] = $pid;
			unset( $page_query->query_vars[ 'pagename' ] );
			unset( $page_query->queried_object );
			if ( $page_query->query_vars[ 'page_id' ] == get_option ( 'page_for_posts' ) ) {
				// it's the blog page.
				$page_query->is_page       = false;
				$page_query->is_home       = true;
				$page_query->is_posts_page = true;
			}
		} elseif ( (bool) $page_name_for_query === true ) {
			$page_query->query_vars[ 'pagename' ]  = $page_name_for_query;
			$page_query->query_vars[ 'post_type' ] = 'page';
		}

		return $page_query;
	}

	private function get_single_slug_adjusted_IDs($page_name_for_query){
		global $wpdb;

		$pages_with_name                 = $wpdb->get_col (
			$wpdb->prepare (
				"
				SELECT ID
				FROM $wpdb->posts p
				JOIN {$wpdb->prefix}icl_translations t
				ON p.ID = t.element_id AND element_type='post_page'
				WHERE p.post_name = %s AND p.post_parent = 0
				",
				$page_name_for_query
			)
		);

		return $pages_with_name;
	}

	private function get_multiple_slug_adjusted_IDs( $slugs ) {
		global $wpdb;

		$pages_with_name = $wpdb->get_results (
			"
				SELECT ID, post_name, post_parent
				FROM $wpdb->posts p
				JOIN {$wpdb->prefix}icl_translations t
				ON p.ID = t.element_id AND element_type='post_page'
				WHERE p.post_name IN (" . wpml_prepare_in ( $slugs ) . ")
				"
		);

		$page_names = array();
		$page_objects = array();

		foreach ( $pages_with_name as $page ) {
			$page_names[ $page->ID ] = $page->post_name;
			$page_objects[ $page->ID ]   = &$page;
		}

		array_pop ( $slugs );

		foreach ( $pages_with_name as &$page ) {
			$page = $this->remove_by_wrong_parent ( $page, $page->post_parent, $page_names, $page_objects, $slugs );
		}

		return array_filter ( $pages_with_name );
	}

	private function remove_by_wrong_parent( $page, $current_parent, $page_names, $page_objects, $slugs ) {

		$parent_slug = array_pop ( $slugs );

		if ( $current_parent != "0"
		     && isset($page_names[ $current_parent ])
		     && $page_names[ $current_parent ] === $parent_slug ) {
			$ret = $this->remove_by_wrong_parent (
				$page,
				$page_objects[ $current_parent ]->post_parent,
				$page_names,
				$page_objects,
				$slugs
			);
		} elseif ( $parent_slug === null ) {
			$ret = $page->ID;
		} else {
			$ret = null;
		}

		return $ret;
	}
}