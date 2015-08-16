<?php

//@todo [WPML 3.3] Move the class to a new file. Also, this file may become quite useless once the class is moved out.
class WPML_Translation_Editor_UI {
	private $all_translations;
	private $all_translations_finished;
	private $current_element;
	private $current_element_content_original;
	private $current_element_content_translated;
	private $current_element_field_style;
	private $current_element_field_type;
	/**
	 * @var WP_User
	 */
	private $current_user;
	/**
	 * @var WPML_Translation_Editor
	 */
	private $editor_object;
	private $is_valid_job;
	private $job;
	private $job_id;
	private $job_language_pairs;
	private $original_post;
	private $rtl_original;
	private $rtl_original_attribute_object;
	private $rtl_translation;
	private $rtl_translation_attribute;
	/**
	 * @var TranslationManagement
	 */
	private $sitepressTM;
	/**
	 * @var SitePress
	 */
	private $sitepress;
	private $translator_has_job_language_pairs;
	private $user_can_take_this_job;

	/**
	 * @param SitePress             $sitepress
	 * @param TranslationManagement $iclTranslationManagement
	 * @param WP_User               $current_user
	 */
	function __construct( $sitepress, $iclTranslationManagement, $current_user ) {
		$this->sitepress             = $sitepress;
		$this->sitepressTM           = $iclTranslationManagement;
		$this->current_user          = $current_user;
		$this->current_element_index = 0;

		require WPML_TM_PATH . '/menu/wpml-translation-editor.class.php';
	}

	function render() {
		$this->init_job();
		if ( $this->is_valid_job ) {
			$this->render_translations_queue();
		} else {
			$this->render_editor();
		}
	}

	private function init_job() {
		$this->job                               = false;
		$this->translator_has_job_language_pairs = false;
		$this->user_can_take_this_job            = false;

		$this->job_id = WPML_Translation_Editor::get_job_id_from_request();

		if ( $this->job_id ) {
			WPML_Translation_Job_Terms::set_translated_term_values( $this->job_id );
			$this->job = $this->sitepressTM->get_translation_job( $this->job_id, false, true, 1 );

			if ( $this->job ) {
				$this->user_can_take_this_job            = $this->job->translator_id == 0 || ( $this->job->translator_id == $this->current_user->ID );
				$this->job_language_pairs                = array(
					'lang_from' => $this->job->source_language_code,
					'lang_to'   => $this->job->language_code
				);
				$this->translator_has_job_language_pairs = $this->sitepressTM->is_translator( $this->current_user->ID, $this->job_language_pairs );
			}
		}

		$this->is_valid_job = ! $this->job || ( ( ! $this->user_can_take_this_job || ! $this->translator_has_job_language_pairs ) && ! current_user_can( 'manage_options' ) );
	}

	private function render_editor() {
		list( $this->rtl_original, $this->rtl_translation ) = $this->init_rtl_settings();

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		?>
		<div class="wrap icl-translation-editor">
			<div id="icon-wpml" class="icon32"><br/></div>
			<h2><?php echo __( 'Translation editor', 'wpml-translation-management' ) ?></h2>

			<?php
			do_action( 'icl_tm_messages' );
			$this->init_original_post();
			$this->init_editor_object();
			$this->render_editor_header();
			$this->render_translator_note();
			?>

			<form id="icl_tm_editor" method="post" action="">
				<input type="hidden" name="icl_tm_action" value="save_translation"/>
				<input type="hidden" name="job_id" value="<?php echo esc_attr( $this->job_id ) ?>"/>
				<?php
				$this->render_elements_content();
				?>
				<br clear="all"/>
				<label><input type="checkbox" name="complete" <?php if ( ! $this->all_translations_finished): ?>disabled="disabled"<?php endif; ?> <?php
					if ( $this->job->translated ):?> checked="checked"<?php endif; ?> value="1"/>&nbsp;<?php
					_e( 'Translation of this document is complete', 'wpml-translation-management' ) ?></label>

				<div id="icl_tm_validation_error" class="icl_error_text"><?php _e( 'Please review the document translation and fill in all the required fields.', 'wpml-translation-management' ) ?></div>
				<p class="submit-buttons">
					<input type="submit" class="button-primary" value="<?php _e( 'Save translation', 'wpml-translation-management' ) ?>"/>&nbsp;
					<?php
					if ( isset( $_POST[ 'complete' ] ) && $_POST[ 'complete' ] ) {
						$cancel_txt = __( 'Jobs queue', 'wpml-translation-management' );
					} else {
						$cancel_txt = __( 'Cancel', 'wpml-translation-management' );
					}
					?>
					<a class="button-secondary" href="<?php echo admin_url( 'admin.php?page=' . WPML_TM_FOLDER . '/menu/translations-queue.php' ) ?>"><?php echo $cancel_txt; ?></a>
					<input type="submit"
					       id="icl_tm_resign"
					       class="button-secondary"
					       value="<?php _e( 'Resign', 'wpml-translation-management' ) ?>"
					       onclick="if(confirm('<?php echo esc_js( __( 'Are you sure you want to resign from this job?', 'wpml-translation-management' ) ) ?>')) {jQuery(this).next().val(1);}else {return false;}"/><input type="hidden"
					                                                                                                                                                                                                        name="resign"
					                                                                                                                                                                                                        value="0"/>
				</p>
				<?php do_action( 'edit_form_advanced' ); ?>
				<?php wp_nonce_field( 'icl_get_job_original_field_content_nonce', 'icl_get_job_original_field_content_nonce' ) ?>
			</form>

		</div>
	<?php
	}

