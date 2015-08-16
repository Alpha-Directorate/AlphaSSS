<?php

class WPML_Admin_Texts {

	protected function __construct() {
		add_action( 'plugins_loaded', array( $this, 'icl_st_set_admin_options_filters' ), 10 );
		add_filter( 'wpml_unfiltered_admin_string', array( $this, 'unfiltered_admin_string_filter' ), 10, 2 );
	}

	public static function get_instance() {

		static $instance = null;
		if ( null === $instance ) {
			$instance = new WPML_Admin_Texts();
		}

		return $instance;
	}

	function get_wpml_config_file( $data ) {
		$output = "<wpml-config>\n\t<admin-texts>\n";

		$output .= $this->output_xml( $data, 0 );

		$output .= "\t</admin-texts>\n</wpml-config>\n";

		return $output;
	}

	private function output_xml( $data, $level ) {
		$output = '';

		foreach ( $data as $key => $value ) {
			$tabs = '';
			for ( $i = 0; $i < $level + 2; $i ++ ) {
				$tabs .= "\t";
			}
			if ( is_array( $value ) && ! empty( $value ) ) {
				$output .= $tabs . '<key name="' . $key . '">' . "\n";
				$output .= $this->output_xml( $value, $level + 1 );
				$output .= $tabs . "</key>\n";
			} else {
				$output .= $tabs . '<key name="' . $key . '" />' . "\n";
			}
		}

		return $output;
	}

	function icl_register_admin_options( $array, $key = "", $option = array() ) {

		foreach ( $array as $k => $v ) {
			if ( is_array( $v ) ) {
				array_push( $option, $k );
				$this->icl_register_admin_options( $v, $key . '[' . $k . ']', $option );
				array_pop( $option );
			} else {

				$context = $this->get_context( $key, $k );

				if ( $v === '' ) {
					icl_unregister_string( $context, $key . $k );
				} else {
					icl_register_string( $context, $key . $k, $v );

					$vals = array( $k => 1 );
					if ( count( $option ) ) {
						for ( $i = count( $option ) - 1; $i >= 0; $i -- ) {
							$vals = array( $option[ $i ] => $vals );
						}
					}

					$_icl_admin_option_names = get_option( '_icl_admin_option_names' );

					$_icl_admin_option_names = array_merge_recursive( (array) $_icl_admin_option_names, $vals );

					update_option( '_icl_admin_option_names', $_icl_admin_option_names );
				}
			}
		}
	}

	function icl_st_render_option_writes( $option_name, $option_value, $option_key = '' ) {
		$has_translations = '';
		if ( is_array( $option_value ) || is_object( $option_value ) ) {
			echo '<h4><a class="icl_stow_toggler" href="#">+ ' . $option_name . '</a></h4>';
			echo '<ul class="icl_st_option_writes" style="display: none">';
			foreach ( $option_value as $key => $value ) {
				echo '<li>';
				$this->icl_st_render_option_writes( $key, $value, $option_key . '[' . $option_name . ']' );
				echo '</li>';
			}
			echo '</ul>';
		} elseif ( is_string( $option_value ) || is_numeric( $option_value ) ) {

			$context = $this->get_context( $option_key, $option_name );

			if ( icl_st_is_registered_string( $context, $option_key . $option_name ) ) {
				$checked = ' checked="checked"';
				if ( icl_st_string_has_translations( $context, $option_key . $option_name ) ) {
					$has_translations = ' class="icl_st_has_translations"';
				} else {
					$has_translations = '';
				}
			} else {
				$checked = '';
			}
			if ( is_numeric( $option_value ) ) {
				$class = 'icl_st_numeric';
			} else {
				$class = 'icl_st_string';
			}

			global $iclTranslationManagement;

			$int = preg_match_all( '#\[([^\]]+)\]#', $option_key . '[' . $option_name . ']', $matches );
			$_v  = $iclTranslationManagement->admin_texts_to_translate;
			if ( $int ) {
				foreach ( $matches[ 1 ] as $m ) {
					if ( isset( $_v[ $m ] ) ) {
						$_v = $_v[ $m ];
					} else {
						$_v = 0;
						break;
					}
				}
			}

			if ( $_v ) {
				$disabled         = ' disabled="disabled"';
				$has_translations = ''; // remove "has_translation" class because we can't uncheck it anyway.
			} else {
				$disabled = '';
			}
			echo '<div class="icl_st_admin_string ' . $class . '">';
			echo '<input' . $disabled . ' type="hidden" name="icl_admin_options' . $option_key . '[' . $option_name . ']" value=""  />';
			echo '<input' . $disabled . $has_translations . ' type="checkbox" name="icl_admin_options' . $option_key . '[' . $option_name . ']" value="' . htmlspecialchars( $option_value ) . '"
				' . $checked . ' />';
			echo '<input type="text" readonly="readonly" value="' . $option_name . '" size="32" />';
			echo '<input type="text" value="' . htmlspecialchars( $option_value ) . '" readonly="readonly" size="48" />';
			//echo '<br /><input type="text" size="100" value="icl_admin_options'.$option_key.'['.$option_name.']" />';
			echo '</div><br clear="all" />';
		}
	}

