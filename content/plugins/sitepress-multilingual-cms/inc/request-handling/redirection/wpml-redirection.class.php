<?php

abstract class WPML_Redirection {

	public abstract function get_redirect_target();

	protected function redirect_hidden_home(){
		global $wpml_request_handler, $wpml_language_resolution, $wpml_url_converter;

		$target = false;
		if ( $wpml_language_resolution->is_language_hidden ( $wpml_request_handler->get_request_uri_lang () )
		     && !$wpml_request_handler->show_hidden ()
		) {
			$target = $wpml_url_converter->get_abs_home ();
		}

		return $target;
	}

}