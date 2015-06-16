<?php

/**
 * Plugin Update Checker Library 1.3
 * http://w-shadow.com/
 * 
 * Copyright 2012 Janis Elsts
 * Licensed under the GNU GPL license.
 * http://www.gnu.org/licenses/gpl.html
 */

if ( !class_exists('RoyalSliderUpdater') ):

class RoyalSliderUpdater {

	protected $slug = 'new-royalslider/newroyalslider.php';
	protected $license = '';
	
	public function __construct(){
		if( NewRoyalSliderMain::$purchase_code ) {
			$license = NewRoyalSliderMain::$purchase_code;
			if(strlen($license) > 3) {
				$this->license = $license;
				add_filter('pre_set_site_transient_update_plugins', array(&$this, 'update_plugin_transient'));
				add_filter('plugins_api', array(&$this, 'inject_plugin_info'), 20, 3);
				add_filter( "upgrader_source_selection", array( &$this, "post_install" ), 10, 3 );//upgrader_source_selection
			} 
		}
	}
	public function check_purchase_code($code) {
		if(!$code) {
			return;
		}
		global $wp_version;
		$purchase_code =  get_option('royalslider-purchase-code');
		$result = wp_remote_get('http://upd.dimsemenov.com/new-royalslider.php', array(
			'headers' => array(
				'Accept' => 'application/json'
			),
			'body' => array(
				'action' => 'check_purchase_code',
				'url' => urlencode(get_bloginfo('url')),
				'wp_version' => $wp_version,
				'license' => urlencode($code)
			)

		));

		return $this->_toJson($result);

	}
	public function check_update() {
		
		global $wp_version;
		$result = wp_remote_get('http://upd.dimsemenov.com/new-royalslider.php', array(
			'headers' => array(
				'Accept' => 'application/json'
			),
			'body' => array(
				'action' => 'check_update',
				'url' => urlencode(get_bloginfo('url')),
				'wp_version' => $wp_version,
				'license' => urlencode($this->license)
			)
		));
		return $this->_toJson($result);
	}
	protected function _toJson($result) {
		if( !is_wp_error($result) && isset($result['body']) ) {
			$data =  json_decode($result['body']);
			if(is_object($data)) {
				return $data;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function inject_plugin_info($result, $action = null, $args = null){
		if($action == 'plugin_information' && isset($args->slug)  && ($args->slug == $this->slug) ) {
			$pluginInfo = $this->check_update();
			if(!$pluginInfo) {
				return;
			}
			$pluginInfo->slug = $this->slug;
			$pluginInfo->sections = (array)$pluginInfo->sections;
			$pluginInfo->download_link = $pluginInfo->package;
			return $pluginInfo;
		} else {
			return $result;
		}

	}


	public function update_plugin_transient($transient) {

		if ( empty( $transient->checked ) ) {
		    return $transient;
		}

		$pluginInfo = $this->check_update();

		if(!$pluginInfo) {
			return $transient;
		}

		if(!isset($transient->response)) {
			$transient->response = array();
		}

		if( !isset($pluginInfo->version) ) {
			return $transient;
		}

		$doUpdate = version_compare( $pluginInfo->version, $transient->checked[$this->slug] );

		if($doUpdate) {
			$obj = new stdClass();
		    $obj->slug = $this->slug;
		    $obj->new_version = $pluginInfo->version;//$this->githubAPIResult->tag_name;
		    $obj->url = $pluginInfo->url;
		    $obj->package = $pluginInfo->package;
		    $transient->response[$this->slug] = $obj;
		}
		
	    return $transient;
	}

	// Partly based on GitHub updater https://github.com/afragen/github-updater by Andy Fragen
	public function post_install( $source, $remote_source , $upgrader ) {

		global $wp_filesystem;
		$update = array( 'update-selected', 'update-selected-themes', 'upgrade-theme', 'upgrade-plugin' );
		$plugin_name = 'new-royalslider';

		if ( ! isset( $_GET['action'] ) || ! in_array( $_GET['action'], $update, true ) ) {
			return $source;
		}

		if ( ! isset( $source, $remote_source, $plugin_name ) || false === stristr( basename( $source ), $plugin_name ) ) {
			return $source;
		}

		$path_parts = pathinfo($source);
		$newsource = trailingslashit( $path_parts['dirname'] ) . trailingslashit( 'new-royalslider' );

		$upgrader->skin->feedback(
			sprintf(
				__( 'Renaming %s to %s&#8230;', 'new-royalslider' ),
				'<span class="code">' . basename( $source ) . '</span>',
				'<span class="code">' . basename( $newsource ) . '</span>'
			)
		);
		
		if ( $wp_filesystem->move( $source, $newsource, true ) ) {
			$upgrader->skin->feedback( __( 'Rename successful&#8230;', 'new-royalslider' ) );
			return $newsource;
		}

		$upgrader->skin->feedback( __( 'Unable to rename downloaded archive.', 'new-royalslider' ) );
		return new WP_Error();
	}


}

endif;
