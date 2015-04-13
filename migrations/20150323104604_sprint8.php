<?php

use Phinx\Migration\AbstractMigration;

// Load all the admin APIs, for convenience
require_once 'wordpress-api.php';
//--

class Sprint8 extends AbstractMigration
{
	/**
	* Migrate Up.
	*/
	public function up()
	{
		// Update Home page URI
		$this->execute("UPDATE `wp_postmeta` SET `meta_value`='/' WHERE post_id=32 AND `meta_key` = '_menu_item_url'");
	
		if ( $plugin = (new \WP_CLI\Fetchers\Plugin)->get( 'alphasss-profile' ) ) {
			activate_plugin( $plugin->file, '' );
		}
	}

	/**
	* Migrate Down.
	*/
	public function down()
	{
		// Rollback Home page URI
		$this->execute("UPDATE `wp_postmeta` SET `meta_value`='///alphasss.com' WHERE post_id=32 AND `meta_key` = '_menu_item_url'");
	
		if ( $plugin = (new \WP_CLI\Fetchers\Plugin)->get( 'alphasss-profile' ) ) {
			deactivate_plugins( $plugin->file );
		}
	}
}