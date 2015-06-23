<?php

use Phinx\Migration\AbstractMigration;

// Load all the admin APIs, for convenience
require_once 'wordpress-api.php';
//--

class Sprint16 extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
    public function change()
    {

    }
    */

    /**
     * Migrate Up.
     */
    public function up()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-gf-finances' ) ) {
            activate_plugin( $plugin->file, '' );
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-gf-finances' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }
    }
}
