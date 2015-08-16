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
        update_option( 'buddyboss_panel_hide', '1' );

        // Upgrade WordPress database
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        wp_upgrade();
        //--

        // Set the main menu
        $menu      = wp_get_nav_menu_object( 'logged-in-out' );
        $location  = 'left-panel-menu';
        $locations = get_nav_menu_locations();
        
        $locations[ $location ] = $menu->term_id;

        set_theme_mod( 'nav_menu_locations', $locations );
        //--
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
