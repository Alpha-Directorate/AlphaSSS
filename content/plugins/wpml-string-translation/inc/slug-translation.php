<?php

class WPML_Slug_Translation {

	static function init() {
		global $sitepress_settings;
		if ( ! empty( $sitepress_settings[ 'posts_slug_translation' ][ 'on' ] ) ) {
			add_filter( 'option_rewrite_rules',
			            array( 'WPML_Slug_Translation', 'rewrite_rules_filter' ),
			            1,
			            1 ); // high priority
			add_filter( 'post_type_link',
			            array( 'WPML_Slug_Translation', 'post_type_link_filter' ),
			            1,
			            4 ); // high priority
			add_filter( 'query_vars', array( 'WPML_Slug_Translation', 'add_cpt_names' ), 1, 2 );
			add_filter( 'pre_get_posts', array( 'WPML_Slug_Translation', 'filter_pre_get_posts' ), - 1000, 2 );
		}
		add_action( 'icl_ajx_custom_call', array( 'WPML_Slug_Translation', 'gui_save_options' ), 10, 2 );
	}

	private static function get_slug_by_type( $type ) {
		$post_type_obj = get_post_type_object ( $type );

		return isset( $post_type_obj->rewrite[ 'slug' ] ) ? trim ( $post_type_obj->rewrite[ 'slug' ], '/' ) : false;
	}

	static function rewrite_rules_filter( $value ) {
		global $sitepress, $sitepress_settings, $wpdb;

		if ( isset( $sitepress_settings[ 'st' ][ 'strings_language' ] ) ) {
			$strings_language = $sitepress_settings[ 'st' ][ 'strings_language' ];
		} else {
			$strings_language = false;
		}

		$current_language = $sitepress->get_current_language();
		if ( $current_language != $strings_language ) {

			$queryable_post_types = get_post_types( array( 'publicly_queryable' => true ) );

			foreach ( $queryable_post_types as $type ) {

				if ( ! isset( $sitepress_settings[ 'posts_slug_translation' ][ 'types' ][ $type ] ) || ! $sitepress_settings[ 'posts_slug_translation' ][ 'types' ][ $type ] || ! $sitepress->is_translated_post_type( $type ) ) {
					continue;
				}

				$slug = self::get_slug_by_type ( $type );
				if ( $slug === false ) {
					continue;
				}
				$slug_translation = $wpdb->get_var( $wpdb->prepare( "
                            SELECT t.value 
                            FROM {$wpdb->prefix}icl_string_translations t
                                JOIN {$wpdb->prefix}icl_strings s ON t.string_id = s.id
                            WHERE t.language = %s AND s.name = %s AND s.value = %s AND t.status = %d
                        ",
				                                                    $current_language,
				                                                    'URL slug: ' . $slug,
				                                                    $slug,
				                                                    ICL_TM_COMPLETE ) );

				if ( ! $slug_translation ) {
					$slug_translation = $slug;
					$slug             = $wpdb->get_var( $wpdb->prepare( "
                            SELECT s.value
                            FROM {$wpdb->prefix}icl_string_translations t
                                JOIN {$wpdb->prefix}icl_strings s ON t.string_id = s.id
                            WHERE t.language = %s AND s.name LIKE 'URL slug:%%' AND t.value = %s AND t.status = %d
                        ",
					                                                    $current_language,
					                                                    $slug,
					                                                    ICL_TM_COMPLETE ) );
				}

				$using_tags = false;

				/* case of slug using %tags% - PART 1 of 2 - START */
				if ( preg_match( '#%([^/]+)%#', $slug ) ) {
					$slug       = preg_replace( '#%[^/]+%#', '.+?', $slug );
					$using_tags = true;
				}
				if ( preg_match( '#%([^/]+)%#', $slug_translation ) ) {
					$slug_translation = preg_replace( '#%[^/]+%#', '.+?', $slug_translation );
					$using_tags       = true;
				}
				/* case of slug using %tags% - PART 1 of 2 - END */

				$buff_value = array();
				foreach ( (array) $value as $k => $v ) {

					if ( $slug && $slug != $slug_translation ) {
						if ( preg_match( '#^[^/]*/?' . preg_quote( $slug ) . '/#',
						                 $k ) && $slug != $slug_translation
						) {
							$k = str_replace( $slug . '/', $slug_translation . '/', $k );
						}

					}
					$buff_value[ $k ] = $v;
				}

				$value = $buff_value;
				unset( $buff_value );

				/* case of slug using %tags% - PART 2 of 2 - START */
				if ( $using_tags ) {
					if ( preg_match( '#\.\+\?#', $slug ) ) {
						$slug = preg_replace( '#\.\+\?#', '(.+?)', $slug );
					}
					if ( preg_match( '#\.\+\?#', $slug_translation ) ) {
						$slug_translation = preg_replace( '#\.\+\?#', '(.+?)', $slug_translation );
					}
					$buff_value = array();
					foreach ( $value as $k => $v ) {

						if ( trim( $slug ) && trim( $slug_translation ) && $slug != $slug_translation ) {
							if ( preg_match( '#^[^/]*/?' . preg_quote( $slug ) . '/#',
							                 $k ) && $slug != $slug_translation
							) {
								$k = str_replace( $slug . '/', $slug_translation . '/', $k );
							}
						}
						$buff_value[ $k ] = $v;
					}

					$value = $buff_value;
					unset( $buff_value );
				}
				/* case of slug using %tags% - PART 2 of 2 - END */

			}
		}

		return $value;
	}

