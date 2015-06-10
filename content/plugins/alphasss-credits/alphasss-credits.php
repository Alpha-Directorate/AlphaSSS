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
		if ( in_array( $credits_amount, Credit::creditList() ) ) {
			
		} else {
			//@TODO add notification that wrong amount was selected
		}
	}

	// Once when member registred add zero credit amount to member
	add_action('member_registered', function(){
		update_user_meta(get_current_user_id(), 'credit_balance', 0);
	});

	class AlphaSSS_Donation extends BP_Group_Extension {

		public function __construct() {
			parent::init( [
				'slug'   => 'micro-donate',
				'name'   => __( 'micro-Donate', 'alphasss-donation' ),
				'access' => 'non-admin'
			] );
		}

		public function display( $group_id = NULL ) {
			if ( ! $group_id ) $group_id = bp_get_group_id();

			?>
			<hr />
			<script type="text/javascript">
				$(document).ready(function(){
					$('#pay-btc').click(function(){
						var btc_amount = $('#btc-amount').val();

						data = {
							action: "pay",
							btc_amount: btc_amount
						};

						$.post(ajaxurl, data, function(data){
							$('#balance').text(data.data.amount);
						},"json");

						return false;
					});
				});
			</script>
			<span>Your address is <?php echo get_user_meta( get_current_user_id( ), 'credit_balance', true); ?></span><br />

			<?php
		}
	}



	/**
	 * This action send money to user
	 */
	add_action( 'wp_ajax_pay', function(){

		header('Content-Type: application/json');

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
		$item->setPrice((float) $_POST['btc_amount']);
		$invoice->setItem($item);

		$pairingCode = [
			'68JLfgC',
			'KeUvWSB',
			'hw5wtD0',
			'1rBSo39',
			'gicRr89',
			'MRPvdvu',
			'tmzbAHh',
			'TKCegg6',
			'20dqWv0',
			'cZjfj5K'
		];

		/**
		 * You will need to set the token that was returned when you paired your
		 * keys.
		 */
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


		// Prepare data
		$data = array(
			'data' => array(
				'amount' => 50
			)
		);
		//--

		echo json_encode($data);

		wp_die();
	});
});