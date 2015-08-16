<?php
/**
 * @package wpml-core
 * @package wpml-core-pro-translation
 */

class WPML_Pro_Translation{

	public $errors;
	private $tmg;
	protected static $__asian_languages = array( 'ja', 'ko', 'zh-hans', 'zh-hant', 'mn', 'ne', 'hi', 'pa', 'ta', 'th' );

	const CMS_FAILED = 0;
	const CMS_SUCCESS = 1;

	var $cms_id_parts_glue = '|||';

	function __construct() {

		$this->errors = array();
		global $iclTranslationManagement;
		$this->tmg =& $iclTranslationManagement;

		add_filter( 'xmlrpc_methods', array( $this, 'custom_xmlrpc_methods' ) );

		add_action( 'post_submitbox_start', array( $this, 'post_submitbox_start' ) );
		add_action( 'icl_ajx_custom_call', array( $this, 'ajax_calls' ), 10, 2 );
		add_action( 'icl_hourly_translation_pickup', array( $this, 'poll_for_translations' ) );
	}

	function ajax_calls( $call, $data ) {
		global $sitepress_settings, $sitepress;
		switch ( $call ) {
			case 'set_pickup_mode':
				$method                                     = intval( $data[ 'icl_translation_pickup_method' ] );
				$iclsettings[ 'translation_pickup_method' ] = $method;
				$sitepress->save_settings( $iclsettings );

				try {
					$project = TranslationProxy::get_current_project(  );
					$project->set_delivery_method( $method == ICL_PRO_TRANSLATION_PICKUP_XMLRPC ? 'xmlrpc' : 'polling' );
				} catch ( Exception $e ) {
					echo wp_json_encode( array( 'error' => __( 'Could not update the translation pickup mode.', 'sitepress' ) ) );
				}

				if ( $method == ICL_PRO_TRANSLATION_PICKUP_XMLRPC ) {
					wp_clear_scheduled_hook( 'icl_hourly_translation_pickup' );
				} else {
					wp_schedule_event( time(), 'hourly', 'icl_hourly_translation_pickup' );
				}

				echo json_encode( array( 'message' => 'OK' ) );
				break;
			case 'pickup_translations':
				$errors                  = '';
				$status_completed        = '';
				$status_cancelled        = '';

				if ( $sitepress_settings[ 'translation_pickup_method' ] == ICL_PRO_TRANSLATION_PICKUP_POLLING ) {
					$results = $this->poll_for_translations( true );

					if ( $results[ 'errors' ]  ) {
						$status = __( 'Error', 'sitepress' );
						$errors = join( '<br />', $results[ 'errors' ] );
					} else {
						$status = __( 'OK', 'sitepress' );

						$status_completed = '&nbsp;' . sprintf( __( 'Fetched %d translations.', 'sitepress' ), $results[ 'completed' ] );
						if ( $results[ 'cancelled' ] ) {
							$status_cancelled = '&nbsp;' . sprintf( __( '%d translations have been marked as cancelled.', 'sitepress' ), $results[ 'cancelled' ] );
						}
					}
				} else {
					$status = __( 'Manual pick up is disabled.', 'sitepress' );
				}
				echo json_encode( array(
						                  'status'           => $status,
						                  'errors'           => $errors,
						                  'completed'        => $status_completed,
						                  'cancelled'        => $status_cancelled,
				                  ) );

				break;
		}
	}

	function get_xliff_file( $job_id ) {
		$xliff = new WPML_TM_xliff();
		$xliff_content = $xliff->generate_job_xliff( $job_id );
		$file          = fopen( 'php://temp', 'r+' );
		fwrite( $file, $xliff_content );
		rewind( $file );
	}

	/**
	 * @param WP_Post|WPML_Package $post
	 * @param                      $target_languages
	 * @param int                  $translator_id
	 * @param                      $job_id
	 *
	 * @return bool|int
	 */
	function send_post( $post, $target_languages, $translator_id, $job_id ) {
		global $sitepress, $sitepress_settings, $wpdb, $iclTranslationManagement;

		$this->maybe_init_translation_management( $iclTranslationManagement );

		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}
		if ( !$post ) {
			return false;
		}

		$post_id             = $post->ID;
		$post_type           = $post->post_type;
		$element_type_prefix = $iclTranslationManagement->get_element_type_prefix_from_job_id( $job_id );
		$element_type        = $element_type_prefix . '_' . $post_type;

		$err = false;
		$res = false;

		$source_language = $sitepress->get_language_for_element( $post_id, $element_type );

		if ( empty( $target_languages ) ) {
			return false;
		}

