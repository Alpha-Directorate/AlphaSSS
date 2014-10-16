<?php

add_action('bbp_template_redirect', 'private_group_enforce_permissions', 1);
add_filter('protected_title_format', 'pg_remove_protected_title');
add_filter('private_title_format', 'pg_remove_private_title');
add_filter('bbp_get_forum_freshness_link', 'custom_freshness_link' );
add_filter('bbp_get_author_link', 'pg_get_author_link' ) ;
add_action ('bbp_user_register', 'pg_role_group') ;



/*
 * Check if the current user has rights to view the given Post ID
 * Much of this code is from the work of Aleksandar Adamovic in his Tehnik BBPress Permissions - thanks !
 * 
 */
 
 
function private_groups_check_can_user_view_post() {
//uses $post_id and $post_type to get the forum ($forum_id) that the post belongs to
    global $wp_query;

    // Get Forum Id for the current post    
    $post_id = $wp_query->post->ID;
    $post_type = $wp_query->get('post_type');
	
	if (bbp_is_topic_super_sticky($post_id)) return true;
	
	
    $forum_id = private_groups_get_forum_id_from_post_id($post_id, $post_type);
//then call the function that checks if the user can view this forum, and hence this post
    if (private_groups_can_user_view_post_id($forum_id))
        return true;
}



/**
 * Use the given query to determine which forums the user has access to. 
 * 
 * returns: an array of post IDs which user has access to.
 */
function private_groups_get_permitted_post_ids($post_query) {
    
    //Init the Array which will hold our list of allowed posts
    $allowed_posts = array();
    

    //Loop through all the posts
    while ($post_query->have_posts()) :
        $post_query->the_post();
		//Get the Post ID and post type
        $post_id = $post_query->post->ID;
		$post_type = $post_query->post->post_type;
        //Get the Forum ID based on Post Type (Reply, Topic, Forum)
        $forum_id = private_groups_get_forum_id_from_post_id($post_id, $post_type);
		//Check if User has permissions to view this Post ID
		//by calling the function that checks if the user can view this forum, and hence this post
        if (private_groups_can_user_view_post_id($forum_id)) {
		
            //User can view this post - add it to the allowed array
            array_push($allowed_posts, $post_id);
        }

    endwhile;

    //Return the list		
    return $allowed_posts;
}




/*
 * Returns the bbPress Forum ID from given Post ID and Post Type
 * 
 * returns: bbPRess Forum ID
 */
function private_groups_get_forum_id_from_post_id($post_id, $post_type) {
    $forum_id = 0;

    // Check post type
    switch ($post_type) {
        // Forum
        case bbp_get_forum_post_type() :
            $forum_id = bbp_get_forum_id($post_id);
            break;

        // Topic
        case bbp_get_topic_post_type() :
            $forum_id = bbp_get_topic_forum_id($post_id);
            break;

        // Reply
        case bbp_get_reply_post_type() :
            $forum_id = bbp_get_reply_forum_id($post_id);
            break;
    }

    return $forum_id;
}

//enforce permission to ensure users only see permitted posts
function private_group_enforce_permissions() {
    global $rpg_settingsf ;
	// Bail if not viewing a bbPress item
    if (!is_bbpress())
        return;

    // Bail if not viewing a single item or if user has caps
    if (!is_singular() || bbp_is_user_keymaster() || current_user_can('read_hidden_forums'))
        return;

    if (!private_groups_check_can_user_view_post()) {
        if (!is_user_logged_in()) {
			if($rpg_settingsf['redirect_page2']) {
				$link=$rpg_settingsf['redirect_page2'] ;
				header( "Location: $link" );
			}
			else {		
				auth_redirect();
			}
		}
		else {
			if($rpg_settingsf['redirect_page1']) {
				$link=$rpg_settingsf['redirect_page1'] ;
				header( "Location: $link" );
			}	
			else {
				bbp_set_404();
			}
  	
		}
	}
}

//removes 'private' and protected prefix for forums
function pg_remove_private_title($title) {
	global $rpg_settingsg ;
	if( $rpg_settingsg['activate_remove_private_prefix']) {
	return '%s';
	}
		else {
		Return $title ;
		}
}

function pg_remove_protected_title($title) {
	global $rpg_settingsg ;
	if( $rpg_settingsg['activate_remove_private_prefix']) {
	return '%s';
	}
		else {
		Return $title ;
		}
}

