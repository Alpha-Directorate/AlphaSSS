<?php
/**
 * @package WordPress
 * @subpackage Alphasss Invitation
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alphasss_Invitation_BP_Component' ) ):

	class Alphasss_Invitation_BP_Component extends BP_Component
	{
		public function __construct()
		{
			parent::start(
				'invitation',
				__( 'Invitation' , 'alphasss-invitation' ),
				dirname( __FILE__ )
			);
		}

		public function option( $key )
		{
			return alphasss_invitation()->option( $key );
		}

		public function setup_globals( $args = array() )
		{
			if (true) {
				
				add_action( 'bp_setup_nav', array($this, 'update_bp_menus'), 100 );
			}

			parent::setup_globals();
		}

		public function setup_actions()
		{
			parent::setup_actions();
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

			$profile_link = $bp->loggedin_user->domain . $bp->activity->slug . '/';

			bp_core_new_nav_item( array(
				'name'                => __( 'Invitations', 'alphasss-invitation' ),
				'slug'                => 'invitations',
				'position'            => 100,
				'screen_function'     => 'alphasss_invitation_screen_grid',
				'default_subnav_slug' => 'invite-someone'
			) );

			$alphasss_invitation_link = $bp->displayed_user->domain . $this->slug . '/';

			bp_core_new_subnav_item( array(
				'name'            => __( 'Invite Someone', 'alphasss-invitation' ),
				'slug'            => 'invite-someone',
				'parent_slug'     => 'invitations',
				'parent_url'      => $alphasss_invitation_link,
				'screen_function' => 'alphasss_invitation_screen_grid',
				'position'        => 10
			) );
		}

		public function setup_admin_bar( $wp_admin_nav = array() ) {
			parent::setup_admin_bar($wp_admin_nav);
		}
	}

	function alphasss_invitation_screen_grid() {
		add_action( 'bp_template_content', function() {
			alphasss_invitation_load_template( 'members/single/alphasss-invitation-index' );
		} );
		bp_core_load_template( apply_filters( 'alphasss_invitation_screen_grid', 'members/single/plugins' ) );
	}

	function alphasss_invitation_load_template($template) {
		$template .= '.php';

		include_once alphasss_invitation()->templates_dir.'/'.$template;
	}
endif;
?>