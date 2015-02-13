<?php
/**
 * Plugin Name: BuddyBoss Invitation
 * Plugin URI:  http://alphasss.com/
 * Description: BuddyBoss Invitation
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */
 
 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Directory
if ( ! defined( 'BUDDYBOSS_INVITATION_PLUGIN_DIR' ) ) {
  define( 'BUDDYBOSS_INVITATION_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Url
if ( ! defined( 'BUDDYBOSS_INVITATION_PLUGIN_URL' ) ) {
  $plugin_url = plugin_dir_url( __FILE__ );

  // If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
  if ( is_ssl() )
    $plugin_url = str_replace( 'http://', 'https://', $plugin_url );

  define( 'BUDDYBOSS_INVITATION_PLUGIN_URL', $plugin_url );
}

// File
if ( ! defined( 'BUDDYBOSS_INVITATION_PLUGIN_FILE' ) ) {
  define( 'BUDDYBOSS_INVITATION_PLUGIN_FILE', __FILE__ );
}

add_action( 'plugins_loaded', function(){

	try {

		$main_include = BUDDYBOSS_INVITATION_PLUGIN_DIR  . 'includes/main-class.php';

		if ( ! file_exists( $main_include ) ) {
			$msg = sprintf( __( "Couldn't load main class at:<br/>%s", 'buddyboss-invitation' ), $main_include );
			throw new Exception( $msg, 404 );
		}

		require( $main_include );

		// Declare global access scope to the to BuddyBoss_Invitation_Plugin instance
		global $buddyboss_invitation;
		$buddyboss_invitation = BuddyBoss_Invitation_Plugin::instance();

	} catch (Exception $e) {

		$msg = sprintf( __( "<h1>Fatal error:</h1><hr/><pre>%s</pre>", 'buddyboss-invitation' ), $e->getMessage() );
    	echo $msg;
	}

} );

/**
 * Must be called after hook 'plugins_loaded'
 * @return BuddyBoss_Invitation_Plugin
 */
function buddyboss_invitation()
{
  global $buddyboss_invitation;

  return $buddyboss_invitation;
}

add_action( 'wp_ajax_get_invitation_code', function(){

	header('Content-Type: application/json');

	if ( ! current_user_can('generate_invitation_code') ) {

		status_header(404);

		wp_die();
	}

	$data = array(
		'data' => array(
			'invitation_code' => buddyboss_invitation()->get_invitation_code()
		)
	);

	echo json_encode($data);

	wp_die();
});

// Setup database
register_activation_hook( __FILE__, 'buddyboss_invitation_setup_db_tables' );

/**
* Setup database table for invitation codes.
* Runs on plugin activation.
*/
function buddyboss_invitation_setup_db_tables( ){
	global $wpdb;

	$table_name = $wpdb->prefix . 'buddyboss_invitation_codes';

	$sql = "CREATE TABLE " . $table_name . " (
			`id` int(32) unsigned NOT NULL AUTO_INCREMENT,
			`invitation_code` varchar(10) CHARACTER SET utf8 NOT NULL,
			`member_id` int(20) unsigned DEFAULT NULL,
			`requested_member_id` int(20) unsigned DEFAULT NULL,
			`activated_member_id` int(20) unsigned DEFAULT NULL,
			`created_date` datetime DEFAULT NULL,
			`expired_date` datetime DEFAULT NULL,
			`is_active` enum('YES','NO') DEFAULT 'NO',
			PRIMARY KEY (`id`),
			KEY `invitation_code_member_idx` (`invitation_code`,`member_id`)
		)ENGINE=InnoDB DEFAULT CHARSET=utf8;
	);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

?>
