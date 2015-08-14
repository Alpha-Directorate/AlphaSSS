<?php

require 'wpml-language-switcher-settings.class.php';

class WPML_Installation {

	function go_to_setup1() {
		global $wpdb;

		icl_set_setting( 'existing_content_language_verified', 0 );
		icl_set_setting( 'setup_wizard_step', 1 );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}icl_translations" );
		$wpdb->update( $wpdb->prefix . 'icl_languages', array( 'active' => 0 ), array( 'active' => 1 ) );
		icl_save_settings();
	}

	/**
	 * Sets the locale in the icl_locale_map if it has not yet been set
	 *
	 * @param $initial_language_code
	 */
	private function maybe_set_locale( $initial_language_code ) {
		global $wpdb;

		$q          = "SELECT code FROM {$wpdb->prefix}icl_locale_map WHERE code=%s";
		$q_prepared = $wpdb->prepare( $q, $initial_language_code );
		if ( ! $wpdb->get_var( $q_prepared ) ) {
			$q              = "SELECT default_locale FROM {$wpdb->prefix}icl_languages WHERE code=%s";
			$q_prepared     = $wpdb->prepare( $q, $initial_language_code );
			$default_locale = $wpdb->get_var( $q_prepared );
			if ( $default_locale ) {
				$wpdb->insert(
					$wpdb->prefix . 'icl_locale_map',
					array( 'code' => $initial_language_code, 'locale' => $default_locale )
				);

			}
		}
	}

	public function finish_step2( $active_languages ) {
		return $this->set_active_languages( $active_languages );
	}

	public function set_active_languages( $arr ) {
		global $wpdb;

		$tmp = $this->sanitize_language_input( $arr );
		if ( (bool) $tmp === false ) {
			return false;
		}

		foreach ( $tmp as $code ) {
			$default_locale_prepared = $wpdb->prepare(
				"SELECT default_locale FROM {$wpdb->prefix}icl_languages WHERE code= %s LIMIT 1",
				$code
			);
			$default_locale          = $wpdb->get_var( $default_locale_prepared );
			if ( $default_locale ) {
				$code_exists_prepared = $wpdb->prepare(
					"SELECT code FROM {$wpdb->prefix}icl_locale_map WHERE code = %s LIMIT 1",
					$code
				);
				$code_exists          = $wpdb->get_var( $code_exists_prepared );
				if ( $code_exists ) {
					$wpdb->update(
						$wpdb->prefix . 'icl_locale_map',
						array( 'locale' => $default_locale ),
						array( 'code' => $code )
					);
				} else {
					$wpdb->insert(
						$wpdb->prefix . 'icl_locale_map',
						array( 'code' => $code, 'locale' => $default_locale )
					);
				}
			}
			SitePress_Setup::insert_default_category( $code );
		}

		$wpdb->query(
			"UPDATE {$wpdb->prefix}icl_languages SET active = 1 WHERE code IN (" . wpml_prepare_in( $tmp ) . " ) "
		);
		$wpdb->query(
			"UPDATE {$wpdb->prefix}icl_languages SET active = 0 WHERE code NOT IN (" . wpml_prepare_in( $tmp ) . " ) "
		);
		$this->updated_active_languages();

		return true;
	}

	private function sanitize_language_input( $lang_codes ) {
		global $sitepress;

		$languages = $sitepress->get_languages( false, false, true );

		$sanitized_codes = array();
		$lang_codes      = array_filter( array_unique( $lang_codes ) );
		foreach ( $lang_codes as $code ) {
			$code = esc_sql( trim( $code ) );
			if ( isset( $languages[ $code ] ) ) {
				$sanitized_codes[ ] = $code;
			}
		}

		return $sanitized_codes;
	}

	public function finish_installation( $site_key = false ) {
		icl_set_setting( 'setup_complete', 1, true );
		if ( $site_key ) {
			icl_set_setting( 'site_key', $site_key, true );
		}
	}

	public function finish_step3( $ls_sidebars, $ls_options ) {
		$ls_setup = new WPML_Language_Switcher_Settings( $ls_sidebars, $ls_options );
		if ( $ls_sidebars ) {
			$ls_setup->set_ls_sidebar();
		}
		$ls_setup->set_ls_options();
		$this->maybe_move_setup( 4 );
	}

	private function maybe_move_setup( $step ) {
		$setup_complete = icl_get_setting( 'setup_complete' );
		if ( empty( $setup_complete ) ) {
			icl_set_setting( 'setup_wizard_step', $step, true );
		}
	}

	private function updated_active_languages() {
		global $sitepress;

		wp_cache_init();
		icl_cache_clear();
		$this->refresh_active_lang_cache( wpml_get_setting_filter( false, 'default_language' ) );
		wpml_reload_active_languages_setting( true );
		$active_langs = $sitepress->get_active_languages( true );
		$this->maybe_move_setup( 3 );
		if ( count( $active_langs ) > 1 ) {
			icl_set_setting( 'dont_show_help_admin_notice', true );
		}
	}

	public function finish_step1( $initial_language_code ) {

		$this->set_initial_default_category( $initial_language_code );
		$this->prepopulate_translations( $initial_language_code );
		icl_set_setting( 'existing_content_language_verified', 1 );
		icl_set_setting( 'default_language', $initial_language_code );
		icl_set_setting( 'admin_default_language', $initial_language_code );

		$this->maybe_set_locale( $initial_language_code );
		icl_set_setting( 'setup_wizard_step', 2 );
		icl_save_settings();
		$this->refresh_active_lang_cache( $initial_language_code );
		do_action( 'icl_initial_language_set' );
	}

	private function set_initial_default_category( $initial_lang ) {
		global $wpdb;

		$blog_default_cat        = get_option( 'default_category' );
		$blog_default_cat_tax_id = $wpdb->get_var(
			$wpdb->prepare(
				"	SELECT term_taxonomy_id
	                FROM {$wpdb->term_taxonomy}
	                WHERE term_id=%d
	                  AND taxonomy='category'",
				$blog_default_cat
			)
		);
		icl_set_setting( 'default_categories', array( $initial_lang => $blog_default_cat_tax_id ), true );
	}

	/**
	 * @param string $display_language
	 * @param bool   $active_only
	 *
	 * @return array
	 */
	public function refresh_active_lang_cache( $display_language, $active_only = false ) {
		global $wpdb, $sitepress;

		$active_snippet     = $active_only ? " l.active = 1 AND " : "";
		$res_query
							= "
            SELECT
              l.code,
              l.id, english_name,
              nt.name AS native_name,
              major,
              active,
              default_locale,
              encode_url,
              tag,
              lt.name AS display_name
			FROM {$wpdb->prefix}icl_languages l
			JOIN {$wpdb->prefix}icl_languages_translations nt
			  ON ( nt.language_code = l.code AND nt.display_language_code = l.code )
            LEFT OUTER JOIN {$wpdb->prefix}icl_languages_translations lt ON l.code=lt.language_code
			WHERE {$active_snippet}
			  ( lt.display_language_code = %s
			  OR (lt.display_language_code = 'en'
			    AND NOT EXISTS ( SELECT *
			          FROM {$wpdb->prefix}icl_languages_translations ls
			          WHERE ls.language_code = l.code
			            AND ls.display_language_code = %s ) ) )
            GROUP BY l.code
			ORDER BY major DESC, english_name ASC";
		$res_query_prepared = $wpdb->prepare( $res_query, $display_language, $display_language );
		$res                = $wpdb->get_results( $res_query_prepared, ARRAY_A );
		$languages          = array();

		foreach ( (array) $res as $r ) {
			$languages[ $r[ 'code' ] ] = $r;
			$sitepress->icl_language_name_cache->set( 'language_details_' . $r[ 'code' ] . $display_language, $r );
		}

		if ( $active_only ) {
			$sitepress->icl_language_name_cache->set( 'in_language_' . $display_language, $languages );
		} else {
			$sitepress->icl_language_name_cache->set( 'all_language_' . $display_language, $languages );
		}

		return $languages;
	}

	private function prepopulate_translations( $lang ) {
		global $wpdb;

		$existing_lang_verified = icl_get_setting( 'existing_content_language_verified' );
		if ( ! empty( $existing_lang_verified ) ) {
			return;
		}

		icl_cache_clear();

		// case of icl_sitepress_settings accidentally lost
		// if there's at least one translation do not initialize the languages for elements
		$one_translation = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT translation_id FROM {$wpdb->prefix}icl_translations WHERE language_code<>%s",
				$lang
			)
		);
		if ( $one_translation ) {
			return;
		}

		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}icl_translations" );
		$wpdb->query(
			$wpdb->prepare(
				"
			INSERT INTO {$wpdb->prefix}icl_translations(element_type, element_id, trid, language_code, source_language_code)
			SELECT CONCAT('post_',post_type), ID, ID, %s, NULL FROM {$wpdb->posts} WHERE post_status IN ('draft', 'publish','schedule','future','private', 'pending')
			",
				$lang
			)
		);

		$maxtrid = 1 + $wpdb->get_var( "SELECT MAX(trid) FROM {$wpdb->prefix}icl_translations" );

		global $wp_taxonomies;
		$taxonomies = array_keys( (array) $wp_taxonomies );
		foreach ( $taxonomies as $tax ) {
			$element_type   = 'tax_' . $tax;
			$insert_query
							= "
				INSERT INTO {$wpdb->prefix}icl_translations(element_type, element_id, trid, language_code, source_language_code)
				SELECT %s, term_taxonomy_id, %d+term_taxonomy_id, %s, NULL FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s
				";
			$insert_prepare = $wpdb->prepare( $insert_query, array( $element_type, $maxtrid, $lang, $tax ) );
			$wpdb->query( $insert_prepare );
			$maxtrid = 1 + $wpdb->get_var( "SELECT MAX(trid) FROM {$wpdb->prefix}icl_translations" );
		}

		$wpdb->query(
			$wpdb->prepare(
				"
			INSERT INTO {$wpdb->prefix}icl_translations(element_type, element_id, trid, language_code, source_language_code)
			SELECT 'comment', comment_ID, {$maxtrid}+comment_ID, %s, NULL FROM {$wpdb->comments}
			",
				$lang
			)
		);

		$wpdb->update( $wpdb->prefix . 'icl_languages', array( 'active' => '1' ), array( 'code' => $lang ) );
	}

	function reset_language_data() {

		global $wpdb, $sitepress;

		$active = $wpdb->get_col( "SELECT code FROM {$wpdb->prefix}icl_languages WHERE active = 1" );

		$wpdb->query( "TRUNCATE TABLE `{$wpdb->prefix}icl_languages`" );
		SitePress_Setup::fill_languages();
		$wpdb->query( "TRUNCATE TABLE `{$wpdb->prefix}icl_languages_translations`" );
		SitePress_Setup::fill_languages_translations();
		$wpdb->query( "TRUNCATE TABLE `{$wpdb->prefix}icl_flags`" );
		SitePress_Setup::fill_flags();

		//restore active
		$wpdb->query(
			"UPDATE {$wpdb->prefix}icl_languages SET active=1 WHERE code IN(" . wpml_prepare_in( $active ) . ")"
		);

		$wpdb->update( $wpdb->prefix . 'icl_flags', array( 'from_template' => 0 ), null );

		$codes = $wpdb->get_col( "SELECT code FROM {$wpdb->prefix}icl_languages" );
		foreach ( $codes as $code ) {
			if ( ! $code || $wpdb->get_var(
					$wpdb->prepare( "SELECT lang_code FROM {$wpdb->prefix}icl_flags WHERE lang_code = %s", $code )
				)
			) {
				continue;
			}
			if ( ! file_exists( ICL_PLUGIN_PATH . '/res/flags/' . $code . '.png' ) ) {
				$file = 'nil.png';
			} else {
				$file = $code . '.png';
			}
			$wpdb->insert(
				$wpdb->prefix . 'icl_flags',
				array( 'lang_code' => $code, 'flag' => $file, 'from_template' => 0 )
			);
		}

		$last_default_language = $sitepress !== null ? $sitepress->get_default_language() : 'en';
		if ( ! in_array( $last_default_language, $codes ) ) {
			$last_active_languages = $sitepress->get_active_languages();
			foreach ( $last_active_languages as $code => $last_active_language ) {
				if ( in_array( $code, $codes ) ) {
					$sitepress->set_default_language( $code );
					break;
				}
			}
		}

		icl_cache_clear();
	}

}