	static function get_translated_slug( $slug, $language ) {
		global $wpdb, $sitepress_settings, $sitepress;

		if ( !$slug ) {
			return false;
		}

		$current_language = $sitepress->get_current_language();

		// Pre cache all results -- BEGIN
		$cache_key_args = array( $slug, $current_language, $language );

		$cache_key   = implode( ':', array_filter( $cache_key_args ) );
		$cache_group = 'get_translated_slug';

		$slugs_translations = wp_cache_get( $cache_key, $cache_group );

		if ( ! $slugs_translations ) {
			$slugs_translations_sql = false;

			if ( $language != $sitepress_settings[ 'st' ][ 'strings_language' ] ) {
				$slugs_translations_sql = "
												SELECT s.value as original, t.value
												FROM {$wpdb->prefix}icl_strings s
												JOIN {$wpdb->prefix}icl_string_translations t ON t.string_id = s.id
												WHERE t.language = %s
												AND s.name LIKE %s
												AND s.language = %s
                                                AND t.status = %d
							";
			} else if ( $sitepress_settings[ 'st' ][ 'strings_language' ] != $current_language ) {
				$slugs_translations_sql = "
										SELECT t.value as original, s.value
										FROM {$wpdb->prefix}icl_strings s
										JOIN {$wpdb->prefix}icl_string_translations t ON t.string_id = s.id
										WHERE t.language = %s
										AND s.name LIKE %s
										AND s.language = %s
                                        AND t.status = %d
							";
				$language               = $current_language;
			}

			if ( $slugs_translations_sql ) {
				$slugs_translations_prepared = $wpdb->prepare( $slugs_translations_sql,
				                                               array(
					                                               $language,
					                                               'URL slug:%',
					                                               $sitepress_settings[ 'st' ][ 'strings_language' ],
					                                               ICL_TM_COMPLETE
				                                               ) );
				$slugs_translations          = $wpdb->get_results( $slugs_translations_prepared, 'ARRAY_A' );
				wp_cache_set( $cache_key, $slugs_translations, $cache_group );
			}
		}
		// Pre cache all results -- END

		if ( $slugs_translations != null && $slugs_translations ) {
			$translated_slug = false;
			foreach ( $slugs_translations as $slugs_row ) {
				if ( $slugs_row[ 'original' ] == $slug ) {
					$translated_slug = $slugs_row[ 'value' ];
					break;
				}
			}

			if ( $translated_slug ) {
				return $translated_slug;
			}
		}

		return $slug;
	}

