<?php

class WPML_Admin_Scripts_Setup{

	private $default_language;
	private $current_language;
	private $page;

	public function __construct( $default_language, $current_language, $page ) {
		add_action ( 'admin_print_scripts', array( $this, 'wpml_js_scripts_setup' ) );
		add_action ( 'admin_print_styles', array( $this, 'wpml_css_setup' ) );
		$this->default_language = $default_language;
		$this->current_language = $current_language;
		$this->page             = $page;
	}

	private function print_js_globals(){
		global $sitepress;

		$icl_ajax_url_root = rtrim ( get_site_url (), '/' );
		if ( defined ( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) {
			$icl_ajax_url_root = str_replace ( 'http://', 'https://', $icl_ajax_url_root );
		}
		$icl_ajax_url = $icl_ajax_url_root . '/wp-admin/admin.php?page=' . ICL_PLUGIN_FOLDER . '/menu/languages.php';
		?>
		<script type="text/javascript">
			// <![CDATA[
			var icl_ajx_url;
			icl_ajx_url = '<?php echo $icl_ajax_url; ?>';
			var icl_ajx_saved = '<?php echo icl_js_escape( __('Data saved','sitepress')); ?>';
			var icl_ajx_error = '<?php echo icl_js_escape( __('Error: data not saved','sitepress')); ?>';
			var icl_default_mark = '<?php echo icl_js_escape(__('default','sitepress')); ?>';
			var icl_this_lang = '<?php echo $this->current_language ?>';
			var icl_ajxloaderimg_src = '<?php echo ICL_PLUGIN_URL ?>/res/img/ajax-loader.gif';
			var icl_cat_adder_msg = '<?php echo icl_js_escape(sprintf(__('To add categories that already exist in other languages go to the <a%s>category management page</a>','sitepress'), ' href="'.admin_url('edit-tags.php?taxonomy=category').'"'));?>';
			// ]]>

			<?php if(!$sitepress->get_setting('ajx_health_checked')): ?>
			addLoadEvent(function () {
				jQuery.ajax({
					type: "POST", url: icl_ajx_url, data: "icl_ajx_action=health_check", error: function (msg) {
						var icl_initial_language = jQuery('#icl_initial_language');
						if (icl_initial_language.length) {
							icl_initial_language.find('input').attr('disabled', 'disabled');
						}
						jQuery('.wrap').prepend('<div class="error"><p><?php
							echo icl_js_escape(sprintf(__("WPML can't run normally. There is an installation or server configuration problem. %sShow details%s",'sitepress'),
							'<a href="#" onclick="jQuery(this).parent().next().slideToggle()">', '</a>'));
						?></p><p style="display:none"><?php echo icl_js_escape(__('AJAX Error:', 'sitepress'))?> ' + msg.statusText + ' [' + msg.status + ']<br />URL:' + icl_ajx_url + '</p></div>');
					}
				});
			});
			<?php endif; ?>
		</script>
		<?php

	}

	public function wpml_js_scripts_setup() {
	//TODO: [WPML 3.3] move javascript to external resource (use wp_localize_script() to pass arguments)
		global $pagenow, $wpdb, $sitepress, $wpml_post_translations;
		$default_language = $this->default_language;
		$current_language = $this->current_language;

		$page_basename = $this->page;

		$this->print_js_globals();

		if ( 'options-reading.php' === $pagenow ) {
			$this->print_reading_options_js();
		} elseif ( in_array ( $pagenow, array( 'edit.php', 'edit-pages.php', 'categories.php', 'edit-tags.php' ), true )
		           && $current_language !== $default_language
		) {
			// display correct links on the posts by status break down
			// also fix links to category and tag pages
			?>
			<script type="text/javascript">
				addLoadEvent(
					function () {
						jQuery(document).ready(
							function () {
								jQuery('.subsubsub:not(.icl_subsubsub) li a').each(
									function () {
										var h = jQuery(this).attr('href');
										var urlg;
										if (-1 == h.indexOf('?')) {
											urlg = '?';
										} else {
											urlg = '&';
										}
										jQuery(this).attr('href', h + urlg + 'lang=<?php echo $current_language?>');
									}
								);
								jQuery('.column-categories a, .column-tags a, .column-posts a').each(
									function () {
										jQuery(this).attr('href', jQuery(this).attr('href') + '&lang=<?php echo $current_language?>');
									}
								);
							}
						);
					}
				);
			</script>
		<?php
		}

		if ( 'edit-tags.php' === $pagenow ) {
			?>
			<script type="text/javascript">
				addLoadEvent(function () {
					var edit_tag = jQuery('#edittag');
					if (edit_tag.find('[name="_wp_original_http_referer"]').length && edit_tag.find('[name="_wp_http_referer"]').length) {
						edit_tag.find('[name="_wp_original_http_referer"]').val('<?php
							$post_type = isset($_GET['post_type']) ? '&post_type=' . esc_html($_GET['post_type']) : '';
							echo admin_url('edit-tags.php?taxonomy=' . esc_js($_GET['taxonomy']) . '&lang='.$current_language.'&message=3'.$post_type) ?>');
					}
				});
			</script>
		<?php
		}
		$trid = filter_input(INPUT_GET, 'trid', FILTER_SANITIZE_NUMBER_INT);
		$source_lang = $trid !== null ? filter_input(INPUT_GET, 'source_lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
		if ( 'post-new.php' === $pagenow ) {
			if ( $trid ) {
				$translations = $wpdb->get_col (
					$wpdb->prepare (
						"SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d",
						$_GET[ 'trid' ]
					)
				);
				remove_filter (
					'pre_option_sticky_posts',
					array(
						$sitepress,
						'option_sticky_posts'
					)
				); // remove filter used to get language relevant stickies. get them all
				$sticky_posts = get_option ( 'sticky_posts' );
				add_filter ( 'pre_option_sticky_posts', array( $sitepress, 'option_sticky_posts' ), 10, 2); // add filter back
				$is_sticky = false;
				foreach ( $translations as $t ) {
					if ( in_array ( $t, $sticky_posts ) ) {
						$is_sticky = true;
						break;
					}
				}
				if ( $sitepress->get_setting('sync_ping_status') || $sitepress->get_setting('sync_comment_status') ) {
				?>
					<script type="text/javascript">addLoadEvent(function () {
							var comment_status = jQuery('#comment_status');
							var ping_status = jQuery('#ping_status');
							<?php if($sitepress->get_setting('sync_comment_status')): ?>
							<?php if($wpml_post_translations->get_original_comment_status($trid, $source_lang) === 'open'): ?>
							comment_status.attr('checked', 'checked');
							<?php else: ?>
							comment_status.removeAttr('checked');
							<?php endif; ?>
							<?php endif; ?>
							<?php if($sitepress->get_setting('sync_ping_status')): ?>
							<?php if($wpml_post_translations->get_original_ping_status($trid, $source_lang) === 'open'): ?>
							ping_status.attr('checked', 'checked');
							<?php else: ?>
							ping_status.removeAttr('checked');
							<?php endif; ?>
							<?php endif; ?>
						});</script><?php
				}

				if ( 'private' === $wpml_post_translations->get_original_post_status ( $trid, $source_lang )
				) {
					?>
					<script type="text/javascript">addLoadEvent(function () {
							jQuery('#visibility-radio-private').attr('checked', 'checked');
							jQuery('#post-visibility-display').html('<?php echo icl_js_escape(__('Private', 'sitepress')); ?>');
						});
					</script><?php
				}

				if ( $sitepress->get_setting('sync_post_taxonomies') ) {

					$post_type         = isset( $_GET[ 'post_type' ] ) ? $_GET[ 'post_type' ] : 'post';
					$source_lang       = isset( $_GET[ 'source_lang' ] ) ? filter_input ( INPUT_GET, 'source_lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : $default_language;
					$translatable_taxs = $sitepress->get_translatable_taxonomies ( true, $post_type );
					$all_taxs          = get_object_taxonomies ( $post_type );

					$translations = $sitepress->get_element_translations ( $_GET[ 'trid' ], 'post_' . $post_type );
					$js           = array();
					if ( !empty( $all_taxs ) ) {
						foreach ( $all_taxs as $tax ) {
							$tax_detail = get_taxonomy ( $tax );
							$terms      = get_the_terms ( $translations[ $source_lang ]->element_id, $tax );
							$term_names = array();
							if ( $terms ) {
								foreach ( $terms as $term ) {
									if ( $tax_detail->hierarchical ) {
										$term_id =  in_array ( $tax, $translatable_taxs )
											? icl_object_id ( $term->term_id, $tax, false ) : $term->term_id;
										$js[ ] = "jQuery('#in-" . $tax . "-" . $term_id . "').attr('checked', 'checked');";
									} else {
										if ( in_array ( $tax, $translatable_taxs ) ) {
											$term_id = icl_object_id ( $term->term_id, $tax, false );
											if ( $term_id ) {
												$term          = get_term( $term_id, $tax );
												$term_names[ ] = esc_js ( $term->name );
											}
										} else {
											$term_names[ ] = esc_js ( $term->name );
										}
									}
								}
							}

							if ( $term_names ) {
								$js[ ] = "jQuery('#{$tax} .taghint').css('visibility','hidden');";
								$js[ ] = "jQuery('#new-tag-{$tax}').val('" . join ( ', ', $term_names ) . "');";
							}
						}
					}

					if ( $js ) {
						echo '<script type="text/javascript">';
						echo PHP_EOL . '// <![CDATA[' . PHP_EOL;
						echo 'addLoadEvent(function(){' . PHP_EOL;
						echo join ( PHP_EOL, $js );
						echo PHP_EOL . 'jQuery().ready(function() {
								jQuery(".tagadd").click();
								jQuery(\'html, body\').prop({scrollTop:0});
								jQuery(\'#title\').focus();
								});' . PHP_EOL;
						echo PHP_EOL . '});' . PHP_EOL;
						echo PHP_EOL . '// ]]>' . PHP_EOL;
						echo '</script>';
					}
				}

				// sync custom fields
				$tm_settings               = $sitepress->get_setting ( 'translation-management' );
				$custom_fields_translation = !empty( $tm_settings[ 'custom_fields_translation' ] )
					? (array)$tm_settings[ 'custom_fields_translation' ] : array();
				foreach ( $custom_fields_translation as $key => $sync_opt ) {
					if ( $sync_opt == 1 ) {
						$copied_cf[ ] = $key;
					}
				}
				if ( !empty( $copied_cf ) ) {
					$source_lang     = $source_lang ? $source_lang : $default_language;
					$lang_details    = $sitepress->get_language_details ( $source_lang );
					$original_custom = get_post_custom ( $translations[ $source_lang ]->element_id );
					$copied_cf       = array_intersect ( $copied_cf, array_keys ( $original_custom ) );
					$copied_cf       = apply_filters (
						'icl_custom_fields_to_be_copied',
						$copied_cf,
						$translations[ $source_lang ]->element_id
					);
					$user_preferences = $sitepress->get_user_preferences();
					if ( $copied_cf && ( !isset($user_preferences[ 'notices' ][ 'hide_custom_fields_copy' ]) || !$user_preferences[ 'notices' ][ 'hide_custom_fields_copy' ] ) ) {
						$ccf_note = '<img src="' . ICL_PLUGIN_URL . '/res/img/alert.png" alt="Notice" width="16" height="16" style="margin-right:8px" />';
						$ccf_note .= '<a class="icl_user_notice_hide" href="#hide_custom_fields_copy" style="float:right;margin-left:20px;">' . __ (
								'Never show this.',
								'sitepress'
							) . '</a>';
						$ccf_note .= wp_nonce_field ( 'save_user_preferences_nonce', '_icl_nonce_sup', false, false );
						$ccf_note .= sprintf (
							__ ( 'WPML will copy %s from %s when you save this post.', 'sitepress' ),
							'<i><strong>' . join ( '</strong>, <strong>', $copied_cf ) . '</strong></i>',
							$lang_details[ 'display_name' ]
						);
						$sitepress->admin_notices ( $ccf_note, 'error' );
					}
				}
			}
			?>
			<?php if ( !empty( $is_sticky ) && $sitepress->get_setting( 'sync_sticky_flag' ) ): ?>
				<script type="text/javascript">
					addLoadEvent(
						function () {
							jQuery('#sticky').attr('checked', 'checked');
							var post_visibility_display = jQuery('#post-visibility-display');
							post_visibility_display.html(post_visibility_display.html() + ', <?php echo icl_js_escape(__('Sticky', 'sitepress')) ?>');
						});
				</script>
			<?php endif; ?>
		<?php
		}
		if ( 'page-new.php' == $pagenow || ( 'post-new.php' == $pagenow && isset( $_GET[ 'post_type' ] ) ) ) {
			if ( $trid && ( $sitepress->get_setting('sync_page_template' ) || $sitepress->get_setting('sync_page_ordering' ) ) ) {

				$menu_order = $sitepress->get_setting('sync_page_ordering' )
					? $wpml_post_translations->get_original_menu_order($trid, $source_lang)
					: null;

				$page_template = $sitepress->get_setting('sync_page_template' )
					? get_post_meta ( $wpml_post_translations->get_original_post_ID( $trid, $source_lang ),
					                  '_wp_page_template', true )
					: null;

				if ( $menu_order || $page_template ) {
					?>
					<script type="text/javascript">addLoadEvent(function () { <?php
						if($menu_order){ ?>
							jQuery('#menu_order').val(<?php echo $menu_order ?>);
							<?php }
					if($page_template && 'default' != $page_template){ ?>
							jQuery('#page_template').val('<?php echo $page_template ?>');
							<?php }
					?>
						});</script><?php
				}
			}
		}

	// sync post dates
		if ( icl_is_post_edit () ) {
			// @since 3.1.5
			// Enqueing 'wp-jquery-ui-dialog', just in case it doesn't get automatically enqueued
			wp_enqueue_style ( 'wp-jquery-ui-dialog' );
			wp_enqueue_style (
				'sitepress-post-edit',
				ICL_PLUGIN_URL . '/res/css/post-edit.css',
				array(),
				ICL_SITEPRESS_VERSION
			);
			wp_enqueue_script (
				'sitepress-post-edit',
				ICL_PLUGIN_URL . '/res/js/post-edit.js',
				array( 'jquery-ui-dialog', 'jquery-ui-autocomplete', 'autosave' ),
				ICL_SITEPRESS_VERSION
			);

			if ( $sitepress->get_setting ( 'sync_post_date' ) ) {
				if ( !$trid ) {
					$post_id = filter_input ( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
					$trid    = $post_id !== null ? $wpml_post_translations->get_element_trid ( $post_id ) : null;
				}
				if ( $trid ) {
					$original_date = $wpml_post_translations->get_original_post_date ( $trid, $source_lang );
					$exp           = explode ( ' ', $original_date );
					list( $aa, $mm, $jj ) = explode ( '-', $exp[ 0 ] );
					list( $hh, $mn, $ss ) = explode ( ':', $exp[ 1 ] );
					?>
					<script type="text/javascript">
						addLoadEvent(
							function () {
								jQuery('#aa').val('<?php echo $aa ?>').attr('readonly', 'readonly');
								jQuery('#mm').val('<?php echo $mm ?>').attr('readonly', 'readonly');
								jQuery('#jj').val('<?php echo $jj ?>').attr('readonly', 'readonly');
								jQuery('#hh').val('<?php echo $hh ?>').attr('readonly', 'readonly');
								jQuery('#mn').val('<?php echo $mn ?>').attr('readonly', 'readonly');
								jQuery('#ss').val('<?php echo $ss ?>').attr('readonly', 'readonly');
								var timestamp = jQuery('#timestamp');
								timestamp.find('b').append(( '<span> <?php esc_html_e('Copied From the Original', 'sitepress') ?></span>'));
								timestamp.next().html('<span style="margin-left:1em;"><?php esc_html_e('Edit', 'sitepress') ?></span>');
							});
					</script>
				<?php
				}
			}
		}

		if ( 'post-new.php' === $pagenow && isset( $_GET[ 'trid' ] ) && $sitepress->get_setting( 'sync_post_format' ) && function_exists (
				'get_post_format'
			)
		) {
			$format = $wpml_post_translations->get_original_post_format($trid, $source_lang);
			?>
			<script type="text/javascript">
				addLoadEvent(function () {
					jQuery('#post-format-' + '<?php echo $format ?>').attr('checked', 'checked');
				});
			</script><?php
		}

		wp_enqueue_script ( 'theme-preview' );

		if ( 'languages' === $page_basename || 'string-translation' === $page_basename ) {
			wp_enqueue_script ( 'wp-color-picker' );
			wp_register_style (
				'wpml-color-picker',
				ICL_PLUGIN_URL . '/res/css/colorpicker.css',
				array( 'wp-color-picker' ),
				ICL_SITEPRESS_VERSION
			);
			wp_enqueue_style ( 'wpml-color-picker' );
			wp_enqueue_script ( 'jquery-ui-sortable' );
		}
	}

	private function print_reading_options_js(){
		list( $warn_home, $warn_posts ) = $this->verify_home_and_blog_pages_translations ();
		if ( $warn_home || $warn_posts ) {
			?>
			<script type="text/javascript">
				addLoadEvent(function () {
					jQuery('input[name="show_on_front"]').parent().parent().parent().parent().append('<?php echo str_replace("'","\\'",$warn_home . $warn_posts); ?>');
				});
			</script>
		<?php
		}
	}

	function wpml_css_setup() {
		if ( isset( $_GET[ 'page' ] ) ) {
			$page          = basename( $_GET[ 'page' ] );
			$page_basename = str_replace( '.php', '', $page );
			$page_basename = preg_replace('/[^\w-]/', '', $page_basename);
		}
		wp_enqueue_style( 'sitepress-style', ICL_PLUGIN_URL . '/res/css/style.css', array(), ICL_SITEPRESS_VERSION );
		if ( isset( $page_basename ) && file_exists( ICL_PLUGIN_PATH . '/res/css/' . $page_basename . '.css' ) ) {
			wp_enqueue_style( 'sitepress-' . $page_basename, ICL_PLUGIN_URL . '/res/css/' . $page_basename . '.css', array(), ICL_SITEPRESS_VERSION );
		}

		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style( 'translate-taxonomy', ICL_PLUGIN_URL . '/res/css/taxonomy-translation.css', array(), ICL_SITEPRESS_VERSION );

	}

	private function verify_home_and_blog_pages_translations() {
		$warn_home     = $warn_posts = '';
		$page_on_front = get_option ( 'page_on_front' );
		if ( 'page' === get_option ( 'show_on_front' ) && $page_on_front ) {
			$warn_home = $this->missing_page_warning (
				$page_on_front,
				__ ( 'Your home page does not exist or its translation is not published in %s.', 'sitepress' )
			);
		}
		$page_for_posts = get_option ( 'page_for_posts' );
		if ( $page_for_posts ) {
			$warn_posts = $this->missing_page_warning (
				$page_for_posts,
				__ ( 'Your blog page does not exist or its translation is not published in %s.', 'sitepress' ),
				'margin-top:4px;'
			);
		}

		return array( $warn_home, $warn_posts );
	}

	private function missing_page_warning( $original_page_id, $label, $additional_css = '' ) {
		global $wpml_post_translations, $sitepress;

		$warn_posts = '';
		if ( $original_page_id ) {
			$page_posts_translations = $wpml_post_translations->get_element_translations (
				$original_page_id,
				false,
				false
			);
			$missing_posts           = array();
			$active_languages        = $sitepress->get_active_languages ();
			foreach ( $active_languages as $lang ) {
				if ( !isset( $page_posts_translations[ $lang[ 'code' ] ] )
				     || get_post_status ( $page_posts_translations[ $lang[ 'code' ] ] ) !== 'publish'
				) {
					$missing_posts[ ] = $lang[ 'display_name' ];
				}
			}

			$warn_posts = '';
			if ( !empty( $missing_posts ) ) {
				$warn_posts = '<div class="icl_form_errors" style="font-weight:bold;' . $additional_css . '">';
				$warn_posts .= sprintf ( $label, join ( ', ', $missing_posts ) );
				$warn_posts .= '<br />';
				$warn_posts .= '<a href="' . get_edit_post_link ( $original_page_id ) . '">' . __ (
						'Edit this page to add translations',
						'sitepress'
					) . '</a>';
				$warn_posts .= '</div>';
			}
		}
		return $warn_posts;
	}
}