	private function get_context( $option_key, $option_name ) {
		if ( $option_key ) {
			preg_match( '#\[([^\]]+)\]#', $option_key, $matches );
			$context = $matches[ 1 ];
		} else {
			$context = $option_name;
		}

		return 'admin_texts_' . $context;
	}

	function icl_st_scan_options_strings() {
		$black_list = array(
			'active_plugins',
			'users_can_register',
			'admin_email',
			'start_of_week',
			'use_balanceTags',
			'use_smilies',
			'require_name_email',
			'comments_notify',
			'posts_per_rss',
			'sticky_posts',
			'widget_categories',
			'widget_text',
			'widget_rss',
			'page_for_post',
			'page_on_front',
			'default_post_format',
			'link_manager_enabled',
			'icl_sitepress_settings',
			'wpml_config_index',
			'wpml_config_index_updated',
			'wpml_config_files_arr',
			'icl_admin_messages',
			'wpml_media',
			'wpml_ta_settings',
			'_icl_admin_option_names',
			'_icl_cache',
			'icl_sitepress_version',
			'rewrite_rules',
			'sidebars_widgets',
			'widget_meta',
			'widget_archive',
			'recently_activated',
			'wpml_tm_version',
			'wp_installer_settings',
			'icl_adl_settings',
			'rss_use_excerpt',
			'template',
			'stylesheet',
			'comment_whitelist',
			'comment_registration',
			'html_type',
			'use_trackback',
			'default_role',
			'db_version',
			'siteurl',
			'home',
			'blogname',
			'blogdescription',
			'mailserver_url',
			'mailserver_login',
			'mailserver_pass',
			'mailserver_port',
			'default_category',
			'default_comment_status',
			'default_ping_status',
			'default_pingback_flag',
			'comment_moderation',
			'moderation_notify',
			'permalink_structure',
			'gzipcompression',
			'hack_file',
			'blog_charset',
			'ping_sites',
			'advanced_edit',
			'comment_max_links',
			'gmt_offset',
			'default_email_category',
			'uploads_use_yearmonth_folders',
			'upload_path',
			'blog_public',
			'default_link_category',
			'tag_base',
			'show_avatars',
			'avatar_rating',
			'WPLANG',
			'cron',
			'_transient_WPML_ST_MO_Downloader_lang_map',
			'icl_translation_jobs_basket'
		);

		$options = wp_load_alloptions();
		foreach ( $options as $name => $value ) {
			if ( in_array( $name, $black_list ) ) {
				unset( $options[ $name ] );
			} else {
				$options[ $name ] = maybe_unserialize( $value );
			}
		}

		return $options;
	}

	function icl_st_set_admin_options_filters() {
		static $option_names;
		if ( empty( $option_names ) ) {
			$option_names = get_option( '_icl_admin_option_names' );
		}

		if ( is_array( $option_names ) ) {
			foreach ( $option_names as $option_key => $option ) {
				if ( $option_key != 'theme' && $option_key != 'plugin' ) { // theme and plugin are an obsolete format before 3.2
					add_filter( 'option_' . $option_key, array( $this, 'icl_st_translate_admin_string' ) );
				}
			}
		}
	}

