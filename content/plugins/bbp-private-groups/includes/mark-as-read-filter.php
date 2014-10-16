<?php

//This filter works for Pippin Wilkinson's bbpress-mark-as-read plugin to ensure the correct display of unread topics in the subscriptions area

add_filter('bbp_get_user_unread', 'pg_get_user_unread' );


// This function starts from pippin's plugin function 'bbp_get_user_unread' which it now filters
//then uses private groups to get a list of topics the user can see
//then create an allowed list based on these two
//then passes this through the bbp-has-topics code


//get all unread topic IDs for the specified user
	function pg_get_user_unread( $user_id = 0 ) {

		// Default to the displayed user
		$user_id = bbp_get_user_id( $user_id );
		if ( empty( $user_id ) )
			return false;
		// If user has unread topics, load them
		$read_ids = (string) get_user_meta( $user_id, '_bbp_mar_read_ids', true );
		$read_ids = (array) explode( ',', $read_ids );
		$read_ids = array_filter( $read_ids );
			if ( !empty( $read_ids ) ) {
			//so we have unreads, so need to create a list of unread that the user can see
			//so first we create a list of topics the user can see
			global $wpdb;
			$topic= bbp_get_topic_post_type() ;
			$post_ids=$wpdb->get_col("select ID from $wpdb->posts where post_type = '$topic'") ;
			//check this list against those the user is allowed to see, and create a list of valid ones for the wp_query in bbp_has_topics
			$allowed_posts = check_private_groups_topic_ids($post_ids) ;
			//now we need to take out of that list all read topics for that user
			foreach ($read_ids as $read_id) {
				if (($key = array_search($read_id, $allowed_posts)) !== false) {
				unset($allowed_posts[$key]); }
			}	
	//so now we have an allowed list that has only topics the user can see, but not topics the user has read
	//now we use the code from bbp_has_topics to run the list - we can't call it as PG already filters the original function
		
		global $wp_rewrite;

	/** Defaults **************************************************************/

	// Other defaults
	$default_topic_search  = !empty( $_REQUEST['ts'] ) ? $_REQUEST['ts'] : false;
	$default_show_stickies = (bool) ( bbp_is_single_forum() || bbp_is_topic_archive() ) && ( false === $default_topic_search );
	$default_post_parent   = bbp_is_single_forum() ? bbp_get_forum_id() : 'any';

	// Default argument array
	$default = array(
		'post_type'      => bbp_get_topic_post_type(), // Narrow query down to bbPress topics
		'post_parent'    => $default_post_parent,      // Forum ID
		'meta_key'       => '_bbp_last_active_time',   // Make sure topic has some last activity time
		'orderby'        => 'meta_value',              // 'meta_value', 'author', 'date', 'title', 'modified', 'parent', rand',
		'order'          => 'DESC',                    // 'ASC', 'DESC'
		'posts_per_page' => bbp_get_topics_per_page(), // Topics per page
		'paged'          => bbp_get_paged(),           // Page Number
		's'              => $default_topic_search,     // Topic Search
		'show_stickies'  => $default_show_stickies,    // Ignore sticky topics?
		'max_num_pages'  => false,                     // Maximum number of pages to show
		'post__in'		=> $allowed_posts				// only the allowed posts from above
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

	// Maybe query for topic tags
	if ( bbp_is_topic_tag() ) {
		$default['term']     = bbp_get_topic_tag_slug();
		$default['taxonomy'] = bbp_get_topic_tag_tax_id();
	}

	/** Setup *****************************************************************/

	// Parse arguments against default values
	//stopped to prevent parsing
	//$r = bbp_parse_args( $args, $default, 'has_topics' );

	// Get bbPress
	$bbp = bbpress();

	// Call the query
	//now query the original default
	$bbp->topic_query = new WP_Query( $default);

	// Set post_parent back to 0 if originally set to 'any'
	if ( 'any' === $r['post_parent'] )
		$r['post_parent'] = 0;

	// Limited the number of pages shown
	if ( !empty( $r['max_num_pages'] ) )
		$bbp->topic_query->max_num_pages = $r['max_num_pages'];

	/** Stickies **************************************************************/

	// Put sticky posts at the top of the posts array
	if ( !empty( $r['show_stickies'] ) && $r['paged'] <= 1 ) {

		// Get super stickies and stickies in this forum
		$stickies = bbp_get_super_stickies();

		// Get stickies for current forum
		if ( !empty( $r['post_parent'] ) ) {
			$stickies = array_merge( $stickies, bbp_get_stickies( $r['post_parent'] ) );
		}

		// Remove any duplicate stickies
		$stickies = array_unique( $stickies );

		// We have stickies
		if ( is_array( $stickies ) && !empty( $stickies ) ) {

			// Start the offset at -1 so first sticky is at correct 0 offset
			$sticky_offset = -1;

			// Loop over topics and relocate stickies to the front.
			foreach ( $stickies as $sticky_index => $sticky_ID ) {

				// Get the post offset from the posts array
				$post_offsets = wp_filter_object_list( $bbp->topic_query->posts, array( 'ID' => $sticky_ID ), 'OR', 'ID' );

				// Continue if no post offsets
				if ( empty( $post_offsets ) ) {
					continue;
				}

				// Loop over posts in current query and splice them into position
				foreach ( array_keys( $post_offsets ) as $post_offset ) {
					$sticky_offset++;

					$sticky = $bbp->topic_query->posts[$post_offset];

					// Remove sticky from current position
					array_splice( $bbp->topic_query->posts, $post_offset, 1 );

					// Move to front, after other stickies
					array_splice( $bbp->topic_query->posts, $sticky_offset, 0, array( $sticky ) );

					// Cleanup
					unset( $stickies[$sticky_index] );
					unset( $sticky );
				}

				// Cleanup
				unset( $post_offsets );
			}

			// Cleanup
			unset( $sticky_offset );

			// If any posts have been excluded specifically, Ignore those that are sticky.
			if ( !empty( $stickies ) && !empty( $r['post__not_in'] ) ) {
				$stickies = array_diff( $stickies, $r['post__not_in'] );
			}

			// Fetch sticky posts that weren't in the query results
			if ( !empty( $stickies ) ) {

				// Query to use in get_posts to get sticky posts
				$sticky_query = array(
					'post_type'   => bbp_get_topic_post_type(),
					'post_parent' => 'any',
					'meta_key'    => '_bbp_last_active_time',
					'orderby'     => 'meta_value',
					'order'       => 'DESC',
					'include'     => $stickies
				);

				// Cleanup
				unset( $stickies );

				// Conditionally exclude private/hidden forum ID's
				$exclude_forum_ids = bbp_exclude_forum_ids( 'array' );
				if ( ! empty( $exclude_forum_ids ) ) {
					$sticky_query['post_parent__not_in'] = $exclude_forum_ids;
				}

				// What are the default allowed statuses (based on user caps)
				if ( bbp_get_view_all() ) {
					$sticky_query['post_status'] = $r['post_status'];

				// Lean on the 'perm' query var value of 'readable' to provide statuses
				} else {
					$sticky_query['post_status'] = $r['perm'];
				}

				// Get all stickies
				$sticky_posts = get_posts( $sticky_query );
				if ( !empty( $sticky_posts ) ) {

					// Get a count of the visible stickies
					$sticky_count = count( $sticky_posts );

					// Merge the stickies topics with the query topics .
					$bbp->topic_query->posts       = array_merge( $sticky_posts, $bbp->topic_query->posts );

					// Adjust loop and counts for new sticky positions
					$bbp->topic_query->found_posts = (int) $bbp->topic_query->found_posts + (int) $sticky_count;
					$bbp->topic_query->post_count  = (int) $bbp->topic_query->post_count  + (int) $sticky_count;

					// Cleanup
					unset( $sticky_posts );
				}
			}
		}
	}

	// If no limit to posts per page, set it to the current post_count
	if ( -1 === $r['posts_per_page'] )
		$r['posts_per_page'] = $bbp->topic_query->post_count;

	// Add pagination values to query object
	$bbp->topic_query->posts_per_page = $r['posts_per_page'];
	$bbp->topic_query->paged          = $r['paged'];

	// Only add pagination if query returned results
	if ( ( (int) $bbp->topic_query->post_count || (int) $bbp->topic_query->found_posts ) && (int) $bbp->topic_query->posts_per_page ) {

		// Limit the number of topics shown based on maximum allowed pages
		if ( ( !empty( $r['max_num_pages'] ) ) && $bbp->topic_query->found_posts > $bbp->topic_query->max_num_pages * $bbp->topic_query->post_count )
			$bbp->topic_query->found_posts = $bbp->topic_query->max_num_pages * $bbp->topic_query->post_count;

		// If pretty permalinks are enabled, make our pagination pretty
		if ( $wp_rewrite->using_permalinks() ) {

			// User's topics
			if ( bbp_is_single_user_topics() ) {
				$base = bbp_get_user_topics_created_url( bbp_get_displayed_user_id() );

			// User's favorites
			} elseif ( bbp_is_favorites() ) {
				$base = bbp_get_favorites_permalink( bbp_get_displayed_user_id() );

			// User's subscriptions
			} elseif ( bbp_is_subscriptions() ) {
				$base = bbp_get_subscriptions_permalink( bbp_get_displayed_user_id() );

			// Root profile page
			} elseif ( bbp_is_single_user() ) {
				$base = bbp_get_user_profile_url( bbp_get_displayed_user_id() );

			// View
			} elseif ( bbp_is_single_view() ) {
				$base = bbp_get_view_url();

			// Topic tag
			} elseif ( bbp_is_topic_tag() ) {
				$base = bbp_get_topic_tag_link();

			// Page or single post
			} elseif ( is_page() || is_single() ) {
				$base = get_permalink();

			// Forum archive
			} elseif ( bbp_is_forum_archive() ) {
				$base = bbp_get_forums_url();

			// Topic archive
			} elseif ( bbp_is_topic_archive() ) {
				$base = bbp_get_topics_url();

			// Default
			} else {
				$base = get_permalink( (int) $r['post_parent'] );
			}

			// Use pagination base
			$base = trailingslashit( $base ) . user_trailingslashit( $wp_rewrite->pagination_base . '/%#%/' );

		// Unpretty pagination
		} else {
			$base = add_query_arg( 'paged', '%#%' );
		}

		// Pagination settings with filter
		$bbp_topic_pagination = apply_filters( 'bbp_topic_pagination', array (
			'base'      => $base,
			'format'    => '',
			'total'     => $r['posts_per_page'] === $bbp->topic_query->found_posts ? 1 : ceil( (int) $bbp->topic_query->found_posts / (int) $r['posts_per_page'] ),
			'current'   => (int) $bbp->topic_query->paged,
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
			'mid_size'  => 1
		) );

		// Add pagination to query object
		$bbp->topic_query->pagination_links = paginate_links( $bbp_topic_pagination );

		// Remove first page from pagination
		$bbp->topic_query->pagination_links = str_replace( $wp_rewrite->pagination_base . "/1/'", "'", $bbp->topic_query->pagination_links );
	}

	// Return object
	return apply_filters( 'pg_get_user_unread', $bbp->topic_query->have_posts(), $bbp->topic_query );
}

			
		
		//if no unread 
		return bbp_has_topics(); // default query

	}