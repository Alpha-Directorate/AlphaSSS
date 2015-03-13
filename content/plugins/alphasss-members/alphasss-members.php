<?php
/**
 * Plugin Name: Alphasss Members
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss Members
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Directory
if ( ! defined( 'ALPHASSS_MEMBERS_PLUGIN_DIR' ) ) {
	define( 'ALPHASSS_MEMBERS_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Url
if ( ! defined( 'ALPHASSS_MEMBERS_PLUGIN_URL' ) ) {
  $plugin_url = plugin_dir_url( __FILE__ );

  // If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
  if ( is_ssl() )
    $plugin_url = str_replace( 'http://', 'https://', $plugin_url );

  define( 'ALPHASSS_MEMBERS_PLUGIN_URL', $plugin_url );
}

add_action( 'plugins_loaded', function(){

	try {

		$main_include = ALPHASSS_MEMBERS_PLUGIN_DIR  . 'includes/main-class.php';

		if ( ! file_exists( $main_include ) ) {
			$msg = sprintf( __( "Couldn't load main class at:<br/>%s", 'buddyboss-invitation' ), $main_include );
			throw new Exception( $msg, 404 );
		}

		require( $main_include );

		// Declare global access scope to the to Alphasss_Members_Plugin instance
		global $alphasss_members;
		$alphasss_members = Alphasss_Members_Plugin::instance();

	} catch (Exception $e) {

		$msg = sprintf( __( "<h1>Fatal error:</h1><hr/><pre>%s</pre>", 'alphasss-members' ), $e->getMessage() );
    	echo $msg;
	}

	add_action( 'bp_init', function(){
		bp_register_member_type( 'member', array(
			'labels' => array(
				'name'          => 'Members',
				'singular_name' => 'Member',
			)
		));
	});

	wp_enqueue_script( 'pubnub', '//cdn.pubnub.com/pubnub.min.js', array('jquery') );
	wp_enqueue_script( 'alphasss-alerts', ALPHASSS_MEMBERS_PLUGIN_URL . '/assets/js/alerts.js' );

	if ( ! is_user_logged_in() || ! current_user_can('generate_invitation_code') ) {
		wp_enqueue_script( 'alphasss-members', ALPHASSS_MEMBERS_PLUGIN_URL . '/assets/js/non-member.js' );

		add_action( 'bp_directory_members_actions', function(){
			echo bp_get_button( array(
				'id'                => 'request_invitation',
				'component'         => 'members',
				'must_be_logged_in' => false,
				'block_self'        => false,
				'link_href'         => wp_nonce_url( bp_get_group_permalink( $group ) . 'request-invitation', 'groups_request_membership' ),
				'link_text'         => __( 'Request Invitation', 'buddypress' ),
				'link_title'        => __( 'Request Invitation', 'buddypress' ),
				'link_class'        => 'group-button request-invitation',
			) );
		} );
	} else {
		wp_enqueue_script( 'alphasss-members', ALPHASSS_MEMBERS_PLUGIN_URL . 'assets/js/member.js',array('jquery') );

		/**
		 * This action returns uuid
		 */
		add_action( 'wp_ajax_get_uuid', function(){
			header('Content-Type: application/json');

			$user = (array)wp_get_current_user()->data;

			// Prepare data
			$data = array(
				'data' => array(
					'user' => array(
						'username' => md5($user['display_name'])
					)
				)
			);
			//--

			echo json_encode($data);

			wp_die();
		});
	}

	// Pre-member is logged in
	if ( is_user_logged_in() && trim($_SERVER['REQUEST_URI'], '/') != 'activate') {
		$user   = wp_get_current_user();
		$params = array('nickname' => $user->display_name);
		
		// Show top alert on all pages except activate(top navigation element Register)
		$params['show_top_alert'] = (trim($_SERVER['REQUEST_URI'], '/') != 'activate');
	} else {
		$params = array('nickname' => null);
	}

	// Setup PubNub connection params
	$params['pubnub'] = array(
		'ssl'           => is_ssl(),
		'publish_key'   => 'pub-c-bd645d1e-f4aa-4719-9008-d14e29514bab',
		'subscribe_key' => 'sub-c-8e1b190a-b033-11e4-83d7-0619f8945a4f',
		'uuid'          => md5($params['nickname'])
	);

	$params['i18n'] = array(
		'RequestSent'      => sprintf(__('Okay! Great, we have sent your request to %s.<br />In a couple of seconds, we will display your code in this window, right here.', 'alphasss-members'), $params['nickname']),
		'RequestSentShort' => __('Request sent', 'alphasss-members'),
		'UserLeaveAlphass' => __('Sorry but the member %s went offline just a moment ago. Here\'s what you can do:<br /><p>&nbsp;&nbsp;1. The fastest: Request invitation from anybody who is online. You\'ll your code within seconds.</p><p>&nbsp;&nbsp;2. Post your invitation request in the general forum. Someone will read it and send you invitation.</p>', 'alphasss-members'),
		'TopAlert'         => __('Your registration is not quite finished yet. To complete it, go to <a href="/activate/">registration</a> page. Morbo will now introduce tonight\'s candidates... PUNY HUMAN NUMBER ONE, PUNY HUMAN NUMBER TWO, and Morbo\'s good friend, Richard Nixon. <a href="/browse/">Browse</a>. Would you censor the Venus de Venus just because you can see her spewers? Yeah, lots of people did. Soon enough.', 'alphasss-members'),
		'ConnectionError'  => __('Bendless Love<br /> Bender, we\'re trying our best. You wouldn\'t. Ask anyway! I saw you with those two \'\'ladies of the evening\'\' at Elzars. Explain that.','alphasss-members')
	);

	wp_localize_script( 'alphasss-members', 'php_vars', $params );
	wp_localize_script( 'alphasss-alerts', 'php_vars', $params );
});

/**
 * Must be called after hook 'plugins_loaded'
 * @return Alphasss_Members_Plugin
 */
function alphasss_members()
{
  global $alphasss_members;

  return $alphasss_members;
}

/**
 * Settings Link
 * @since 1.1.2
 */
add_filter ('plugin_action_links', function($links, $file) {

	if ($file == plugin_basename (__FILE__)) {

		$settings_link = '<a href="' . add_query_arg( array( 'page' => 'alphasss-members/includes/admin.php'   ), admin_url( 'options-general.php' ) ) . '">' . esc_html__( 'Settings', 'alphasss-members' ) . '</a>';

		array_unshift ($links, $settings_link);
	}

	return $links;	
}, 10, 2);