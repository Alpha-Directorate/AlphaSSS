<?php
//this is only used (as far as I can see) by the 'replies created' part of the user profile
 
add_filter ('bbp_get_user_replies_created', 'pg_get_user_replies_created') ;

function pg_get_user_replies_created( $user_id = 0 ) {

	// Validate user
	$user_id2 = bbp_get_user_id( $user_id );
	$current_user= wp_get_current_user()->ID;
	if ( empty( $user_id ) )
		return false;
		
		if ( bbp_is_user_keymaster()) $limit='n' ; 
		
		if (user_can( $current_user, 'moderate' ) ) {
		$check=get_user_meta( $current_user, 'private_group',true);
		if ($check=='') $limit='n' ;
		}
	if ($limit != 'n') {
	global $wpdb;
	$reply=bbp_get_reply_post_type() ;
	$post_ids=$wpdb->get_col("select ID from $wpdb->posts where post_type = '$reply'") ;
	//check this list against those the user is allowed to see, and create a list of valid ones for the wp_query in bbp_has_topics
	$allowed_posts = check_private_groups_reply_ids($post_ids) ;
	
    }
		
		// The default reply query with allowed topic and reply ids array added
		
		
    		
   	// Try to get the topics
	$query = bbp_has_replies( array(
		'post_type' => bbp_get_reply_post_type(),
		'order'     => 'DESC',
		'author'    => $user_id2,
		'post__in'  => $allowed_posts
	) );
	
return apply_filters( 'pg_get_user_replies_created', $query, $user_id );
}

//the function to check the above !
function check_private_groups_reply_ids($post_ids) {
    
    //Init the Array which will hold our list of allowed posts
    $allowed_posts = array();
    

    //Loop through all the posts
	foreach ( $post_ids as $post_id ) {
		//Get the Forum ID based on Post Type Topic
		$reply=bbp_get_reply_post_type() ;
        $forum_id = private_groups_get_forum_id_from_post_id($post_id, $reply);
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




