<?php

//version 1.9.2 fixed private groups forums for visibility

// filter the forums so only those viewable by user are shown
add_filter('bbp_before_has_forums_parse_args', 'private_groups_forums', 10, 2);
// filter the sub-forums so only those viewable by user are shown
add_filter('bbp_forum_get_subforums', 'private_groups_get_permitted_subforums', 10, 1);
add_filter( 'bbp_before_forum_get_subforums_parse_args', 'bbp_list_private_groups_subforums' );
//adds descriptions to the sub forums, and sends non-logged in or users who can't view to a sign-up page
add_filter('bbp_list_forums', 'custom_list_forums' );
//restrict the forum display on topic-form to allowed forums
add_filter ('bbp_before_get_dropdown_parse_args', 'pg_forum_dropdown') ;

/**
 * This function filters the list of forums based on the the users group
 * some of this code is based on the work of Aleksandar Adamovic in his Tehnik BBPress Permissions - thanks !
 */

 
 function private_groups_forums ($args) {
 global $rpg_settingsf ;
		//check if being called by subscriptions and if so skip filtering
		if($args['post__in'] ) {
		return $args ;
		}
		//if forums are visible to everyone, then skip filtering
		if (!$rpg_settingsf['set_forum_visibility']) {
		//Get an array of forums which the current user has permissions to view posts in
		global $wpdb;
		$forum=bbp_get_forum_post_type() ;
		$forum_ids=$wpdb->get_col("select ID from $wpdb->posts where post_type = '$forum'") ;
		//check this list against those the user is allowed to see, and create a list of valid ones for the wp_query
		$allowed_posts = private_groups_check_permitted_forums($forum_ids) ;
		// the above generates a list of allowed forums, which is now added to the wp query parameters post__in if set sets which posts are valid to return
		$args['post__in'] = $allowed_posts;
		}
		return $args ;
}
		
	
function private_groups_check_permitted_forums($forum_ids) {
	
		$filtered_forums = array();
		
	
				//Get Current User ID
				$user_id = wp_get_current_user()->ID;
				foreach ($forum_ids as $forum_id) 
					{
																
						//check if user can view this forum (and hence posts in this forum)
						if(private_groups_can_user_view_post($user_id, $forum_id))
							{
							array_push($filtered_forums, $forum_id);
							}
						
					}		
		return (array) $filtered_forums;
	
}



/**
 * This function filters the list of sub-forums based on the the users group
 */
function private_groups_get_permitted_subforums($sub_forums = '') {

//this code is from includes/forums/template bbp_forum_get_subforums and sets up which forums to look in based on user capabilities
// Use passed integer as post_parent
	if ( is_numeric( $args ) )
		$args = array( 'post_parent' => $args );

	// Setup possible post__not_in array
	$post_stati[] = bbp_get_public_status_id();

	// Super admin get whitelisted post statuses
	if ( bbp_is_user_keymaster() ) {
		$post_stati = array( bbp_get_public_status_id(), bbp_get_private_status_id(), bbp_get_hidden_status_id() );

	// Not a keymaster, so check caps
	} else {

		// Check if user can read private forums
		if ( current_user_can( 'read_private_forums' ) ) {
			$post_stati[] = bbp_get_private_status_id();
		}

		// Check if user can read hidden forums
		if ( current_user_can( 'read_hidden_forums' ) ) {
			$post_stati[] = bbp_get_hidden_status_id();
		}
	}

	// Parse arguments against default values
	$r = bbp_parse_args( $args, array(
		'post_parent'         => 0,
		'post_type'           => bbp_get_forum_post_type(),
		'post_status'         => implode( ',', $post_stati ),
		'posts_per_page'      => get_option( '_bbp_forums_per_page', 50 ),
		'orderby'             => 'menu_order title',
		'order'               => 'ASC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true
	), 'forum_get_subforums' );
	$r['post_parent'] = bbp_get_forum_id( $r['post_parent'] );

	// Create a new query for the subforums
	$get_posts = new WP_Query();

	// No forum passed
	$sub_forums = !empty( $r['post_parent'] ) ? $get_posts->query( $r ) : array();
	
	global $rpg_settingsf ;
		//if make forums visible set, then show all public forums
		if ($rpg_settingsf['set_forum_visibility']) {
		return (array) apply_filters( 'pg_forum_get_subforums',$sub_forums, $r );
		}
		
	//Otherwise now we filter this list to exclude those that the user can't see either because not logged in, or forum does not allowed this group

   $filtered_sub_forums = private_groups_get_permitted_forums($sub_forums);
  
    return (array) apply_filters( 'pg_forum_get_subforums',$filtered_sub_forums, $r );
	//}
}

	
/**
 * Use the given query to determine which forums the user has access to. 
 * Return an array of forums which user has permission to access
 */
function private_groups_get_permitted_forums($forum_list) {
	
		$filtered_forums = array();
		
	
				//Get Current User ID
				$user_id = wp_get_current_user()->ID;
				foreach ($forum_list as $forum) 
					{
					$forum_id = $forum->ID;
												
						//check if user can view this forum (and hence posts in this forum)
						if(private_groups_can_user_view_post($user_id, $forum_id))
							{
							array_push($filtered_forums, $forum);
							}
						
					}		
		return (array) $filtered_forums;
	
}

