<?php

class WPML_Redirect_By_Domain extends WPML_Redirection {

	private $domains;
	private $request_uri;
	private $requested_domain;

	public function __construct( $domains, $request_uri, $requested_domain ) {

		$this->domains          = $domains;
		$this->request_uri      = $request_uri;
		$this->requested_domain = $requested_domain;
	}

	public function get_redirect_target( $language = false ) {
		global $wpml_language_resolution;

		$target = $wpml_language_resolution->is_language_hidden ( $language )
		          && strpos ( $_SERVER[ 'REQUEST_URI' ], 'wp-login.php' ) === false
		          && !user_can ( wp_get_current_user (), 'manage_options' )
			? trailingslashit ( $this->domains[ $language ] ) . 'wp-login.php' : false;

		return $target;
	}
}