<?php
/**
 * Plugin Name: AlphaSSS Group
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss BuddyPress Group customization
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 * Text Domain: alphasss
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use AlphaSSS\Repositories\User;
use AlphaSSS\Helpers\Arr;

load_textdomain( 'alphasss', WP_LANG_DIR . '/plugins/alphasss/alphasss-' . get_locale() . '.mo' );

add_action( 'plugins_loaded', function(){

	global $bp;

	add_filter ('bp_user_can_create_groups', function($can_create, $restricted) {

		global $bp;

		$can_create_group = false;
		if ( User::hasRole('gf') AND ! User::isAdminOfGroup() ) {
			 $can_create_group = true;
		} else if ( bp_is_current_component('groups') AND bp_is_current_action('create') ) {

			$current_step = Arr::get( $bp->action_variables, 1 );

			if ( Arr::get( $bp->action_variables, 0 ) == 'step' ) {
				$can_create_group = true;
			}
		}

		return $can_create_group;
		
	}, 10, 2);

	add_action( 'wp_before_admin_bar_render', function($wp_admin_bar){

		// GF already has own group
		if ( is_user_logged_in() AND User::hasRole('gf') AND User::isAdminOfGroup() ) {

			global $wp_admin_bar;

			// Remove top-bar menu items
			$wp_admin_bar->remove_menu('my-account-groups-memberships');
			$wp_admin_bar->remove_menu('my-account-groups-invites');
			//--

			// Setup the logged in user variables
			$user_domain = bp_loggedin_user_domain();
			$groups_link = trailingslashit( $user_domain . 'groups' );

			// Pending group invites
			$count   = groups_get_invite_count_for_user();
			$title   = _x( 'Groups', 'My Account Groups', 'buddypress' );
			$pending = _x( 'No Pending Invites', 'My Account Groups sub nav', 'buddypress' );

			if ( !empty( $count['total'] ) ) {
				$title   = sprintf( _x( 'Groups <span class="count">%s</span>', 'My Account Groups nav', 'buddypress' ), $count );
				$pending = sprintf( _x( 'Pending Invites <span class="count">%s</span>', 'My Account Groups sub nav', 'buddypress' ), $count );
			}

			// My Groups
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-groups',
				'id'     => 'my-account-groups-memberships',
				'title'  => _x( 'Memberships', 'My Account Groups sub nav', 'buddypress' ),
				'href'   => trailingslashit( $groups_link )
			) );

			// Invitations
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-groups',
				'id'     => 'my-account-groups-invites',
				'title'  => $pending,
				'href'   => trailingslashit( $groups_link . 'invites' )
			) );

			// Add new top-bar menu items
			$wp_admin_bar->add_menu( array(
				'parent'   => 'my-account-groups',
				'id'       => 'my-account-group-created',
				'title'    => _x( 'Group Created', 'My Account Groups sub nav' ,'alphasss' ),
				'position' => 0
			) );
		}

	}, 10, 2 );


	// Pre define group name
	add_action( 'bp_get_new_group_name', function($group_name){

		if (! $group_name) {
			$group_name = sprintf( __( "%s's Group", 'alphasss' ), bp_core_get_user_displayname( bp_loggedin_user_id() ) );
		}

		return $group_name;
	});

	// Make non visible "Edit Group" in admin bar for all users except adminstrators 
	add_action( 'admin_bar_menu', function(){
		global $wp_admin_bar;

		if ( ! User::hasRole( 'administrator' ) ){
			$wp_admin_bar->remove_menu('group-admin');
		}
	}, 100 );

	// Remove "Manage" section for Girlfriend even if Girlfriend is admin of group
	add_action( 'groups_setup_nav', function(){
		global $bp;

		// Is this group page and user don't have role administrator
		if ( bp_is_group() AND ! User::hasRole( 'administrator' ) ) {
			bp_core_remove_subnav_item($bp->groups->current_group->slug, 'admin');
		}
	}, 100 );

	add_action('groups_created_group', function($group_id){

		$group = new BP_Groups_Group($group_id);

		// Customize group options 
		$group->status       = 'private';
		$group->enable_forum = 1;
		$group->save();
		//--

		// Create the initial forum for group
		$forum_id = bbp_insert_forum( array(
			'post_parent'  => bbp_get_group_forums_root_id(),
			'post_title'   => $group->name,
			'post_content' => $group->description,
			'post_status'  => 'private'
		) );

		bbp_add_forum_id_to_group( $group_id, $forum_id );
		bbp_add_group_id_to_forum( $forum_id, $group_id );
	}, 100);
});

/**
* Ovewrite BuddyPress group creation functionality
* @see /plugins/buddypress/bp-groups/bp-groups-actions.php function groups_action_create_group
*/ 
add_action('bp_loaded', function(){

	remove_action('bp_actions', 'groups_action_create_group');
	add_action('bp_actions', function(){
		// If we're not at domain.org/groups/create/ then return false
		if ( !bp_is_groups_component() || !bp_is_current_action( 'create' ) )
			return false;

		if ( !is_user_logged_in() )
			return false;

	 	if ( !bp_user_can_create_groups() ) {
			bp_core_add_message( __( 'Sorry, you are not allowed to create groups.', 'buddypress' ), 'error' );
			bp_core_redirect( bp_get_groups_directory_permalink() );
		}

		$bp = buddypress();

		unset( $bp->groups->group_creation_steps['group-settings'] );
		unset( $bp->groups->group_creation_steps['forum'] );

		// Make sure creation steps are in the right order
		groups_action_sort_creation_steps();

		// If no current step is set, reset everything so we can start a fresh group creation
		$bp->groups->current_create_step = bp_action_variable( 1 );
		if ( !bp_get_groups_current_create_step() ) {
			unset( $bp->groups->current_create_step );
			unset( $bp->groups->completed_create_steps );

			setcookie( 'bp_new_group_id', false, time() - 1000, COOKIEPATH );
			setcookie( 'bp_completed_create_steps', false, time() - 1000, COOKIEPATH );

			$reset_steps = true;
			$keys        = array_keys( $bp->groups->group_creation_steps );
			bp_core_redirect( trailingslashit( bp_get_groups_directory_permalink() . 'create/step/' . array_shift( $keys ) ) );
		}

		// If this is a creation step that is not recognized, just redirect them back to the first screen
		if ( bp_get_groups_current_create_step() && empty( $bp->groups->group_creation_steps[bp_get_groups_current_create_step()] ) ) {
			bp_core_add_message( __('There was an error saving group details. Please try again.', 'buddypress'), 'error' );
			bp_core_redirect( trailingslashit( bp_get_groups_directory_permalink() . 'create' ) );
		}

		// Fetch the currently completed steps variable
		if ( isset( $_COOKIE['bp_completed_create_steps'] ) && !isset( $reset_steps ) )
			$bp->groups->completed_create_steps = json_decode( base64_decode( stripslashes( $_COOKIE['bp_completed_create_steps'] ) ) );

		// Set the ID of the new group, if it has already been created in a previous step
		if ( isset( $_COOKIE['bp_new_group_id'] ) ) {
			$bp->groups->new_group_id = (int) $_COOKIE['bp_new_group_id'];
			$bp->groups->current_group = groups_get_group( array( 'group_id' => $bp->groups->new_group_id ) );

			// Only allow the group creator to continue to edit the new group
			if ( ! bp_is_group_creator( $bp->groups->current_group, bp_loggedin_user_id() ) ) {
				bp_core_add_message( __( 'Only the group creator may continue editing this group.', 'buddypress' ), 'error' );
				bp_core_redirect( trailingslashit( bp_get_groups_directory_permalink() . 'create' ) );
			}
		}

		// If the save, upload or skip button is hit, lets calculate what we need to save
		if ( isset( $_POST['save'] ) ) {

			// Check the nonce
			check_admin_referer( 'groups_create_save_' . bp_get_groups_current_create_step() );

			if ( 'group-details' == bp_get_groups_current_create_step() ) {
				if ( empty( $_POST['group-name'] ) || empty( $_POST['group-desc'] ) || !strlen( trim( $_POST['group-name'] ) ) || !strlen( trim( $_POST['group-desc'] ) ) ) {
					bp_core_add_message( __( 'Please fill in all of the required fields', 'buddypress' ), 'error' );
					bp_core_redirect( trailingslashit( bp_get_groups_directory_permalink() . 'create/step/' . bp_get_groups_current_create_step() ) );
				}

				$new_group_id = isset( $bp->groups->new_group_id ) ? $bp->groups->new_group_id : 0;

				if ( !$bp->groups->new_group_id = groups_create_group( array( 'group_id' => $new_group_id, 'name' => $_POST['group-name'], 'description' => $_POST['group-desc'], 'slug' => groups_check_slug( sanitize_title( esc_attr( $_POST['group-name'] ) ) ), 'date_created' => bp_core_current_time(), 'status' => 'public' ) ) ) {
					bp_core_add_message( __( 'There was an error saving group details. Please try again.', 'buddypress' ), 'error' );
					bp_core_redirect( trailingslashit( bp_get_groups_directory_permalink() . 'create/step/' . bp_get_groups_current_create_step() ) );
				}
			}

			if ( 'group-settings' == bp_get_groups_current_create_step() ) {
				$group_status = 'public';
				$group_enable_forum = 1;

				if ( !isset($_POST['group-show-forum']) ) {
					$group_enable_forum = 0;
				} else {
					// Create the forum if enable_forum = 1
					if ( bp_is_active( 'forums' ) && !groups_get_groupmeta( $bp->groups->new_group_id, 'forum_id' ) ) {
						groups_new_group_forum();
					}
				}

				if ( 'private' == $_POST['group-status'] )
					$group_status = 'private';
				elseif ( 'hidden' == $_POST['group-status'] )
					$group_status = 'hidden';

				if ( !$bp->groups->new_group_id = groups_create_group( array( 'group_id' => $bp->groups->new_group_id, 'status' => $group_status, 'enable_forum' => $group_enable_forum ) ) ) {
					bp_core_add_message( __( 'There was an error saving group details. Please try again.', 'buddypress' ), 'error' );
					bp_core_redirect( trailingslashit( bp_get_groups_directory_permalink() . 'create/step/' . bp_get_groups_current_create_step() ) );
				}

				/**
				 * Filters the allowed invite statuses.
				 *
				 * @since BuddyPress (1.5.0)
				 *
				 * @param array $value Array of statuses allowed.
				 *                     Possible values are 'members,
				 *                     'mods', and 'admins'.
				 */
				$allowed_invite_status = apply_filters( 'groups_allowed_invite_status', array( 'members', 'mods', 'admins' ) );
				$invite_status	       = !empty( $_POST['group-invite-status'] ) && in_array( $_POST['group-invite-status'], (array) $allowed_invite_status ) ? $_POST['group-invite-status'] : 'members';

				groups_update_groupmeta( $bp->groups->new_group_id, 'invite_status', $invite_status );
			}

			if ( 'group-invites' === bp_get_groups_current_create_step() ) {
				if ( ! empty( $_POST['friends'] ) ) {
					foreach ( (array) $_POST['friends'] as $friend ) {
						groups_invite_user( array(
							'user_id'  => $friend,
							'group_id' => $bp->groups->new_group_id,
						) );
					}
				}

				groups_send_invites( bp_loggedin_user_id(), $bp->groups->new_group_id );
			}

			/**
			 * Fires before finalization of group creation and cookies are set.
			 *
			 * This hook is a variable hook dependent on the current step
			 * in the creation process.
			 *
			 * @since BuddyPress (1.1.0)
			 */
			do_action( 'groups_create_group_step_save_' . bp_get_groups_current_create_step() );

			/**
			 * Fires after the group creation step is completed.
			 *
			 * Mostly for clearing cache on a generic action name.
			 *
			 * @since BuddyPress (1.1.0)
			 */
			do_action( 'groups_create_group_step_complete' );

			/**
			 * Once we have successfully saved the details for this step of the creation process
			 * we need to add the current step to the array of completed steps, then update the cookies
			 * holding the information
			 */
			$completed_create_steps = isset( $bp->groups->completed_create_steps ) ? $bp->groups->completed_create_steps : array();
			if ( !in_array( bp_get_groups_current_create_step(), $completed_create_steps ) )
				$bp->groups->completed_create_steps[] = bp_get_groups_current_create_step();

			// Reset cookie info
			setcookie( 'bp_new_group_id', $bp->groups->new_group_id, time()+60*60*24, COOKIEPATH );
			setcookie( 'bp_completed_create_steps', base64_encode( json_encode( $bp->groups->completed_create_steps ) ), time()+60*60*24, COOKIEPATH );

			// If we have completed all steps and hit done on the final step we
			// can redirect to the completed group
			$keys = array_keys( $bp->groups->group_creation_steps );
			if ( count( $bp->groups->completed_create_steps ) == count( $keys ) && bp_get_groups_current_create_step() == array_pop( $keys ) ) {
				unset( $bp->groups->current_create_step );
				unset( $bp->groups->completed_create_steps );

				setcookie( 'bp_new_group_id', false, time() - 3600, COOKIEPATH );
				setcookie( 'bp_completed_create_steps', false, time() - 3600, COOKIEPATH );

				// Once we completed all steps, record the group creation in the activity stream.
				groups_record_activity( array(
					'type' => 'created_group',
					'item_id' => $bp->groups->new_group_id
				) );

				/**
				 * Fires after the group has been successfully created.
				 *
				 * @since BuddyPress (1.1.0)
				 *
				 * @param int $new_group_id ID of the newly created group.
				 */
				do_action( 'groups_group_create_complete', $bp->groups->new_group_id );

				bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) );
			} else {
				/**
				 * Since we don't know what the next step is going to be (any plugin can insert steps)
				 * we need to loop the step array and fetch the next step that way.
				 */
				foreach ( $keys as $key ) {
					if ( $key == bp_get_groups_current_create_step() ) {
						$next = 1;
						continue;
					}

					if ( isset( $next ) ) {
						$next_step = $key;
						break;
					}
				}

				bp_core_redirect( trailingslashit( bp_get_groups_directory_permalink() . 'create/step/' . $next_step ) );
			}
		}

		// Remove invitations
		if ( 'group-invites' === bp_get_groups_current_create_step() && ! empty( $_REQUEST['user_id'] ) && is_numeric( $_REQUEST['user_id'] ) ) {
			if ( ! check_admin_referer( 'groups_invite_uninvite_user' ) ) {
				return false;
			}

			$message = __( 'Invite successfully removed', 'buddypress' );
			$error   = false;

			if( ! groups_uninvite_user( (int) $_REQUEST['user_id'], $bp->groups->new_group_id ) ) {
				$message = __( 'There was an error removing the invite', 'buddypress' );
				$error   = 'error';
			}

			bp_core_add_message( $message, $error );
			bp_core_redirect( trailingslashit( bp_get_groups_directory_permalink() . 'create/step/group-invites' ) );
		}

		// Group avatar is handled separately
		if ( 'group-avatar' == bp_get_groups_current_create_step() && isset( $_POST['upload'] ) ) {
			if ( ! isset( $bp->avatar_admin ) ) {
				$bp->avatar_admin = new stdClass();
			}

			if ( !empty( $_FILES ) && isset( $_POST['upload'] ) ) {
				// Normally we would check a nonce here, but the group save nonce is used instead

				// Pass the file to the avatar upload handler
				if ( bp_core_avatar_handle_upload( $_FILES, 'groups_avatar_upload_dir' ) ) {
					$bp->avatar_admin->step = 'crop-image';

					// Make sure we include the jQuery jCrop file for image cropping
					add_action( 'wp_print_scripts', 'bp_core_add_jquery_cropper' );
				}
			}

			// If the image cropping is done, crop the image and save a full/thumb version
			if ( isset( $_POST['avatar-crop-submit'] ) && isset( $_POST['upload'] ) ) {
				// Normally we would check a nonce here, but the group save nonce is used instead

				if ( !bp_core_avatar_handle_crop( array( 'object' => 'group', 'avatar_dir' => 'group-avatars', 'item_id' => $bp->groups->current_group->id, 'original_file' => $_POST['image_src'], 'crop_x' => $_POST['x'], 'crop_y' => $_POST['y'], 'crop_w' => $_POST['w'], 'crop_h' => $_POST['h'] ) ) )
					bp_core_add_message( __( 'There was an error saving the group profile photo, please try uploading again.', 'buddypress' ), 'error' );
				else
					bp_core_add_message( __( 'The group profile photo was uploaded successfully!', 'buddypress' ) );
			}
		}

		/**
		 * Filters the template to load for the group creation screen.
		 *
		 * @since BuddyPress (1.0.0)
		 *
		 * @param string $value Path to the group creation template to load.
		 */
		bp_core_load_template( apply_filters( 'groups_template_create_group', 'groups/create' ) );
	});

});