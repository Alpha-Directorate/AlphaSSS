<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Media
 *
 * @todo Better logging, log to file, debug mode, remote error messages/notifications
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle logging
 *
 * @param  string $msg Log message
 * @return void
 */
function buddyboss_media_log( $msg )
{
  global $buddyboss_media;

  // $buddyboss_media->log[] = $msg;
}

/**
 * Print log at footer
 *
 * @return void
 */
function buddyboss_media_print_log()
{
  ?>
  <div class="buddyboss-media-log">
    <pre>
      <?php print_r( $buddyboss_media->log ); ?>
    </pre>

    <br/><br/>
    <hr/>
  </div>
  <?php
}
// add_action( 'wp_footer', 'buddyboss_media_print_log' );

/**
 * Get the default slug used by buddyboss media component.
 * 
 * @return string
 */
function buddyboss_media_default_component_slug(){
	return 'photos';
}

/**
 * Get the correct slug used by buddyboss media component.
 * The slug is configurable from settings.
 * 
 * @return string
 */
function buddyboss_media_component_slug(){
	return buddyboss_media()->types->photo->slug;
}

/*
 * @todo: make the sql filterable, e.g: to add custom conditions
 */
function buddyboss_media_screen_content_pages_sql( $sql ){
	/*
	 * $pages_sql = "SELECT COUNT(*) FROM $activity_table a
                INNER JOIN $activity_meta_table am ON a.id = am.activity_id
                LEFT JOIN (SELECT id FROM $groups_table WHERE status != 'public' ) grp ON a.item_id = grp.id
                WHERE a.user_id = $user_id
                AND (am.meta_key = 'buddyboss_media_aid' OR am.meta_key = 'buddyboss_pics_aid' OR am.meta_key = 'bboss_pics_aid')
                AND (a.component != 'groups' || a.item_id != grp.id)";
	 */
	$activity_table = bp_core_get_table_prefix() . 'bp_activity';
	$activity_meta_table = bp_core_get_table_prefix() . 'bp_activity_meta';
	$groups_table = bp_core_get_table_prefix() . 'bp_groups';
	
	return "SELECT COUNT(*) FROM $activity_table a
                INNER JOIN $activity_meta_table am ON a.id = am.activity_id
                LEFT JOIN (SELECT id FROM $groups_table WHERE status != 'public' ) grp ON a.item_id = grp.id
                WHERE 1=1 
                AND (am.meta_key = 'buddyboss_media_aid' OR am.meta_key = 'buddyboss_pics_aid' OR am.meta_key = 'bboss_pics_aid')
                AND (a.component != 'groups' || a.item_id != grp.id)";
}

/*
 * @todo: make the sql filterable, e.g: to perform custom orderby queries
 */
function buddyboss_media_screen_content_sql( $sql ){
	/*
		$sql = "SELECT a.*, am.meta_value FROM $activity_table a
          INNER JOIN $activity_meta_table am ON a.id = am.activity_id
          LEFT JOIN (SELECT id FROM $groups_table WHERE status != 'public' ) grp ON a.item_id = grp.id
          WHERE a.user_id = $user_id
          AND (am.meta_key = 'buddyboss_media_aid' OR am.meta_key = 'buddyboss_pics_aid' OR am.meta_key = 'bboss_pics_aid')
          AND (a.component != 'groups' || a.item_id != grp.id)
          ORDER BY a.date_recorded DESC";
	 */
	$activity_table = bp_core_get_table_prefix() . 'bp_activity';
	$activity_meta_table = bp_core_get_table_prefix() . 'bp_activity_meta';
	$groups_table = bp_core_get_table_prefix() . 'bp_groups';
	
	return "SELECT a.*, am.meta_value FROM $activity_table a
          INNER JOIN $activity_meta_table am ON a.id = am.activity_id
          LEFT JOIN (SELECT id FROM $groups_table WHERE status != 'public' ) grp ON a.item_id = grp.id
          WHERE 1=1 
          AND (am.meta_key = 'buddyboss_media_aid' OR am.meta_key = 'buddyboss_pics_aid' OR am.meta_key = 'bboss_pics_aid')
          AND (a.component != 'groups' || a.item_id != grp.id)
          ORDER BY a.date_recorded DESC";
}
?>