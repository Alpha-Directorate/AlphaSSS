<?php

class WPML_String_Scanner {
	/**
	 * @param string|NULL $type 'plugin' or 'theme'
	 */
	protected $current_type;
	protected $current_path;

	private $domains;
	private $registered_strings;
	private $lang_codes;
	private $currently_scanning;
	private $domains_found;
	/**
	 * @var array
	 */
	private $scan_stats;
	private $scanned_files;

	public function __construct() {
		$this->domains            = array();
		$this->registered_strings = array();
		$this->lang_codes         = array();
		$this->domains_found      = array();
		$this->scan_stats         = array();
		$this->scanned_files      = array();
	}

	private function remove_trailing_new_line( $text ) {
		if ( substr( $text, - 1 ) == PHP_EOL || substr( $text, - 1 ) == "\n" ) {
			$text = substr( $text, 0, - 1 );
		}

		return $text;
	}

	protected function scan_starting( $scanning ) {
		$this->currently_scanning                         = $scanning;
		$this->domains_found[ $this->currently_scanning ] = array();
	}

	protected function scan_response() {
		$scan_stats = $this->scan_stats ? implode( PHP_EOL, $this->scan_stats ) : '';
		echo '1|' . $scan_stats;
		exit;
	}

	protected function get_domains_found() {
		return $this->domains_found[ $this->currently_scanning ];
	}

	protected function add_translations( $contexts, $context_prefix ) {

		if ( $contexts ) {
			$path     = $this->current_path;
			$mo_files = icl_st_get_mo_files( $path );
			foreach ( (array) $mo_files as $m ) {
				$i = preg_match( '#[-]?([a-z_]+)\.mo$#i', $m, $matches );
				if ( $i && $lang = $this->get_lang_code( $matches[ 1 ] ) ) {
					$tr_pairs = icl_st_load_translations_from_mo( $m );
					foreach ( $tr_pairs as $original => $translation ) {
						foreach ( $contexts as $tld ) {

							$this->fix_existing_string_with_wrong_context( $original, $context_prefix . $tld );
							if ( $this->add_translation( $original, $translation, $lang, $context_prefix . $tld ) ) {
								break;
							}
						}
					}
				}
			}
		}
	}

	private function fix_existing_string_with_wrong_context( $original_value, $new_string_context ) {
		if ( ! isset( $this->current_type ) || ! isset( $this->current_path ) ) {
			return;
		}

        $old_context = $this->get_old_context( );

		$new_context_string_id = $this->get_string_id( $original_value, $new_string_context );

		if ( ! $new_context_string_id ) {
			$old_context_string_id = $this->get_string_id( $original_value, $old_context );
			if ( $old_context_string_id ) {
				$this->fix_string_context( $old_context_string_id, $new_string_context );
				unset( $this->registered_strings[ $old_context ] );
				unset( $this->registered_strings[ $new_string_context ] );
			}
		}
	}
    
    private function get_old_context( ) {
		
        $plugin_or_theme_path = $this->current_path;

		$name    = basename( $plugin_or_theme_path );
		$old_context = $this->current_type . ' ' . $name;
        
        return $old_context;
        
    }

	private function get_lang_code( $lang_locale ) {
		global $wpdb;

		if ( ! isset( $this->lang_codes[ $lang_locale ] ) ) {
			$this->lang_codes[ $lang_locale ] = $wpdb->get_var( $wpdb->prepare( "SELECT code FROM {$wpdb->prefix}icl_locale_map WHERE locale=%s", $lang_locale ) );
		}

		return $this->lang_codes[ $lang_locale ];
	}

	private function add_translation( $original, $translation, $lang, $context ) {
		global $wpdb;

		$string_id = $this->get_string_id( $original, $context );
		if ( $string_id ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}icl_string_translations WHERE string_id=%d AND language=%s", $string_id, $lang ) ) ) {
				icl_add_string_translation( $string_id, $lang, $translation, ICL_TM_COMPLETE );
			}

