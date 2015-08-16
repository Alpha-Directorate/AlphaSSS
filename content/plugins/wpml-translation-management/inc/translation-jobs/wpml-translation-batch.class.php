<?php

class WPML_Translation_Batch {

	private $name = false;
	private $id = false;
	private $url = false;
	/** @var WPML_Translation_Job[] $job_objects  */
	private $job_objects = array();

	public function __construct( $batch_id = 0 ) {
		$this->id   = $batch_id > 0 ? $batch_id : $this->retrieve_generic_batch_id();
		$this->name = $batch_id <= 0 ? $this->generate_generic_batch_name() : false;
	}

	public function get_batch_url() {
		if ( $this->url === false ) {
			$this->url = TranslationManagement::get_batch_url( $this->id );
		}

		return $this->url;
	}

	public function get_batch_meta_array() {
		$batch_url  = $this->get_batch_url();
		$batch_name = $batch_url ? $this->get_batch_name() : '';

		return array(
			'batch_url'    => $batch_url,
			'batch_name'   => $batch_name,
			'item_count'   => $this->get_item_count(),
			'last_update'  => $this->get_last_update(),
			'status_array' => $this->get_status_array()
		);
	}

    /**
     * Cancels all remote translation jobs in this batch
     */
    public function cancel_all_remote_jobs() {
        /** @var wpdb $wpdb */
        /** @var TranslationManagement $iclTranslationManagement */
        global $wpdb, $iclTranslationManagement, $WPML_String_Translation;

        $translation_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT translation_id
                 FROM {$wpdb->prefix}icl_translation_status
                 WHERE batch_id = %d
                  AND translation_service <> 'local' ",
                $this->id
            )
        );
        foreach ( $translation_ids as $translation_id ) {
            $iclTranslationManagement->cancel_translation_request( $translation_id );
        }

        $string_translation_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}icl_string_translations WHERE batch_id = %d
                  AND translation_service <> 'local' ",
                $this->id
            )
        );
        foreach ( $string_translation_ids as $st_trans_id ) {
            $rid = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT MAX(rid)
                     FROM {$wpdb->prefix}icl_string_status
                     WHERE string_translation_id = %d",
                    $st_trans_id
                )
            );
            if ( $rid ) {
                $WPML_String_Translation->cancel_remote_translation( $rid );
            }
        }
    }

	//todo: [WPML 3.2.1] This method and other similar methods can likely be removed
	public function get_last_update() {
		return TranslationManagement::get_batch_last_update( $this->id );
	}

	/**
	 * @param WPML_Translation_Job $job
	 */
	public function add_job( $job ) {
		$this->job_objects[ $job->get_id() ] = $job;
	}

	public function get_jobs_as_array() {
		$res = array();
		krsort( $this->job_objects );
		foreach ( $this->job_objects as $job ) {
			$res[ ] = $job->to_array();
		}

		return $res;
	}

	public function get_item_count() {

		return count( $this->job_objects );
	}

	public function get_id() {

		return $this->id;
	}

	public function get_batch_name() {
		if ( $this->name == false ) {
			$this->name = TranslationManagement::get_batch_name( $this->get_id() );
		}

		return $this->name;
	}

	private function generate_generic_batch_name() {
		return 'Manual Translations from ' . date( 'F \t\h\e jS\, Y' );
	}

	public function get_status_array() {
		$status_array = array();
		foreach ( $this->job_objects as $job ) {
			if ( ! isset( $status_array[ $job->get_status() ] ) ) {
				$status_array[ $job->get_status() ] = 0;
			}
			$status_array[ $job->get_status() ] ++;
		}

		return $status_array;
	}

	private function retrieve_generic_batch_id() {
		return TranslationProxy_Batch::update_translation_batch( $this->generate_generic_batch_name() );
	}
}
