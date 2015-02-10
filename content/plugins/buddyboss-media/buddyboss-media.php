<?php
/**
 * Plugin Name: BuddyBoss Media
 * Plugin URI:  http://buddyboss.com/product/buddyboss-media/
 * Description: BuddyBoss Media Photo Uploading
 * Author:      BuddyBoss
 * Author URI:  http://buddyboss.com
 * Version:     2.0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ========================================================================
 * CONSTANTS
 * ========================================================================
 */

// Codebase version
if ( ! defined( 'BUDDYBOSS_MEDIA_PLUGIN_VERSION' ) ) {
  define( 'BUDDYBOSS_MEDIA_PLUGIN_VERSION', '2.0.8' );
}

// Database version
if ( ! defined( 'BUDDYBOSS_MEDIA_PLUGIN_DB_VERSION' ) ) {
  define( 'BUDDYBOSS_MEDIA_PLUGIN_DB_VERSION', 2 );
}

// Directory
if ( ! defined( 'BUDDYBOSS_MEDIA_PLUGIN_DIR' ) ) {
  define( 'BUDDYBOSS_MEDIA_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Url
if ( ! defined( 'BUDDYBOSS_MEDIA_PLUGIN_URL' ) ) {
  $plugin_url = plugin_dir_url( __FILE__ );

  // If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
  if ( is_ssl() )
    $plugin_url = str_replace( 'http://', 'https://', $plugin_url );

  define( 'BUDDYBOSS_MEDIA_PLUGIN_URL', $plugin_url );
}

// File
if ( ! defined( 'BUDDYBOSS_MEDIA_PLUGIN_FILE' ) ) {
  define( 'BUDDYBOSS_MEDIA_PLUGIN_FILE', __FILE__ );
}

/**
 * ========================================================================
 * MAIN FUNCTIONS
 * ========================================================================
 */

/**
 * Main
 *
 * @return void
 */
function buddyboss_media_init()
{
  global $bp, $buddyboss_media;

  $main_include  = BUDDYBOSS_MEDIA_PLUGIN_DIR  . 'includes/main-class.php';

  try
  {
    if ( file_exists( $main_include ) )
    {
      require( $main_include );
    }
    else{
      $msg = sprintf( __( "Couldn't load main class at:<br/>%s", 'buddyboss-media' ), $main_include );
      throw new Exception( $msg, 404 );
    }
  }
  catch( Exception $e )
  {
    $msg = sprintf( __( "<h1>Fatal error:</h1><hr/><pre>%s</pre>", 'buddyboss-media' ), $e->getMessage() );
    echo $msg;
  }

  $buddyboss_media = BuddyBoss_Media_Plugin::instance();
}
add_action( 'plugins_loaded', 'buddyboss_media_init' );

/**
 * Must be called after hook 'plugins_loaded'
 *
 * @return BuddyBoss Media main/global object
 * @see  class BuddyBoss_Media
 */
function buddyboss_media()
{
  global $buddyboss_media;

  return $buddyboss_media;
}

/**
 * Settings Link
 * @since 1.1.0
 */
add_filter ('plugin_action_links', 'buddyboss_media_meta', 10, 2);
function buddyboss_media_meta ($links, $file)
{
  if ($file == plugin_basename (__FILE__))
  {
    $settings_link = '<a href="' . add_query_arg( array( 'page' => 'buddyboss-media/includes/admin.php'   ), admin_url( 'options-general.php' ) ) . '">' . esc_html__( 'Settings', 'buddyboss-media' ) . '</a>';
    array_unshift ($links, $settings_link);
  }
  return $links;
}

register_activation_hook( __FILE__, 'buddyboss_media_setup_db_tables' );
/**
* Setup database table for albums.
* Runs on plugin activation.
* 
* @since BuddyBoss Media (1.1)
*/
function buddyboss_media_setup_db_tables( $network_wide=false ){
   global $wpdb;
   if ( is_multisite() && $network_wide ) {
	   // store the current blog id
	   $current_blog = $wpdb->blogid;

	   // Get all blogs in the network and activate plugin on each one
	   $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	   foreach ( $blog_ids as $blog_id ) {
		   switch_to_blog( $blog_id );
		   buddyboss_media_create_db_tables();
		   restore_current_blog();
	   }
   } else {
	   buddyboss_media_create_db_tables();
   }
}

/**
* Create database table for albums.
* 
* @since BuddyBoss Media (1.1)
*/
function buddyboss_media_create_db_tables(){
   global $wpdb;
   $table_name = $wpdb->prefix . 'buddyboss_media_albums';

   $sql = "CREATE TABLE " . $table_name . " (
	   id bigint(20) NOT NULL AUTO_INCREMENT,
	   user_id bigint(20) NOT NULL,
	   date_created datetime NULL DEFAULT '0000-00-00',
	   title text NOT NULL,
	   description text NULL,
	   total_items mediumint(9) NULL DEFAULT '0',
	   privacy varchar(50) NULL DEFAULT 'public',
	   PRIMARY KEY  (id)
   );";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );
   
   update_option( 'buddyboss_media_db_version', BUDDYBOSS_MEDIA_PLUGIN_DB_VERSION );
}

/**
 * Allow automatic updates via the WordPress dashboard
 */
require_once('includes/vendor/wp-updates-plugin.php');
new WPUpdatesPluginUpdater_521( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));

?>