		// Make sure the previous request is complete.
		// Only send if it needs update
		foreach ( $target_languages as $target_language ) {

			if ( $target_language == $source_language ) {
				continue;
			}

			$translation = $this->tmg->get_element_translation( $post_id, $target_language, $element_type );

			if ( !$translation ) { // translated the first time
				$err = true;
			}

			if (!$err && ( $translation->needs_update || $translation->status == ICL_TM_NOT_TRANSLATED || $translation->status == ICL_TM_WAITING_FOR_TRANSLATOR )) {

				$project = TranslationProxy::get_current_project();

				if ( $post_type == 'page' ) {
					$post_url = site_url() . '?page_id=' . ( $post_id );
				} else {
					$post_url = site_url() . '?p=' . ( $post_id );
				}
				if ( $iclTranslationManagement->is_external_type( $element_type_prefix ) ) {
					$post_url = apply_filters( 'wpml_external_item_url', $post_url, $post_id );
				}

				if ( $sitepress->get_setting( 'tm_block_retranslating_terms' ) ) {
					WPML_Translation_Job_Terms::set_translated_term_values( $job_id, true );
				}

				$xliff		= new WPML_TM_xliff();
				$file       = $xliff->get_job_xliff_file( $job_id );
				$title      = $post->post_title;
				$cms_id     = $this->build_cms_id( $post_id, $post_type, $source_language, $target_language, $job_id );
				$url        = $post_url;
				$word_count = self::estimate_total_word_count( $post, $source_language );
				$note       = isset( $note ) ? $note : '';
				$is_update  = intval( !empty( $translation->element_id ) );

				try {
					if ( TranslationProxy::is_batch_mode() ) {
						$res = $project->send_to_translation_batch_mode( $file, $title, $cms_id, $url, $source_language, $target_language, $word_count, $translator_id, $note, $is_update );
					} else {
						$res = $project->send_to_translation( $file, $title, $cms_id, $url, $source_language, $target_language, $word_count, $translator_id, $note, $is_update );
					}
				} catch ( Exception $err ) {
					// The translation entry will be removed
					$res = 0;
				}
				if ( $res ) {
					$this->tmg->update_translation_status( array(
															   'translation_id' => $translation->translation_id,
															   'status'         => ICL_TM_IN_PROGRESS,
															   'needs_update'   => 0
														   ) );
				} else {
					$this->enqueue_project_errors( $project );

					$previous_state = $wpdb->get_var( $wpdb->prepare( "SELECT _prevstate FROM {$wpdb->prefix}icl_translation_status WHERE translation_id=%d", $translation->translation_id ) );
					if ( !empty( $previous_state ) ) {
						$previous_state = unserialize( $previous_state );
						$data = array(
								'status'              => $previous_state[ 'status' ],
								'translator_id'       => $previous_state[ 'translator_id' ],
								'needs_update'        => $previous_state[ 'needs_update' ],
								'md5'                 => $previous_state[ 'md5' ],
								'translation_service' => $previous_state[ 'translation_service' ],
								'translation_package' => $previous_state[ 'translation_package' ],
								'timestamp'           => $previous_state[ 'timestamp' ],
								'links_fixed'         => $previous_state[ 'links_fixed' ]
						);
						$data_where = array( 'translation_id' => $translation->translation_id );
						$wpdb->update( $wpdb->prefix . 'icl_translation_status', $data, $data_where );
					} else {
						$data = array( 'status' => ICL_TM_NOT_TRANSLATED, 'needs_update' => 0 );
						$data_where = array( 'translation_id' => $translation->translation_id );
						$wpdb->update( $wpdb->prefix . 'icl_translation_status', $data, $data_where );
					}
					$err = true;
				}
			} // if needs translation
		} // foreach target lang
		return $err ? false : $res; //last $ret
	}

    public static function server_languages_map($language_name, $server2plugin = false){
        if(is_array($language_name)){
            return array_map(array(__CLASS__, 'server_languages_map'), $language_name);
        }
        $map = array(
            'Norwegian Bokmål' => 'Norwegian',
            'Portuguese, Brazil' => 'Portuguese',
            'Portuguese, Portugal' => 'Portugal Portuguese'
        );
        if($server2plugin){
            $map = array_flip($map);
        }
        if(isset($map[$language_name])){
            return $map[$language_name];
        }else{
            return $language_name;
        }
    }

	function custom_xmlrpc_methods( $methods ) {
		//ICanLocalize XMLRPC calls for migration
		//Translation proxy XMLRPC calls
		$icl_methods[ 'translationproxy.test_xmlrpc' ]                = array( $this, '_test_xmlrpc' );
		$icl_methods[ 'translationproxy.updated_job_status' ]         = array( $this, 'xmlrpc_updated_job_status_with_log' );
		$icl_methods[ 'translationproxy.notify_comment_translation' ] = array( $this, '_xmlrpc_add_message_translation' );

		$methods = array_merge( $methods, $icl_methods );
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			if ( preg_match( '#<methodName>([^<]+)</methodName>#i', $GLOBALS[ 'HTTP_RAW_POST_DATA' ], $matches ) ) {
				$method = $matches[ 1 ];
				if ( in_array( $method, array_keys( $icl_methods ) ) ) {
					//error_reporting(E_NONE);
					//ini_set('display_errors', '0');
					//$old_error_handler = set_error_handler( array( $this, "_translation_error_handler" ), E_ERROR | E_USER_ERROR );
					set_error_handler( array( $this, "translation_error_handler" ), E_ERROR | E_USER_ERROR );
				}
			}
		}

		return $methods;
	}

	function xmlrpc_updated_job_status_with_log( $args ) {
		
		require_once WPML_TM_PATH . '/inc/translation-proxy/translationproxy-com-log.class.php';

		TranslationProxy_Com_Log::log_xml_rpc( array( 'tp_job_id' => $args[0],
													  'cms_id'    => $args[1],
													  'status'    => $args[2],
													  'signature' => 'UNDISCLOSED') );
	
		$ret = $this->xmlrpc_updated_job_status( $args );
		
		TranslationProxy_Com_Log::log_xml_rpc( array( 'result'    => $ret ) );
		
		return $ret;
	}

	/**
	 *
	 * Handle job update notifications from TP
	 *
	 * @param $args
	 * @return int|string
	 */
	function xmlrpc_updated_job_status($args)
	{

		$translation_proxy_job_id 	= $args[0];
		$cms_id 					= $args[1];
		$status 					= $args[2];
		$signature 					= $args[3];
		

		//get current project
		$project = TranslationProxy::get_current_project();
        if (!$project) {
            return "Project does not exist";
        }

		//check signature
		$signature_chk = sha1( $project->id . $project->access_key . $translation_proxy_job_id . $cms_id . $status );

		if ( $signature_chk != $signature ) {
			return "Wrong signature";
		}

		switch ($status) {
			case "translation_ready" :
				$ret = $this->download_and_process_translation( $translation_proxy_job_id, $cms_id );
				break;
			case "cancelled" :
				$ret =  $this->cancel_translation( $translation_proxy_job_id, $cms_id );
				break;
			default :
				return "Not supported status: {$status}";
		}

		if ( $this->errors ) {
			return join( '', $this->errors );
		}

		if ( (bool) $ret === true ) {
			return self::CMS_SUCCESS;
		}

		// return failed by default
		return self::CMS_FAILED;

	}


	/**
	 *
	 * Cancel translation for given cms_id
	 *
	 * @param $rid
	 * @param $cms_id
	 * @return bool
	 */
	function cancel_translation( $rid, $cms_id ) {
		global $sitepress, $wpdb, $WPML_String_Translation, $iclTranslationManagement;

		$res           = false;
		if ( empty( $cms_id ) ) { // it's a string
			if ( isset( $WPML_String_Translation ) ) {
				$res = $WPML_String_Translation->cancel_remote_translation( $rid ) ;
			}
		}
		else{
			$cms_id_parts      = $this->parse_cms_id( $cms_id );
			$post_type    = $cms_id_parts[ 0 ];
			$_element_id  = $cms_id_parts[ 1 ];
			$_target_lang = $cms_id_parts[ 3 ];
			$job_id       = isset( $cms_id_parts[ 4 ] ) ? $cms_id_parts[ 4 ] : false;

			$element_type_prefix = 'post';
			if ( $job_id ) {
				$element_type_prefix = $iclTranslationManagement->get_element_type_prefix_from_job_id( $job_id );
			}

			$element_type = $element_type_prefix . '_' . $post_type;
			if ( $_element_id && $post_type && $_target_lang ) {
				$trid = $sitepress->get_element_trid( $_element_id, $element_type );
			} else {
				$trid = null;
			}

			if ( $trid ) {
				$translation_id_query   = "SELECT i.translation_id
																FROM {$wpdb->prefix}icl_translations i
																JOIN {$wpdb->prefix}icl_translation_status s
																ON i.translation_id = s.translation_id
																WHERE i.trid=%d
																	AND i.language_code=%s
																	AND s.status IN (%d, %d)
																LIMIT 1";
				$translation_id_args    = array( $trid, $_target_lang, ICL_TM_IN_PROGRESS, ICL_TM_WAITING_FOR_TRANSLATOR );
				$translation_id_prepare = $wpdb->prepare( $translation_id_query, $translation_id_args );
				$translation_id = $wpdb->get_var( $translation_id_prepare );

				if ( $translation_id ) {
					global $iclTranslationManagement;
					$iclTranslationManagement->cancel_translation_request( $translation_id );
					$res = true;
				}
			}


		}

		return $res;
	}

	function _test_xmlrpc() {
		return true;
	}

	function _xmlrpc_add_message_translation( $args ) {
		global $wpdb, $sitepress, $wpml_add_message_translation_callbacks;
		$signature   = $args[ 0 ];
		$rid         = $args[ 2 ];
		$translation = $args[ 3 ];

		$access_key      = $sitepress->get_setting( 'access_key' );
		$site_id         = $sitepress->get_setting( 'site_id' );
		$signature_check = md5( $access_key . $site_id . $rid );
		if ( $signature != $signature_check ) {
			return 0; // array('err_code'=>1, 'err_str'=> __('Signature mismatch','sitepress'));
		}

		$res = $wpdb->get_row( $wpdb->prepare("	SELECT to_language, object_id, object_type
												FROM {$wpdb->prefix}icl_message_status
												WHERE rid= %d ", $rid ) );
		if ( ! $res ) {
			return 0;
		}

		$to_language = $res->to_language;
		$object_id   = $res->object_id;
		$object_type = $res->object_type;

		try {
			if ( is_array( $wpml_add_message_translation_callbacks[ $object_type ] ) ) {
				foreach ( $wpml_add_message_translation_callbacks[ $object_type ] as $callback ) {
					if ( ! is_null( $callback ) ) {
						call_user_func( $callback, $object_id, $to_language, $translation );
					}
				}
			}
			$wpdb->update( $wpdb->prefix . 'icl_message_status', array( 'status' => MESSAGE_TRANSLATION_COMPLETE ), array( 'rid' => $rid ) );
		} catch ( Exception $e ) {
			return $e->getMessage() . '[' . $e->getFile() . ':' . $e->getLine() . ']';
		}

		return 1;
	}

	/**
	 *
	 * Download translation from TP and updates document
	 *
	 * @param $translation_proxy_job_id
	 * @param $cms_id
	 *
	 * @return bool|string
	 *
	 */
	private function download_and_process_translation( $translation_proxy_job_id, $cms_id ) {
		try {
			global $sitepress, $iclTranslationManagement, $wpdb;

			if ( empty( $cms_id ) ) { // it's a string
				//TODO: [WPML 3.3] this should be handled as any other element type in 3.3
				$target = $wpdb->get_var( $wpdb->prepare( "SELECT target FROM {$wpdb->prefix}icl_core_status WHERE rid=%d", $translation_proxy_job_id ) );

				return $this->process_translated_string( $translation_proxy_job_id, $target );
			} else {
				$cms_id_parts = $this->parse_cms_id( $cms_id );

				$post_type   = $cms_id_parts[ 0 ];
				$_element_id = $cms_id_parts[ 1 ];
				$_lang       = $cms_id_parts[ 3 ];
				$job_id      = isset( $cms_id_parts[ 4 ] ) ? $cms_id_parts[ 4 ] : false;

				$element_type_prefix = 'post';
				if ( $job_id ) {
					$element_type_prefix = $iclTranslationManagement->get_element_type_prefix_from_job_id( $job_id );
				}
				$element_type = $element_type_prefix . '_' . $post_type;
				$trid        = $sitepress->get_element_trid( $_element_id, $element_type );

				// check only for posts that still exists
				if ( !empty ( $trid ) ){

					$translation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND language_code=%s", $trid, $_lang ) );

					if ( $this->add_translated_document( $translation->translation_id, $translation_proxy_job_id ) === true ) {
						$this->throw_exception_for_mysql_errors();
						return true;
					} else {
						$this->throw_exception_for_mysql_errors();
						return false;
					}
				//in other case do not process that request
				} else {

					return false;
				}
			}
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}

	function add_translated_document( $translation_id, $translation_proxy_job_id ) {
		global $wpdb, $sitepress, $iclTranslationManagement;
		$project = TranslationProxy::get_current_project();

		$translation_info = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}icl_translations WHERE translation_id=%d", $translation_id ) );
		$translation      = $project->fetch_translation( $translation_proxy_job_id );
		if(!$translation) {
			$this->errors = array_merge($this->errors, $project->errors);
		} else {
			$translation = apply_filters( 'icl_data_from_pro_translation', $translation );
		}
		$ret = true;

		if ( !empty( $translation ) ) {

			try {
				/** @var $job_xliff_translation WP_Error|Array */
				$xliff = new WPML_TM_xliff();
				$job_xliff_translation = $xliff->get_job_xliff_translation( $translation );
				if(is_wp_error($job_xliff_translation)) {
					$this->add_error($job_xliff_translation->get_error_message());
					return false;
				} else {
					$data = $job_xliff_translation[1];
				}
				$iclTranslationManagement->save_translation( $data );

				$translations = $sitepress->get_element_translations( $translation_info->trid, $translation_info->element_type, false, true, true );
				if ( isset( $translations[ $translation_info->language_code ] ) ) {
					$translation = $translations[ $translation_info->language_code ];
					if(isset($translation->element_id) && $translation->element_id) {
						$translation_post_type_prepared = $wpdb->prepare( "SELECT post_type FROM $wpdb->posts WHERE ID=%d", array( $translation->element_id ) );
						$translation_post_type          = $wpdb->get_var( $translation_post_type_prepared );
					} else {
						$translation_post_type = implode('_', array_slice(explode('_', $translation_info->element_type), 1));
					}
					if ( $translation_post_type == 'page' ) {
						$url = get_option( 'home' ) . '?page_id=' . $translation->element_id;
					} else {
						$url = get_option( 'home' ) . '?p=' . $translation->element_id;
					}
					$project->update_job( $translation_proxy_job_id, $url );
				} else {
					$project->update_job( $translation_proxy_job_id );
				}
			} catch ( Exception $e ) {
				$ret = false;
			}
		}

		return $ret;
	}

	function save_post_translation( $translation_id, $translation ) {
		global $wpdb, $sitepress_settings, $sitepress, $icl_adjust_id_url_filter_off;
		$icl_adjust_id_url_filter_off = true;

		$translation_info     = $wpdb->get_row( $wpdb->prepare( "
                SELECT * FROM {$wpdb->prefix}icl_translations tr
                    JOIN {$wpdb->prefix}icl_translation_status ts ON ts.translation_id = tr.translation_id
                WHERE tr.translation_id=%d", $translation_id ) );
		$lang_code = $translation_info->language_code;
		$trid      = $translation_info->trid;

		$original_post_details = $wpdb->get_row( "
            SELECT p.post_author, p.post_type, p.post_status, p.comment_status, p.ping_status, p.post_parent, p.menu_order, p.post_date, t.language_code
            FROM {$wpdb->prefix}icl_translations t 
            JOIN {$wpdb->posts} p ON t.element_id = p.ID AND CONCAT('post_',p.post_type) = t.element_type
            WHERE trid='{$trid}' AND p.ID = '{$translation['original_id']}'
        " );

		//is the original post a sticky post?
		$sticky_posts       = get_option( 'sticky_posts' );
		$is_original_sticky = $original_post_details->post_type == 'post' && in_array( $translation[ 'original_id' ], $sticky_posts );

		$this->_content_fix_image_paths_in_body( $translation );
		$this->_content_fix_relative_link_paths_in_body( $translation );
		$this->_content_decode_shortcodes( $translation );

        // handle the page parent and set it to the translated parent if we have one.
        if($original_post_details->post_parent){
	        $post_parent_trid = $wpdb->get_var(
		        $wpdb->prepare(
			        "	SELECT trid
						FROM {$wpdb->prefix}icl_translations
						WHERE element_type= %s AND element_id = %d ",
			        'post_' . $original_post_details->post_type,
			        $original_post_details->post_parent
		        )
	        );
            if($post_parent_trid){
                $parent_id = $wpdb->get_var( $wpdb->prepare("SELECT element_id
															 FROM {$wpdb->prefix}icl_translations
															 WHERE element_type = %s
															  AND trid = %d
															  AND language_code = %s ",
                                                            'post_' . $original_post_details->post_type,
                                                            $post_parent_trid,
                                                            $lang_code ));
            }            
        }
                
        // determine post id based on trid
        $post_id = $translation_info->element_id;
        
        if($post_id){
            // see if the post really exists - make sure it wasn't deleted while the plugin was 
	        if ( !$wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID = %d ", $post_id ) ) ) {
                $is_update = false;
								$q = "DELETE FROM {$wpdb->prefix}icl_translations WHERE element_type=%s AND element_id=%d";
								$q_prepared = $wpdb->prepare($q, array('post_'.$original_post_details->post_type, $post_id) );
                $wpdb->query($q_prepared);
            }else{
                $is_update = true;
                $postarr['ID'] = $_POST['post_ID'] = $post_id;
            }
        }else{
            $is_update = false;
        } 
        $postarr['post_title'] = $translation['title'];        
        if($sitepress_settings['translated_document_page_url'] == 'translate' && isset($translation['URL'])){
            $postarr['post_name'] = $translation['URL'];
        }
        $postarr['post_content'] = $translation['body'];
        if (isset($translation['excerpt']) && $translation['excerpt'] != "") {
            $postarr['post_excerpt'] = $translation['excerpt'];
        }
        if(isset($translated_taxonomies) && is_array($translated_taxonomies)){
            foreach($translated_taxonomies as $taxonomy=>$values){
                $postarr['tax_input'][$taxonomy] = join(',',(array)$values);
            }
        }
        $postarr['post_author'] = $original_post_details->post_author;  
        $postarr['post_type'] = $original_post_details->post_type;
        if($sitepress_settings['sync_comment_status']){
            $postarr['comment_status'] = $original_post_details->comment_status;
        }
        if($sitepress_settings['sync_ping_status']){
            $postarr['ping_status'] = $original_post_details->ping_status;
        }
        if($sitepress_settings['sync_page_ordering']){
            $postarr['menu_order'] = $original_post_details->menu_order;
        }
        if($sitepress_settings['sync_private_flag'] && $original_post_details->post_status=='private'){    
            $postarr['post_status'] = 'private';
        }
        if(!$is_update){
            $postarr['post_status'] = !$sitepress_settings['translated_document_status'] ? 'draft' : $original_post_details->post_status;
        } else {
            // set post_status to the current post status.
            $postarr['post_status'] = $wpdb->get_var($wpdb->prepare("SELECT post_status
																	 FROM {$wpdb->prefix}posts
																	 WHERE ID = %d ",
                                                                    $post_id));
        }
        if($sitepress_settings['sync_post_date']){
            $postarr['post_date'] = $original_post_details->post_date;
        }        
        
        if(isset($parent_id) && $sitepress_settings['sync_page_parent']){
            $_POST['post_parent'] = $postarr['post_parent'] = $parent_id;  
            $_POST['parent_id'] = $postarr['parent_id'] = $parent_id;  
        }
        
        if($is_update){
            $postarr['post_name'] = $wpdb->get_var($wpdb->prepare("SELECT post_name FROM {$wpdb->posts} WHERE ID=%d", $post_id));
        }
         
        $_POST['trid'] = $trid;
        $_POST['lang'] = $lang_code;
        $_POST['skip_sitepress_actions'] = true;

        global $wp_rewrite;
        if(!isset($wp_rewrite)) $wp_rewrite = new WP_Rewrite();
            
        kses_remove_filters();
        
        $postarr = apply_filters('icl_pre_save_pro_translation', $postarr);
        
        $new_post_id = wp_insert_post($postarr);    
        
        do_action('icl_pro_translation_saved', $new_post_id);
        
        // set stickiness
        if($is_original_sticky && $sitepress_settings['sync_sticky_flag']){
            stick_post($new_post_id);
        }else{
            if($original_post_details->post_type=='post' && $is_update){
                unstick_post($new_post_id); //just in case - if this is an update and the original post stckiness has changed since the post was sent to translation
            }
        }
                                                                                                         
        foreach((array)$sitepress_settings['translation-management']['custom_fields_translation'] as $cf => $op){
            if ($op == 1) {
                $sitepress->_sync_custom_field($translation['original_id'], $new_post_id, $cf);
            }elseif ($op == 2 && isset($translation['field-'.$cf])) {                
                $field_translation = $translation['field-'.$cf];
                $field_type = $translation['field-'.$cf.'-type'];
                if ($field_type == 'custom_field') {
                    $field_translation = str_replace ( '&#0A;', "\n", $field_translation );                                
                    // always decode html entities  eg decode &amp; to &
                    $field_translation = html_entity_decode($field_translation);
                    update_post_meta($new_post_id, $cf, $field_translation);
                }            
            }
        }

        // set specific custom fields
        $copied_custom_fields = array('_top_nav_excluded', '_cms_nav_minihome');    
        foreach($copied_custom_fields as $ccf){
            $val = get_post_meta($translation['original_id'], $ccf, true);
            update_post_meta($new_post_id, $ccf, $val);
        }    
        
        // sync _wp_page_template
        if($sitepress_settings['sync_page_template']){
            $_wp_page_template = get_post_meta($translation['original_id'], '_wp_page_template', true);
            update_post_meta($new_post_id, '_wp_page_template', $_wp_page_template);
        }

		// sync post format
		if ( $sitepress_settings[ 'sync_post_format' ] ) {
			$_wp_post_format = get_post_format( $translation[ 'original_id' ] );
			set_post_format( $new_post_id, $_wp_post_format );
		}

		if ( !$new_post_id ) {
			return false;
		}

		if ( !$is_update ) {
			$wpdb->update( $wpdb->prefix . 'icl_translations', array( 'element_id' => $new_post_id ), array( 'translation_id' => $translation_id ) );
		}
		update_post_meta( $new_post_id, '_icl_translation', 1 );

		TranslationManagement::set_page_url( $new_post_id );

		global $iclTranslationManagement;


		$ts = array(
			'status'         => ICL_TM_COMPLETE,
			'needs_update'   => 0,
			'translation_id' => $translation_id
		);

		$translator_id = $wpdb->get_var( $wpdb->prepare( "SELECT translator_id FROM {$wpdb->prefix}icl_translation_status WHERE translation_id=%d", $translation_id ) );
		if ( !$translator_id ) {
			$lang_status = TranslationProxy_Translator::get_language_pairs();
			foreach ( $lang_status as $languages_pair ) {
				if ( $languages_pair[ 'from' ] == $original_post_details->language_code && $languages_pair[ 'to' ] == $lang_code && isset( $languages_pair[ 'translators' ][ 0 ][ 'id' ] ) ) {
					$ts[ 'translator_id' ] = $languages_pair[ 'translators' ][ 0 ][ 'id' ];
					break;
				}
			}
		}

		// update translation status
		$iclTranslationManagement->update_translation_status( $ts );

		// add new translation job

		//$translation_package = $iclTranslationManagement->create_translation_package(get_post($translation['original_id']));
		//$job_id = $iclTranslationManagement->add_translation_job($translation_info->rid, $translation_info->translator_id, $translation_package);
		$job_id = $iclTranslationManagement->get_translation_job_id( $trid, $lang_code );

		// save the translation
		$iclTranslationManagement->mark_job_done( $job_id );
		$parts = explode( '_', $translation[ 'original_id' ] );
		if ( $parts[ 0 ] != 'external' ) {
			$iclTranslationManagement->save_job_fields_from_post( $job_id, get_post( $new_post_id ) );

			$this->_content_fix_links_to_translated_content( $new_post_id, $lang_code, "post_{$original_post_details->post_type}" );

			// Now try to fix links in other translated content that may link to this post.
			$sql          = "SELECT
                        tr.element_id
                    FROM
                        {$wpdb->prefix}icl_translations tr
                    JOIN
                        {$wpdb->prefix}icl_translation_status ts
                    ON
                        tr.translation_id = ts.translation_id
                    WHERE
                        ts.links_fixed = 0 AND tr.element_type = %s AND tr.language_code = %s AND tr.element_id IS NOT NULL";

			$sql_prepared = $wpdb->prepare($sql, array('post_' . $original_post_details->post_type, $lang_code));

			$needs_fixing = $wpdb->get_results( $sql_prepared );
			foreach ( $needs_fixing as $id ) {
				if ( $id->element_id != $new_post_id ) { // fix all except the new_post_id. We have already done this.
					$this->_content_fix_links_to_translated_content( $id->element_id, $lang_code, "post_{$original_post_details->post_type}" );
				}
			}

			// if this is a parent page then make sure it's children point to this.
			$this->fix_translated_children( $translation[ 'original_id' ], $new_post_id, $lang_code );
		}

		WPML_Translation_Job_Terms::save_terms_from_job( $job_id, $lang_code );

		do_action( 'icl_pro_translation_completed', $new_post_id );

		return true;
	}

	function _content_fix_image_paths_in_body(&$translation) {
        $body = $translation['body'];
        $image_paths = $this->_content_get_image_paths($body);
        
        $source_path = get_permalink($translation['original_id']);
      
        foreach($image_paths as $path) {
      
            $src_path = $this->resolve_url($source_path, $path[2]);
            if ($src_path != $path[2]) {
                $search = $path[1] . $path[2] . $path[1];
                $replace = $path[1] . $src_path . $path[1];
                $new_link = str_replace($search, $replace, $path[0]);
          
                $body = str_replace($path[0], $new_link, $body);
          
              
            }
        
        }
        $translation['body'] = $body;
    }    
    
    /*
     Decode any html encoding in shortcodes
     http://codex.wordpress.org/Shortcode_API
    */
    function _content_decode_shortcodes(&$translation) {
        $body = $translation['body'];
        
        global $shortcode_tags;
        if (isset($shortcode_tags)) {
            $tag_names = array_keys($shortcode_tags);
        $tag_regexp = join( '|', array_map('preg_quote', $tag_names) );

            $regexp = '/\[('.$tag_regexp.')\b(.*?)\]/s';
            
            if (preg_match_all($regexp, $body, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $body = str_replace($match[0], '[' . $match[1] . html_entity_decode($match[2]) . ']', $body);
                }
            }
            
        }
        
        $translation['body'] = $body;
    }

	/**
	 * get the paths to images in the body of the content
	 *
	 * @param string $body
	 *
	 * @return array
	 */
    function _content_get_image_paths($body) {

      $regexp_links = array(
                          "/<img\ssrc\s*=\s*([\"\']??)([^\"]*)\".*>/siU",
                          "/&lt;script\ssrc\s*=\s*([\"\']??)([^\"]*)\".*>/siU",
                          "/<embed\ssrc\s*=\s*([\"\']??)([^\"]*)\".*>/siU",
                          );

      $links = array();

      foreach($regexp_links as $regexp) {
        if (preg_match_all($regexp, $body, $matches, PREG_SET_ORDER)) {
          foreach ($matches as $match) {
            $links[] = $match;
          }
        }
      }

      return $links;
    }

	/**
	 * Resolve a URL relative to a base path. This happens to work with POSIX
	 * file names as well. This is based on RFC 2396 section 5.2.
	 *
	 * @param string $base
	 * @param string $url
	 *
	 * @return bool|string
	 */
    function resolve_url($base, $url) {
            if (!strlen($base)) return $url;
            // Step 2
            if (!strlen($url)) return $base;
            // Step 3
            if (preg_match('!^[a-z]+:!i', $url)) return $url;
            $base = parse_url($base);
            if ($url{0} == "#") {
                    // Step 2 (fragment)
                    $base['fragment'] = substr($url, 1);
                    return $this->unparse_url($base);
            }
            unset($base['fragment']);
            unset($base['query']);
            if (substr($url, 0, 2) == "//") {
                    // Step 4
                    return $this->unparse_url(array(
                            'scheme'=>$base['scheme'],
                            'path'=>$url,
                    ));
            } else if ($url{0} == "/") {
                    // Step 5
                    $base['path'] = $url;
            } else {
                    // Step 6
                    $path = explode('/', $base['path']);
                    $url_path = explode('/', $url);
                    // Step 6a: drop file from base
                    array_pop($path);
                    // Step 6b, 6c, 6e: append url while removing "." and ".." from
                    // the directory portion
                    $end = array_pop($url_path);
                    foreach ($url_path as $segment) {
                            if ($segment == '.') {
                                    // skip
                            } else if ($segment == '..' && $path && $path[sizeof($path)-1] != '..') {
                                    array_pop($path);
                            } else {
                                    $path[] = $segment;
                            }
                    }
                    // Step 6d, 6f: remove "." and ".." from file portion
                    if ($end == '.') {
                            $path[] = '';
                    } else if ($end == '..' && $path && $path[sizeof($path)-1] != '..') {
                            $path[sizeof($path)-1] = '';
                    } else {
                            $path[] = $end;
                    }
                    // Step 6h
                    $base['path'] = join('/', $path);

            }
            // Step 7
            return $this->unparse_url($base);
    }

    function unparse_url($parsed){
        if (! is_array($parsed)) return false;
        $uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((wpml_mb_strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
        $uri .= isset($parsed['user']) ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
        $uri .= isset($parsed['host']) ? $parsed['host'] : '';
        $uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';
        if(isset($parsed['path']))
            {
            $uri .= (substr($parsed['path'],0,1) == '/')?$parsed['path']:'/'.$parsed['path'];
            }
        $uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
        $uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
        return $uri;
    }

    function _content_fix_relative_link_paths_in_body(&$translation) {
        $body = $translation['body'];
        $link_paths = $this->_content_get_link_paths($body);

        $source_path = get_permalink($translation['original_id']);

        foreach($link_paths as $path) {
          
            if ($path[2][0] != "#"){
                $src_path = $this->resolve_url($source_path, $path[2]);
                if ($src_path != $path[2]) {
                    $search = $path[1] . $path[2] . $path[1];
                    $replace = $path[1] . $src_path . $path[1];
                    $new_link = str_replace($search, $replace, $path[0]);
                    
                    $body = str_replace($path[0], $new_link, $body);
                }
            }      
        }
        $translation['body'] = $body;
    }

    function _content_get_link_paths($body) {
      
        $regexp_links = array(
                            /*"/<a.*?href\s*=\s*([\"\']??)([^\"]*)[\"\']>(.*?)<\/a>/i",*/
                            "/<a[^>]*href\s*=\s*([\"\']??)([^\"^>]+)[\"\']??([^>]*)>/i",
                            );
        
        $links = array();
        
        foreach($regexp_links as $regexp) {
            if (preg_match_all($regexp, $body, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                  $links[] = $match;
                }
            }
        }
        return $links;
    }    
    
    public static function _content_make_links_sticky($element_id, $element_type='post', $string_translation = true) {        
        if(strpos($element_type, 'post') === 0){
            // only need to do it if sticky links is not enabled.
            // create the object
            require_once ICL_PLUGIN_PATH . '/inc/absolute-links/absolute-links.class.php';        
            $icl_abs_links = new AbsoluteLinks;
            $icl_abs_links->process_post($element_id);
        }elseif($element_type=='string'){             
            require_once ICL_PLUGIN_PATH . '/inc/absolute-links/absolute-links.class.php';        
            $icl_abs_links = new AbsoluteLinks; // call just for strings
            $icl_abs_links->process_string($element_id, $string_translation);                                        
        }
    }

    function _content_fix_links_to_translated_content($element_id, $target_lang_code, $element_type='post'){
        global $wpdb, $sitepress, $wp_taxonomies;
        self::_content_make_links_sticky($element_id, $element_type);

		$post = false;
		$body = false;
        if(strpos($element_type, 'post') === 0){
            $post_prepared = $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d", array($element_id));
            $post = $wpdb->get_row($post_prepared);
            $body = $post->post_content;
        }elseif($element_type=='string'){
            $body_prepared = $wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_string_translations WHERE id=%d", array($element_id));
            $body = $wpdb->get_var($body_prepared);
        }
        $new_body = $body;

        $base_url_parts = parse_url(site_url());
        
        $links = $this->_content_get_link_paths($body);
        
        $all_links_fixed = 1;

        $pass_on_query_vars = array();
        $pass_on_fragments = array();

		$all_links_arr = array();

        foreach($links as $link_idx => $link) {
            $path = $link[2];
            $url_parts = parse_url($path);
            
            if(isset($url_parts['fragment'])){
                $pass_on_fragments[$link_idx] = $url_parts['fragment'];
            }
            
            if((!isset($url_parts['host']) or $base_url_parts['host'] == $url_parts['host']) and
                    (!isset($url_parts['scheme']) or $base_url_parts['scheme'] == $url_parts['scheme']) and
                    isset($url_parts['query'])) {
                $query_parts = explode('&', $url_parts['query']);
                
                foreach($query_parts as $query){
                    // find p=id or cat=id or tag=id queries
                    list($key, $value) = explode('=', $query);
                    $translations = NULL;
                    $is_tax = false;
					$kind = false;
					$taxonomy = false;
                    if($key == 'p'){
                        $kind = 'post_' . $wpdb->get_var( $wpdb->prepare("SELECT post_type
																		  FROM {$wpdb->posts}
																		  WHERE ID = %d ",
                                                                         $value));
                    } else if($key == "page_id"){
                        $kind = 'post_page';
                    } else if($key == 'cat' || $key == 'cat_ID'){
                        $kind = 'tax_category';
                        $taxonomy = 'category';
                    } else if($key == 'tag'){
                        $is_tax = true;
                        $taxonomy = 'post_tag';
                        $kind = 'tax_' . $taxonomy;                    
                        $value = $wpdb->get_var($wpdb->prepare("SELECT term_taxonomy_id
																FROM {$wpdb->terms} t
                                                                JOIN {$wpdb->term_taxonomy} x
                                                                  ON t.term_id = x.term_id
                                                                WHERE x.taxonomy = %s
                                                                  AND t.slug = %s", $taxonomy, $value ) );
                    } else {
                        $found = false;
                        foreach($wp_taxonomies as $taxonomy_name => $taxonomy_object){
                            if($taxonomy_object->query_var && $key == $taxonomy_object->query_var){
                                $found = true;
                                $is_tax = true;
                                $kind = 'tax_' . $taxonomy_name;
                                $value = $wpdb->get_var($wpdb->prepare("
                                    SELECT term_taxonomy_id
                                    FROM {$wpdb->terms} t
                                    JOIN {$wpdb->term_taxonomy} x
                                      ON t.term_id = x.term_id
                                    WHERE x.taxonomy = %s
                                      AND t.slug = %s",
                                    $taxonomy_name, $value ));
                                $taxonomy = $taxonomy_name;
                            }                        
                        }
                        if(!$found){
                            $pass_on_query_vars[$link_idx][] = $query;
                            continue;
                        } 
                    }

                    $link_id = (int)$value;  
                    
                    if (!$link_id) {
                        continue;
                    }

                    $trid = $sitepress->get_element_trid($link_id, $kind);
                    if(!$trid){
                        continue;
                    }
                    if($trid !== NULL){
                        $translations = $sitepress->get_element_translations($trid, $kind);
                    }
                    if(isset($translations[$target_lang_code]) && $translations[$target_lang_code]->element_id != null){
                        
                        // use the new translated id in the link path.
                        
                        $translated_id = $translations[$target_lang_code]->element_id;
                        
                        if($is_tax){
                            $translated_id = $wpdb->get_var($wpdb->prepare("SELECT slug
																			FROM {$wpdb->terms} t
																			JOIN {$wpdb->term_taxonomy} x
																				ON t.term_id=x.term_id
																			WHERE x.term_taxonomy_id = %d",
                                                                           $translated_id));
                        }
                        
                        // if absolute links is not on turn into WP permalinks                                                
                        if(empty($GLOBALS['WPML_Sticky_Links'])){
                            ////////
							$replace = false;
                            if(preg_match('#^post_#', $kind)){
                                $replace = get_permalink($translated_id);
                            }elseif(preg_match('#^tax_#', $kind)){
                                if(is_numeric($translated_id)) $translated_id = intval($translated_id);
                                $replace = get_term_link($translated_id, $taxonomy);                                
                            }
                            $new_link = str_replace($link[2], $replace, $link[0]);
                            
                            $replace_link_arr[$link_idx] = array('from'=> $link[2], 'to'=>$replace);
                        }else{
                            $replace = $key . '=' . $translated_id;
							$new_link = $link[0];
							if($replace) {
                            	$new_link = str_replace($query, $replace, $link[0]);
							}
                            
                            $replace_link_arr[$link_idx] = array('from'=> $query, 'to'=>$replace);
                        }
                        
                        // replace the link in the body.                        
                        // $new_body = str_replace($link[0], $new_link, $new_body);
                        $all_links_arr[$link_idx] = array('from'=> $link[0], 'to'=>$new_link);
                        // done in the next loop
                        
                    } else {
                        // translation not found for this.
                        $all_links_fixed = 0;
                    }
                }
            }
                        
        }

		if ( !empty( $replace_link_arr ) ) {
			foreach ( $replace_link_arr as $link_idx => $rep ) {
				$rep_to   = $rep[ 'to' ];
				$fragment = '';

				// if sticky links is not ON, fix query parameters and fragments
				if ( empty( $GLOBALS[ 'WPML_Sticky_Links' ] ) ) {
					if ( !empty( $pass_on_fragments[ $link_idx ] ) ) {
						$fragment = '#' . $pass_on_fragments[ $link_idx ];
					}
					if ( !empty( $pass_on_query_vars[ $link_idx ] ) ) {
						$url_glue = ( strpos( $rep[ 'to' ], '?' ) === false ) ? '?' : '&';
						$rep_to   = $rep[ 'to' ] . $url_glue . join( '&', $pass_on_query_vars[ $link_idx ] );
					}
				}

				$all_links_arr[ $link_idx ][ 'to' ] = str_replace( $rep[ 'to' ], $rep_to . $fragment, $all_links_arr[ $link_idx ][ 'to' ] );

			}
		}
        
        if(!empty($all_links_arr))
        foreach($all_links_arr as $link){
            $new_body = str_replace($link['from'], $link['to'], $new_body);
        }
        
        if ($new_body != $body){
            
            // save changes to the database.
            if(strpos($element_type, 'post') === 0){        
                $wpdb->update($wpdb->posts, array('post_content'=>$new_body), array('ID'=>$element_id));
                
                // save the all links fixed status to the database.
                $icl_element_type = 'post_' . $post->post_type;
                $translation_id = $wpdb->get_var($wpdb->prepare("SELECT translation_id
																 FROM {$wpdb->prefix}icl_translations
																 WHERE element_id=%d
																  AND element_type=%s",
                                                                $element_id,
                                                                $icl_element_type));
	            $q          = "UPDATE {$wpdb->prefix}icl_translation_status SET links_fixed=%s WHERE translation_id=%d";
	            $q_prepared = $wpdb->prepare( $q, array( $all_links_fixed, $translation_id ) );
                $wpdb->query($q_prepared);
                
            }elseif($element_type == 'string'){
                $wpdb->update($wpdb->prefix.'icl_string_translations', array('value'=>$new_body), array('id'=>$element_id));
            }
                    
        }
        
    }
    
    function fix_translated_children($original_id, $translated_id, $lang_code){
        global $wpdb, $sitepress;

        // get the children of of original page.
        $original_children = $wpdb->get_col($wpdb->prepare("SELECT ID
															FROM {$wpdb->posts}
															WHERE post_parent = %d
																AND post_type = 'page'",
                                                           $original_id));
        foreach($original_children as $original_child){
            // See if the child has a translation.
            $trid = $sitepress->get_element_trid($original_child, 'post_page');
            if($trid){
                $translations = $sitepress->get_element_translations($trid, 'post_page');
                if (isset($translations[$lang_code]) && isset($translations[$lang_code]->element_id)){
                    $current_parent = $wpdb->get_var($wpdb->prepare("SELECT post_parent
																	 FROM {$wpdb->posts}
																	 WHERE ID = %d ",
                                                                    $translations[$lang_code]->element_id));
                    if ($current_parent != $translated_id){
						$q = "UPDATE {$wpdb->posts} SET post_parent=%d WHERE ID = %d";
						$q_prepared = $wpdb->prepare($q, array($translated_id, $translations[$lang_code]->element_id) );
                        $wpdb->query($q_prepared);
                    }
                }
            }
        }
    }

    function fix_translated_parent($original_id, $translated_id, $lang_code){
        global $wpdb, $sitepress;

        $original_parent = $wpdb->get_var($wpdb->prepare("SELECT post_parent
														  FROM {$wpdb->posts}
														  WHERE ID = %d
														    AND post_type = 'page'",
                                                         $original_id));
        if ($original_parent){
            $trid = $sitepress->get_element_trid($original_parent, 'post_page');
            if($trid){
                $translations = $sitepress->get_element_translations($trid, 'post_page');
                if (isset($translations[$lang_code])){
                    $current_parent = $wpdb->get_var($wpdb->prepare("SELECT post_parent
																	 FROM {$wpdb->posts}
																	 WHERE ID = %d ",
                                                                    $translated_id));
                    if ($current_parent != $translations[$lang_code]->element_id){
						$q = "UPDATE {$wpdb->posts} SET post_parent=%d WHERE ID = %d";
						$q_prepared = $wpdb->prepare($q, array($translations[$lang_code]->element_id, $translated_id) );
                        $wpdb->query($q_prepared);
                    }
                }
            }
        }
    }

	function throw_exception_for_mysql_errors() {
		global $EZSQL_ERROR, $sitepress_settings;
		if ( isset( $sitepress_settings[ 'troubleshooting_options' ][ 'raise_mysql_errors' ] ) && $sitepress_settings[ 'troubleshooting_options' ][ 'raise_mysql_errors' ] ) {
			if ( !empty( $EZSQL_ERROR ) ) {
				$mysql_errors = array();
				foreach ( $EZSQL_ERROR as $v ) {
					$mysql_errors[ ] = $v[ 'error_str' ] . ' [' . $v[ 'query' ] . ']';
				}
				throw new Exception( join( "\n", $mysql_errors ) );
			}
		}
	}

	function translation_error_handler($error_number, $error_string, $error_file, $error_line){
        switch($error_number){
            case E_ERROR:
            case E_USER_ERROR:
                throw new Exception ($error_string . ' [code:e' . $error_number . '] in '. $error_file . ':' . $error_line);
            case E_WARNING:
            case E_USER_WARNING:
                return true;                
            default:
                return true;
        }
        
    }    
    
    function post_submitbox_start(){
        global $post, $iclTranslationManagement;
        if(empty($post)|| !$post->ID){
            return;
        }
        
        $translations = $iclTranslationManagement->get_element_translations($post->ID, 'post_' . $post->post_type);
        $show_box = 'display:none';
        foreach($translations as $t){
            if($t->element_id == $post->ID){
				return;
            } 
            if($t->status == ICL_TM_COMPLETE && !$t->needs_update){
                $show_box = '';
                break;
            }
        }
        
        echo '<p id="icl_minor_change_box" style="float:left;padding:0;margin:3px;'.$show_box.'">';
        echo '<label><input type="checkbox" name="icl_minor_edit" value="1" style="min-width:15px;" />&nbsp;';
        echo __('Minor edit - don\'t update translation','sitepress');        
        echo '</label>';
        echo '<br clear="all" />';
        echo '</p>';
    }   
    
    public static function estimate_total_word_count($post, $lang_code) {
        return self::estimate_word_count($post, $lang_code) +
            self::estimate_custom_field_word_count($post->ID, $lang_code);
    }

    public static function estimate_word_count($data, $lang_code) {
        $words = 0;
        if(isset($data->post_title)){
            if(in_array($lang_code, self::$__asian_languages)){
                $words += strlen(strip_tags($data->post_title)) / 6;
            } else {
                $words += count(preg_split(
                    '/[\s\/]+/', $data->post_title, 0, PREG_SPLIT_NO_EMPTY));
            }
        }
        if(isset($data->post_content)){
            if(in_array($lang_code, self::$__asian_languages)){
                $words += strlen(strip_tags($data->post_content)) / 6;
            } else {
                $words += count(preg_split(
                    '/[\s\/]+/', strip_tags($data->post_content), 0, PREG_SPLIT_NO_EMPTY));
            }
        }        
        return (int)$words;
    }

	public static function estimate_custom_field_word_count( $post_id, $lang_code ) {
		global $sitepress;

		$tm_settings = $sitepress->get_setting('translation-management');
		$tm_cf_settings = ($tm_settings && isset($tm_settings['custom_fields_translation']) ? $tm_settings['custom_fields_translation'] : false);
		if(!$tm_cf_settings) {
			$tm_cf_settings = array();
		}

		$words         = 0;
		$custom_fields = array();
		foreach ( $tm_cf_settings as $cf => $op ) {
			if ( $op == 2 ) {
				$custom_fields[ ] = $cf;
			}
		}
		foreach ( $custom_fields as $cf ) {
			$custom_fields_value = get_post_meta( $post_id, $cf );
			if ( $custom_fields_value && is_scalar( $custom_fields_value ) ) {
				if ( in_array( $lang_code, self::$__asian_languages ) ) {
					$words += strlen( strip_tags( $custom_fields_value ) ) / 6;
				} else {
					$words += count( preg_split( '/[\s\/]+/', strip_tags( $custom_fields_value ), 0, PREG_SPLIT_NO_EMPTY ) );
				}
			} else {
				foreach ( $custom_fields_value as $custom_fields_value_item ) {
					if ( $custom_fields_value_item && is_scalar( $custom_fields_value_item ) ) {
						if ( in_array( $lang_code, self::$__asian_languages ) ) {
							$words += strlen( strip_tags( $custom_fields_value_item ) ) / 6;
						} else {
							$words += count( preg_split( '/[\s\/]+/', strip_tags( $custom_fields_value_item ), 0, PREG_SPLIT_NO_EMPTY ) );
						}
					}
				}
			}
		}

		return (int) $words;
	}

    function get_total_jobs_in_progress(){
        return $this->get_jobs_in_progress() + $this->get_strings_in_progress();
    }

	function get_jobs_in_progress() {
		global $wpdb;
		$jobs_in_progress_sql      = "SELECT COUNT(*) FROM {$wpdb->prefix}icl_translation_status WHERE status=%d AND translation_service=%s";
		$jobs_in_progress_prepared = $wpdb->prepare( $jobs_in_progress_sql, array(ICL_TM_IN_PROGRESS, TranslationProxy::get_current_service_id()) );
		$jobs_in_progress          = $wpdb->get_var( $jobs_in_progress_prepared );

		return $jobs_in_progress;
	}

	function get_strings_in_progress() {
		global $wpdb;
		$strings_in_progress_snipped = wpml_prepare_in( array( ICL_TM_IN_PROGRESS, ICL_TM_WAITING_FOR_TRANSLATOR ),
		                                                '%d' );
		$strings_in_progress_sql = "	SELECT COUNT(*)
											FROM {$wpdb->prefix}icl_string_translations
											WHERE status IN ({$strings_in_progress_snipped})
												AND translation_service = %d";
		$strings_in_progress_prepared = $wpdb->prepare( $strings_in_progress_sql,
		                                                TranslationProxy::get_current_service_id() );
		$strings_in_progress = $wpdb->get_var( $strings_in_progress_prepared );

		return $strings_in_progress;
	}

	function poll_for_translations( $force = false ) {
		/** @var WPML_String_Translation $WPML_String_Translation */
		global $sitepress, $WPML_String_Translation;

		if ( !$force ) {
			// Limit to once per hour
			$translation_offset = strtotime( current_time( 'mysql' ) ) - @intval( $sitepress->get_setting( 'last_picked_up' ) ) - 3600;
			if ( $translation_offset < 0 || $force ) {
				return 0;
			}
		}

		$project               = TranslationProxy::get_current_project();
		$pending_jobs          = $project->pending_jobs();
		$cancelled_jobs        = $project->cancelled_jobs();

		$results = array(
			'completed'        => 0,
			'cancelled'        => 0,
			'errors'           => 0,
		);

		if ( $this->errors ) {
			$results[ 'errors' ] = $this->errors;
		}

		$posts_need_sync = array();
		if ( $pending_jobs ) {
			foreach ( $pending_jobs as $job ) {
				$ret = $this->download_and_process_translation( $job->id, $job->cms_id );
				if ( $ret ) {
					$results[ 'completed' ] ++;
					$cms_id_parts       = $this->parse_cms_id( $job->cms_id );
					$posts_need_sync[ ] = $cms_id_parts[ 1 ];
				}
			}
		}

		if ( ! empty( $cancelled_jobs ) ) {
			foreach ( $cancelled_jobs as $job ) {
				$ret = false;
				if ( $job->cms_id != "" ) {
					//we have a cms id for post translations
					$ret = $this->cancel_translation( $job->id, $job->cms_id );
					$ret = $ret ? 1 : 0;
				} else {
					//we only have an empty string here for string translations
					if ( isset( $WPML_String_Translation ) ) {
						$ret = isset( $job->id ) ? $WPML_String_Translation->cancel_remote_translation( $job->id ) : false;
					}
				}
				if ( $ret ) {
					$results[ 'cancelled' ] += $ret;
				}
			}
		}

		$sitepress->set_setting( 'last_picked_up', strtotime( current_time( 'mysql' ) ) );
		$sitepress->save_settings();
		$this->enqueue_project_errors( $project );
		do_action('wpml_new_duplicated_terms', $posts_need_sync, false);

		return $results;
	}


	function process_translated_string( $translation_proxy_job_id, $language ) {

		$project     = TranslationProxy::get_current_project( );
		$translation = $project->fetch_translation( $translation_proxy_job_id );
		$translation = apply_filters( 'icl_data_from_pro_translation', $translation );

		$ret = false;

		$xliff = new WPML_TM_xliff();
		$translation = $xliff->get_strings_xliff_translation( $translation );

		if ( $translation ) {
			$ret = icl_translation_add_string_translation( $translation_proxy_job_id, $translation, $language );
			if ( $ret ) {
				$project->update_job( $translation_proxy_job_id );
			}
		}

		return $ret;
	}

	private function add_error( $project_error ) {
		$this->errors[] = $project_error;
	}

	/**
	 * @param $project TranslationProxy_Project
	 */
	protected function enqueue_project_errors( $project ) {
		if ( isset( $project ) && isset( $project->errors ) && $project->errors ) {
			foreach ( $project->errors as $project_error ) {
				$this->add_error( $project_error );
			}
		}
	}

	/**
	 * @param TranslationManagement $iclTranslationManagement
	 */
	private function maybe_init_translation_management( $iclTranslationManagement ) {
		if ( empty( $this->tmg->settings ) ) {
			$iclTranslationManagement->init();
		}
	}

	/**
	 * @param int    $post_id
	 * @param string $post_type
	 * @param string $source_language
	 * @param string $target_language
	 * @param int    $job_id
	 *
	 * @return string
	 */
	private function build_cms_id( $post_id, $post_type, $source_language, $target_language, $job_id ) {
		$cms_id_parts = array( $post_type, $post_id, $source_language, $target_language, $job_id );

		return implode( $this->cms_id_parts_glue, $cms_id_parts );
	}

	/**
	 * @param string $cms_id
	 *
	 * @return array;
	 */
	private function parse_cms_id( $cms_id ) {
		$pattern_elements = array(
			'(.+)',
			'([0-9]+)',
			'(.+)',
			'(.+)',
			'([0-9]+)',
		);
		$pattern = '#' . implode( preg_quote($this->cms_id_parts_glue), $pattern_elements) . '#';
		preg_match( $pattern, $cms_id, $matches );

		$parts = array();
		if($matches && count($matches) > 1) {
			for($i = 1; $i<count($matches); $i++) {
				$parts[] = $matches[$i];
			}
		}

		return $parts;
	}
}
