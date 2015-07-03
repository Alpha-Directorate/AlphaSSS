<?php

use Phinx\Migration\AbstractMigration;

// Load all the admin APIs, for convenience
require_once 'wordpress-api.php';
//--

class Sprint17 extends AbstractMigration
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
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'customizer-export-import' ) ) {
            activate_plugin( $plugin->file, '' );
        }

        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-theme-migration' ) ) {
            activate_plugin( $plugin->file, '' );
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'customizer-export-import' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }

        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-theme-migration' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }
    }
}