function bbp_list_private_groups_subforums( $args ) {
	// Use passed integer as post_parent
	if ( is_numeric( $args ) )
		$args = array( 'post_parent' => $args );
	// Setup possible post__not_in array
	$post_stati[] = bbp_get_public_status_id();

	// Super admin get whitelisted post statuses
	if ( bbp_is_user_keymaster() ) {
		$post_stati = array( bbp_get_public_status_id(), bbp_get_private_status_id(), bbp_get_hidden_status_id() );

	// Not a keymaster, so check caps
	} else {

		// Check if user can read private forums
		if ( current_user_can( 'read_private_forums' ) ) {
			$post_stati[] = bbp_get_private_status_id();
		}

		// Check if user can read hidden forums
		if ( current_user_can( 'read_hidden_forums' ) ) {
			$post_stati[] = bbp_get_hidden_status_id();
		}
	}
	$args['post_status'] = implode( ',', $post_stati ) ;
	return $args ;
	}
	
//This function adds descriptions to the sub forums, and sends non-logged in or users who can't view to a sign-up page
function custom_list_forums( $args = '' ) {

	// Define used variables
	global $rpg_settingsg ;
	global $rpg_settingsf ;
	$output = $sub_forums = $topic_count = $reply_count = $counts = '';
	$i = 0;
	$count = array();

	// Parse arguments against default values
	$r = bbp_parse_args( $args, array(
		'before'            => '<ul class="bbp-forums-list">',
		'after'             => '</ul>',
		'link_before'       => '<li class="bbp-forum">',
		'link_after'        => '</li>',
		'count_before'      => ' (',
		'count_after'       => ')',
		'count_sep'         => ', ',
		'separator'         => '<br> ',
		'forum_id'          => '',
		'show_topic_count'  => true,
		'show_reply_count'  => true,
	), 'listb_forums' );
	
						
	
	// Loop through forums and create a list
	$sub_forums = bbp_forum_get_subforums( $r['forum_id'] );
	if ( !empty( $sub_forums ) ) {

		// Total count (for separator)
		$total_subs = count( $sub_forums );
		foreach ( $sub_forums as $sub_forum ) {
			$i++; // Separator count

			// Get forum details
			$count     = array();
			$show_sep  = $total_subs > $i ? $r['separator'] : '';
			$permalink = bbp_get_forum_permalink( $sub_forum->ID );
			$title     = bbp_get_forum_title( $sub_forum->ID );
			$content = bbp_get_forum_content($sub_forum->ID) ;
			if($rpg_settingsg['activate_descriptions'] == true) {
			$content = bbp_get_forum_content($sub_forum->ID) ;
					}
					else {
					$content='';
					}
			

			// Show topic count
			if ( !empty( $r['show_topic_count'] ) && !bbp_is_forum_category( $sub_forum->ID ) ) {
				$count['topic'] = bbp_get_forum_topic_count( $sub_forum->ID );
			}

			// Show reply count
			if ( !empty( $r['show_reply_count'] ) && !bbp_is_forum_category( $sub_forum->ID ) ) {
				$count['reply'] = bbp_get_forum_reply_count( $sub_forum->ID );
			}

			// Counts to show
			if ( !empty( $count ) ) {
				$counts = $r['count_before'] . implode( $r['count_sep'], $count ) . $r['count_after'];
			}
			
			if($rpg_settingsg['hide_counts'] == true) {
				$counts='';
			}
			//Build this sub forums link
			if (bbp_is_forum_private($sub_forum->ID)) {
				if (!current_user_can( 'read_private_forums' ) ) {
					if(!$rpg_settingsf['redirect_page']) {
					$link='/home' ;
					}
					else {
					$link=$rpg_settingsf['redirect_page'] ;
					}
					$output .= $r['before'].$r['link_before'] . '<a href="' .$link . '" class="bbp-forum-link">' . $title . $counts . '</a>' . $show_sep . $r['link_after'].'<div class="bbp-forum-content">'.$content.'</div>'.$r['after'];
				}
				else {
				$output .= $r['before'].$r['link_before'] . '<a href="' . esc_url( $permalink ) . '" class="bbp-forum-link">' . $title . $counts . '</a>' . $show_sep . $r['link_after'].'<div class="bbp-forum-content">'.$content.'</div>'.$r['after'];
				}
			}
			else {
			$output .= $r['before'].$r['link_before'] . '<a href="' . esc_url( $permalink ) . '" class="bbp-forum-link">' . $title . $counts . '</a>' . $show_sep . $r['link_after'].'<div class="bbp-forum-content">'.$content.'</div>'.$r['after'];
			}
	}
	 //Output the list
		return $output ;
	
}
}



function pg_forum_dropdown( $args = '' ) {
	//Get an array of forums which the current user has permissions to view 
		global $wpdb;
		$forum=bbp_get_forum_post_type() ;
		if ( bbp_is_user_keymaster()) return $args; 
		
		$user_id = wp_get_current_user()->ID;
		if (user_can( $user_id, 'moderate' ) ) {
		$check=get_user_meta( $user_id, 'private_group',true);
		if ($check=='') return $args;
		}
		
		$post_ids=$wpdb->get_col("select ID from $wpdb->posts where post_type = '$forum'") ;
		//check this list against those the user is allowed to see, and create a list of valid ones for the wp_query
		$allowed_posts = private_groups_get_dropdown_forums($post_ids) ;
	
		// the above generates a list of allowed forums, and we compare this against the original list to create and 'exclude' list
			
    $result=array_diff($post_ids, $allowed_posts) ;
	$args['exclude'] = $result ;
return $args;
}


function private_groups_get_dropdown_forums($forum_list) {
	
		$filtered_forums = array();
		
	
				//Get Current User ID
				$user_id = wp_get_current_user()->ID;
				foreach ($forum_list as $forum) 
					{
												
					//check if user can view this forum (and hence posts in this forum)
					if(private_groups_can_user_view_post($user_id, $forum))
						{
						array_push($filtered_forums, $forum);
							
						}
						
					}				
		return (array) $filtered_forums;
	
}




