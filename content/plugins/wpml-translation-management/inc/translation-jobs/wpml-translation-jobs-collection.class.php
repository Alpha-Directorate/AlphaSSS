<?php

require WPML_TM_PATH . '/inc/translation-jobs/jobs/wpml-post-translation-job.class.php';
require WPML_TM_PATH . '/inc/translation-jobs/jobs/wpml-string-translation-job.class.php';

class WPML_Translation_Jobs_Collection {

	/** @var WPML_Translation_Batch[] $translation_batches */
	private $translation_batches;

	public function __construct( $icl_translation_filter ) {
		$this->filter = $icl_translation_filter;
		$this->load_translation_jobs();
	}

	public function get_batches_array() {
		return $this->translation_batches;
	}

	public function get_paginated_batches( $page, $per_page ) {
		$left_before_batch = $page * $per_page;
		$left_in_page      = $per_page;
		$metrics           = array();
		$batches           = array();

		if ( $this->translation_batches ) {
			krsort( $this->translation_batches );
			foreach ( $this->translation_batches as $id => $batch ) {
				$count_in_batch = $batch->get_item_count();
				if ( $left_before_batch - $count_in_batch < $per_page ) {
					$metrics[ $id ] = $batch->get_batch_meta_array();
					$batches[ $id ] = $batch;
					if ( $left_before_batch > $per_page ) {
						$metrics[ $id ][ 'display_from' ] = floor( $left_before_batch / $per_page ) * $per_page
															- ( $per_page - $left_before_batch % $per_page )
															+ 1;
					} else {
						$metrics[ $id ][ 'display_from' ] = 1;
					}
					$left_in_batch = $count_in_batch - $metrics[ $id ][ 'display_from' ];
					if ( $left_in_page > $left_in_batch ) {
						$left_in_page -= $count_in_batch;
						$metrics[ $id ][ 'display_to' ] = $count_in_batch;
					} else {
						$metrics[ $id ][ 'display_to' ] = $metrics[ $id ][ 'display_from' ] + $left_in_page - 1;
					}
				}
				$left_before_batch -= $count_in_batch;

				if ( $left_before_batch <= 0 ) {
					break;
				}
			}
		}

		return array( 'batches' => $batches, 'metrics' => $metrics );
	}

	public function get_count() {
		$count = 0;
		if ( $this->translation_batches ) {
			foreach ( $this->translation_batches as $batch ) {
				$count += $batch->get_item_count();
			}
		}

		return $count;
	}

	public function add_job( $job ) {
		/** @var WPML_Translation_Job $job */
		if ( ! isset( $this->translation_batches[ $job->get_batch_id() ] ) ) {
			$batch = new WPML_Translation_Batch( $job->get_batch_id() );
		} else {
			$batch = $this->translation_batches[ $job->get_batch_id() ];
		}

		$batch->add_job( $job );
		$this->translation_batches[ $batch->get_id() ] = $batch;
	}

	private function load_translation_jobs() {
		/** @var WPML_Translation_Job_Factory $wpml_translation_job_factory */
		global $wpml_translation_job_factory;

		$jobs = $wpml_translation_job_factory->get_translation_jobs( $this->filter, true );
		if ( $jobs && is_array( $jobs ) && count( $jobs ) ) {

			foreach ( $jobs as $job ) {
				$this->add_job( new WPML_Post_Translation_Job( $job->job_id, $job->batch_id ) );
			}
		}

		if ( class_exists( 'WPML_String_Translation' ) ) {
			$string_translation_jobs = $wpml_translation_job_factory->get_strings_jobs( $this->filter, true );
			if ( $string_translation_jobs ) {
				foreach ( $string_translation_jobs as $job ) {
					$this->add_job( new WPML_String_Translation_Job( $job->id, $job->batch_id ) );
				}
			}
		}
	}
}
