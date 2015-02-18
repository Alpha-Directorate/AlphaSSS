<?php
/**
 * Plugin Name: BuddyBoss Members
 * Plugin URI:  http://alphasss.com/
 * Description: BuddyBoss Members
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

add_action( 'bp_directory_members_actions', function(){
	echo bp_get_button( array(
		'id'                => 'request_invitation',
		'component'         => 'members',
		'must_be_logged_in' => false,
		'block_self'        => false,
		'link_href'         => wp_nonce_url( bp_get_group_permalink( $group ) . 'request-invitation', 'groups_request_membership' ),
		'link_text'         => __( 'Request Invitation', 'buddypress' ),
		'link_title'        => __( 'Request Invitation', 'buddypress' ),
		'link_class'        => 'group-button request-invitation',
	) );
} );