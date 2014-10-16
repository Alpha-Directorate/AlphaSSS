<?php
/**
 * Plugin Name: BuddyBoss Media
 * Plugin URI:  http://buddyboss.com/product/buddyboss-media/
 * Description: BuddyBoss Media Photo Uploading
 * Author:      BuddyBoss
 * Author URI:  http://buddyboss.com
 * Version:     1.0.6
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
  define( 'BUDDYBOSS_MEDIA_PLUGIN_VERSION', '1.0.6' );
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
 * Allow automatic updates via the WordPress dashboard
 */
require_once('includes/vendor/wp-updates-plugin.php');
new WPUpdatesPluginUpdater_521( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));

?>