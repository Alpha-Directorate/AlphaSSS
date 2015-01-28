<?php
/*
 * Conditional tag to check if a user can view a specific post, by checking if they can see the forum that it belongs to.  
 * Non-logged in site visitors cannot view posts if groups were selected for the forum they belong to. 
* 	A user cannot view a post if their group role has not been selected in the meta box on the edit forum screen in the dashboard.  
 *  If no groups were selected, all users and site visitors can view the content.
 
 *  Keymasters can always view, and moderators with no group set can view
 *
 * 
 *Some of this code is Justin Tadlocks' from the members plugin - thanks Justin for awesome work ! 
 */
 
 function private_groups_can_user_view_post_id($forum_id) {
	//get user ID
	$user_id = wp_get_current_user()->ID;
	//then check if they can view the forum the post belongs to
    return private_groups_can_user_view_post($user_id, $forum_id);
}

 
function private_groups_can_user_view_post( $user_id, $forum_id = '' ) {
//the $forum_id that needs to be passed to this function is the forum_id that the post belongs to

	/* Assume the user can view the post at this point. */
	$can_view = true;
	
	
	/* Get the groups for the forum */
		$groups = get_post_meta( $forum_id, '_private_group', false );

		/* If we have groups set for this forum let's get to work. */
		if ( !empty( $groups ) && is_array( $groups ) ) {

			/**
			 * Since specific groups exist let's assume the user can't view the post at 
			 * this point.  The rest of this functionality should try to disprove this.
			 */
			$can_view = false;
			
			/* If the user's not logged in, assume it's blocked at this point. */
			if (!is_user_logged_in() ) {
				$can_view = false;
			}
					
			
			/*Check if user is keymaster*/
			if ( bbp_is_user_keymaster()) $can_view = true; 
			//now we'll check if the user is a moderator. 
			else {
			$role = bbp_get_user_role( $user_id );
			$check=get_user_meta( $user_id, 'private_group',true);
			
			//if they are a mod, and they have no forum groups set, then they can moderate and see across all forums
			if ($role == 'bbp_moderator' && (empty($check))) {
				$can_view= true ;		
				//otherwise if they are a moderator with groups set, then they can see (and moderate) only their visible forums 
				//- just as everyone else does
				
				}			
			/* Else, let's check the user's group against the selected group. */
			else {
				
				/* Loop through each group and set $can_view to true if the user has this group. */
				$check=get_user_meta( $user_id, 'private_group',true);
				
				
				foreach ( $groups as $group ) {
				//single group set?
				if ($check==$group ) $can_view = true;
				//multiple group set
				if (strpos($check, '*'.$group.'*') !== FALSE) $can_view = true;
				
						
				}
			}
			}
		}
	

	/* Allow developers to overwrite the final return value. */
	return apply_filters( 'private_groups_can_user_view_post', $can_view, $user_id, $forum_id );
 
}
?>