	private function render_translations_queue() {
		//@todo [WPML 3.3] Make it an object, pass `$job_checked` to the constructor
		$job_checked = true;
		include WPML_TM_PATH . '/menu/translations-queue.php';
	}

	private function is_external_element() {
		static $is_external_element;
		if ( ! isset( $is_external_element ) ) {
			$is_external_element = $this->sitepressTM->is_external_type( $this->job->element_type_prefix );
		}

		return $is_external_element;
	}

	private function init_original_post() {
		// we do not need the original document of the job here
		// but the document with the same trid and in the $this->job->source_language_code
		$this->all_translations = $this->sitepress->get_element_translations( $this->job->trid, $this->job->original_post_type );
		$this->original_post    = false;
		foreach ( $this->all_translations as $t ) {
			if ( $t->language_code === $this->job->source_language_code ) {
				$this->original_post = $this->sitepressTM->get_post( $t->element_id, $this->job->element_type_prefix );
				//if this fails for some reason use the original doc from which the trid originated
				$this->original_post = $this->original_post ? $this->original_post : $this->sitepressTM->get_post( $this->job->original_doc_id, $this->job->element_type_prefix );
				break;
			}
		}
	}

	private function init_editor_object() {
		$this->editor_object = new WPML_Translation_Editor( $this->job->to_language, $this->job->from_language, $this->rtl_original_attribute_object, $this->rtl_translation_attribute );
	}

	/**
	 * @return array
	 */
	private function init_rtl_settings() {
		$this->rtl_original                  = $this->sitepress->is_rtl( $this->job->source_language_code );
		$this->rtl_translation               = $this->sitepress->is_rtl( $this->job->language_code );
		$this->rtl_original_attribute_object = $this->rtl_original ? ' dir="rtl"' : ' dir="ltr"';
		$this->rtl_translation_attribute     = $this->rtl_translation ? ' dir="rtl"' : ' dir="ltr"';

		return array( $this->rtl_original, $this->rtl_translation );
	}

	private function render_editor_header() {
		?>
		<p class="updated fade">
			<?php
			$tm_post_link = TranslationManagement::tm_post_link( $this->original_post->ID );
			if ( $this->is_external_element() ) {
				$tm_post_link = apply_filters( 'wpml_external_item_link', $tm_post_link, $this->job->original_doc_id, false );
			}
			printf( __( 'You are translating %s from %s to %s.', 'wpml-translation-management' ), $tm_post_link, $this->job->from_language, $this->job->to_language );
			echo '<p>' . $this->editor_object->render_copy_from_original_link() . '</p>';
			?>
		</p>

	<?php
	}

	private function render_translator_note() {
		if ( $translators_note = get_post_meta( $this->original_post->ID, '_icl_translator_note', true ) ) {
			?>
			<i><?php _e( 'Note for translator', 'wpml-translation-management' ); ?></i>
			<br/>
			<div class="icl_cyan_box">
				<?php echo $translators_note ?>
			</div>
		<?php
		}
	}

