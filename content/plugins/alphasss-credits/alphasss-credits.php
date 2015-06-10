<?php
/**
 * Plugin Name: AlphaSSS Credits
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss credits plugin
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

use AlphaSSS\Helpers\Arr;
use AlphaSSS\HTTP\HTTP;
use \AlphaSSS\Repositories\Credit;

add_action( 'bp_loaded', function(){

	load_plugin_textdomain( 'alphasss-donation', false, basename( dirname( __FILE__ ) ) . '/languages' );

	if ( HTTP::isPost() AND $credits_amount = Arr::get( $_POST, 'credits-amount' ) ) {

		// Check is member selected option is valid
		if ( in_array( $credits_amount, Credit::creditList() ) ) {
			
			$private = new \Bitpay\PrivateKey();
			$public  = new \Bitpay\PublicKey();
			$sin     = new \Bitpay\SinKey();

			// Generate Private Key values
			$private->generate();

			// Generate Public Key values
			$public->setPrivateKey($private);
			$public->generate();

			// Generate Sin Key values
			$sin->setPublicKey($public);
			$sin->generate();

			// @var \Bitpay\Client\Client
			$client = new \Bitpay\Client\Client();

			//Set the network being paired with.
			$client->setNetwork(new Bitpay\Network\Livenet);

			//Set Keys
			$client->setPublicKey($public);
			$client->setPrivateKey($private);

			// Initialize our network adapter object for cURL
			$client->setAdapter(new Bitpay\Client\Adapter\CurlAdapter());

			$invoice = new \Bitpay\Invoice();
			$invoice->setCurrency(new \Bitpay\Currency('USD'));

			$item = new \Bitpay\Item();
			$item->setPrice((float) $credits_amount);
			$invoice->setItem($item);

			/**
			* You will need to set the token that was returned when you paired your
			* keys.
			*/

			/*
			$token = $client->createToken(
				array(
					'id'          => (string) $sin,
					'pairingCode' => array_rand($pairingCode),
					'label'       => 'tests',
				)
			);

			$client->setToken($token);
			// Send invoice
			$client->createInvoice($invoice);
			*/

		} else {
			//@TODO add notification that wrong amount was selected
		}
	}

	// Once when member registred add zero credit amount to member balance
	add_action('member_registered', function(){
		update_user_meta(get_current_user_id(), 'credit_balance', 0);
	});

});