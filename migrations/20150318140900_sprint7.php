<?php

use Phinx\Migration\AbstractMigration;

/**
 * @todo Move this to the wrapper
 */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/wp/' );
}

define( 'DB_NAME', getenv( 'DB_NAME' ) );
define( 'DB_USER', getenv( 'DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'DB_HOST' ) );
define( 'WP_MEMORY_LIMIT', '96M' );

require_once(ABSPATH . "/wp-load.php");

// Load all the admin APIs, for convenience
require ABSPATH . 'wp-admin/includes/admin.php';
//--

class Sprint7 extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
		if ( $plugin = (new \WP_CLI\Fetchers\Plugin)->get( 'alphasss-top-bar' ) ) {
			activate_plugin( $plugin->file, '' );
		}

		if ( $plugin = (new \WP_CLI\Fetchers\Plugin)->get( 'alphasss-forgot-password' ) ) {
			activate_plugin( $plugin->file, '' );
		}
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
    	if ( $plugin = (new \WP_CLI\Fetchers\Plugin)->get( 'alphasss-top-bar' ) ) {
			deactivate_plugins( $plugin->file );
		}

		if ( $plugin = (new \WP_CLI\Fetchers\Plugin)->get( 'alphasss-forgot-password' ) ) {
			deactivate_plugins( $plugin->file );
		}
    }
}