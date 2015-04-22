<?php

use Phinx\Migration\AbstractMigration;

// Load all the admin APIs, for convenience
require_once 'wordpress-api.php';
//--

class Sprint11 extends AbstractMigration
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
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-login' ) ) {
            activate_plugin( $plugin->file, '' );
        }

        // Enable group creation for members
        $this->execute( 'UPDATE `wp_options` SET `option_value` = 0 WHERE `option_name`="bp_restrict_group_creation"' );

        // Add member role to administrator
        $user = ( new \WP_CLI\Fetchers\User )->get_check( 'Founder_Counselor' );

        $user->remove_role( 'pre_member' );
        $user->add_role( 'member' );
        //--

        // Change owner of test group
        $this->execute( 'UPDATE `wp_bp_groups` SET `creator_id` = 78 WHERE `id`=1' );
        $this->execute( 'UPDATE `wp_bp_groups_members` SET `user_id` = 78 WHERE `id`=1' );
        //--
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        if ( $plugin = ( new \WP_CLI\Fetchers\Plugin )->get( 'alphasss-login' ) ) {
            deactivate_plugins( $plugin->file );
        }

        // Disable group creation for members
        $this->execute( 'UPDATE `wp_options` SET `option_value` = 1 WHERE `option_name`="bp_restrict_group_creation"' );

        // Change owner of test group
        $this->execute( 'UPDATE `wp_bp_groups` SET `creator_id` = 1 WHERE `id`=1' );
        $this->execute( 'UPDATE `wp_bp_groups_members` SET `user_id` = 1 WHERE `id`=1' );
        //--
    }
}