	private function is_current_element_a_term() {
		return preg_match( '/^t_/', $this->current_element->field_type );
	}

	/**
	 * @param $current_element
	 *
	 * @return mixed
	 */
	private function set_current_element( $current_element ) {
		$this->current_element_index = ( ! isset( $this->current_element_index ) || $this->current_element_index < 1 ) ? 1 : $this->current_element_index + 1;

		return $this->current_element = $current_element;
	}

	/**
	 * @return bool
	 */
	private function is_current_element_a_custom_field() {
		return ( 0 === strpos( $this->current_element->field_type, 'field-' ) );
	}

	/**
	 *
	 */
	private function render_translation_element() {
		?>
		<div class="metabox-holder" id="icl-translation-job-elements-<?php echo $this->current_element_index ?>">
			<div class="postbox-container icl-tj-postbox-container-<?php echo $this->current_element->field_type ?>">
				<div class="meta-box-sortables ui-sortable" id="icl-translation-job-sortables-<?php echo $this->current_element_index ?>">
					<div class="postbox" id="icl-translation-job-element-<?php echo $this->current_element_index ?>">
						<?php echo

						$this->editor_object->get_click_to_toggle_html();
						$this->init_current_field_attributes();

						echo $this->editor_object->get_post_box_header( $this->current_element_field_type );
						?>
						<div class="inside">
							<?php
							$this->render_current_field_description();

							$this->init_current_element_content();

							echo $this->editor_object->get_translated_content_paragraph( $this->current_element->field_type, true );

							$this->render_current_element_field();

							$multiple = ( $this->current_element->field_format == 'csv_base64' );
							echo $this->editor_object->get_finished_checkbox_html( $this->current_element->field_finished, $multiple, $this->current_element->field_type );
							?>
							<br/>
							<?php
							echo $this->editor_object->get_original_content_paragraph();

							$this->render_current_element_field_original_content();
							?>
							<input type="hidden" name="fields[<?php echo esc_attr( $this->current_element->field_type ) ?>][format]" value="<?php echo $this->current_element->field_format ?>"/>
							<input type="hidden" name="fields[<?php echo esc_attr( $this->current_element->field_type ) ?>][tid]" value="<?php echo $this->current_element->tid ?>"/>
							<?php
							$this->render_current_element_diff();
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	private function init_current_field_attributes() {
		// allow custom field names to be filtered
		if ( $this->is_external_element() ) {
			// Get human readable string Title and editor style from the WPML string package.
			$this->current_element_field_type  = apply_filters( 'wpml_tm_editor_string_name', $this->current_element->field_type, $this->original_post );
			$this->current_element_field_style = $this->current_element->field_type;
			$this->current_element_field_style = apply_filters( 'wpml_tm_editor_string_style', $this->current_element_field_style, $this->current_element->field_type, $this->original_post );
		} else if ( $this->is_current_element_a_custom_field() ) {
			$custom_field_data                 = $this->editor_object->custom_field_data( $this->current_element, $this->job );
			$this->current_element_field_type  = $custom_field_data[ 0 ];
			$this->current_element_field_style = $custom_field_data[ 1 ];
			$this->current_element             = $custom_field_data[ 2 ];
		} else {
			$this->current_element_field_type  = $this->current_element->field_type;
			$this->current_element_field_style = false;
		}
	}

	private function render_current_field_description() {
		if ( $this->is_current_element_a_custom_field() ) {
			$icl_editor_cf_description = apply_filters( 'icl_editor_cf_description', '', $this->current_element->field_type );
			if ( $icl_editor_cf_description !== null ) {
				echo '<p class="icl_tm_field_description">' . $icl_editor_cf_description . '</p>';
			}
		}
	}

	private function init_current_element_content() {
		$this->current_element_content_original   = TranslationManagement::decode_field_data( $this->current_element->field_data, $this->current_element->field_format );
		$this->current_element_content_translated = TranslationManagement::decode_field_data( $this->current_element->field_data_translated, $this->current_element->field_format );
		if(!$this->current_element_content_translated) {
			$post_fields                    = isset($_POST[ 'fields' ]) ? $_POST[ 'fields' ] : null;
			$current_element_in_post_fields = $post_fields ? $post_fields[ $this->current_element->field_type ] : null;
			if ( $current_element_in_post_fields && $current_element_in_post_fields[ 'tid' ] == $this->current_element->tid ) {
				$this->current_element_content_translated = $current_element_in_post_fields[ 'data' ];
			}
		}
	}

	private function render_current_element_field_body() {

		?>
		<div id="poststuff">
			<?php
			$settings = array(
				'media_buttons' => false,
				'textarea_name' => 'fields[' . strtolower( $this->current_element->field_type ) . '][data]',
				'textarea_rows' => 20,
				'editor_css'    => $this->rtl_translation ? ' <style type="text/css">.wp-editor-container textarea.wp-editor-area{direction:rtl;}</style>' : ''
			);
			$this->render_wp_editor( $settings );
			?>
		</div>

	<?php
	}

	private function render_current_element_field_csv_base64() {
		foreach ( $this->current_element_content_original as $k => $c ) {
			?>
			<input title="<?php echo sanitize_title( $this->current_element->field_type ) ?>"
			       id="<?php echo sanitize_title( $this->current_element->field_type ) ?>"
			       class="icl_multiple"
			       type="text"
			       name="fields[<?php echo esc_attr( $this->current_element->field_type )
			       ?>][data][<?php echo $k ?>]"
			       value="<?php if ( isset( $this->current_element_content_translated[ $k ] ) ) {
				       echo esc_attr( $this->current_element_content_translated[ $k ] );
			       } ?>"<?php echo $this->rtl_translation_attribute; ?> />
		<?php
		}
	}

	private function render_current_element_field_textarea() {
		?>
		<textarea title="<?php echo sanitize_title( $this->current_element->field_type ) ?>"
		          id="<?php echo sanitize_title( $this->current_element->field_type ) ?>"
		          style="width:100%;"
		          rows="4"
		          name="fields[<?php echo esc_attr( $this->current_element->field_type ) ?>][data]"<?php
		echo $this->rtl_translation_attribute; ?>><?php echo esc_html( $this->current_element_content_translated ); ?></textarea>

	<?php
	}

	private function render_current_element_field_wysiwyg() {
		$settings = array(
			'media_buttons' => false,
			'textarea_name' => 'fields[' . strtolower( $this->current_element->field_type ) . '][data]',
			'textarea_rows' => 4
		);
		$this->render_wp_editor( $settings );
	}

	private function render_current_element_field_single_line() {
		?>
		<input title="<?php echo sanitize_title( $this->current_element->field_type ) ?>"
		       id="<?php echo sanitize_title( $this->current_element->field_type ) ?>"
		       type="text"
		       name="fields[<?php echo esc_attr( $this->current_element->field_type ) ?>][data]"
		       value="<?php
		       echo esc_attr( $this->current_element_content_translated ); ?>"<?php echo $this->rtl_translation_attribute; ?> />
	<?php
	}

	private function render_wp_editor( $settings ) {
		wp_editor( $this->current_element_content_translated, $this->current_element->field_type, $settings );
	}

	private function render_current_element_field_original_body() {
		?>
		<div class="icl_single visual"<?php echo $this->rtl_original_attribute_object; ?>>
			<?php
			$settings = array(
				'media_buttons' => false,
				'textarea_name' => 'fields[' . strtolower( $this->current_element->field_type ) . '][original]',
				'textarea_rows' => 20,
				'editor_css'    => $this->rtl_translation ? ' <style type="text/css">.wp-editor-container textarea.wp-editor-area{direction:rtl;}</style>' : ''
			);
			wp_editor( $this->current_element_content_original, 'original_' . strtolower( $this->current_element->field_type ), $settings );
			?>
			<br clear="all"/></div>
	<?php
	}

	private function render_current_element_field_original_csv_base64() {
		foreach ( $this->current_element_content_original as $c ) {
			?>
			<div class="icl_multiple"<?php echo $this->rtl_original_attribute_object; ?>>
				<div style="float: left;margin-right:4px;"><?php echo $c ?></div>
				<?php if ( isset( $term_descriptions[ $c ] ) ) {
					icl_pop_info( $term_descriptions[ $c ], 'info', array( 'icon_size' => 10 ) );
				} ?>
				<br clear="all"/>
			</div>
		<?php
		}
	}

	private function render_current_element_field_original_single_line() {
		?>
		<div class="icl_single"<?php if ( $this->rtl_original ) {
			echo ' dir="rtl" style="text-align:right;"';
		} else {
			echo ' dir="ltr" style="text-align:left;"';
		} ?>><span style="white-space:pre-wrap;" id="icl_tm_original_<?php echo sanitize_title( $this->current_element->field_type ) ?>"><?php echo esc_html( $this->current_element_content_original ) ?></span><br clear="all"/>
		</div>
	<?php
	}

	private function render_current_element_field_original() {
		if ( $this->current_element->field_type == 'body' || $this->current_element_field_style == 2 ) {
			$this->render_current_element_field_original_body();
		} elseif ( $this->current_element->field_format == 'csv_base64' ) {
			$this->render_current_element_field_original_csv_base64();
		} else {
			$this->render_current_element_field_original_single_line();
		}
	}

	private function render_current_element_field() {
		if ( $this->current_element->field_type == 'body' ) {
			$this->render_current_element_field_body();
		} elseif ( $this->current_element->field_format == 'csv_base64' ) {
			$this->render_current_element_field_csv_base64();
			// CASE 3 - multiple lines ***********************
		} elseif ( ( $this->is_current_element_a_custom_field() || $this->original_post ) && $this->current_element_field_style == 1 ) {
			$this->render_current_element_field_textarea();
			// CASE 4 - wysiwyg ***********************
		} elseif ( ( $this->is_current_element_a_custom_field() || $this->original_post ) && $this->current_element_field_style == 2 ) {
			$this->render_current_element_field_wysiwyg();
		} else {
			$this->render_current_element_field_single_line();
		}
	}

	private function render_current_element_field_original_content() {
		?>
		<div class="icl-tj-original<?php echo $this->is_current_element_a_custom_field() ? ' icl-tj-original-cf' : ''; ?>">
			<?php
			$this->render_current_element_field_original();
			?>
		</div>
	<?php
	}

	private function render_current_element_diff() {
		if ( ! $this->current_element->field_finished && ! empty( $this->job->prev_version ) ) {

			$prev_value = '';
			foreach ( $this->job->prev_version->elements as $pel ) {
				if ( $this->current_element->field_type == $pel->field_type ) {
					$prev_value = TranslationManagement::decode_field_data( $pel->field_data, $pel->field_format );
				}
			}
			if ( $this->current_element->field_format != 'csv_base64' ) {
				$diff = wp_text_diff( $prev_value, TranslationManagement::decode_field_data( $this->current_element->field_data, $this->current_element->field_format ) );
			}
			if ( ! empty( $diff ) ) {
				?>
				<div class="wpml_diff_wrapper">
					<p><a href="#" class="wpml_diff_toggle"><?php
							_e( 'Show Changes', 'sitepress' ); ?></a></p>

					<div class="wpml_diff">
						<?php echo $diff ?>
					</div>
				</div>
			<?php
			}
		}
	}

	/**
	 * @param $current_element
	 */
	private function render_element( $current_element ) {
		$this->set_current_element( $current_element );

		if ( $this->current_element->field_data ) {

			if ( $this->is_current_element_a_term() ) {
				$this->editor_object->add_term( $this->current_element );
			} else {
				if ( ! $this->current_element->field_finished ) {
					$this->all_translations_finished = false;
				}
				$this->render_translation_element();
			}
		}
	}

	private function render_elements() {
		foreach ( $this->job->elements as $current_element ) {
			$this->render_element( $current_element );
		}
	}

	private function render_elements_content() {
		?>
		<div id="dashboard-widgets-wrap">
			<?php
			$this->all_translations_finished = true;
			$this->render_elements();
			echo $this->editor_object->render_term_metaboxes();
			?>
		</div>
	<?php
	}
}

global $sitepress, $iclTranslationManagement, $current_user;

$translation_editor_ui = new WPML_Translation_Editor_UI( $sitepress, $iclTranslationManagement, $current_user );
$translation_editor_ui->render();