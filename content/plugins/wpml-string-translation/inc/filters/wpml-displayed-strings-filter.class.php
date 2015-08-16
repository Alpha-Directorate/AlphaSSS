<?php

class WPML_Displayed_String_Filter {

	protected $language;
	protected $original_cache = array();
	protected $name_cache = array();
	protected $use_original_cache;

	public function __construct( $language, $use_original_cache ) {
		$this->language = $language;
		$this->use_original_cache = $use_original_cache;
		$this->warm_cache();
	}

	protected function warm_cache() {
		global $wpdb;

		$query = $wpdb->prepare( "
					SELECT st.value AS tra, s.name AS nam, s.value AS org, s.context AS ctx
					FROM {$wpdb->prefix}icl_strings s
					JOIN {$wpdb->prefix}icl_string_translations st
						ON s.id = st.string_id
					WHERE st.status = %d AND st.language = %s",
		                         ICL_TM_COMPLETE,$this->language );
		$res   = $wpdb->get_results( $query, ARRAY_A );

		$name_cache = array();
		$warm_cache = array();
		foreach ( $res as $str ) {
			$name_cache[ $str[ 'nam' ] . $str[ 'ctx' ] ] = &$str[ 'tra' ];
			
			// use the original cache if some string were registered with 'plugin XXXX' or 'theme XXXX' context
			// This is how they were registered before the 3.2 release of WPML
			if ( $this->use_original_cache ) {
				$warm_cache[ md5( stripcslashes( $str[ 'org' ] ) ) ] = stripcslashes( $str[ 'tra' ] );
			}
		}

		$this->original_cache = $warm_cache;
		$this->name_cache     = $name_cache;
	}

	public function translate_by_name_and_context( $untranslated_text, $name, $context = "", &$has_translation = null ) {
		$res = $this->string_from_registered( $name, $context );

		if ( $res === false && $untranslated_text !== false && $this->use_original_cache ) {
			// lookup translation from original text
			$key = md5( $untranslated_text );
			$res = isset( $this->original_cache[ $key ] ) ? $this->original_cache[ $key ] : false;
		}

		$has_translation = $res !== false ? true : null;
		$res             = ( $res === false && $untranslated_text !== false ) ? $untranslated_text : $res;

		
		if ( $res === false ) {
			$res = $this->string_by_name_and_ctx( $name, $context );
		}

		return $res;
	}

	/**
	 * @param string $name
	 * @param string $context
	 *
	 * Tries to retrieve a string from the cache and runs fallback logic for the default WP context
	 *
	 * @return bool
	 */
	protected function string_from_registered( $name, $context = "" ) {
		$res = $this->get_string_from_cache( $name, $context );
		$res = $res === false && $context === 'default' ? $this->get_string_from_cache( $name, 'WordPress' ) : $res;
		$res = $res === false && $context === 'WordPress' ? $this->get_string_from_cache( $name, 'default' ) : $res;

		return $res;
	}

	private function get_string_from_cache( $name, $context ) {
		list( $name, $context ) = $this->truncate_name_and_context( $name, $context );
		$key = $name . $context;
		$res = isset( $this->name_cache[ $key ] ) ? $this->name_cache[ $key ] : false;

		return $res;
	}
	
	protected function truncate_name_and_context( $name, $context) {
		if (strlen( $name ) > WPML_STRING_TABLE_NAME_CONTEXT_LENGTH ) {
			// truncate to match length in db
			$name = substr( $name, 0, intval( WPML_STRING_TABLE_NAME_CONTEXT_LENGTH ) );
		}
		if (strlen( $context ) > WPML_STRING_TABLE_NAME_CONTEXT_LENGTH ) {
			// truncate to match length in db
			$context = substr( $context, 0, intval( WPML_STRING_TABLE_NAME_CONTEXT_LENGTH ) );
		}
		
		return array( $name, $context );
	}

	public function export_cache() {
		return array(
			'use_original_cache' => $this->use_original_cache,
			'original_cache'     => $this->original_cache,
			'name_cache'         => $this->name_cache,
		);
	}

	protected function string_by_name_and_ctx( $name, $context ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		if ( ! isset( $this->name_cache[ $name . $context ] ) ) {
			$query = $wpdb->prepare(
				"SELECT value FROM {$wpdb->prefix}icl_strings WHERE name = %s AND context = %s LIMIT 1",
				$name,
				$context
			);

			$value                                = $wpdb->get_var( $query );
			$this->name_cache[ $name . $context ] = isset( $value ) ? $value : false;
		}

		return $this->name_cache[ $name . $context ];
	}
}