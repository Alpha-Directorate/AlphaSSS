<?php
/**
 * This adds the meta box for the permissions component.  This allows restriction to bbpress groups
 */
/** 
 */

 
 
add_action( 'admin_menu', 'private_groups_create_meta_box' );

/* Saves the content permissions metabox data to a custom field. */
add_action( 'save_post', 'private_groups_save_meta', 1, 2 );


function private_groups_create_meta_box() {
	add_meta_box( 'forum-group-meta-box', 'Forum Groups', 'private_groups_meta_box', 'forum', 'normal', 'high' );
	//add_meta_box( 'forum-group-meta-box', 'Forum Groups', 'private_groups_meta_box', 'topic', 'normal', 'high' );
	//add_meta_box( 'forum-group-meta-box', 'Forum Groups', 'private_groups_meta_box', 'reply', 'normal', 'high' );
}

/**
 * Controls the display of the content permissions meta box.  This allows users
 * to select groups that should have access to an individual post/page.
 */
 
function private_groups_meta_box( $object, $box ) {
	global $post ?>

	<input type="hidden" name="private_groups_meta_nonce" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />

	<p>
		<label for="groups"><?php _e('<strong>Groups:</strong> Restrict the content to these groups on the front end of the site.  If all boxes are left unchecked, everyone can view the content.', 'private_groups'); ?></label>
	</p>

	<div style="overflow: hidden;">

		<?php

		/* Get the meta*/
		
		$meta = get_post_meta( $post->ID, '_private_group', false );
		global $rpg_groups ;

		/* Loop through each of the available roles. */
		if(empty($rpg_groups)) {
		echo '<b>No groups have yet been set up - go to Dashboard>Settings>bbp Private Groups to set</b>' ; 
		}
		else {
		foreach ( $rpg_groups as $group => $details ) {
			$checked = false;
		
			/* If the role has been selected, make sure it's checked. */
			if ( is_array( $meta ) && in_array( $group, $meta ) )
				$checked = ' checked="checked" '; ?>

			<p style="width: 32%; float: left; margin-right: 0;">
				<label for="group-<?php echo $group; ?>">
					<input type="checkbox" name=<?php echo $group ; ?> id=<?php echo $group ; ?> <?php echo $checked; ?> value="<?php echo $group; ?>" /> 
					<?php echo $group." ".$details ; ?>
				</label>
			</p>
			
		<?php }} ?>

	</div><?php
}

/**
 * Saves the content permissions metabox data to a custom field.
 *
 */
function private_groups_save_meta( $post_id, $post ) {
	global $rpg_groups ;

	/* Only allow users that can edit the current post to submit data. */
	if ( 'post' == $post->post_type && !current_user_can( 'edit_posts', $post_id ) )
		return;

	/* Only allow users that can edit the current page to submit data. */
	elseif ( 'page' == $post->post_type && !current_user_can( 'edit_pages', $post_id ) )
		return;

	/* Don't save if the post is only a revision. */
	if ( 'revision' == $post->post_type )
		return;

	/* Loop through each of the site's available roles. */
	if(!empty($rpg_groups)) {
		foreach ( $rpg_groups as $group => $details ){
	

		/* Get post metadata for the custom field key 'group'. */
		$meta = (array)get_post_meta( $post_id, '_private_group', false );
		      
		/* Check if the group was selected. */
		if ( $_POST[$group] ) {

			/* If selected and already saved, continue looping through the roles and do nothing for this role. */
			if ( in_array( $group, $meta ) )
			continue;

			/* If the group was selected and not already saved, add the group as a new value to the 'group' custom field. */
			else
				add_post_meta( $post_id, '_private_group', $group, false );
		}

		/* If role not selected, delete. */
		else
			delete_post_meta( $post_id, '_private_group', $group );

	} // End loop through site's groups.
}
}
?>