<?php
require 'wpml-redirection.class.php';
require ICL_PLUGIN_PATH . '/inc/request-handling/redirection/wpml-redirect-by-param.class.php';

/**
 * Redirects to a URL corrected for the language information in it, in case request URI and $_REQUEST['lang'],
 * requested domain or $_SERVER['REQUEST_URI'] do not match and gives precedence to the explicit language parameter if
 * there.
 *
 * @return string The language code of the currently requested URL in case no redirection was necessary.
 */
function wpml_maybe_frontend_redirect() {
	/** @var WPML_Redirection $redirect_helper */
	list( $redirect_helper, $language_code ) = _wpml_get_redirect_helper (
		$_SERVER[ 'REQUEST_URI' ],
		$_SERVER[ 'HTTP_HOST' ]
	);
	if ( ( $target = $redirect_helper->get_redirect_target () ) !== false ) {
		wp_safe_redirect ( $target );
		exit;
	};

	// allow forcing the current language when it can't be decoded from the URL
	return apply_filters ( 'icl_set_current_language', $language_code );
}

/**
 * @param string $request_uri
 * @param string $http_host
 *
 * @return array (WPML_Redirection|string)[] containing the actual redirect helper object at the 0 index and the language code of the currently
 *               queried url at the 1 index
 */
function _wpml_get_redirect_helper( $request_uri, $http_host ) {
	global $wpml_url_converter;

	$lang_neg_type = icl_get_setting ( 'language_negotiation_type' );
	$language_code = $wpml_url_converter->get_language_from_url ( $http_host . $request_uri );
	switch ( $lang_neg_type ) {
		case 1:

			global $wpml_url_filters;
			if ( $wpml_url_filters->frontend_uses_root () !== false
			) {
				require ICL_PLUGIN_PATH . '/inc/request-handling/redirection/wpml-rootpage-redirect-by-subdir.class.php';
				$redirect_helper = new WPML_RootPage_Redirect_By_Subdir(
					icl_get_setting ( 'urls' ),
					$request_uri,
					$http_host
				);
			} else {
				require ICL_PLUGIN_PATH . '/inc/request-handling/redirection/wpml-redirect-by-subdir.class.php';
				$redirect_helper = new WPML_Redirect_By_Subdir(
					icl_get_setting ( 'urls' ),
					$request_uri,
					$http_host
				);
			}
			break;
		case 2:
			require ICL_PLUGIN_PATH . '/inc/request-handling/redirection/wpml-redirect-by-domain.class.php';
			$redirect_helper = new WPML_Redirect_By_Domain(
				icl_get_setting ( 'language_domains' ),
				$request_uri,
				$http_host
			);
			break;
		case 3:
		default:
			$redirect_helper = new WPML_Redirect_By_Param(
				icl_get_setting ( 'taxonomies_sync_option', array() ),
				$language_code,
				$request_uri
			);
	}

	return array( $redirect_helper, $language_code );
}