	static function post_type_link_filter( $post_link, $post, $leavename, $sample ) {
		global $sitepress, $sitepress_settings;

		static $no_recursion_flag;

		if ( ! empty( $no_recursion_flag ) ) {
			return $post_link;
		}

		if ( ! $sitepress->is_translated_post_type( $post->post_type ) ) {
			return $post_link;
		}

		// get element language
		$ld = $sitepress->get_element_language_details( $post->ID, 'post_' . $post->post_type );

		if ( empty( $ld ) ) {
			return $post_link;
		}

		static $cache;

		if ( ! isset( $cache[ $post->ID ][ $leavename . '#' . $sample ] ) ) {

			$strings_language = $sitepress_settings[ 'st' ][ 'strings_language' ];

			// fix permalink when object is not in the current language
			if ( $ld->language_code != $strings_language ) {

				$slug_this = self::get_slug_by_type($post->post_type);
				$slug_real = self::get_translated_slug( $slug_this, $ld->language_code );

				if ( empty( $slug_real ) ) {
					return $post_link;
				}

				global $wp_rewrite;

				if ( isset( $wp_rewrite->extra_permastructs[ $post->post_type ] ) ) {
					$struct_original = $wp_rewrite->extra_permastructs[ $post->post_type ][ 'struct' ];

					$lslash                                                         = false !== strpos( $struct_original,
					                                                                                    '/' . $slug_this ) ? '/' : '';
					$wp_rewrite->extra_permastructs[ $post->post_type ][ 'struct' ] = preg_replace( '@' . $lslash . $slug_this . '/@',
					                                                                                $lslash . $slug_real . '/',
					                                                                                $struct_original );
					$no_recursion_flag                                              = true;
					$post_link                                                      = get_post_permalink( $post->ID,
					                                                                                      $leavename,
					                                                                                      $sample );
					$no_recursion_flag                                              = false;
					$wp_rewrite->extra_permastructs[ $post->post_type ][ 'struct' ] = $struct_original;
				} else {
					$post_link = str_replace( $slug_this . '=', $slug_real . '=', $post_link );
				}
				$cache[ $post->ID ][ $leavename . '#' . $sample ] = $post_link;
			}
		} else {
			$post_link = $cache[ $post->ID ][ $leavename . '#' . $sample ];
		}

		return $post_link;
	}

	private static function get_all_slug_translations() {
		global $wpdb;

		return $wpdb->get_col( $wpdb->prepare( "SELECT t.value
										FROM {$wpdb->prefix}icl_strings s
										JOIN {$wpdb->prefix}icl_string_translations t ON t.string_id = s.id
										WHERE s.name LIKE 'URL slug:%%'
											AND t.status = %d",
		                                       ICL_TM_COMPLETE ) );
	}

	public static function add_cpt_names( $qvars ) {

		$all_slugs_translations = self::get_all_slug_translations();
		$qvars                  = array_merge( $qvars, $all_slugs_translations );

		return $qvars;
	}

	public static function filter_pre_get_posts( $query ) {

		$all_slugs_translations = self::get_all_slug_translations();

		foreach ( $query->query as $slug => $post_name ) {
			if ( in_array( $slug, $all_slugs_translations ) ) {
				$new_slug = self::get_translated_slug( $slug, 'en' );
				unset( $query->query[ $slug ] );
				$query->query[ $new_slug ]   = $post_name;
				$query->query[ 'name' ]      = $post_name;
				unset( $query->query_vars[ $slug ] );
				$query->query_vars[ $new_slug ]   = $post_name;
				$query->query_vars[ 'name' ]      = $post_name;
			}
		}

		return $query;
	}

	static function gui_save_options( $action, $data ) {

		switch ( $action ) {
			case 'icl_slug_translation':
				global $sitepress;
				$iclsettings[ 'posts_slug_translation' ][ 'on' ] = intval( ! empty( $_POST[ 'icl_slug_translation_on' ] ) );
				$sitepress->save_settings( $iclsettings );
				echo '1|' . $iclsettings[ 'posts_slug_translation' ][ 'on' ];
				break;
		}

	}

}