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
	protected $activity_photo_size;
	
	public function activity_photo_size(){
		if( !$this->activity_photo_size ){
			$this->activity_photo_size = buddyboss_media()->option( 'activity-photo-size' );
			if( !$this->activity_photo_size )
				$this->activity_photo_size = 'medium';
		}
		
		return $this->activity_photo_size;
	}
	
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
      /*$action  = '<a href="'.$user->domain.'">'.$user->fullname.'</a> '
        . __( 'posted a photo', 'buddyboss-media' );*/
		
		$action  = '%USER% ' . __( 'posted a photo', 'buddyboss-media' );
		
		/**
		 * If the activity is posted in a group
		 */
		if( 'groups'==$activity->component ){
			if( bp_has_groups( array( 'include'=>$activity->item_id ) ) ){
				while( bp_groups() ){
					bp_the_group();
					$group_link = sprintf( "<a href='%s'>%s</a>", bp_get_group_permalink(), bp_get_group_name() );
					$action .= ' ' . __( 'in the group', 'buddyboss-media' ) . ' ' . $group_link;
				}
			}
		}
		
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

    $current_activity_index = $activities_template->current_activity;

    $current_activity       = $activities_template->activities[ $current_activity_index ];

    $current_activity_id    = $current_activity->id;

    $buddyboss_media_action = buddyboss_media_compat_get_meta( $current_activity_id, 'activity.action_keys' );

    if ( $buddyboss_media_action )
    {
		//convert placeholder into real user link
		//display You if its current users activity
		$replacement = '';
		if( $current_activity->user_id == get_current_user_id() ){
			$replacement = __( 'You', 'buddyboss-media' );
		} else {
			$userdomain = bp_core_get_user_domain( $current_activity->user_id );
			$user_fullname = bp_core_get_user_displayname( $current_activity->user_id );
			
			$replacement = '<a href="'.esc_url( $userdomain ).'">' . $user_fullname . '</a>';
		}
		
		$buddyboss_media_action = str_replace( '%USER%', $replacement, $buddyboss_media_action );
		
      // Strip any legacy time since placeholders from BP 1.0-1.1
      $content = str_replace( '<span class="time-since">%s</span>', '', $buddyboss_media_action );

      // Insert the time since.
      $time_since = apply_filters_ref_array( 'bp_activity_time_since', array( '<span class="time-since">' . bp_core_time_since( $activities_template->activity->date_recorded ) . '</span>', &$activities_template->activity ) );

      // Insert the permalink
      if ( !bp_is_single_activity() )
        $content = apply_filters_ref_array( 'bp_activity_permalink', array( sprintf( '%1$s <a href="%2$s" class="view activity-time-since" title="%3$s">%4$s</a>', $content, bp_activity_get_permalink( $activities_template->activity->id, $activities_template->activity ), esc_attr__( 'View Discussion', 'buddyboss-media' ), $time_since ), &$activities_template->activity ) );
      else
        $content .= str_pad( $time_since, strlen( $time_since ) + 2, ' ', STR_PAD_BOTH );

      return apply_filters( 'buddyboss_media_activity_action', $content );
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
	  /**
	   * if we are displaying grid layout instead of activity post layout, images should be 'thumbnail' size
	   */
	  if( buddyboss_media_check_custom_activity_template_load() ){
		  $img_size = 'thumbnail';//hardcoded !?
	  } else {
		//$img_size = 'buddyboss_media_photo_wide';
		$img_size = $this->activity_photo_size();
	  }

      $image = wp_get_attachment_image_src( $media_id, $img_size );

      if ( ! empty( $image ) && is_array( $image ) && count( $image ) > 2 )
      {
        $src = $image[0];
        $w = $image[1];
        $h = $image[2];
		
		//alt tag
		$clean_content = wp_strip_all_tags( $content, true );
		$alt_text = !empty( $clean_content ) ? substr( $clean_content, 0, 100 ) : '';//first 100 characters ?
		$alt = ' alt="' . esc_attr( $alt_text ) . '"';

        $full = wp_get_attachment_image_src( $media_id, 'full' );

        $width_markup = $w > 0 ? ' width="'.$w.'"' : '';

        if ( $full !== false && is_array( $full ) && count( $full ) > 2 )
        {
		$owner = ($activities_template->activities[$curr_id]->user_id == get_current_user_id())?'1':'0';
          $content .= '<a class="buddyboss-media-photo-wrap" href="'.$full[0].'">';
          $content .= '<img data-permalink="'. bp_get_activity_thread_permalink() .'" class="buddyboss-media-photo" src="'.$src.'"'.$width_markup.' ' . $alt . ' data-media="'.$act_id.'" data-owner="'.$owner.'"/></a>';
        }
        else {
          $content .= '<img data-permalink="'. bp_get_activity_thread_permalink() .'" data-media="'.$act_id.'" data-owner="'.$owner.'" class="buddyboss-media-photo" src="'.$src.'"'.$width_markup.' ' . $alt .' />';
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

  public function bp_get_member_latest_update( $update )
  {
    global $members_template;

    if ( !bp_is_active( 'activity' ) || empty( $members_template->member->latest_update ) || !$update = maybe_unserialize( $members_template->member->latest_update ) )
      return false;

    $current_activity_id    = $update['id'];

    $buddyboss_media_action = buddyboss_media_compat_get_meta( $current_activity_id, 'activity.action_keys' );

    if ( $buddyboss_media_action )
    {
      // Strip any legacy time since placeholders from BP 1.0-1.1
      $content = str_replace( '<span class="time-since">%s</span>', '', $buddyboss_media_action );
	  
	  //remove user placeholder
	  $content = str_replace( "%USER%", "", $content );
	  
      $activity_action_text = __( 'new photo', 'buddyboss-media' );

      // Look for 'posted a photo' and linkify
      if ( stristr( $content, $activity_action_text ) )
      {
        $permalink_href = bp_activity_get_permalink( $current_activity_id );

        if ( ! empty( $permalink_href ) )
        {
          $permalink = sprintf( '<a href="%s" title="%s">%s</a>', $permalink_href, strip_tags( $content ), $activity_action_text );

          $content = str_replace( $activity_action_text, $permalink, $content );
        }
      }

      return apply_filters( 'buddyboss_media_activity_action', $content );
    }

    return $update['content'];
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

    //$img_size = 'buddyboss_media_photo_wide';
	$img_size = 'buddyboss_media_photo_tn';

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
