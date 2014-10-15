<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Media
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// class BuddyBoss_Media_Activity_Group_Filter implements BuddyBoss_Media_Group_Filter
// {
// }

class BuddyBoss_Media_Photo_Hooks
{
  // Fires when the activity item is saved
  public function bp_activity_after_save( &$activity )
  {
    global $buddyboss_media, $bp;

    $user = $bp->loggedin_user;
    $new_action = $result = false;

    $compat_class_search = ( strstr( $activity->content, 'class="buddyboss-media-photo-link"' ) !== false
                             || strstr( $activity->content, 'class="buddyboss-pics-photo-link"' ) !== false
                             || strstr( $activity->content, 'class="buddyboss-pics-picture-link"' ) !== false );

    if ( $user && $compat_class_search && isset($_POST['has_pic'])
         && isset($_POST['has_pic']['attachment_id']) )
    {
      $action  = '<a href="'.$user->domain.'">'.$user->fullname.'</a> '
        . __( 'posted a new picture', 'buddyboss-media' );

      $attachment_id = (int)$_POST['has_pic']['attachment_id'];

      $action_key = buddyboss_media_compat( 'activity.action_key' );
      $item_key = buddyboss_media_compat( 'activity.item_key' );

      bp_activity_update_meta( $activity->id, $action_key, $action );
      bp_activity_update_meta( $activity->id, $item_key, $attachment_id );

      // Execute our after save action
      do_action( 'buddyboss_media_photo_posted', $activity, $attachment_id, $action );

      // Prevent BuddyPress from sending notifications, we'll send our own
    }
  }
  // Filter's the activity item's action text
  public function bp_get_activity_action( $action )
  {
    global $activities_template;

    $current_activity = $activities_template->current_activity;

    $current_activity_id = $activities_template->activities[ $current_activity ]->id;

    $buddyboss_media_action = buddyboss_media_compat_get_meta( $current_activity_id, 'activity.action_keys' );

    if ( $buddyboss_media_action )
    {
      $with_meta = $buddyboss_media_action  . ' <a class="activity-time-since"><span class="time-since">' . bp_core_time_since( bp_get_activity_date_recorded() ) . '</span></a>';

      if ( $with_meta )
        return $with_meta;

      return $buddyboss_media_action;
    }

    return $action;
  }
  // Filter's the activity item's content
  public function bp_get_activity_content_body( $content )
  {
    global $buddyboss_media_img_size, $activities_template;

    $curr_id = $activities_template->current_activity;

    $act_id = (int)$activities_template->activities[$curr_id]->id;

    // This is manual for now
    $type    = 'photo'; // photo/video/audio/file/doc/pdf/etc
    $storage = 'wp';    // wp (media library)/cdn

    // Check for activity ID in $_POST if this is a single
    // activity request from a [read more] action
    if ( $act_id === 0 && ! empty( $_POST['activity_id'] ) )
    {
      $activity_array = bp_activity_get_specific( array(
        'activity_ids'     => $_POST['activity_id'],
        'display_comments' => 'stream'
      ) );

      $activity = ! empty( $activity_array['activities'][0] ) ? $activity_array['activities'][0] : false;

      $act_id = (int)$activity->id;
    }

    // This should never happen, but if it does, bail.
    if ( $act_id === 0 )
    {
      return $content;
    }

    $media_id = buddyboss_media_compat_get_meta( $act_id, 'activity.item_keys' );

    // Photo
    if ( $type === 'photo' && ! empty( $media_id ) )
    {
      $img_size = 'buddyboss_media_photo_wide';

      $image = wp_get_attachment_image_src( $media_id, $img_size );

      if ( ! empty( $image ) && is_array( $image ) && count( $image ) > 2 )
      {
        $src = $image[0];
        $w = $image[1];
        $h = $image[2];

        $full = wp_get_attachment_image_src( $media_id, 'full' );

        $width_markup = $w > 0 ? ' width="'.$w.'"' : '';

        if ( $full !== false && is_array( $full ) && count( $full ) > 2 )
        {
          $content .= '<a class="buddyboss-media-photo-wrap" href="'.$full[0].'">';
          $content .= '<img data-permalink="'. bp_get_activity_thread_permalink() .'" class="buddyboss-media-photo" src="'.$src.'"'.$width_markup.' /></a>';
        }
        else {
          $content .= '<img data-permalink="'. bp_get_activity_thread_permalink() .'" class="buddyboss-media-photo" src="'.$src.'"'.$width_markup.' />';
        }
      }
    }

    return $content;
  }
  // Filter's the activity item's content when the plugin is off
  public function off_bp_get_activity_content_body( $content )
  {
    global $buddyboss_media_img_size, $activities_template;

    $curr_id = $activities_template->current_activity;

    $act_id = $activities_template->activities[$curr_id]->id;

    $buddyboss_media_aid = buddyboss_media_compat_get_meta( $act_id, 'activity.item_keys' );

    $image = wp_get_attachment_image_src( $buddyboss_media_aid, 'full' );

    if ( $image !== false && is_array( $image ) && count( $image ) > 2 )
    {
      $src = $image[0];
      $w = $image[1];
      $h = $image[2];
      $content .= '<a href="'. $image[0] .'" target="_blank">'. basename( $image[0] ) .'</a>';
    }

    return $content;
  }
}