function custom_freshness_link( $forum_id = 0 ) {
global $rpg_settingsf ;
		$forum_id  = bbp_get_forum_id( $forum_id );
		$active_id = bbp_get_forum_last_active_id( $forum_id );
		$link_url  = $title = '';
		$forum_title= bbp_get_forum_title ($forum_id) ;

		if ( empty( $active_id ) )
			$active_id = bbp_get_forum_last_reply_id( $forum_id );

		if ( empty( $active_id ) )
			$active_id = bbp_get_forum_last_topic_id( $forum_id );

		if ( bbp_is_topic( $active_id ) ) {
			$link_url = bbp_get_forum_last_topic_permalink( $forum_id );
			//$link_id added to get post_id and type to allow for later check
			$link_id= bbp_get_forum_last_topic_id ($forum_id) ;
			$check="topic" ;
			$title    = bbp_get_forum_last_topic_title( $forum_id );
			$forum_id_last_active = bbp_get_topic_forum_id($active_id);
		} elseif ( bbp_is_reply( $active_id ) ) {
			$link_url = bbp_get_forum_last_reply_url( $forum_id );
			//$link-id added to get post-id and type to allow for later check
			$link_id = bbp_get_forum_last_reply_id ( $forum_id );
			$check="reply" ;
			$title    = bbp_get_forum_last_reply_title( $forum_id );
			$forum_id_last_active = bbp_get_reply_forum_id($active_id);
		}

		$time_since = bbp_get_forum_last_active_time( $forum_id );
		

		if ( !empty( $time_since ) && !empty( $link_url ) ) {
			//ADDITIONAL CODE to original bbp_get_forum_freshness_link function
			//test if user can see this post, and post link if they can
			$user_id = wp_get_current_user()->ID;
			//get the forum id for the post - that's the forum ID against which we check to see if it is in a group - no idea what forum group the stuff above produces, suspect post id of when last changed.
			$forum_id_check = private_groups_get_forum_id_from_post_id($link_id, $check);
			//now we can check if the user can view this, and if it's not private
			if (private_groups_can_user_view_post($user_id,$forum_id_check) &&  !bbp_is_forum_private($forum_id_last_active)) {
			$anchor = '<a href="' .esc_url( $link_url) . '" title="' . esc_attr( $title ) . '">' .esc_html( $time_since ) .'</a>';
			}
			//if it is private, then check user can view
			elseif (private_groups_can_user_view_post($user_id,$forum_id_check) && bbp_is_forum_private($forum_id_last_active) && current_user_can( 'read_private_forums' ) ) {
			$anchor = '<a href="' .esc_url( $link_url) . '" title="' . esc_attr( $title ) . '">' .esc_html( $time_since ) .'</a>';
			}
		//else user cannot see post so... 
		else {
			//set up which link to send them to
			if (!is_user_logged_in()) {
			if($rpg_settingsf['redirect_page2']) {
				$link=$rpg_settingsf['redirect_page2'] ;
			}
			else {		
				$link="/wp-login";
			}
			}
			else {
			if($rpg_settingsf['redirect_page1']) {
				$link=$rpg_settingsf['redirect_page1'] ;
							}	
			else {
				$link='/404';
			}
  	
			}
			//now see if there is a freshness message
			if ($rpg_settingsf['set_freshness_message']) {
				$title=$rpg_settingsf['freshness_message'] ;
				//and set up anchor 
				$anchor = '<a href="' . esc_url($link) . '">' .$title. '</a>';
				}
			else{
			$anchor = '<a href="' . esc_url($link) .  '">' .esc_html( $time_since ) .'</a>';
			}
	
			}
		}
				
		else
			$anchor = esc_html__( 'No Topics', 'bbpress' );

		return $anchor;
	}


	
function pg_get_author_link( ) {
$user_id2 = wp_get_current_user()->ID;
	
		// Parse arguments against default values
		$r = bbp_parse_args( $args, array(
			'post_id'    => $post_id,
			'link_title' => '',
			'type'       => 'both',
			'size'       => 14
		), 'pg_get_author_link' );
	
	
	//confirmed topic
	if( bbp_is_topic( $post_id) ) {
	$topic=bbp_get_topic_post_type() ;
	$forum_id_check = private_groups_get_forum_id_from_post_id($post_id, $topic);
		//now we can check if the user can view this
		if (!private_groups_can_user_view_post($user_id2,$forum_id_check)) 
				return;
		return bbp_get_topic_author_link( $r );
			

	// Confirmed reply
	} elseif ( bbp_is_reply( $post_id ) ) {
	$reply=bbp_get_reply_post_type() ;
	$forum_id_check = private_groups_get_forum_id_from_post_id($post_id, $reply);
		//now we can check if the user can view this
		if (!private_groups_can_user_view_post($user_id2,$forum_id_check)) 
		return ;
		return bbp_get_reply_author_link( $r );
		}
		// Neither a reply nor a topic, so could be a revision
		//if it isn't a topic or reply, not sure so better to just not display the author in this case
		//could be revised to look up the post_parent of this post and then churn that round the code above if required return ;
		return ;

}


//This function is added to bbp_user_register which in turn hooks to wordpress user_register.  It checks if the user role has a group set against it, and if so assigns that to the user

function pg_role_group ($user_id) {
$test=get_option ('rpg_roles') ;
//if no roles set, then exit
if (empty ($test)) return ;
//we have roles set, so now cycle through the roles for this user
//$user_id = get_current_user_id(); 
//if ($user_id == 0) return ;  // bail if no user ID
	$roles = get_usermeta( $user_id, 'wp_capabilities',false )  ;
		foreach ($roles as $role=>$value) {
				foreach ($test as $check=>$group){
				if ($role ==  $check ) {
					if ($group != 'no-group') update_user_meta( $user_id, 'private_group', $group); 
					}
				}		
		}
}





?>