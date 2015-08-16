<?php
/**
 * @package wpml-core
 * @subpackage wpml-core
 */
require_once WPML_TM_PATH . '/inc/translation-proxy/interfaces/TranslationProxy_Api_Interface.php';

if ( !class_exists( 'TranslationProxy_Api_Error' ) ) {
	class TranslationProxy_Api_Error extends Exception {
		
		public function __construct( $message ) {
			TranslationProxy_Com_Log::log_error( $message );
			
			parent::__construct( $message );
		}
	}
}

if ( !class_exists( 'TranslationProxy_Api' ) ) {
	class TranslationProxy_Api implements TranslationProxy_Api_Interface {
		const API_VERSION = 1.1;

		public static function proxy_request( $path, $params = array(), $method = 'GET', $multi_part = false, $has_return_value = true ) {
			$url = OTG_TRANSLATION_PROXY_URL . $path;

			return self::_send_request( $url, $params, $method, $multi_part, false, $has_return_value );
		}

		public static function proxy_download( $path, $params ) {
			$url = OTG_TRANSLATION_PROXY_URL . $path;

			return self::_send_request( $url, $params, 'GET', false, true, true, false );
		}

		public static function service_request( $url, $params = array(), $method = 'GET', $multi_part = false, $has_return_value = true, $json_response = false, $has_api_response = false ) {
			return self::_send_request( $url, $params, $method, $multi_part, false, $has_return_value, $json_response, $has_api_response );
		}

		protected static function _send_request( $url, $params = array(), $method = 'GET', $multi_part, $gzencoded = false, $has_return_value = true, $json_response = true, $has_api_response = true ) {
			$response = null;
			$method   = strtoupper( $method );

			if ( $params ) {
				$url = self::add_parameters_to_url( $url, $params );
				if ( $method == 'GET' ) {
					$url .= '?' . http_build_query( $params );
				}
			}
			if(!isset($params[ 'api_version' ]) || !$params[ 'api_version' ]) {
				$params[ 'api_version' ] = self::API_VERSION;
			}

			TranslationProxy_Com_Log::log_call( $url, $params, $method, $multi_part);
			$api_response = self::_call_remote_api( $url, $params, $method, $multi_part, $has_return_value );

			if ( $gzencoded ) {
				try {
//					set_error_handler('icl_handleError');
					$gzdecoded_response = @gzdecode( $api_response );
//					restore_error_handler();
					if(!$gzdecoded_response) {
						throw new TranslationProxy_Api_Error('gzdecode() returned an empty value. api_response: ' . print_r($api_response, true),0);
					} else {
						$api_response = $gzdecoded_response;
					}
				} catch ( Exception $e ) {
					throw new TranslationProxy_Api_Error('gzdecode() failed. api_response: ' . print_r($api_response, true),0);
				}
			}

			if ($json_response) {
				TranslationProxy_Com_Log::log_response( $api_response );
			} else {
				TranslationProxy_Com_Log::log_response( 'XLIFF received' );
			}
			
			if ( $has_return_value ) {
				if ( $json_response ) {
					$response = json_decode( $api_response );
					if($has_api_response) {
						$response = self::_get_api_response( $response );
					}
				} else {
					$response = $api_response;
				}
			}

			return $response;
		}

		/**
		 * @param string $url
		 * @param array  $params
		 * @param string $method
		 * @param bool   $multipart
		 * @param bool   $has_return_value
		 *
		 * @throws TranslationProxy_Api_Error
		 *
		 * @return null|string
		 */
		protected static function _call_remote_api( $url, $params, $method, $multipart, $has_return_value = true ) {
			$response = null;
			$context  = self::_get_stream_context( $params, $method, $multipart );
			$response = @file_get_contents( $url, false, $context );
			if ( $has_return_value && $response === false ) {
				throw new TranslationProxy_Api_Error( "Cannot communicate with the remote service" );
			}

			return $response;
		}

		protected static function _get_stream_context( $params, $method, $multipart ) {
			if ( $multipart ) {
				list( $header, $content ) = self::_prepare_multipart_request( $params );
			} else {
				$content = wp_json_encode( $params );
				$header  = 'Content-type: application/json';
			}
			$options = array(
					'http' => array(
							'method'  => $method,
							'content' => $content,
							'header'  => $header
					),
					'ssl' => array(
						'verify_peer' => false,
					)
			);

			return stream_context_create( $options );
		}

		public static function add_parameters_to_url( $url, $params ) {
			if ( preg_match_all( '/\{.+?\}/', $url, $symbs ) ) {
				foreach ( $symbs[ 0 ] as $symb ) {
					$without_braces = preg_replace( '/\{|\}/', '', $symb );
					if ( preg_match_all( '/\w+/', $without_braces, $indexes ) ) {
						try {
							foreach ( $indexes[ 0 ] as $index ) {
								if ( isset( $params[ $index ] ) ) {
									$value = $params[ $index ];
									$url   = preg_replace( preg_quote( "/$symb/" ), $value, $url );
								}
							}
						} catch ( Exception $e ) {
							throw new InvalidArgumentException( 'Invalid parameter in URL' );
						}
					}
				}
			}

			return $url;
		}

		protected static function _get_api_response( $response ) {
			if ( !$response || !isset( $response->status->code ) ) {
				throw new TranslationProxy_Api_Error( "Cannot communicate with the remote service" );
			}
			if ( $response->status->code != 0 ) {
				throw new TranslationProxy_Api_Error( $response->status->message, $response->status->code );
			}

			return $response->response;
		}

		protected static function _prepare_multipart_request( $params ) {
			$boundary = '----' . microtime( true );
			$header   = "Content-Type: multipart/form-data; boundary=$boundary";
			$content  = self::_add_multipart_contents( $boundary, $params );
			$content .= "--$boundary--\r\n";

			return array( $header, $content );
		}

		protected static function _add_multipart_contents( $boundary, $params, $context = array() ) {
			$initial_context = $context;
			$content         = '';

			foreach ( $params as $key => $value ) {
				$context    = $initial_context;
				$context[ ] = $key;

				if ( is_array( $value ) ) {
					$content .= self::_add_multipart_contents( $boundary, $value, $context );
				} else {
//					if(is_array($value)) {
						$pieces = array_slice( $context, 1 );
						if($pieces) {
							$name = "{$context[0]}[" . implode( "][", $pieces ) . "]";
						} else {
							$name = "{$context[0]}";
						}

//					} else {
//						$name = "{$context[0]}";
//					}
					$content .= "--$boundary\r\n" . "Content-Disposition: form-data; name=\"$name\"";

					if ( is_resource( $value ) ) {
						$filename = self::_get_file_name( $params, $key );
						$content .= "; filename=\"$filename\"\r\n" . "Content-Type: application/octet-stream\r\n\r\n" . gzencode( stream_get_contents( $value ) ) . "\r\n";
					} else {
						$content .= "\r\n\r\n$value\r\n";
					}
				}
			}

			return $content;
		}

		protected static function  parse_params( &$json_data, &$binary_data ) {
			foreach ( $json_data as $key => $value ) {
				if ( is_resource( $value ) ) {
					$binary_data[ $key ] = $value;
					unset( $json_data[ $key ] );
				} elseif ( is_array( $value ) ) {
					self::parse_params( $value, $binary_data );
					$json_data[ $key ] = $value;
				}
			}
		}

		protected static function _get_file_name( $params, $default = 'file' ){

			$title =  isset( $params['title'] ) ? sanitize_title_with_dashes( strtolower( filter_var( $params['title'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH ) ) ) : '';
			if ( str_replace( array('-', '_'), '', $title ) == '' ){
				$title = $default;
			}
			$source_language = isset( $params['source_language'] ) ?  $params['source_language'] : '';
			$target_language = isset( $params['target_language'] ) ?  $params['target_language'] : '';

			$filename = implode( '-', array_filter( array( $title, $source_language, $target_language) ) );

			return $filename.".xliff.gz";
		}
	}

	if ( !function_exists( "gzdecode" ) ) {
		/**
		 * Inflates a string enriched with gzip headers. Counterpart to gzencode().
		 * Extracted from upgradephp
		 * http://include-once.org/p/upgradephp/
		 *
		 * officially available by default in php @since 5.4.
		 */
		function gzdecode( $gzdata, $maxlen = null ) {

			#-- decode header
			$len = strlen( $gzdata );
			if ( $len < 20 ) {
				return;
			}
			$head = substr( $gzdata, 0, 10 );
			$head = unpack( "n1id/C1cm/C1flg/V1mtime/C1xfl/C1os", $head );
			list( $ID, $CM, $FLG, $MTIME, $XFL, $OS ) = array_values( $head );
			$FTEXT    = 1 << 0;
			$FHCRC    = 1 << 1;
			$FEXTRA   = 1 << 2;
			$FNAME    = 1 << 3;
			$FCOMMENT = 1 << 4;
			$head     = unpack( "V1crc/V1isize", substr( $gzdata, $len - 8, 8 ) );
			list( $CRC32, $ISIZE ) = array_values( $head );

			#-- check gzip stream identifier
			if ( $ID != 0x1f8b ) {
				trigger_error( "gzdecode: not in gzip format", E_USER_WARNING );

				return;
			}
			#-- check for deflate algorithm
			if ( $CM != 8 ) {
				trigger_error( "gzdecode: cannot decode anything but deflated streams", E_USER_WARNING );

				return;
			}

			#-- start of data, skip bonus fields
			$s = 10;
			if ( $FLG & $FEXTRA ) {
				$s += $XFL;
			}
			if ( $FLG & $FNAME ) {
				$s = strpos( $gzdata, "\000", $s ) + 1;
			}
			if ( $FLG & $FCOMMENT ) {
				$s = strpos( $gzdata, "\000", $s ) + 1;
			}
			if ( $FLG & $FHCRC ) {
				$s += 2; // cannot check
			}

			#-- get data, uncompress
			$gzdata = substr( $gzdata, $s, $len - $s );
			if ( $maxlen ) {
				$gzdata = gzinflate( $gzdata, $maxlen );

				return ( $gzdata ); // no checks(?!)
			} else {
				$gzdata = gzinflate( $gzdata );
			}

			#-- check+fin
			$chk = crc32( $gzdata );
			if ( $CRC32 != $chk ) {
				trigger_error( "gzdecode: checksum failed (real$chk != comp$CRC32)", E_USER_WARNING );
			} elseif ( $ISIZE != strlen( $gzdata ) ) {
				trigger_error( "gzdecode: stream size mismatch", E_USER_WARNING );
			} else {
				return ( $gzdata );
			}
		}
	}
}
