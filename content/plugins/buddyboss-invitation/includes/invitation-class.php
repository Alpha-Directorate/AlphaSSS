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

		public function setup_actions()
		{
			// Add body class
			//add_filter( 'body_class', array( $this, 'body_class' ) );

			parent::setup_actions();
		}

		public function body_class()
		{
			//$classes[] = apply_filters( 'buddyboss_invitation_body_class', 'buddyboss-invitation' );
			//return $classes;
		}

		/**
		 * Method add new intem in profile menu
		 */
		public function update_bp_menus()
		{
			global $bp;

			if ( 
				! is_user_logged_in() || 
				! current_user_can('generate_invitation_code') || 
				( $bp->displayed_user->domain != $bp->loggedin_user->domain ) ) return;

			buddyboss_wall_log('Updating Menus');

			$profile_link = $bp->loggedin_user->domain . $bp->activity->slug . '/';

			bp_core_new_nav_item( array(
				'name'                => __( 'Invitations', 'buddyboss-invitation' ),
				'slug'                => 'invitations',
				'position'            => 100,
				'screen_function'     => 'buddyboss_invitation_screen_grid',
				'default_subnav_slug' => 'my-invitations'
			) );

			$buddyboss_invitation_link = $bp->displayed_user->domain . $this->slug . '/';

			bp_core_new_subnav_item( array(
				'name'            => __( 'Invitations', 'buddyboss-invitation' ),
				'slug'            => 'invitations',
				'parent_slug'     => $this->slug,
				'parent_url'      => $buddyboss_invitation_link,
				'screen_function' => 'buddyboss_invitation_screen_grid',
				'position'        => 10
			) );
		}
	}

	function buddyboss_invitation_screen_grid() {
		add_action( 'bp_template_content', function() {
			buddyboss_invitation_load_template( 'members/single/buddyboss-invitation-index' );
		} );
		bp_core_load_template( apply_filters( 'buddyboss_invitation_screen_grid', 'members/single/plugins' ) );
	}

	function buddyboss_invitation_load_template($template) {
		$template .= '.php';

		include_once buddyboss_invitation()->templates_dir.'/'.$template;
	}
endif;
?>