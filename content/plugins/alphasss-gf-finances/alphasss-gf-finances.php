<?php
/**
 * Plugin Name: AlphaSSS GF Finances
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss GF Finances
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 * Text Domain: alphasss-gf-finances
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \AlphaSSS\Repositories\AccountingEvent;
use \AlphaSSS\Helpers\Arr;
use Carbon\Carbon;

// Directory
if ( ! defined( 'ALPHASSS_GF_FINANCES_PLUGIN_DIR' ) ) {
	define( 'ALPHASSS_GF_FINANCES_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Url
if ( ! defined( 'ALPHASSS_GF_FINANCES_PLUGIN_URL' ) ) {
  $plugin_url = plugin_dir_url( __FILE__ );

  // If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
  if ( is_ssl() )
    $plugin_url = str_replace( 'http://', 'https://', $plugin_url );

  define( 'ALPHASSS_GF_FINANCES_PLUGIN_URL', $plugin_url );
}

// File
if ( ! defined( 'ALPHASSS_GF_FINANCES_PLUGIN_FILE' ) ) {
  define( 'ALPHASSS_GF_FINANCES_PLUGIN_FILE', __FILE__ );
}

add_action( 'plugins_loaded', function(){

	try {

		$main_include = ALPHASSS_GF_FINANCES_PLUGIN_DIR  . 'includes/main-class.php';

		if ( ! file_exists( $main_include ) ) {
			$msg = sprintf( __( "Couldn't load main class at:<br/>%s", 'alphasss' ), $main_include );
			throw new Exception( $msg, 404 );
		}

		require( $main_include );

		// Declare global access scope to the to Alphasss_Gf_Finances_Plugin instance
		global $alphasss_gf_finances;
		$alphasss_gf_finances = Alphasss_Gf_Finances_Plugin::instance();

	} catch (Exception $e) {

		$msg = sprintf( __( "<h1>Fatal error:</h1><hr/><pre>%s</pre>", 'alphasss' ), $e->getMessage() );
    	echo $msg;
	}

});

/**
 * Must be called after hook 'plugins_loaded'
 * @return Alphasss_Gf_Finances_Plugin
 */
function alphasss_gf_finances()
{
  global $alphasss_gf_finances;

  return $alphasss_gf_finances;
}

add_action('set_user_role', function($user_id, $role){
	if ($role == 'gf') {
		AccountingEvent::createSingUpEvent($user_id);
		AccountingEvent::createSingUpBonusEvent($user_id, Carbon::now()->addMinutes(1));
		AccountingEvent::create($user_id, AccountingEvent::TALK_SESSION_EVENT, 30, 0, Carbon::now()->addMinutes(40));
		AccountingEvent::create($user_id, AccountingEvent::GIFT_CARD_PURCHASE_EVENT, 0, 60, Carbon::now()->addMinutes(60));
	}
}, 10, 2);

add_action( 'wp_ajax_get_gf_accountiong_events', function(){

	// Not have GF role? Return 404
	if ( ! \AlphaSSS\Repositories\User::hasRole('gf') ) {

		status_header(404);

		wp_die();
	}
	//--

	header('Content-Type: application/json');

	if ( ! $event_records = \AlphaSSS\Repositories\AccountingEvent::findBy( ['user_id' => get_current_user_id()] ) ) {
		$event_records = [];
	}

	// Getting user timerzone or wordpress timezone
	$gf_timezone = Arr::get( $_POST, 'timezone_name', get_option('timezone_string') );

	// Formatting event date
	foreach ($event_records as &$event_record) {
		$event_record['display_date'] = (new Carbon( $event_record['event_date'], 'UTC' ) )->setTimezone($gf_timezone)->format('F j, Y, g:i a');
	}

	// Preparing the responce data
	$data = [
		'data' => [
			'event_records' => $event_records
		]
	];
	//--

	echo json_encode($data);

	wp_die();
});

?>
