<?php
/**
 * Plugin Name: Alphasss Top Bar
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss Top Bar
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use AlphaSSS\HTTP\HTTP;

//Loads the plugin's translated strings
load_plugin_textdomain('alphasss-top-bar', false, basename(dirname( __FILE__ )) . '/languages');

add_action( 'plugins_loaded', function(){

	if (is_user_logged_in()) {

		add_action( 'admin_bar_menu', function($wp_admin_bar){

			$user_id      = get_current_user_id();
			$current_user = wp_get_current_user();
			$profile_url  = get_edit_profile_url( $user_id );

			if ( ! $user_id )
				return;

			$wp_admin_bar->add_group( array(
				'parent' => 'my-account',
				'id'     => 'user-actions',
			) );

			$user_info  = get_avatar( $user_id, 64 );
			$user_info .= "<span class='display-name'>{$current_user->display_name}</span>";

			if ( $current_user->display_name !== $current_user->user_login ){
				$user_info .= "<span class='username'>{$current_user->user_login}</span>";
			}

			$wp_admin_bar->add_menu( array(
				'parent' => 'user-actions',
				'id'     => 'user-info',
				'title'  => $user_info,
				'href'   => $profile_url,
				'meta'   => array(
					'tabindex' => -1,
				),
			) );

			// Remove top-bar menu items
			$wp_admin_bar->remove_menu('my-account-buddypress');
			$wp_admin_bar->remove_menu('edit-profile');
			$wp_admin_bar->remove_menu('logout');
			//--

			// Add new top-bar menu items
			$wp_admin_bar->add_menu( array(
				'parent'   => 'user-actions',
				'id'       => 'edit-profile',
				'title'    => __( 'Profile', 'alphasss-top-bar' ),
				'href'     => bp_get_members_component_link( 'profile' ),
				'position' => 1
			) );	

			if ( ! current_user_can('generate_invitation_code')) {
				$wp_admin_bar->add_menu( array(
					'parent'   => 'user-actions',
					'id'       => 'register-profile',
					'title'    => __( 'Register', 'alphasss-top-bar' ),
					'href'     => get_pre_member_register_url(),
					'position' => 2
				) );
			}

			$wp_admin_bar->add_menu( array(
				'parent'   => 'user-actions',
				'id'       => 'logout',
				'title'    => __( 'Log Out', 'alphasss-top-bar' ),
				'href'     => wp_logout_url(),
				'position' => 3
			) );

			if ( current_user_can('generate_invitation_code') ) {
				$wp_admin_bar->add_menu( array(
					'parent'    => 'top-secondary',
					'id'        => 'user-credits-icon',
					'title'     => '<img class="avatar user-78-avatar avatar-26 photo" height="26" width="26"  src="' . plugins_url( 'assets/img/coin_single_gold.png', __FILE__ ) . '">',
				) );

				$wp_admin_bar->add_menu( array(
					'parent'   => 'user-credits-icon',
					'id'       => 'user-credits',
					'title'    => __('Buy Credits'),
					'href'     => str_replace( '/wp', '',  site_url( '/purchase-credits/', HTTP::protocol() ) )
				) );
			}
			//--

		});
	}
});