<?php

require_once WPML_TM_PATH . '/inc/translation-jobs/jobs/wpml-translation-job.class.php';

class WPML_Post_Translation_Job extends WPML_Translation_Job {

	private $original_doc_id = false;

	public function get_type() {
		return 'Post';
	}

	protected function load_resultant_element_id() {
		global $wpdb;
		$this->maybe_load_basic_data();

		return $wpdb->get_var( $wpdb->prepare( "SELECT element_id
												FROM {$wpdb->prefix}icl_translations
												WHERE translation_id = %d
												LIMIT 1",
											   $this->basic_data->translation_id ) );
	}

	protected function load_job_data( $job_id ) {
		global $wpml_translation_job_factory;

		return $wpml_translation_job_factory->get_translation_job( $job_id );
	}

	public function to_array() {
		$data_array                      = $this->basic_data_to_array( $this->basic_data );
		$data_array[ 'id' ]              = $this->basic_data->job_id;
		$data_array[ 'translation_id' ]  = $this->basic_data->translation_id;
		$data_array[ 'status' ]          = $this->get_status();
		$data_array[ 'translated_link' ] = $this->get_edit_link( true );
		$data_array[ 'edit_link' ]       = $this->get_edit_link();
		$data_array[ 'post_title' ]      = get_post_field( 'post_title', $this->get_original_element_id() );

		return $data_array;
	}

	protected function load_status() {
		$this->maybe_load_basic_data();

		return TranslationManagement::get_job_status_string( $this->basic_data->status,
															 $this->basic_data->needs_update );
	}

	public function get_original_element_id() {
		global $wpdb;
		if ( ! $this->original_doc_id ) {
			$original_element_id_query   = "SELECT field_data FROM {$wpdb->prefix}icl_translate WHERE job_id = %d AND field_type='original_id'";
			$original_element_id_args    = array( $this->get_id() );
			$original_element_id_prepare = $wpdb->prepare( $original_element_id_query, $original_element_id_args );
			$original_element_id         = $wpdb->get_var( $original_element_id_prepare );
			$this->original_doc_id       = $original_element_id;
		} else {
			$original_element_id = $this->original_doc_id;
		}

		return $original_element_id;
	}

	public function get_id() {

		return $this->job_id;
	}

	protected function get_batch_id_table_col() {
		global $wpdb;

		$table = $wpdb->prefix . 'icl_translation_status';

		return array(
			$table,
			"(SELECT job_id FROM {$wpdb->prefix}icl_translate_job trans_job WHERE trans_job.rid = {$table}.rid LIMIT 1)"
		);
	}

	private function get_edit_link( $original = false ) {
		$edit_link = get_edit_post_link( ( $original || $this->get_status() === 'Complete' )
											 ? $this->get_resultant_element_id()
											 : $this->get_original_element_id() );

		return $edit_link;
	}
}
