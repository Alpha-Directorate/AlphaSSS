<?php

use Phinx\Migration\AbstractMigration;

// Load all the admin APIs, for convenience
require_once 'wordpress-api.php';
//--

class Sprint12 extends AbstractMigration
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
        // Adding GF role
        add_role( 'gf', 'Girlfriend' );

        // Add gf role to elen
        $elen = ( new \WP_CLI\Fetchers\User )->get_check( 'elen' );
        $elen->remove_role( 'member' );
        $elen->add_role( 'gf' );
        //--

        // Add gf role to nadya
        $nadya = ( new \WP_CLI\Fetchers\User )->get_check( 'nadya' );
        $nadya->remove_role( 'member' );
        $nadya->add_role( 'gf' );
        //--

        // Add caps to gf
        $role = get_role( 'gf' );
        $role->add_cap( 'generate_invitation_code' );
        //--

        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-group' ) ) {
            activate_plugin( $plugin->file, '' );
        }
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        // Remove gf role to elen
        $elen = ( new \WP_CLI\Fetchers\User )->get_check( 'elen' );
        $elen->remove_role( 'gf' );
        $elen->add_role( 'member' );
        //--

        // Remove gf role to nadya
        $nadya = ( new \WP_CLI\Fetchers\User )->get_check( 'nadya' );
        $nadya->remove_role( 'gf' );
        $nadya->add_role( 'member' );
        //--

        // Remove caps to gf
        $role = get_role( 'gf' );
        $role->remove_cap( 'generate_invitation_code' );
        //--

        // Removing GF role
        remove_role( 'gf' );

        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-group' ) ) {
            deactivate_plugins( $plugin->file, '' );
        }
    }
}