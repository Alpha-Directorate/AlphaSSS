<?php

class WPML_URL_Filters {

	private $default_language;

	public function __construct() {

		$this->default_language = icl_get_setting ( 'default_language' );

		if ( $this->frontend_uses_root () === true ) {
			require_once ICL_PLUGIN_PATH . '/inc/url-handling/wpml-root-page.class.php';
			add_filter ( 'page_link', array( $this, 'permalink_filter_root' ), 1, 2 );
		} else {
			add_filter ( 'page_link', array( $this, 'permalink_filter' ), 1, 2 );
		}

		add_filter ( 'home_url', array( $this, 'home_url_filter' ), -10, 1 );
		// posts and pages links filters
		add_filter ( 'post_link', array( $this, 'permalink_filter' ), 1, 2 );
		add_filter ( 'post_type_link', array( $this, 'permalink_filter' ), 1, 2 );
	}

	public function permalink_filter_root( $link, $pid ) {

		$pid  = is_object( $pid ) ? $pid->ID : $pid;
		$link = WPML_Root_Page::get_root_id() != $pid
			? $this->permalink_filter( $link, $pid ) : $this->filter_root_permalink( $link );

		return $link;
	}

	/**
	 * @param $url
	 * Filters links to the root page, so that they are displayed properly in the front-end.
	 *
	 * @return mixed
	 */
	public function filter_root_permalink( $url ) {
		global $wpml_url_converter;

		if ( WPML_Root_Page::get_root_id() > 0 && WPML_Root_Page::is_root_page( $url ) ) {
			$url_parts = parse_url( $url );
			$query     = isset( $url_parts[ 'query' ] ) ? $url_parts[ 'query' ] : '';
			$path      = isset( $url_parts[ 'path' ] ) ? $url_parts[ 'path' ] : '';
			$slugs     = array_filter( explode( '/', $path ) );
			$last_slug = array_pop( $slugs );
			$new_url   = $wpml_url_converter->get_abs_home();
			$new_url   = is_numeric( $last_slug ) ? trailingslashit( trailingslashit( $new_url ) . $last_slug ) : $new_url;
			$query     = self::unset_page_query_vars( $query );
			$new_url   = trailingslashit( $new_url );
			$url       = (bool) $query === true ? trailingslashit( $new_url ) . '?' . $query : $new_url;
		}

		return $url;
	}

	private function unset_page_query_vars( $query ) {
		parse_str ( (string) $query, $query_parts );
		foreach ( array( 'p', 'page_id', 'page', 'pagename', 'page_name', 'attachement_id' ) as $part ) {
			if ( isset( $query_parts[ $part ] ) && !( $part === 'page_id' && !empty( $query_parts[ 'preview' ] ) ) ) {
				unset( $query_parts[ $part ] );
			}
		}

		return http_build_query ( $query_parts );
	}

	private function default_language(){
		$this->default_language = $this->default_language ? $this->default_language : icl_get_setting ( 'default_language' );

		return $this->default_language;
	}

	/**
	 * @param string $link
	 * @param int|WP_Post $post_object
	 *
	 * @return bool|mixed|string
	 */
	public function permalink_filter( $link, $post_object ) {
		/* @var WPML_URL_Converter $wpml_url_converter */
		global $wp_query, $sitepress, $wpml_url_converter;

		$post_object = is_object( $post_object ) ? $post_object->ID : $post_object;
		$post_type   = isset( $post_object->post_type ) ? $post_object->post_type : get_post_type( $post_object );

		if ( ! $sitepress->is_translated_post_type( $post_type ) ) {
			return $link;
		}

		$code = $this->get_permalink_filter_lang( $post_object );

		$link = $wpml_url_converter->convert_url( $link, $code );
		$link = isset( $wp_query ) && is_feed() ? str_replace( "&lang=", "&#038;lang=", $link ) : $link;

		return $link;
	}

	public function home_url_filter( $url ) {
		/* @var WPML_URL_Converter $wpml_url_converter */
		global $wpml_url_converter;

		$server_name = isset( $_SERVER[ 'SERVER_NAME' ] ) ? $_SERVER[ 'SERVER_NAME' ] : "";
		$request_uri = isset( $_SERVER[ 'REQUEST_URI' ] ) ? $_SERVER[ 'REQUEST_URI' ] : "";
		$server_name = strpos( $request_uri, '/' ) === 0
			? untrailingslashit( $server_name ) : trailingslashit( $server_name );

		$url_snippet = $server_name . $request_uri;

		return $wpml_url_converter->convert_url(
			$url,
			$wpml_url_converter->get_language_from_url(
				$url_snippet
			)
		);
	}

	public function frontend_uses_root() {
		$urls = icl_get_setting ( 'urls' );

		return isset( $urls[ 'root_page' ] ) && isset( $urls[ 'show_on_root' ] )
		       && !empty( $urls[ 'directory_for_default_language' ] )
		       && ( $urls[ 'show_on_root' ] === 'page' || $urls[ 'show_on_root' ] === 'html_file' );
	}

	/**
	 * Finds the correct language a post belongs to by handling the special case of the post edit screen.
	 *
	 * @param WP_Post $post_object
	 *
	 * @return bool|mixed|null|String
	 */
	private function get_permalink_filter_lang( $post_object ) {
		global $wpml_url_converter, $wpml_post_translations;

		if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] === 'sample-permalink' ) {
			$code = filter_var( ( isset( $_GET[ 'lang' ] ) ? $_GET[ 'lang' ] : "" ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
			$code = $code
				? $code
				: ( ! isset( $_SERVER[ 'HTTP_REFERER' ] )
					? $this->default_language()
					: $wpml_url_converter->get_language_from_url( $_SERVER[ "HTTP_REFERER" ] ) );
		} else {
			$code = $wpml_post_translations->get_element_lang_code( $post_object );
		}

		return $code;
	}
}

global $wpml_url_filters;
$wpml_url_filters = new WPML_URL_Filters();