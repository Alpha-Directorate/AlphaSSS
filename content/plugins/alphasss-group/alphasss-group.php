<?php
/**
 * Plugin Name: AlphaSSS Group
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss BuddyPress Group customization
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

use AlphaSSS\Repositories\User;

add_filter ('bp_user_can_create_groups', function($can_create, $restricted) {

	$can_create_group = false;

	$current_user = wp_get_current_user();

	if ( User::hasRole('gf') ) {
		 $can_create_group = true;
	}

	return $can_create_group;
	
}, 10, 2);


