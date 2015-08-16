<?php

class WPML_Create_Post_Helper {

	/**
	 * @param $postarr
	 *
	 * @param $lang
	 *
	 * @return int|WP_Error
	 */
	public function icl_insert_post( $postarr, $lang ) {
		global $sitepress;
		$current_language = $sitepress->get_current_language();
		$sitepress->switch_lang( $lang, false );
		$new_post_id = wp_insert_post( $postarr );
		$sitepress->switch_lang( $current_language, false );

		return $new_post_id;
	}
}