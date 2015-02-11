<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Invitation
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'BuddyBoss_Invitation_BP_Component' ) ):

	class BuddyBoss_Invitation_BP_Component extends BP_Component
	{
		public function __construct()
		{
			parent::start(
				'invitation',
				__( 'Invitation' , 'buddyboss-invitation' ),
				dirname( __FILE__ )
			);
		}

		public function option( $key )
		{
			return buddyboss_invitation()->option( $key );
		}

		public function setup_globals( $args = array() )
		{
			if (true ) {
				
				//add_action( 'wp_before_admin_bar_render', array($this, 'update_wp_menus'), 99 );
				add_action( 'bp_setup_nav', array($this, 'update_bp_menus'), 100 );
				//add_action( 'bp_setup_nav', array($this, 'bbg_remove_activity_friends_subnav'), 99 );
				//add_filter( 'bp_get_displayed_user_nav_activity', array($this, 'bbg_replace_activity_link') );
			}

			parent::setup_globals();
		}

		/**
		 * RENAME MENU TABS ON PROFILE
		 */
		public function update_bp_menus()
		{
			buddyboss_wall_log('Updating Menus');
			global $bp;

			$domain = (!empty($bp->displayed_user->id)) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;

			$profile_link = $domain . $bp->activity->slug . '/';

			bp_core_new_nav_item( array(
				'name'     => __( 'Invitations', 'buddyboss-invitations' ),
				'slug'     => 'invitations',
				'position' => 100,
			) );
		}
	}
endif;
?>