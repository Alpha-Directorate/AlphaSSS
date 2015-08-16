<?php

/**
 * Class WPML_Languages
 *
 * @package wpml-core
 */
class WPML_Languages {

	public function get_ls_language( $lang_code, $current_language, $language_array = false ) {
		global $sitepress;

		$ls_language = $language_array
			? $language_array : $sitepress->get_language_details( $lang_code );
		$native_name = $sitepress->get_display_language_name( $lang_code, $lang_code );
		if ( !$native_name ) {
			$native_name = $ls_language[ 'english_name' ];
		}
		$ls_language[ 'native_name' ] = $native_name;
		$translated_name              = $sitepress->get_display_language_name( $lang_code, $current_language );
		if ( !$translated_name ) {
			$translated_name = $ls_language[ 'english_name' ];
		}
		$ls_language[ 'translated_name' ] = $translated_name;
		if ( isset( $ls_language[ 'translated_url' ] ) ) {
			$ls_language[ 'url' ] = $ls_language[ 'translated_url' ];
			unset( $ls_language[ 'translated_url' ] );
		} else {
			$ls_language[ 'url' ] = $sitepress->language_url( $lang_code );
		}

		$flag = $sitepress->get_flag( $lang_code );
		if ( $flag->from_template ) {
			$wp_upload_dir = wp_upload_dir();
			$flag_url      = $wp_upload_dir[ 'baseurl' ] . '/flags/' . $flag->flag;
		} else {
			$flag_url = ICL_PLUGIN_URL . '/res/flags/' . $flag->flag;
		}
		$ls_language[ 'country_flag_url' ] = $flag_url;
		$ls_language[ 'active' ]           = $current_language == $lang_code ? '1' : 0;
		$ls_language[ 'language_code' ]    = $lang_code;

		unset( $ls_language[ 'display_name' ] );
		unset( $ls_language[ 'english_name' ] );

		return $ls_language;
	}

	public function sort_ls_languages( $w_active_languages, $template_args ) {
		global $sitepress;

		// sort languages according to parameters
		$orderby = isset( $template_args[ 'orderby' ] ) ? $template_args[ 'orderby' ] : 'custom';
		$order   = isset( $template_args[ 'order' ] ) ? $template_args[ 'order' ] : 'asc';

		switch ( $orderby ) {
			case 'id':
				uasort( $w_active_languages, array( $this, 'sort_by_id' ) );
				break;
			case 'code':
				krsort( $w_active_languages );
				break;
			case 'name':
				uasort( $w_active_languages, array( $this, 'sort_by_name' ) );
				break;
			case 'custom':
			default:
				$w_active_languages = $sitepress->order_languages( $w_active_languages );
		}

		return $order !== 'asc' ? array_reverse( $w_active_languages, true ) : $w_active_languages;
	}

	private function sort_by_id( $array_a, $array_b ) {

		return (int) $array_a[ 'id' ] > (int) $array_b[ 'id' ] ? - 1 : 1;
	}

	private function sort_by_name( $array_a, $array_b ) {

		return $array_a[ 'translated_name' ] > $array_b[ 'translated_name' ] ? - 1 : 1;
	}
}