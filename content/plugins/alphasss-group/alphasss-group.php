<?php
/**
 * Plugin Name: AlphaSSS Group
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss BuddyPress Group customization
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 * Text Domain: alphasss-group
 */

use AlphaSSS\Repositories\User;

load_plugin_textdomain( 'alphasss-group', false, basename( dirname( __FILE__ ) ) . '/languages' );

add_action( 'plugins_loaded', function(){

	add_filter ('bp_user_can_create_groups', function($can_create, $restricted) {

		$can_create_group = false;

		if ( User::hasRole('gf') AND ! User::isAdminOfGroup() ) {
			 $can_create_group = true;
		}

		return $can_create_group;
		
	}, 10, 2);

	add_action( 'wp_before_admin_bar_render', function($wp_admin_bar){

		// GF already has own group
		if ( is_user_logged_in() AND User::hasRole('gf') AND User::isAdminOfGroup() ) {

			global $wp_admin_bar;

			// Remove top-bar menu items
			$wp_admin_bar->remove_menu('my-account-groups-memberships');
			$wp_admin_bar->remove_menu('my-account-groups-invites');
			//--

			// Setup the logged in user variables
			$user_domain = bp_loggedin_user_domain();
			$groups_link = trailingslashit( $user_domain . 'groups' );

			// Pending group invites
			$count   = groups_get_invite_count_for_user();
			$title   = _x( 'Groups', 'My Account Groups', 'buddypress' );
			$pending = _x( 'No Pending Invites', 'My Account Groups sub nav', 'buddypress' );

			if ( !empty( $count['total'] ) ) {
				$title   = sprintf( _x( 'Groups <span class="count">%s</span>', 'My Account Groups nav', 'buddypress' ), $count );
				$pending = sprintf( _x( 'Pending Invites <span class="count">%s</span>', 'My Account Groups sub nav', 'buddypress' ), $count );
			}

			// My Groups
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-groups',
				'id'     => 'my-account-groups-memberships',
				'title'  => _x( 'Memberships', 'My Account Groups sub nav', 'buddypress' ),
				'href'   => trailingslashit( $groups_link )
			) );

			// Invitations
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-groups',
				'id'     => 'my-account-groups-invites',
				'title'  => $pending,
				'href'   => trailingslashit( $groups_link . 'invites' )
			) );

			// Add new top-bar menu items
			$wp_admin_bar->add_menu( array(
				'parent'   => 'my-account-groups',
				'id'       => 'my-account-group-created',
				'title'    => _x( 'Group Created', 'My Account Groups sub nav' ,'alphasss-group' ),
				'position' => 0
			) );
		}

	}, 10, 2 );

});




