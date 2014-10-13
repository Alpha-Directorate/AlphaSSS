<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Wall
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle logging
 *
 * @param  string $msg Log message
 * @return void
 */
function buddyboss_wall_log( $msg )
{
  global $buddyboss_wall;

  // $buddyboss_wall->log[] = $msg;
}

/**
 * Print log at footer
 *
 * @return void
 */
function buddyboss_wall_print_log()
{
  ?>
  <div class="buddyboss-wall-log">
    <pre>
      <?php print_r( $buddyboss_wall->log ); ?>
    </pre>

    <br/><br/>
    <hr/>
  </div>
  <?php
}
// add_action( 'wp_footer', 'buddyboss_wall_print_log' );

/**
 * Check if the current profile a user is on is a friend or not
 *
 * @since BuddyBoss Wall (1.0.0)
 */
function buddyboss_wall_is_my_friend( $id = null )
{
  global $bp, $buddyboss_wall;

  // Return null if we don't know because BuddyPress doesn't
  // exist/isn't activated or the user isn't logged in.
  if ( empty( $bp ) || ! is_user_logged_in() )
    return null;

  if( !bp_is_active('friends') )
	  return null;
  
  // Defaults to checking for if the displayed user is
  // the logged in user's friend if no uder $id is passed.
  if ( $id === null )
  {
    $id = $bp->displayed_user->id;
  }

  return 'is_friend' == BP_Friends_Friendship::check_is_friend( $bp->loggedin_user->id, $id );
}

/**
 * Return array of users who liked an activity item. Relies on
 * the preparation functions and $buddyboss_wall global
 *
 * @param  int    $activity_id [description]
 * @return array              [description]
 */
function buddyboss_wall_get_users_who_liked( $activity_id )
{
  global $buddyboss_wall;

  $like_users = $buddyboss_wall->like_users;
  $likes_to_users = $buddyboss_wall->likes_to_users;

  if ( empty( $like_users ) || empty( $likes_to_users ) )
  {
    return array();
  }

  $users_who_liked = array();
  $user_ids_who_liked = array();

  if ( ! empty( $likes_to_users[ $activity_id ] ) )
  {
    $user_ids_who_liked = (array) $likes_to_users[ $activity_id ];
  }

  if ( ! empty( $user_ids_who_liked ) )
  {
    foreach( $user_ids_who_liked as $user_id )
    {
      $user_data = $like_users[ $user_id ];

      if ( ! empty( $user_data ) )
      {
        $users_who_liked[ $user_id ] = $user_data;
      }
    }
  }

  return $users_who_liked;
}

/**
 * Retrieve user information from liked activity items
 * all at once
 *
 * @since BuddyBoss Wall (1.0.0)
 */