	function icl_st_translate_admin_string( $option_value, $key = "", $name = "", $rec_level = 0 ) {
		static $__icl_st_cache;

		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) || ICL_PLUGIN_INACTIVE ) {
			return $option_value;
		}

		$option_name = substr( current_filter(), 7 );
		if ( ! $name ) {
			$name = $option_name;
		}

		if ( isset( $__icl_st_cache[ $name ] ) ) {

			return $__icl_st_cache[ $name ];
		}

		// case of double-serialized options (See Arras theme)
		$serialized = false;
		if ( is_serialized( $option_value ) ) {
			$option_value = @unserialize( $option_value );
			$serialized   = true;
		}

		if ( is_array( $option_value ) || is_object( $option_value ) ) {
			foreach ( $option_value as $k => $value ) {

				$val = $this->icl_st_translate_admin_string( $value, $key . '[' . $name . ']', $k, $rec_level + 1 );

				if ( is_object( $option_value ) ) {
					$option_value->$k = $val;
				} else {
					$option_value[ $k ] = $val;
				}
			}
		} else {
			static $option_names;
			if ( empty( $option_names ) ) {
				$option_names = get_option( '_icl_admin_option_names' );
			}

			$tr = icl_t( 'admin_texts_' . $option_name, $key . $name, $option_value, $hast, true );

			if ( isset( $tr ) ) {
				$option_value = $tr;
			}
		}

		// case of double-serialized options (See Arras theme)
		if ( $serialized ) {
			$option_value = serialize( $option_value );
		}

		/*
		 * if sticky links plugin is enabled and set to change links into sticky
		 * in strings, change those links back into permalinks when displayed
		 */
		if ( is_string( $option_value ) and class_exists( "WPML_Sticky_links" ) ) {
			global $WPML_Sticky_Links;
			if ( isset( $WPML_Sticky_Links ) ) {
				if ( $WPML_Sticky_Links->settings[ 'sticky_links_strings' ] ) {
					$option_value = $WPML_Sticky_Links->show_permalinks( $option_value );
				}
			}
		}

		if ( $rec_level == 0 ) {
			$__icl_st_cache[ $name ] = $option_value;
		}

		return $option_value;
	}

	public function parse_config( $config ) {

		global $iclTranslationManagement, $sitepress;

		if ( function_exists( 'icl_register_string' ) ) {

			$requires_upgrade = ! $sitepress->get_setting( 'admin_text_3_2_migration_complete', false );

			$admin_texts = array();
			if ( ! empty( $config[ 'wpml-config' ][ 'admin-texts' ] ) ) {

				if ( ! is_numeric( key( @current( $config[ 'wpml-config' ][ 'admin-texts' ] ) ) ) ) {
					$admin_texts[ 0 ] = $config[ 'wpml-config' ][ 'admin-texts' ][ 'key' ];
				} else {
					$admin_texts = $config[ 'wpml-config' ][ 'admin-texts' ][ 'key' ];
				}

				$type               = 'plugin';
				$admin_text_context = '';

				foreach ( $admin_texts as $a ) {

					if ( isset( $a[ 'type' ] ) ) {
						$type = $a[ 'type' ];
					}
					if ( isset( $a[ 'context' ] ) ) {
						$admin_text_context = $a[ 'context' ];
					}
					if ( ! isset( $type ) ) {
						$type = 'plugin';
					}
					if ( ! isset( $admin_text_context ) ) {
						$admin_text_context = '';
					}

					$keys = array();
					if ( ! isset( $a[ 'key' ] ) ) {
						$arr[ $a[ 'attr' ][ 'name' ] ]         = 1;
						$arr_context[ $a[ 'attr' ][ 'name' ] ] = $admin_text_context;
						$arr_type[ $a[ 'attr' ][ 'name' ] ]    = $type;
						continue;
					} elseif ( ! is_numeric( key( $a[ 'key' ] ) ) ) {
						$keys[ 0 ] = $a[ 'key' ];
					} else {
						$keys = $a[ 'key' ];
					}

					foreach ( $keys as $key ) {
						if ( isset( $key[ 'key' ] ) ) {
							$arr[ $a[ 'attr' ][ 'name' ] ][ $key[ 'attr' ][ 'name' ] ] = self::read_admin_texts_recursive( $key[ 'key' ], $admin_text_context, $type, $arr_context, $arr_type );
						} else {
							$arr[ $a[ 'attr' ][ 'name' ] ][ $key[ 'attr' ][ 'name' ] ] = 1;
						}
						$arr_context[ $a[ 'attr' ][ 'name' ] ] = $admin_text_context;
						$arr_type[ $a[ 'attr' ][ 'name' ] ]    = $type;
					}
				}

				if ( isset( $arr ) ) {
					$iclTranslationManagement->admin_texts_to_translate = array_merge( $iclTranslationManagement->admin_texts_to_translate, $arr );
				}

				$_icl_admin_option_names = get_option( '_icl_admin_option_names' );

				$arr_options = array();
				if ( isset( $arr ) && is_array( $arr ) ) {
					foreach ( $arr as $key => $v ) {

						$value = $this->get_option_without_filtering( $key );
						$value = maybe_unserialize( $value );

						if ( is_array( $value ) && is_array( $v ) ) {

							$v = array_keys( $v );
							// only keep the values defined.

							foreach ( $value as $option_key => $option_value ) {
								if ( ! in_array( $option_key, $v ) ) {
									unset( $value[ $option_key ] );
								}
							}

							// Add any additional settings that are not in the options already.

							foreach ( $v as $option_key ) {
								if ( ! isset( $value[ $option_key ] ) ) {
									$value[ $option_key ] = '';
								}
							}
						}

						$admin_text_context = isset( $arr_context[ $key ] ) ? $arr_context[ $key ] : '';
						$type               = isset( $arr_type[ $key ] ) ? $arr_type[ $key ] : '';

						if ( false === $value ) {

							// wildcard? register all matching options in wp_options
							global $wpdb;
							$src     = str_replace( '*', '%', wpml_like_escape( $key ) );
							$matches = $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE '{$src}'" );
							foreach ( $matches as $match ) {
								$value = maybe_unserialize( $match->option_value );
								if ( is_array( $value ) ) {
									$this->register_string_recursive( $match->option_name, $value, $value, '', $match->option_name, $requires_upgrade, $type, $admin_text_context );
								} else {
									icl_register_string( 'admin_texts_' . $match->option_name, $match->option_name, $match->option_value );
								}
								if ( $requires_upgrade ) {
									$this->migrate_3_2( $type, $admin_text_context, $match->option_name, $match->option_name );
								}

								$_icl_admin_option_names[ $type ][ $admin_text_context ][ ] = $match->option_name;
							}
							unset( $arr[ $key ] );
						}
						if ( is_scalar( $value ) ) {
							icl_register_string( 'admin_texts_' . $key, $key, $value );
							if ( $requires_upgrade ) {
								$this->migrate_3_2( $type, $admin_text_context, $key, $key );
							}
						} else {
							if ( is_object( $value ) ) {
								$value = (array) $value;
							}
							if ( ! empty( $value ) ) {
								$this->register_string_recursive( $key, $value, $arr[ $key ], '', $key, $requires_upgrade, $type, $admin_text_context );
							}
						}
						$arr_options[ $key ] = $v;
					}

					if ( is_array( $_icl_admin_option_names ) ) {
						$_icl_admin_option_names = array_replace_recursive( $arr_options, $_icl_admin_option_names );
					} else {
						$_icl_admin_option_names = $arr_options;
					}
				}

				//$_icl_admin_option_names[ $type ][ $admin_text_context ] = __array_unique_recursive( $_icl_admin_option_names[ $type ][ $admin_text_context ] );

				update_option( '_icl_admin_option_names', $_icl_admin_option_names );
			}

			$sitepress->set_setting( 'admin_text_3_2_migration_complete', true, true );
		}
	}

	private static function read_admin_texts_recursive( $keys, $admin_text_context, $type, &$arr_context, &$arr_type ) {
		if ( ! is_numeric( key( $keys ) ) ) {
			$_keys = array( $keys );
			$keys  = $_keys;
			unset( $_keys );
		}
		$arr = false;
		if ( $keys ) {
			foreach ( $keys as $key ) {
				if ( isset( $key[ 'key' ] ) ) {
					$arr[ $key[ 'attr' ][ 'name' ] ] = self::read_admin_texts_recursive( $key[ 'key' ], $admin_text_context, $type, $arr_context, $arr_type );
				} else {
					$arr[ $key[ 'attr' ][ 'name' ] ]         = 1;
					$arr_context[ $key[ 'attr' ][ 'name' ] ] = $admin_text_context;
					$arr_type[ $key[ 'attr' ][ 'name' ] ]    = $type;
				}
			}
		}

		return $arr;
	}

	private function register_string_recursive( $key, $value, $arr, $prefix = '', $suffix, $requires_upgrade, $type, $admin_text_context_old ) {
		if ( is_scalar( $value ) ) {
			icl_register_string( 'admin_texts_' . $suffix, $prefix . $key, $value, true );
			if ( $requires_upgrade ) {
				$this->migrate_3_2( $type, $admin_text_context_old, $suffix, $prefix . $key );
			}
		} else {
			if ( ! is_null( $value ) ) {
				foreach ( $value as $sub_key => $sub_value ) {
					$is_wildcard = false;
					if ( $arr && ! isset( $arr[ $sub_key ] ) ) { //wildcard
						if ( is_array( $arr ) ) {
							$array_keys = array_keys( $arr );
							if ( is_array( $array_keys ) ) {
								foreach ( $array_keys as $array_key ) {
									$array_key = str_replace( '/', '\/', $array_key );
									$array_key = '/' . str_replace( '*', '(.*)', $array_key ) . '/';
									if ( preg_match( $array_key, $sub_key ) ) {
										$is_wildcard     = true;
										$arr[ $sub_key ] = true; //placeholder
										break;
									};
								}
							}
						}
					}

					if ( isset( $arr[ $sub_key ] ) || $is_wildcard ) {
						$this->register_string_recursive( $sub_key, $sub_value, $arr[ $sub_key ], $prefix . '[' . $key . ']', $suffix, $requires_upgrade, $type, $admin_text_context_old );
					}
				}
			}
		}
	}

	private function migrate_3_2( $type, $old_admin_text_context, $new_admin_text_context, $key ) {
		global $wpdb;

		$old_string_id = icl_st_is_registered_string( 'admin_texts_' . $type . '_' . $old_admin_text_context, $key );
		if ( $old_string_id ) {
			$new_string_id = icl_st_is_registered_string( 'admin_texts_' . $new_admin_text_context, $key );

			if ( $new_string_id ) {

				// make the old translations point to the new translations

				$wpdb->update( $wpdb->prefix . 'icl_string_translations', array( 'string_id' => $new_string_id ), array( 'string_id' => $old_string_id ) );

				// Copy the status.
				$status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$wpdb->prefix}icl_strings WHERE id = %d", $old_string_id ) );
				$wpdb->update( $wpdb->prefix . 'icl_strings', array( 'status' => $status ), array( 'id' => $new_string_id ) );
			}
		}
	}

	/**
	 * @param mixed  $default_value Value to return in case the string does not exists
	 * @param string $option_name   Name of option to retrieve. Expected to not be SQL-escaped.
	 *
	 * @return mixed Value set for the option.
	 */
	function unfiltered_admin_string_filter( $default_value, $option_name ) {
		return $this->get_option_without_filtering( $option_name, $default_value );
	}

	/**
	 * @param string $key     Name of option to retrieve. Expected to not be SQL-escaped.
	 * @param mixed  $default Value to return in case the string does not exists
	 *
	 * @return mixed Value set for the option.
	 */
	private function get_option_without_filtering( $key, $default = false ) {
		remove_filter( 'option_' . $key, array( $this, 'icl_st_translate_admin_string' ) ); // put the filter back on
		$value = get_option( $key, $default );
		add_filter( 'option_' . $key, array( $this, 'icl_st_translate_admin_string' ) ); // put the filter back on

		return $value;
	}
}
