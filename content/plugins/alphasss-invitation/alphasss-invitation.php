<?php
/**
 * Plugin Name: Alphasss Invitation
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss Invitation
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 * Text Domain: alphasss-invitation
 */
 
 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Directory
if ( ! defined( 'ALPHASSS_INVITATION_PLUGIN_DIR' ) ) {
	define( 'ALPHASSS_INVITATION_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Define invitation codes table name
if ( ! defined( 'ALPHASSS_INVITATION_TABLENAME' ) ) {
	global $wpdb;

	define( 'ALPHASSS_INVITATION_TABLENAME', $wpdb->prefix . 'buddyboss_invitation_codes' );
}
//--

// Url
if ( ! defined( 'ALPHASSS_INVITATION_PLUGIN_URL' ) ) {
  $plugin_url = plugin_dir_url( __FILE__ );

  // If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
  if ( is_ssl() )
    $plugin_url = str_replace( 'http://', 'https://', $plugin_url );

  define( 'ALPHASSS_INVITATION_PLUGIN_URL', $plugin_url );
}

// File
if ( ! defined( 'ALPHASSS_INVITATION_PLUGIN_FILE' ) ) {
  define( 'ALPHASSS_INVITATION_PLUGIN_FILE', __FILE__ );
}

add_action( 'plugins_loaded', function(){

	try {

		$main_include = ALPHASSS_INVITATION_PLUGIN_DIR  . 'includes/main-class.php';

		if ( ! file_exists( $main_include ) ) {
			$msg = sprintf( __( "Couldn't load main class at:<br/>%s", 'alphasss-invitation' ), $main_include );
			throw new Exception( $msg, 404 );
		}

		require( $main_include );

		// Declare global access scope to the to Alphasss_Invitation_Plugin instance
		global $alphasss_invitation;
		$alphasss_invitation = Alphasss_Invitation_Plugin::instance();

	} catch (Exception $e) {

		$msg = sprintf( __( "<h1>Fatal error:</h1><hr/><pre>%s</pre>", 'alphasss-invitation' ), $e->getMessage() );
    	echo $msg;
	}

});

/**
 * Must be called after hook 'plugins_loaded'
 * @return Alphasss_Invitation_Plugin
 */
function alphasss_invitation()
{
  global $alphasss_invitation;

  return $alphasss_invitation;
}

/**
 * Settings Link
 * @since 1.1.2
 */
add_filter ('plugin_action_links', function($links, $file) {

	if ($file == plugin_basename (__FILE__)) {
		$settings_link = '<a href="' . add_query_arg( array( 'page' => 'alphasss-invitation/includes/admin.php'   ), admin_url( 'options-general.php' ) ) . '">' . esc_html__( 'Settings', 'alphasss-invitation' ) . '</a>';

		array_unshift ($links, $settings_link);
	}

	return $links;	
}, 10, 2);

/**
 * This action returns generated invitation code
 */
add_action( 'wp_ajax_get_invitation_code', function(){

	header('Content-Type: application/json');

	// Not have credentials? Return 404
	if ( ! current_user_can('generate_invitation_code') ) {

		status_header(404);

		wp_die();
	}
	//--

	$requestor_nickname = isset($_POST['requestor_nickname'])
		? $_POST['requestor_nickname']
		: null;

	// Prepare data
	$data = array(
		'data' => array(
			'invitation_code' => alphasss_invitation()->get_invitation_code($requestor_nickname)
		)
	);
	//--

	echo json_encode($data);

	wp_die();
});

// Setup database
register_activation_hook( __FILE__, 'alphasss_invitation_setup_db_tables' );

/**
* Setup database table for invitation codes.
* Runs on plugin activation.
*/
function alphasss_invitation_setup_db_tables( ){

	$sql = "CREATE TABLE " . ALPHASSS_INVITATION_TABLENAME . " (
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