// AJAX update picture
function buddyboss_media_post_photo()
{
  global $bp, $buddyboss;

  // Check the nonce
  check_admin_referer( 'post_update', '_wpnonce_post_update' );

  if ( !is_user_logged_in() ) {
    echo '-1';
    return false;
  }

  if ( ! function_exists( 'wp_generate_attachment_metadata' ) )
  {
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');
  }

  if ( ! function_exists('media_handle_upload' ) )
  {
    require_once(ABSPATH . 'wp-admin/includes/admin.php');
  }

  add_filter( 'upload_dir', 'buddyboss_media_upload_dir' );

  $aid = media_handle_upload( 'file', 0 );

  remove_filter( 'upload_dir', 'buddyboss_media_upload_dir' );

  // Image rotation fix
  do_action( 'buddyboss_media_add_attachment', $aid );

  $attachment = get_post( $aid );

  $name = $url = null;

  if ( $attachment !== null )
  {
    $name = $attachment->post_title;

    $img_size = 'buddyboss_media_photo_wide';

    $url_nfo = wp_get_attachment_image_src( $aid, $img_size );

    $url = is_array( $url_nfo ) && !empty( $url_nfo ) ? $url_nfo[0] : null;
  }

  $result = array(
    'status'          => ( $attachment !== null ),
    'attachment_id'   => (int)$aid,
    'url'             => esc_url( $url ),
    'name'            => esc_attr( $name )
  );

  echo htmlspecialchars( json_encode( $result ), ENT_NOQUOTES );

  exit(0);
}
add_action( 'wp_ajax_buddyboss_media_post_photo', 'buddyboss_media_post_photo' );


function buddyboss_media_load_template_filter( $found_template, $templates ) {

  global $bp;

  // @TODO: Should we change the component name to 'buddyboss-media'?
  // @TODO: Can we dynamically let the user choose a component's slug?
  if ( $bp->current_component !== buddyboss_media_component_slug() )
    return $found_template;

  $filtered_templates = array();

  $templates_dir = buddyboss_media()->templates_dir;

  foreach ( (array) $templates as $template )
  {
    if ( file_exists( STYLESHEETPATH . '/' . $template ) )
      $filtered_templates[] = STYLESHEETPATH . '/' . $template;
    elseif ( file_exists( TEMPLATEPATH . '/' . $template ) )
      $filtered_templates[] = TEMPLATEPATH . '/' . $template;
    elseif ( file_exists( $templates_dir . '/' . $template ) )
      $filtered_templates[] = $templates_dir . '/' . $template;
  }

  if( !empty( $filtered_templates ) )
    $found_template = $filtered_templates[0];

  return apply_filters( 'buddyboss_media_load_template_filter', $found_template );
}
add_filter( 'bp_located_template', 'buddyboss_media_load_template_filter', 10, 2 );

function buddyboss_media_upload_dir( $filter )
{
  return $filter;
}

function buddyboss_media_greetings_template_text( $text )
{
	if( bp_is_current_component( buddyboss_media_component_slug() ) ){
		$firstname = '';
		if ( is_user_logged_in() && function_exists( 'bp_get_user_firstname' ) ){
			$firstname = bp_get_user_firstname();
		}

		$text =  sprintf( __( "Add a photo, %s", 'buddyboss-media' ), $firstname );
	}

	return $text;
}
add_filter( 'buddyboss_wall_greeting_template', 'buddyboss_media_greetings_template_text' );
?>