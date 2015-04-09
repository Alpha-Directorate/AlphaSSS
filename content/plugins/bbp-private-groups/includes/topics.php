<?php

//version 1.9.2 fixed private groups forums for subscriptions 

add_filter ('bbp_before_has_topics_parse_args', 'pg_has_topics') ;

function pg_has_topics( $args = '' ) {
	//check if being called by subscriptions and if so skip filtering (as you can only subscribe to forums you can already see)
	if(isset($args['post__in']) ){
	return $args ;
	}
	$default_post_parent   = bbp_is_single_forum() ? bbp_get_forum_id() : 'any';
	
	if ($default_post_parent == 'any') {
		if ( bbp_is_user_keymaster()) return $args; 
		$user_id = wp_get_current_user()->ID;
		
		if (user_can( $user_id, 'moderate' ) ) {
		$check=get_user_meta( $user_id, 'private_group',true);
		if ($check=='') return $args;
		}
	
	
	global $wpdb;
	$topic=bbp_get_topic_post_type() ;
	$post_ids=$wpdb->get_col("select ID from $wpdb->posts where post_type = '$topic'") ;
	//check this list against those the user is allowed to see, and create a list of valid ones for the wp_query in bbp_has_topics
	$allowed_posts = check_private_groups_topic_ids($post_ids) ;
	
    $args['post__in'] = $allowed_posts;	
}
return $args;
}


//the function to check the above !
function check_private_groups_topic_ids($post_ids) {
    
    //Init the Array which will hold our list of allowed posts
    $allowed_posts = array();
    

    //Loop through all the posts
	foreach ( $post_ids as $post_id ) {
		//Get the Forum ID based on Post Type Topic
		$topic=bbp_get_topic_post_type() ;
        $forum_id = private_groups_get_forum_id_from_post_id($post_id, $topic);
		//Check if User has permissions to view this Post ID
		//by calling the function that checks if the user can view this forum, and hence this post
        if (private_groups_can_user_view_post_id($forum_id)) {
		
            //User can view this post - add it to the allowed array
            array_push($allowed_posts, $post_id);
        }
}
   
    //Return the list		
    return $allowed_posts;
}

