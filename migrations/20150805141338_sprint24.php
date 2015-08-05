<?php

use Phinx\Migration\AbstractMigration;

// Load all the admin APIs, for convenience
require_once 'wordpress-api.php';
//--

class Sprint24 extends AbstractMigration
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
        update_option( 'template', 'boss' );
        update_option( 'stylesheet', 'boss-child' );

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        wp_upgrade();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        update_option( 'template', 'buddyboss' );
        update_option( 'stylesheet', 'buddyboss-child' );
    }
}
