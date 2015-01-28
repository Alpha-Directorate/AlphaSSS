<?php

//shortcodes

add_shortcode('list-pg-users', 'list_pg_users');  

function list_pg_users ($attr) {
global $rpg_groups ;
$users= get_users() ;
	if ( !empty( $attr['group'] ) )  {
	//we have a group name !
	$content=$attr['group'] ;
	foreach ( $rpg_groups as $group => $details ){
		if ($details == $content) {
		echo '<b>'.$details.'</b>';
		echo '<ul>' ;
				foreach ( $users as $user ) {
				$groupcheck=get_user_meta( $user->ID, 'private_group',true);
				if ($groupcheck == $group) echo '<li>'.esc_html( $user->display_name ).'</li>' ;
				}
		echo '</ul>' ;
		}
	}
	}
	else {
	// we don't have a group name, so show all !
		foreach ( $rpg_groups as $group => $details ){
		if ($details == '') $details = $group.' - no name' ;
		echo '<b>'.$details.'</b>';
		echo '<ul>' ;
			foreach ( $users as $user ) {
			$groupcheck=get_user_meta( $user->ID, 'private_group',true);
			if ($groupcheck == $group) echo '<li>'.esc_html( $user->display_name ).'</li>' ;
			}
		echo '</ul>' ;
		}
	}
}
