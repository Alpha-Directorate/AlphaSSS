<?php

use Phinx\Migration\AbstractMigration;

// Load all the admin APIs, for convenience
require_once 'wordpress-api.php';
//--

class Sprint15 extends AbstractMigration
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
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-donation' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }

        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-credits' ) ) {
            activate_plugin( $plugin->file, '' );
        }

        $id = wp_insert_post( [
            'post_type'     => 'page',
            'post_status'   => 'publish',
            'post_title'    => 'Purchase credits'
        ], true );

        update_post_meta( $id, '_wp_page_template', 'purchase-credits.php' );
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-donation' ) ) {
            activate_plugin( $plugin->file, '' );
        }

        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-credits' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }
    }
}