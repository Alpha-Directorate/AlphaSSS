<?php
/**
 * Plugin Name: AlphaSSS Credits
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss credits plugin
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 * Text Domain: alphasss
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use AlphaSSS\Helpers\Arr;
use AlphaSSS\HTTP\HTTP;
use \AlphaSSS\Repositories\Credit;
use \AlphaSSS\Repositories\Order;
use AlphaSSS\Helpers\EmailAddressEncryption;

add_action( 'bp_loaded', function(){

	load_textdomain( 'alphasss', WP_LANG_DIR . '/plugins/alphasss/alphasss-' . get_locale() . '.mo' );

	if ( HTTP::isPost() AND $credits_amount = Arr::get( $_POST, 'credits-amount' ) ) {

		// Check is member selected option is valid
		if ( in_array( $credits_amount, Credit::creditList() ) ) {

			// Detect the current user
			$user = wp_get_current_user();

			$bitpay = new \Bitpay\Bitpay(
				array(
					'bitpay' => array(
						'network'     => 'testnet',
						'public_key'  => plugin_dir_path( __FILE__ ) . '/keys/api.pub',
						'private_key' => plugin_dir_path( __FILE__ ) . '/keys/api.key',
						'key_storage' => 'Bitpay\Storage\FilesystemStorage'
					)
				)
			);
			
			$client = $bitpay->get('client');
			$sin    = (string)$bitpay->get('public_key')->getSin();

			/**
			* The last object that must be injected is the token object. If you didn't persist (save) it previously,
			* you can make a call to getTokens() and retrieve it.
			*/
			$token = new \Bitpay\Token();
			$token->setToken( getenv( 'BITPAY_TOKEN' ) );

			$item = new \Bitpay\Item();
			$item->setPrice((float) $credits_amount);
			$item->setDescription(sprintf(__("Purchase %.2f Credits ($%.2f USD)", 'alphasss'), $credits_amount, $credits_amount));

			$invoice = new \Bitpay\Invoice();
			$invoice->setCurrency(new \Bitpay\Currency('USD'))
				->setNotificationUrl( str_replace( '/wp', '', site_url( '/ipn.php', \AlphaSSS\HTTP\HTTP::protocol() ) ) )
				->setNotificationEmail( EmailAddressEncryption::decode( $user->user_email ) )
				->setTransactionSpeed(\Bitpay\Invoice::TRANSACTION_SPEED_LOW)
				->setFullNotifications(TRUE)
				->setItem($item);

			try {
				/**
				* You will need to set the token that was returned when you paired your
				* keys.
				*/
				$client->setToken($token);

				// Send invoice
				$client->createInvoice($invoice);

				//New Order creation 
				Order::create($user->ID, $invoice);
			} catch (Exception $e) {
				//@TODO send email here that something going wrong
			}

		} else {
			//@TODO add notification that wrong amount was selected
		}
	}

	// Once when member registred add zero credit amount to member balance
	add_action('member_registered', function(){
		update_user_meta(get_current_user_id(), 'credit_balance', 0);
	});

});