			return true;
		}

		return false;
	}

	private function get_string_id( $original, $context ) {

		$this->warm_cache( $context );

		$string_md5 = md5( $original );
		$string_id  = isset( $this->registered_strings[ $context ] [ 'value' ] [ $string_md5 ] ) ? $this->registered_strings[ $context ] [ 'value' ] [ $string_md5 ] : null;

		return $string_id;
	}

	private function fix_string_context( $string_id, $new_string_context ) {
		global $wpdb;
		$wpdb->update( $wpdb->prefix . 'icl_strings', array( 'context' => $new_string_context ), array( 'id' => $string_id ), '%s', '%d' );
	}

	public function store_results( $string, $domain, $_gettext_context, $file, $line ) {

		global $wpdb;

		$context = $domain ? $domain : 'WordPress';

		if ( ! isset( $this->domains_found[ $this->currently_scanning ] [ $context ] ) ) {
			$this->domains_found[ $this->currently_scanning ] [ $context ] = 1;
		} else {
			$this->domains_found[ $this->currently_scanning ] [ $context ] += 1;
		}

		if ( ! in_array( $context, $this->domains ) ) {
			$this->domains[ ] = $context;

			// clear existing entries (both source and page type)
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}icl_string_positions WHERE string_id IN
                (SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s)", $context ) );
		}

        $string = str_replace( '\n', "\n", $string );
		$string = str_replace( array( '\"', "\\'" ), array( '"', "'" ), $string );
		//replace extra backslashes added by _potx_process_file
		$string = str_replace( array( '\\\\' ), array( '\\' ), $string );

		global $__icl_registered_strings;

		if ( ! isset( $__icl_registered_strings ) ) {
			$__icl_registered_strings = array();
		}

		if ( ! isset( $__icl_registered_strings[ $domain . '||' . $string . '||' . $_gettext_context ] ) ) {

			$name = $_gettext_context ? $_gettext_context . ': ' . $string : md5( $string );
			$this->fix_existing_string_with_wrong_context( $string, $context );
			$this->register_string( $context, $name, $string );

			$__icl_registered_strings[ $domain . '||' . $string . '||' . $_gettext_context ] = true;
		}

		// store position in source
		$this->track_string( $string, $context, ICL_STRING_TRANSLATION_STRING_TRACKING_TYPE_SOURCE, $file, $line );
	}

	private function register_string( $context, $name, $string ) {

		$this->warm_cache( $context );

		if ( ! isset( $this->registered_strings[ $context ] [ 'name-value' ] [ md5( $name . $string ) ] ) ) {
			$string_id                                                                        = icl_register_string( $context, $name, $string );
			$this->registered_strings[ $context ] [ 'name-value' ] [ md5( $name . $string ) ] = $string_id;
			$this->registered_strings[ $context ] [ 'value' ] [ md5( $string ) ]              = $string_id;
		}
	}

	private function warm_cache( $context ) {

		global $wpdb;

		if ( ! isset( $this->registered_strings[ $context ] ) ) {

			$this->registered_strings[ $context ] = array(
				'name-value' => array(),
				'value'      => array()
			);

			$query = $wpdb->prepare( "
                        SELECT * FROM {$wpdb->prefix}icl_strings
                        WHERE context=%s", esc_sql( $context ) );

			$results = $wpdb->get_results( $query, ARRAY_A );
			foreach ( $results as $result ) {
				$this->registered_strings[ $context ] [ 'name-value' ] [ md5( $result[ 'name' ] . $result[ 'value' ] ) ] = $result[ 'id' ];
				$this->registered_strings[ $context ] [ 'value' ] [ md5( $result[ 'value' ] ) ]                          = $result[ 'id' ];
			}
		}
	}

	public function track_string( $text, $context, $kind = ICL_STRING_TRANSLATION_STRING_TRACKING_TYPE_PAGE, $file = null, $line = null ) {
		global $wpdb;
		// get string id
		$string_id = $this->get_string_id( $text, $context );
		if ( $string_id ) {
			// get existing records
			$string_records_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id)
                                            FROM {$wpdb->prefix}icl_string_positions 
                                            WHERE string_id = %d AND kind = %d", $string_id, $kind ) );
			if ( ICL_STRING_TRANSLATION_STRING_TRACKING_THRESHOLD > $string_records_count ) {
				if ( $kind == ICL_STRING_TRANSLATION_STRING_TRACKING_TYPE_PAGE ) {
					// get page url
					$https    = isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] == 'on' ? 's' : '';
					$position = 'http' . $https . '://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
				} else {
					$position = $file . '::' . $line;
				}

				if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id
                         FROM {$wpdb->prefix}icl_string_positions
                         WHERE string_id=%d AND position_in_page=%s AND kind=%s", $string_id, $position, $kind ) )
				) {
					$wpdb->insert( $wpdb->prefix . 'icl_string_positions', array(
						'string_id'        => $string_id,
						'kind'             => $kind,
						'position_in_page' => $position
					) );
				}
			}
		}
	}

	protected function add_stat( $text, $top = false ) {
		$text = $this->remove_trailing_new_line( $text );
		if ( $top ) {
			array_unshift( $this->scan_stats, $text );
		} else {
			$this->scan_stats[ ] = $text;
		}
	}

	protected function get_scan_stats() {
		return $this->scan_stats;
	}

	protected function add_scanned_file( $file ) {
		$this->scanned_files[ ] = $file;
	}

	protected function get_scanned_files() {
		return $this->scanned_files;
	}
    
    protected function cleanup_wrong_contexts( ) {
        global $wpdb;
		
        $old_context = $this->get_old_context( );
        
		$results = $wpdb->get_results( $wpdb->prepare( "
	        SELECT id, name, value
	        FROM {$wpdb->prefix}icl_strings
	        WHERE context = %s",
			$old_context
			) );
		
		foreach( $results as $string ) {
			// See if the string has no translations
			
			$old_translations = $wpdb->get_results( $wpdb->prepare( "
				SELECT id, language, status, value
				FROM {$wpdb->prefix}icl_string_translations
				WHERE string_id = %d",
				$string->id
				) );
			
			if ( empty( $old_translations ) ) {
				// We don't have any translations so we can delete the string.
				
				$wpdb->delete( $wpdb->prefix . 'icl_strings', array( 'id' => $string->id ), array( '%d' ) );
			} else {
				// check if we have a new string in the right context
				
				$domains = $this->get_domains_found( );
				
				foreach ( $domains as $domain => $count ) {
					$new_string_id = $wpdb->get_var( $wpdb->prepare( "
						SELECT id
						FROM {$wpdb->prefix}icl_strings
						WHERE context = %s AND name = %s AND value = %s",
						$domain, $string->name, $string->value
						) );
					
					if ( $new_string_id ) {
						
						// See if it has the same translations
						
						$new_translations = $wpdb->get_results( $wpdb->prepare( "
							SELECT id, language, status, value
							FROM {$wpdb->prefix}icl_string_translations
							WHERE string_id = %d",
							$new_string_id
							) );
						
						foreach ( $new_translations as $new_translation) {
							foreach ( $old_translations as $index => $old_translation ) {
								if ( $new_translation->language == $old_translation->language &&
										$new_translation->status == $old_translation->status &&
										$new_translation->value == $old_translation->value ) {
									unset( $old_translations[$index] );
								}
							}
						}
						if ( empty( $old_translations ) ) {
							// We don't have any old translations that are not in the new strings so we can delete the string.
							
							$wpdb->delete( $wpdb->prefix . 'icl_strings', array( 'id' => $string->id ), array( '%d' ) );
							break;
						}
						
					}					
					
				}
				
			}
		}
		
		// Rename the context for any strings that are in the old context
		// This way the update message will no longer show.
		
		$obsolete_context = str_replace( 'plugin ', '', $old_context );
		$obsolete_context = str_replace( 'theme ', '', $obsolete_context );
		$obsolete_context = $obsolete_context . ' (obsolete)';
		
		$wpdb->query( $wpdb->prepare( "
									 UPDATE {$wpdb->prefix}icl_strings
									 SET context = %s
									 WHERE context = %s
									 ",
									 $obsolete_context,
									 $old_context ) );
        
		WPML_String_Translation::clear_use_original_cache_setting( );
    }
	
	protected function copy_old_translations( $contexts, $prefix ) {
		
		global $wpdb;
		
		foreach ( $contexts as $context ) {
			$new_strings = $wpdb->get_results( $wpdb->prepare( "
				SELECT id, name, value
				FROM {$wpdb->prefix}icl_strings
				WHERE context = %s",
				$context
				) );
			$new_ids = array( );
			foreach ( $new_strings  as $new_string ) {
				$new_ids[ ] = $new_string->id;
			}
			$new_ids = implode( ',', $new_ids );
			
			if ( $new_ids != '' ) {
				$new_translations = $wpdb->get_results( "
							SELECT id, string_id, language, status, value
							FROM {$wpdb->prefix}icl_string_translations
							WHERE string_id IN ({$new_ids})"
							);
			} else {
				$new_translations = array( );
			}
			
			$old_strings = $wpdb->get_results( $wpdb->prepare( "
				SELECT id, name, value
				FROM {$wpdb->prefix}icl_strings
				WHERE context = %s",
				$prefix . ' ' . $context
				) );
			$old_ids = array( );
			foreach ( $old_strings  as $old_string ) {
				$old_ids[ ] = $old_string->id;
			}
			$old_ids = implode( ',', $old_ids );
			
			if ( $old_ids != '' ) {
				$old_translations = $wpdb->get_results( "
							SELECT id, string_id, language, status, value
							FROM {$wpdb->prefix}icl_string_translations
							WHERE string_id IN ({$old_ids})"
							);
			} else {
				$old_translations = array( );
			}
			
			foreach( $old_translations as $old_translation ) {
				// see if we have a new translation.
				$found = false;
				foreach ( $new_translations as $new_translation ) {
					if ( $new_translation->string_id == $old_translation->string_id &&
							$new_translation->language == $old_translation->language ) {
						$found = true;
						break;
					}
				}
				
				if ( ! $found ) {
					// Copy the old translation to the new string.
					
					// Find the original
					foreach ( $old_strings as $old_string ) {
						if ( $old_string->id == $old_translation->string_id ) {
							// See if we have the same string in the new strings
							foreach ( $new_strings as $new_string ) {
								if ( $new_string->value == $old_string->value ) {
									// Add the old translation to new string.
									icl_add_string_translation( $new_string->id, $old_translation->language, $old_translation->value, ICL_TM_COMPLETE );
									break;
								}
							}
							break;
						}
					}
					
				}
				
			}
		}
			
	}
	
}

