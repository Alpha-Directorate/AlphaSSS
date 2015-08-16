<?php

class WPML_Translation_Job_Factory {

	public function __construct() {
		add_filter( 'wpml_translation_jobs', array( $this, 'get_translation_jobs_filter' ), 10, 2 );
		add_filter( 'wpml_translation_job_types', array( $this, 'get_translation_job_types_filter' ), 10, 2 );
		add_filter( 'wpml_get_translation_job', array( $this, 'get_translation_job_filter' ), 10, 3 );
	}

	/**
	 * Creates a local translation job for a given post and target language and returns the job_id of the created job.
	 *
	 * @param int    $post_id
	 * @param string $target_language_code
	 *
	 * @return null|int
	 */
	public function create_local_post_job( $post_id, $target_language_code ) {
		global $wpml_post_translations, $iclTranslationManagement;

		$source_language_code = $wpml_post_translations->get_element_lang_code( $post_id );
		$trid                 = $wpml_post_translations->get_element_trid( $post_id );
		if ( $source_language_code && $trid ) {
			$dummy_basket_data = array(
				'post'           => array( $post_id ),
				'translate_from' => $source_language_code,
				'translate_to'   => array( $target_language_code => 1 )
			);
			$iclTranslationManagement->send_jobs( $dummy_basket_data );
		}

		return $iclTranslationManagement->get_translation_job_id( $trid, $target_language_code );
	}

	public function get_translation_jobs_filter( $jobs, $args ) {

		return array_merge( $jobs, $this->get_translation_jobs( $args ) );
	}

	public function get_translation_job_filter( $job_id, $include_non_translatable_elements = false, $revisions = 0 ) {
		return $this->get_translation_job( $job_id, $include_non_translatable_elements, $revisions );
	}

	/**
	 * @param  int $job_id
	 * @param bool $include_non_translatable_elements
	 * @param int  $revisions
	 *
	 * @return bool|object
	 */
	public function get_translation_job( $job_id, $include_non_translatable_elements = false, $revisions = 0 ) {

		$job_data = false;
		$job      = $this->retrieve_job_data( $job_id );
		if ( (bool) $job !== false ) {
			$job_data = $this->complete_job_data( $job, $include_non_translatable_elements, $revisions );
		}

		return $job_data;
	}

	private function complete_job_data( $job, $include_non_translatable_elements, $revisions ) {
		global $sitepress, $wpdb;

		$job                = $this->add_original_title( $job );
		$_ld                = $sitepress->get_language_details( $job->source_language_code );
		$job->from_language = $_ld[ 'display_name' ];
		$_ld                = $sitepress->get_language_details( $job->language_code );
		$job->to_language   = $_ld[ 'display_name' ];
		$job                = $this->add_job_elements( $job, $include_non_translatable_elements );

		//do we have a previous version
		if ( $revisions > 0 ) {
			$prev_version_job_id = $wpdb->get_var( $wpdb->prepare( "
                                                                SELECT MAX(job_id)
                                                                FROM {$wpdb->prefix}icl_translate_job
                                                                WHERE rid=%d
                                                                  AND job_id < %d",
																   $job->rid,
																   $job->job_id ) );
			if ( $prev_version_job_id ) {
				$job->prev_version = $this->get_translation_job( $prev_version_job_id, false, $revisions - 1 );
			}
		}

		return $job;
	}

	private function add_job_elements( $job, $include_non_translatable_elements ) {
		global $wpdb;

		$jelq = ! $include_non_translatable_elements ? ' AND field_translate = 1' : '';

		$elements = $wpdb->get_results( $wpdb->prepare( " SELECT *
                                                        FROM {$wpdb->prefix}icl_translate
                                                        WHERE job_id = %d {$jelq}
                                                        ORDER BY tid ASC",
														$job->job_id ) );

		// allow adding custom elements
		$job->elements = apply_filters( 'icl_job_elements', $elements, $job->original_doc_id, $job->job_id );

		return $job;
	}

	private function add_original_title( $job ) {
		global $iclTranslationManagement;

		if ( $iclTranslationManagement->is_external_type( $job->element_type_prefix ) ) {
			$job->original_doc_title = $this->get_external_job_post_title( $job->job_id, $job->original_doc_id );
		} else {
			$job->original_doc_title = get_the_title( $job->original_doc_id );
		}

		return $job;
	}

