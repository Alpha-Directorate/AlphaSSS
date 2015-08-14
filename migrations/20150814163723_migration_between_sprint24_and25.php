<?php

use Phinx\Migration\AbstractMigration;

// Load all the admin APIs, for convenience
require_once 'wordpress-api.php';
//--

class MigrationBetweenSprint24And25 extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'sitepress-multilingual-cms' ) ) {
            activate_plugin( $plugin->file, '' );
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'sitepress-multilingual-cms' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }
    }
}
