<?php

abstract class WPML_Translation_Job {
	protected $basic_data;
	protected $element_id = - 1;
	protected $status     = - 1;
	protected $job_id;
	protected $batch_id;

	public function __construct( $job_id, $batch_id = null ) {
		$this->job_id   = $job_id;
		$this->batch_id = $batch_id ? $batch_id : TranslationProxy_Batch::update_translation_batch();
	}

	public abstract function get_type();

	public abstract function get_original_element_id();

	public abstract function to_array();

	protected abstract function load_status();

	protected abstract function load_job_data( $id );

	public function get_status() {
		if ( $this->status == - 1 ) {
			$this->status = $this->load_status();
		}

		return $this->status;
	}

	public function get_id() {
		return $this->job_id;
	}

	protected abstract function load_resultant_element_id();

	public function get_resultant_element_id() {
		if ( $this->element_id == - 1 ) {
			$this->element_id = $this->load_resultant_element_id();
		}

		return $this->element_id;
	}

	public function get_batch_id() {
		if ( ! isset( $this->batch_id ) ) {
			$this->load_batch_id();
		}

		return $this->batch_id;
	}

	public function get_language_code() {
		$this->maybe_load_basic_data();

		return $this->basic_data->language_code;
	}

	public function get_translator_name() {
		$this->maybe_load_basic_data();
		if ( $this->basic_data->translation_service == TranslationProxy::get_current_service_id() ) {
			$this->basic_data->translator_name = TranslationProxy_Translator::get_translator_name( $this->basic_data->translator_id );
		} else {
			$this->basic_data->translator_name = false;
		}

		return $this->basic_data->translator_name;
	}

	protected function basic_data_to_array( $job_data ) {
		$this->maybe_load_basic_data();
		$data_array = (array) $job_data;
		if ( isset( $data_array[ 'post_title' ] ) ) {
			$data_array[ 'post_title' ] = esc_html( $data_array[ 'post_title' ] );
		}
		$data_array[ 'translator_name' ]      = $this->get_translator_name();
		$data_array[ 'batch_id' ]             = $job_data->batch_id;
		$data_array[ 'source_language_code' ] = $this->basic_data->source_language_code;
		$data_array[ 'language_code' ]        = $this->basic_data->language_code;
		$data_array[ 'translator_html' ]      = $this->get_translator_html( $this->basic_data );
		$data_array[ 'type' ]                 = $this->get_type();
		$data_array[ 'lang_text' ]            = $this->generate_lang_text();

		return $data_array;
	}

	protected function maybe_load_basic_data() {
		if ( ! $this->basic_data ) {
			$this->basic_data = $this->load_job_data( $this->job_id );
		}
	}

	protected function get_translator_html( $job ) {

		if ( is_array( $job ) ) {
			$job = (object) $job;
		}

		$current_service_name = TranslationProxy::get_current_service_name();
		$translation_services = array( 'local', TranslationProxy::get_current_service_id() );

		$translator = '';

		if ( $job->translation_service && $job->translation_service !== 'local' ) {
			try {
				$project = TranslationProxy::get_current_project();
				if ( $project ) {
					if ( $project->service->has_translator_selection ) {
						$translator_contact_iframe_url = $project->translator_contact_iframe_url( $job->translator_id );
						$iframe_args                   = array(
							'title'     => __( 'Contact the translator', 'wpml-translation-management' ),
							'unload_cb' => 'icl_thickbox_refresh'
						);
						$translator .= TranslationProxy_Popup::get_link( $translator_contact_iframe_url, $iframe_args );
						$translator .= esc_html( $job->translator_name );
						$translator .= "</a> (" . $current_service_name . ")";
					} else {
						$translator .= $current_service_name;
					}
				} else {
					$translator .= esc_html( $job->translator_name );
				}
			} catch ( Exception $e ) {
				// Just doesn't create the output
			}
		} elseif ( $job->status == ICL_TM_COMPLETE ) {
			$translator_data = get_userdata( $job->translator_id );
			$translator_name = $translator_data ? $translator_data->display_name : "";
			$translator      = '<span class="icl-finished-local-name">' . $translator_name . '</span>';
		} else {
			$translator .= '<span class="icl_tj_select_translator">';
			$selected_translator = isset( $job->translator_id ) ? $job->translator_id : false;
			$disabled            = false;
			if ( $job->translation_service && $job->translation_service !== 'local' && is_numeric(
					$job->translation_service
				)
			) {
				$selected_translator = TranslationProxy_Service::get_wpml_translator_id(
					$job->translation_service,
					$job->translator_id
				);
				$disabled            = true;
			}

			$job_id     = isset( $job->job_id ) ? $job->job_id : $job->id;
			$local_only = isset( $job->local_only ) ? $job->local_only : true;
			$args       = array(
				'id'         => 'icl_tj_translator_for_' . $job_id,
				'name'       => 'icl_tj_translator_for_' . ( $job_id ),
				'from'       => $job->source_language_code,
				'to'         => $job->language_code,
				'selected'   => $selected_translator,
				'services'   => $translation_services,
				'disabled'   => $disabled,
				'echo'       => false,
				'local_only' => $local_only
			);
			$translator .= TranslationManagement::translators_dropdown( $args );
			$translator .= '<input type="hidden" id="icl_tj_ov_' . $job_id . '" value="' . @intval(
					$job->translator_id
				) . '" />';
			$translator .= '<span class="icl_tj_select_translator_controls" id="icl_tj_tc_' . ( $job_id ) . '">';
			$translator .= '<input type="button" class="button-secondary icl_tj_ok" value="' . __(
					'Send',
					'wpml-translation-management'
				) . '" />&nbsp;';
			$translator .= '<input type="button" class="button-secondary icl_tj_cancel" value="' . __(
					'Cancel',
					'wpml-translation-management'
				) . '" />';
			$translator .= '</span>';

		}

		return $translator;
	}

	protected function load_batch_id() {
		global $wpdb;

		list( $table, $col ) = $this->get_batch_id_table_col();
		$this->batch_id = $wpdb->get_var( $wpdb->prepare( " SELECT batch_id
															FROM {$table}
															WHERE {$col} = %d
															LIMIT 1",
														  $this->job_id ) );
	}

	protected abstract function get_batch_id_table_col();

	protected function generate_lang_text() {
		global $sitepress;
		$this->maybe_load_basic_data();
		$lang_from = $sitepress->get_language_details( $this->basic_data->source_language_code );
		$lang_to   = $sitepress->get_language_details( $this->basic_data->language_code );
		$lang_text = $lang_from[ 'display_name' ] . ' &raquo; ' . $lang_to[ 'display_name' ];

		return $lang_text;
	}
}