	private function retrieve_job_data( $job_ids ) {
		global $wpdb;

		$job_ids = is_scalar( $job_ids ) ? array( $job_ids ) : $job_ids;
		if ( (bool) $job_ids === false ) {
			return array();
		}

		$job_id_in    = wpml_prepare_in( $job_ids, '%d' );
		$limit        = count( $job_ids );
		$data_query
					  = "
			SELECT
				j.rid,
				j.translator_id,
				t.translation_id,
				s.batch_id,
				j.translated,
				j.manager_id,
				s.status,
				s.needs_update,
				s.translation_service,
				t.trid,
				t.language_code,
				t.source_language_code,
				iclt.field_data AS original_doc_id,
				iclt.job_id,
				SUBSTRING_INDEX(ito.element_type, '_', 1) AS element_type_prefix,
				ito.element_type AS original_post_type
			FROM {$wpdb->prefix}icl_translate_job j
            JOIN {$wpdb->prefix}icl_translation_status s
              ON j.rid = s.rid
            JOIN {$wpdb->prefix}icl_translations t
              ON s.translation_id = t.translation_id
            JOIN {$wpdb->prefix}icl_translate iclt
              ON iclt.job_id = j.job_id
            JOIN {$wpdb->prefix}icl_translations ito
              ON ito.element_id = iclt.field_data
                AND ito.trid = t.trid
			WHERE j.job_id IN ({$job_id_in})
			  AND iclt.field_type = 'original_id'
            LIMIT %d
			";
		$data_prepare = $wpdb->prepare( $data_query, $limit );
		$data         = $wpdb->get_results( $data_prepare );

		return (bool) $data === false ? array() : ( $limit === 1 ? $data[ 0 ] : $data );
	}

	public function get_translation_jobs( $args = array(), $only_ids = false ) {
		global $wpdb;

		/** @var $order_by array */
		/** @var $include_unassigned bool */
		$include_unassigned = false;
		$order_by           = array();

		extract( $args, EXTR_OVERWRITE );

		$order_by = is_scalar( $order_by ) ? array( $order_by ) : $order_by;
		if ( $include_unassigned ) {
			$order_by[ ] = 'j.translator_id DESC';
		}
		$order_by[ ] = ' j.job_id DESC ';
		$order_by    = join( ', ', $order_by );

		$where = $this->build_where_clause( $args );

		$jobs_sql = $this->get_job_sql( $where, $order_by, $only_ids );
		$jobs     = $wpdb->get_results( $jobs_sql );
		if ( $only_ids === false ) {
			$jobs = $this->add_data_to_post_jobs( $jobs );
		}

		return $jobs;
	}

	private function add_data_to_post_jobs( $jobs ) {
		global $iclTranslationManagement, $sitepress;

		foreach ( $jobs as $job_index => $job ) {
			$post_id = $job->original_doc_id;
			$doc     = $iclTranslationManagement->get_post( $post_id, $job->element_type_prefix );

			if ( $doc ) {
				$element_language_details = $sitepress->get_element_language_details( $post_id,
																					  $job->original_post_type );
				$language_from_code       = $element_language_details->language_code;
				$edit_url                 = get_edit_post_link( $doc->ID );

				if ( $iclTranslationManagement->is_external_type( $job->element_type_prefix ) ) {
					$post_title = $this->get_external_job_post_title( $job->job_id, $post_id );
					$edit_url   = apply_filters( 'wpml_external_item_url', $edit_url, $post_id );
					$edit_url   = apply_filters( 'wpml_document_edit_item_url', $edit_url, $doc->kind_slug, $doc->ID );
				} else {
					$post_title = $doc->post_title;
					$edit_url   = apply_filters( 'wpml_document_edit_item_url',
												 $edit_url,
												 $job->original_post_type,
												 $doc->ID );
				}
				$ldf                                      = $sitepress->get_language_details( $language_from_code );
				$jobs[ $job_index ]->original_doc_id      = $doc->ID;
				$jobs[ $job_index ]->language_code_source = $language_from_code;
			} else {
				$post_title                               = __( "The original has been deleted!", "sitepress" );
				$edit_url                                 = "";
				$jobs[ $job_index ]->original_doc_id      = 0;
				$jobs[ $job_index ]->language_code_source = null;

				$ldf[ 'display_name' ] = __( "Deleted", "sitepress" );
			}

			$jobs[ $job_index ]->post_title = $post_title;
			$jobs[ $job_index ]->edit_link  = $edit_url;

			$ldt = $sitepress->get_language_details( $job->language_code );

			$jobs[ $job_index ]->lang_text            = $ldf[ 'display_name' ] . ' &raquo; ' . $ldt[ 'display_name' ];
			$jobs[ $job_index ]->language_text_source = $ldf[ 'display_name' ];
			$jobs[ $job_index ]->language_text_target = $ldt[ 'display_name' ];
			$jobs[ $job_index ]->language_code_target = $job->language_code;
		}

		return $jobs;
	}

