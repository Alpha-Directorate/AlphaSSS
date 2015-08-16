<?php

class WPML_Redirect_By_Subdir extends WPML_Redirection {

	private $urls;

	public function __construct( $urls, $request_uri, $requested_domain ) {
		$this->urls = $urls;
	}

	public function get_redirect_target( ) {
		return $this->redirect_hidden_home();
	}
}