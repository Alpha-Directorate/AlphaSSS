<?php

//this function filters to the bbp search function to allow only returns from allowed forums

function pg_has_search_results( $args = '' ) {
	
	global $wp_rewrite;
//start with code as per bbp search !
	/** Defaults **************************************************************/

	$default_post_type = array( bbp_get_forum_post_type(), bbp_get_topic_post_type(), bbp_get_reply_post_type() );

	// Default query args
	$default = array(
		'post_type'           => $default_post_type,         // Forums, topics, and replies
		'posts_per_page'      => bbp_get_replies_per_page(), // This many
		'paged'               => bbp_get_paged(),            // On this page
		'orderby'             => 'date',                     // Sorted by date
		'order'               => 'DESC',                     // Most recent first
		'ignore_sticky_posts' => true,                       // Stickies not supported
		's'                   => bbp_get_search_terms(),     // This is a search
	);

	// What are the default allowed statuses (based on user caps)
	if ( bbp_get_view_all() ) {

		// Default view=all statuses
		$post_statuses = array(
			bbp_get_public_status_id(),
			bbp_get_closed_status_id(),
			bbp_get_spam_status_id(),
			bbp_get_trash_status_id()
		);

		// Add support for private status
		if ( current_user_can( 'read_private_topics' ) ) {
			$post_statuses[] = bbp_get_private_status_id();
		}

		// Join post statuses together
		$default['post_status'] = implode( ',', $post_statuses );

	// Lean on the 'perm' query var value of 'readable' to provide statuses
	} else {
		$default['perm'] = 'readable';
	}
	
	//PRIVATE GROUPS then loop to find allowable results
	//bail from this part if there are no search terms, as otherwise it sorts the whole database and overflows memory
	if (! bbp_get_search_terms() == '' ) {
	//change page default to allow filter against all search results - otherwise allowed posts is only the first page of results ie whatever is in  bbp_get_replies_per_page()
	$default['posts_per_page'] = -1;
	$allowed_posts = private_groups_get_permitted_post_ids(new WP_Query( $default ));
	// Then add allowed forum ids to the default query 
    $default['post__in'] = $allowed_posts;
	if (empty ($allowed_posts )) $default['post__in'] = array(0) ;
	//then set per page back (so that we get the correct pagination )
	$default['posts_per_page'] = bbp_get_replies_per_page();
	
	}
	
	
	
	//then return to bbp search code
	
	/** Setup *****************************************************************/

	// Parse arguments against default values
	$r = bbp_parse_args( $args, $default, 'has_search_results' );

	// Get bbPress
	$bbp = bbpress();

	// Call the query
	if ( ! empty( $r['s'] ) ) {
		$bbp->search_query = new WP_Query( $r );
	}

	// Add pagination values to query object
	$bbp->search_query->posts_per_page = $r['posts_per_page'];
	$bbp->search_query->paged          = $r['paged'];

	// Never home, regardless of what parse_query says
	$bbp->search_query->is_home        = false;

	// Only add pagination is query returned results
	if ( ! empty( $bbp->search_query->found_posts ) && ! empty( $bbp->search_query->posts_per_page ) ) {

		// Array of arguments to add after pagination links
		$add_args = array();

		// If pretty permalinks are enabled, make our pagination pretty
		if ( $wp_rewrite->using_permalinks() ) {

			// Shortcode territory
			if ( is_page() || is_single() ) {
				$base = trailingslashit( get_permalink() );

			// Default search location
			} else {
				$base = trailingslashit( bbp_get_search_results_url() );
			}

			// Add pagination base
			$base = $base . user_trailingslashit( $wp_rewrite->pagination_base . '/%#%/' );

		// Unpretty permalinks
		} else {
			$base = add_query_arg( 'paged', '%#%' );
		}

		// Add args
		if ( bbp_get_view_all() ) {
			$add_args['view'] = 'all';
		}

		// Add pagination to query object
		$bbp->search_query->pagination_links = paginate_links(
			apply_filters( 'bbp_search_results_pagination', array(
				'base'      => $base,
				'format'    => '',
				'total'     => ceil( (int) $bbp->search_query->found_posts / (int) $r['posts_per_page'] ),
				'current'   => (int) $bbp->search_query->paged,
				'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
				'next_text' => is_rtl() ? '&larr;' : '&rarr;',
				'mid_size'  => 1,
				'add_args'  => $add_args, 
			) )
		);

		// Remove first page from pagination
		if ( $wp_rewrite->using_permalinks() ) {
			$bbp->search_query->pagination_links = str_replace( $wp_rewrite->pagination_base . '/1/', '', $bbp->search_query->pagination_links );
		} else {
			$bbp->search_query->pagination_links = str_replace( '&#038;paged=1', '', $bbp->search_query->pagination_links );
		}
	}
	//finally filter to return
	// Return object
	return apply_filters( 'pg_has_search_results', $bbp->search_query->have_posts(), $bbp->search_query );
}

add_filter ('bbp_has_search_results', 'pg_has_search_results') ; 

	