	private function get_job_sql( $where, $order_by, $only_ids = false ) {
		global $wpdb;

		$cols = 'j.job_id, s.batch_id' . ( $only_ids === false
				? ",
                  	j.rid,
                    t.trid,
                    t.element_id,
                    t.language_code,
                    t.source_language_code,
                    s.translation_id,
                    s.status,
                    s.needs_update,
                    p.post_title AS title,
                    s.translator_id,
                    u.display_name AS translator_name,
                    s.translation_service,
                    iclt.field_data AS original_doc_id,
				    iclt.job_id,
				    SUBSTRING_INDEX(ito.element_type, '_', 1) AS element_type_prefix,
				    ito.element_type AS original_post_type
                    " : "" );

		return "SELECT SQL_CALC_FOUND_ROWS
					{$cols}
                FROM {$wpdb->prefix}icl_translate_job j
                JOIN {$wpdb->prefix}icl_translation_status s
                  ON j.rid = s.rid
                JOIN {$wpdb->prefix}icl_translations t
                  ON s.translation_id = t.translation_id
                JOIN {$wpdb->prefix}icl_translate iclt
                  ON iclt.job_id = j.job_id
                JOIN {$wpdb->prefix}icl_translations ito
                  ON ito.element_id = iclt.field_data
                    AND ito.trid = t.trid
                LEFT JOIN {$wpdb->prefix}posts p
                  ON t.element_id = p.ID
                LEFT JOIN {$wpdb->users} u
                  ON s.translator_id = u.ID
                WHERE {$where}
                  AND revision IS NULL
                  AND iclt.field_type = 'original_id'
                ORDER BY {$order_by}
            ";
	}

	private function build_where_clause( $args ) {
		global $wpdb;

		// defaults
		/** @var string $translator_id */
		/** @var int $status */
		/** @var bool $include_unassigned */
		/** @var int $limit_no */
		/** @var array $language_pairs */
		/** @var string|bool $service */
		$args_default = array(
			'translator_id'      => 0,
			'status'             => false,
			'include_unassigned' => false,
			'language_pairs'     => array(),
			'service'            => false
		);

		extract( $args_default );
		extract( $args, EXTR_OVERWRITE );

		$where = " s.status > " . ICL_TM_NOT_TRANSLATED;
		$where .= $status != '' ? " AND s.status=" . intval( $status ) : '';
		$where .= $status != ICL_TM_DUPLICATE ? " AND s.status <> " . ICL_TM_DUPLICATE : '';
		$where .= ! empty( $from ) ? $wpdb->prepare( " AND t.source_language_code = %s ", $from ) : '';
		$where .= ! empty( $to ) ? $wpdb->prepare( " AND t.language_code = %s ", $to ) : '';

		if ( $translator_id !== "" ) {
			if ( ! is_numeric( $translator_id ) ) {
				$_exp          = explode( '-', $translator_id );
				$service       = isset( $_exp[ 1 ] ) ? implode( '-', array_slice( $_exp, 1 ) ) : 'local';
				$translator_id = isset( $_exp[ 2 ] ) ? $_exp[ 2 ] : false;
			} else {
				$service = 'local';
			}
			$language_pairs = empty( $to ) || empty( $from ) ?
				get_user_meta( $translator_id, $wpdb->prefix . 'language_pairs', true )
				: $language_pairs;

			$unassigned_snippet = $include_unassigned ? " OR j.translator_id=0 " : '';
			$where .= $wpdb->prepare( " AND (j.translator_id=%d {$unassigned_snippet}) ", $translator_id );
		}

		$where .= ! empty( $service ) ? $wpdb->prepare( " AND s.translation_service=%s ", $service ) : '';

		if ( empty( $from ) && (bool) $language_pairs !== false && is_array( $language_pairs ) && $translator_id ) {
			// only if we filter by translator, make sure to use just the 'from' languages that apply
			// in no translator_id, omit condition and all will be pulled
			if ( ! empty( $to ) ) {
				// get 'from' languages corresponding to $to (to $translator_id)
				$from_languages = array();
				foreach ( $language_pairs as $fl => $tls ) {
					if ( isset( $tls[ $to ] ) ) {
						$from_languages[ ] = $fl;
					}
				}
				$where .= $from_languages ? " AND t.source_language_code IN (" . wpml_prepare_in(
						$from_languages
					) . ") " : '';
			} else {
				// all to all case
				// get all possible combinations for $translator_id
				$from_languages   = array_keys( $language_pairs );
				$where_conditions = array();
				foreach ( $from_languages as $fl ) {
					$where_conditions[ ] = $wpdb->prepare(
						" (t.source_language_code = %s AND t.language_code IN (" . wpml_prepare_in(
							array_keys( $language_pairs[ $fl ] )
						) . ")) ",
						$fl
					);
				}
				$where .= ! empty( $where_conditions ) ? ' AND ( ' . join( ' OR ', $where_conditions ) . ') ' : '';
			}
		}

		if ( empty( $to )
			 && $translator_id
			 && ! empty( $from )
			 && isset( $language_pairs[ $from ] )
			 && (bool) $language_pairs[ $from ] !== false
		) {
			// only if we filter by translator, make sure to use just the 'from' languages that apply
			// in no translator_id, omit condition and all will be pulled
			// get languages the user can translate into from $from
			$where .= " AND t.language_code IN(" . wpml_prepare_in( array_keys( $language_pairs[ $from ] ) ) . ")";
		}

		$where .= ! empty( $type ) ? $wpdb->prepare( " AND ito.element_type=%s ", $type ) : '';

		return $where;
	}

	public function get_translation_job_types_filter( $value, $args ) {

		global $wpdb, $sitepress;

		$where     = $this->build_where_clause( $args );
		$job_types_sql
				   = "SELECT DISTINCT
				    SUBSTRING_INDEX(ito.element_type, '_', 1) AS element_type_prefix,
				    ito.element_type AS original_post_type
                    FROM {$wpdb->prefix}icl_translate_job j
                    JOIN {$wpdb->prefix}icl_translation_status s
                      ON j.rid = s.rid
                    JOIN {$wpdb->prefix}icl_translations t
                      ON s.translation_id = t.translation_id
                    JOIN {$wpdb->prefix}icl_translate iclt
                      ON iclt.job_id = j.job_id
                    JOIN {$wpdb->prefix}icl_translations ito
                      ON ito.element_id = iclt.field_data
                        AND ito.trid = t.trid
                    LEFT JOIN {$wpdb->prefix}posts p
                      ON t.element_id = p.ID
                    LEFT JOIN {$wpdb->users} u
                      ON s.translator_id = u.ID
                    WHERE {$where}
                      AND revision IS NULL
                      AND iclt.field_type = 'original_id'
                ";
		$job_types = $wpdb->get_results( $job_types_sql );

		$post_types = $sitepress->get_translatable_documents( true );
		$post_types = apply_filters( 'wpml_get_translatable_types', $post_types );
		$output     = array();

		foreach ( $job_types as $job_type ) {
			$type = $job_type->original_post_type;
			$name = $type;
			switch ( $job_type->element_type_prefix ) {
				case 'post':
					$type = substr( $type, 5 );
					break;

				case 'package':
					$type = substr( $type, 8 );
					break;
			}

			if ( isset( $post_types[ $type ] ) ) {
				$name = $post_types[ $type ]->labels->singular_name;
			}

			$output[ $job_type->element_type_prefix . '_' . $type ] = $name;

		}

		return $output;
	}

	/**
	 * @param $job_id
	 * @param $post_id
	 *
	 * @return mixed|string|void
	 */
	private function get_external_job_post_title( $job_id, $post_id ) {
		global $wpdb;

		$title_and_name = $wpdb->get_row( $wpdb->prepare( "
													 SELECT n.field_data AS name, t.field_data AS title
													 FROM {$wpdb->prefix}icl_translate AS n
													 JOIN {$wpdb->prefix}icl_translate AS t
													  ON n.job_id = t.job_id
													 WHERE n.job_id = %d
													  AND n.field_type = 'name'
													  AND t.field_type = 'title'
													  LIMIT 1
													  ",
														  $job_id ) );

		$post_title = $title_and_name !== null ? ( $title_and_name->name ?
			base64_decode( $title_and_name->name )
			: base64_decode( $title_and_name->title ) ) : '';
		$post_title = apply_filters( 'wpml_tm_external_translation_job_title', $post_title, $post_id );

		return $post_title;
	}

	/**
	 * Get all string jobs sent to remote translation service
	 * This function takes the same input array as does $iclTranslationManagement->get_translation_jobs
	 * 'from' should contain the language code for the source of the translation job
	 * 'to' specifies the target language code
	 * 'status' follows the same conventions as for normal jobs so 1 and 2 are waiting for translator and 10 is
	 * complete. Other values are not supported for strings at this point and will lead to empty results
	 *
	 * @param array   $args
	 * @param bool    $only_ids
	 *
	 * @return array Array of jobs, every job is object
	 * @global object $wpdb
	 * @global object $sitepress
	 *
	 */
	public function get_strings_jobs( $args = array(), $only_ids = false ) {
		global $wpdb, $sitepress;

		$translator_id = "";
		$from          = "";
		$to            = "";
		$status        = "";
		$service       = false;

		extract( $args, EXTR_OVERWRITE );

		$where = ! empty( $from ) ? $wpdb->prepare( " AND sc.language = %s ", $from ) : '';
		$where .= ! empty( $to ) ? $wpdb->prepare( " AND st.language = %s ", $to ) : '';
		$where .= $status ? $where .= $wpdb->prepare( " AND st.status = %d ",
													  $status == ICL_TM_IN_PROGRESS
														  ? ICL_TM_WAITING_FOR_TRANSLATOR
														  : $status ) : '';

		$service = is_numeric( $translator_id ) ? 'local' : $service;
		$service = $service !== 'local' && strpos( $translator_id, "ts-" ) !== false ? substr( $translator_id, 3 )
			: $service;

		$where .= $service === 'local' ? $wpdb->prepare( " AND st.translator_id = %s ", $translator_id ) : '';
		$where .= $service !== false ? $wpdb->prepare( " AND st.translation_service = %s ", $service ) : '';

		$cols = "st.id, tb.id AS batch_id" . ( $only_ids === false ? ",
                         s.language AS source_language_code,
                         st.language AS language_code,
                         st.status,
                         st.string_id,
                         s.name,
                         s.value,
                         tb.id AS batch_id,
                         st.translation_service,
                         st.translator_id,
                         u.display_name as translator_name,
                         COUNT( st.id ) as strings_count" : "" );

		$query
			= "	SELECT
					{$cols}
					FROM {$wpdb->prefix}icl_string_translations AS st
					INNER JOIN {$wpdb->prefix}icl_strings AS s
						ON st.string_id = s.id
					INNER JOIN {$wpdb->prefix}icl_translation_batches AS tb
						ON tb.id = st.batch_id
					LEFT JOIN {$wpdb->users} u
				      ON st.translator_id = u.ID
					WHERE 1 {$where}
					GROUP BY st.id, s.id";

		$result = $wpdb->get_results( $query );

		if ( $only_ids === false ) {
			foreach ( $result as $num => $value ) {
				$lang_from                 = $sitepress->get_language_details( $value->source_language_code );
				$lang_to                   = $sitepress->get_language_details( $value->language_code );
				$lang_text                 = $lang_from[ 'display_name' ] . ' &raquo; ' . $lang_to[ 'display_name' ];
				$result[ $num ]->lang_text = $lang_text;
				if ( $value->translation_service == TranslationProxy::get_current_service_id() ) {
					$result[ $num ]->translator_name = TranslationProxy_Translator::get_translator_name(
						$value->translator_id
					);
				}
			}
		}

		return $result;
	}
}