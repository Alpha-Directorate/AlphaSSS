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
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'loco-translate' ) ) {
            activate_plugin( $plugin->file, '' );
        }

        // Upgrade WordPress database
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        wp_upgrade();
        //--
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'loco-translate' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }

        // Upgrade WordPress database
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        wp_upgrade();
        //--
    }
}