function buddyboss_wall_prepare_user_likes( $activities_template )
{
  global $buddyboss_wall, $wpdb;

  $activities = array();
  $activity_ids = array();

  $user_result = array();
  $users = array();
  $likes_to_users = array();

  // We don't want the logged in user
  $loggedin_user_id = intval( bp_loggedin_user_id() );

  if ( ! empty( $activities_template->activities ) && is_array( $activities_template->activities ) )
  {
    $activities = $activities_template->activities;
  }

  foreach( $activities as $activity )
  {
    if ( ! empty( $activity->id ) && intval( $activity->id ) > 0 )
    {
      $activity_ids[] = intval( $activity->id );
    }
  }

  if ( ! empty( $activity_ids ) )
  {
    $sql  = "SELECT user_id,meta_value FROM {$wpdb->base_prefix}usermeta
            WHERE meta_key = 'bp_favorite_activities'
            AND user_id != $loggedin_user_id
            AND (";
    $sql .= ' meta_value LIKE "%' . implode( '%" OR meta_value LIKE "%', $activity_ids ) . '%" )';

    $query = $wpdb->get_results( $sql );

    $user_ids = array();

    // var_dump( $query );

    // Add user IDs to array for USer Query below and store likes
    if ( ! empty( $query ) )
    {
      foreach( $query as $result )
      {
        $user_ids[] = $result->user_id;
        $user_likes = maybe_unserialize( $result->meta_value );

        // Make sure all activity IDs are integers
        if ( ! empty( $user_likes ) && is_array( $user_likes ) )
        {
          $users[ $result->user_id ]['likes'] = array_map( 'intval', $user_likes );
        }
        else {
          $users[ $result->user_id ]['likes'] = array();
        }
      }
    }

    // Get users tha have liked activities in this loop
    if ( ! empty( $user_ids ) )
    {
      $user_query = bp_core_get_users( array(
        'include' => $user_ids
      ) );

      if ( ! empty( $user_query['users'] ) )
      {
        $user_result = $user_query['users'];
      }
    }
  }

  // Add profile links and display names
  foreach ( $user_result as $user )
  {
    $users[ $user->ID ]['profile'] = bp_core_get_user_domain( $user->ID );
    $users[ $user->ID ]['name']    = $user->display_name;
  }

  $like_activity_ids = array();

  foreach( $users as $user_id => $user_data )
  {
    $liked_activities = $user_data['likes'];

    foreach( $liked_activities as $liked_activity_id )
    {
      if ( empty( $likes_to_users[ $liked_activity_id ] )
           || ! in_array( $user_id, $likes_to_users[ $liked_activity_id ] ) )
      {
        $likes_to_users[ $liked_activity_id ][] = $user_id;
      }
    }
  }

  $buddyboss_wall->like_users = $users;
  $buddyboss_wall->likes_to_users = $likes_to_users;
  // var_dump( $likes_to_users, $users );
}

function buddyboss_wall_refetch_users_who_liked( $activity_id ){
	global $wpdb;
  $user_result = array();
  $users = array();

  // We don't want the logged in user
  $loggedin_user_id = intval( bp_loggedin_user_id() );

	/* @todo: fix this */
	$activity_ids = array($activity_id);
	
    $sql  = "SELECT user_id,meta_value FROM {$wpdb->base_prefix}usermeta
            WHERE meta_key = 'bp_favorite_activities'
            AND user_id != $loggedin_user_id
            AND (";
    $sql .= ' meta_value LIKE "%' . implode( '%" OR meta_value LIKE "%', $activity_ids ) . '%" )';

    $query = $wpdb->get_results( $sql );

    $user_ids = array();

    // var_dump( $query );

    // Add user IDs to array for USer Query below and store likes
    if ( ! empty( $query ) )
    {
      foreach( $query as $result )
      {
        $user_ids[] = $result->user_id;
        $user_likes = maybe_unserialize( $result->meta_value );

        // Make sure all activity IDs are integers
        if ( ! empty( $user_likes ) && is_array( $user_likes ) )
        {
          $users[ $result->user_id ]['likes'] = array_map( 'intval', $user_likes );
        }
        else {
          $users[ $result->user_id ]['likes'] = array();
        }
      }
    }

    // Get users who have liked activities in this loop
    if ( ! empty( $user_ids ) )
    {
      $user_query = bp_core_get_users( array(
        'include' => $user_ids
      ) );

      if ( ! empty( $user_query['users'] ) )
      {
        $user_result = $user_query['users'];
      }
    }

  // Add profile links and display names
  foreach ( $user_result as $user )
  {
    $users[ $user->ID ]['profile'] = bp_core_get_user_domain( $user->ID );
    $users[ $user->ID ]['name']    = $user->display_name;
  }

  return $users;
}

/**
 * Determines if the currently logged in user is an admin
 * TODO: This should check in a better way, by capability not role title and
 * this function probably belongs in a functions.php file or utility.php
 */
function buddyboss_wall_is_admin()
{
	return is_user_logged_in() && current_user_can( 'administrator' );
}
?>