<?php

include dirname( __FILE__ ) . '/wpml-string-scanner.class.php';

class WPML_Theme_String_Scanner extends WPML_String_Scanner {

	public function __construct() {
		parent::__construct();
	}

	public function scan( $no_echo ) {

		$this->current_type = 'theme';

		global $sitepress_settings;

		$this->scan_starting( 'theme ' );

		$this->current_path = TEMPLATEPATH;
		$this->scan_theme_files();

		if ( isset( $_POST[ 'icl_load_mo' ] ) && $_POST[ 'icl_load_mo' ] ) {
			$this->add_translations( $sitepress_settings[ 'st' ][ 'theme_localization_domains' ], '' );
		}
		$this->copy_old_translations( $sitepress_settings[ 'st' ][ 'theme_localization_domains' ], 'theme' );
		$this->cleanup_wrong_contexts( );
		

		if ( ! $no_echo ) {
			$this->scan_response();
		}
	}

	private function scan_theme_files( $dir_or_file = false, $recursion = 0 ) {
		require_once WPML_ST_PATH . '/inc/potx.php';
		global $sitepress, $sitepress_settings;

		if ( $dir_or_file === false ) {
			$dir_or_file = $this->current_path;
		}
		$this->add_stat( sprintf( __( 'Scanning theme folder: %s', 'wpml-string-translation' ), $dir_or_file ), true );

		$dh = opendir( $dir_or_file );
		while ( $dh && false !== ( $file = readdir( $dh ) ) ) {
			if ( 0 === strpos( $file, '.' ) ) {
				continue;
			}

			if ( is_dir( $dir_or_file . "/" . $file ) ) {
				$recursion ++;
				$this->add_stat( str_repeat( "\t", $recursion ) . sprintf( __( 'Opening folder: %s', 'wpml-string-translation' ), $dir_or_file . "/" . $file ) );
				$this->scan_theme_files( $dir_or_file . "/" . $file, $recursion );
				$recursion --;
			} elseif ( preg_match( '#(\.php|\.inc)$#i', $file ) ) {
				// THE potx way
				$this->add_stat( str_repeat( "\t", $recursion ) . sprintf( __( 'Scanning file: %s', 'wpml-string-translation' ), $dir_or_file . "/" . $file ) );
				$this->add_scanned_file( $dir_or_file . "/" . $file );
				_potx_process_file( $dir_or_file . "/" . $file, 0, array( $this, 'store_results' ), '_potx_save_version', POTX_API_7 );
			} else {
				$this->add_stat( str_repeat( "\t", $recursion ) . sprintf( __( 'Skipping file: %s', 'wpml-string-translation' ), $dir_or_file . "/" . $file ) );
			}
		}

		if ( $dir_or_file == TEMPLATEPATH && TEMPLATEPATH != STYLESHEETPATH ) {
			$this->scan_theme_files( STYLESHEETPATH );
			$double_scan = false;
		}

		if ( ! $recursion && ( empty( $double_scan ) || ! $double_scan ) ) {
			global $__icl_registered_strings;
			$this->add_stat( __( 'Done scanning files', 'wpml-string-translation' ) );

			$sitepress_settings[ 'st' ][ 'theme_localization_domains' ] = array_keys( $this->get_domains_found() );
			$sitepress->save_settings( $sitepress_settings );
			closedir( $dh );

			$scanned_files = join( '</li><li>', $this->get_scanned_files() );
			$pre_stat      = __( '= Your theme was scanned for texts =', 'wpml-string-translation' ) . '<br />';
			$pre_stat .= __( 'The following files were processed:', 'wpml-string-translation' ) . '<br />';
			$pre_stat .= '<ol style="font-size:10px;"><li>' . $scanned_files;
			$pre_stat .= '</li></ol>';
			$pre_stat .= sprintf( __( 'WPML found %s strings. They were added to the string translation table.', 'wpml-string-translation' ), count( $__icl_registered_strings ) );
			$pre_stat .= '<br /><a href="#" onclick="jQuery(this).next().toggle();return false;">' . __( 'More details', 'wpml-string-translation' ) . '</a>';
			$pre_stat .= '<textarea style="display:none;width:100%;height:150px;font-size:10px;">';
			$this->add_stat( $pre_stat, true );
			$this->add_stat( '</textarea>' );
		}
	}
}