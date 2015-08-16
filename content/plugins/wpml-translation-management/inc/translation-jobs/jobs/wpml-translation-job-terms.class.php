<?php

class WPML_Translation_Job_Terms {

	/**
	 * Retrieves an array of all terms associated with a post. This array is indexed by indexes of the for {t_}{term_taxonomy_id}.
	 *
	 * @param $post_id int
	 *
	 * @return array
	 */
	public static function get_term_field_array_for_post( $post_id ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT o.term_taxonomy_id, t.name
								  FROM {$wpdb->term_relationships} o
								  JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = o.term_taxonomy_id
								  JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
								  WHERE o.object_id = %d",
		                         $post_id );
		$res   = $wpdb->get_results( $query );

		$result = array();

		foreach ( $res as $term ) {
			$result[ 't_' . $term->term_taxonomy_id ] = $term->name;
		}

		return $result;
	}

	/**
	 * Saves all taxonomy term data from a job to the database.
	 *
	 * @param $job_id Integer
	 * @param $target_language_code String
	 */
	public static function save_terms_from_job( $job_id, $target_language_code ) {
		/** @var SitePress $sitepress */
		global $sitepress;

		if ( $sitepress->get_setting( 'tm_block_retranslating_terms' ) ) {
			self::set_translated_term_values( $job_id, false );
		}

		$terms            = self::get_terms_affected_by_job_new_format( $job_id );
		foreach ( $terms as $term ) {
			$new_term_action = new WPML_Update_Term_Action( array(
				                                                'term'      => base64_decode( $term->field_data_translated ),
				                                                'lang_code' => $target_language_code,
				                                                'trid'      => $term->trid,
				                                                'taxonomy'  => $term->taxonomy
			                                                ) );
			$new_term_action->execute();
		}
	}

	/**
	 * Retrieves all terms translations affected by a job.
	 *
	 * @param $job_id Integer
	 *
	 * @return array|bool
	 */
	public static function get_terms_affected_by_job_new_format( $job_id ) {
		global $wpdb;

		$tt = $wpdb->term_taxonomy;
		$i  = $wpdb->prefix . 'icl_translations';
		$j  = $wpdb->prefix . 'icl_translate';

		$query_for_terms_in_job = $wpdb->prepare("	SELECT
													  tt.taxonomy,
													  iclt.trid,
													  j.field_data_translated
													FROM
														{$tt} tt JOIN {$i} iclt ON iclt.element_id = tt.term_taxonomy_id AND CONCAT('tax_', tt.taxonomy) = iclt.element_type
														JOIN {$j} j ON j.field_type = CONCAT('t_', tt.term_taxonomy_id)
														WHERE j.job_id = %d ", $job_id);
		$res                    = $wpdb->get_results( $query_for_terms_in_job );

		return $res;
	}

	/**
	 * Saves potentially existing translations of terms to a jobs. This is used to ensure local translators always being presented the most recent version
	 * of the translation they are working on. Also it is used to remove already translated terms from jobs that are sent to remote translation,
	 * in case they are not to be translated again.
	 *
	 * @param $job_id Integer
	 * @param bool $delete If true, terms that are already translated will be removed from the job. This is used in order to prevent
	 * terms, that are not to be translated again, from being sent to remote translation.
	 */
	public static function set_translated_term_values( $job_id, $delete = false ) {
		global $wpdb;

		$i  = $wpdb->prefix . 'icl_translations';
		$j  = $wpdb->prefix . 'icl_translate';

		$get_target_terms_for_job_query = $wpdb->prepare("
					SELECT
					  t.name,
					  iclt_original.element_id ttid
					FROM {$wpdb->terms} t
					JOIN {$wpdb->term_taxonomy} tt
						ON t.term_id = tt.term_id
					JOIN {$i} iclt_translation
						ON iclt_translation.element_id = tt.term_taxonomy_id
							AND CONCAT('tax_', tt.taxonomy) = iclt_translation.element_type
					JOIN {$i} iclt_original
						ON iclt_original.trid = iclt_translation.trid
					JOIN {$j} jobs
						ON jobs.field_type = CONCAT('t_', iclt_original.element_id)
					WHERE jobs.job_id = %d", $job_id);
		$term_values = $wpdb->get_results( $get_target_terms_for_job_query );
		foreach ( $term_values as $term ) {
			if ( $delete ) {
				$wpdb->delete( $j, array( 'field_type' => 't_' . $term->ttid, 'job_id' => $job_id ) );
			} else {

				$wpdb->update( $j,
				               array( 'field_data_translated' => base64_encode( $term->name ) ),
				               array( 'field_type' => 't_' . $term->ttid, 'job_id' => $job_id ) );
			}
		}
	}
}
