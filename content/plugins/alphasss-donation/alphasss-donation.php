<?php
/**
 * Plugin Name: AlphaSSS Donation
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss micro donation plugin
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

use AlphaSSS\Helpers\Arr;
use Blockchain\Blockchain;

add_action( 'bp_loaded', function(){

	load_plugin_textdomain( 'alphasss-donation', false, basename( dirname( __FILE__ ) ) . '/languages' );

	class AlphaSSS_Donation extends BP_Group_Extension {

		public function __construct() {
			parent::init( [
				'slug'   => 'micro-donate',
				'name'   => __( 'micro-Donate', 'alphasss-donation' ),
				'access' => 'non-admin'
			] );
		}

		/**
		 * Method checks whether the current user meets an access condition.
		 * Added one custom option 'non-admin'
		 *
		 * @param string $access_condition 'anyone', 'loggedin', 'member',
		 *        'mod', 'non-admin', 'admin' or 'noone'.
		 * @return bool
		 */
		protected function user_meets_access_condition( $access_condition ) {
			$group = groups_get_group( array(
				'group_id' => $this->group_id,
			) );

			switch ( $access_condition ) {
				case 'admin' :
					$meets_condition = groups_is_user_admin( bp_loggedin_user_id(), $this->group_id );
					break;

				case 'non-admin':
					$meets_condition = ! groups_is_user_admin( bp_loggedin_user_id(), $this->group_id );
					break;

				case 'mod' :
					$meets_condition = groups_is_user_mod( bp_loggedin_user_id(), $this->group_id );
					break;

				case 'member' :
					$meets_condition = groups_is_user_member( bp_loggedin_user_id(), $this->group_id );
					break;

				case 'loggedin' :
					$meets_condition = is_user_logged_in();
					break;

				case 'noone' :
					$meets_condition = false;
					break;

				case 'anyone' :
				default :
					$meets_condition = true;
					break;
			}

			return $meets_condition;
		}

		public function display( $group_id = NULL ) {
			if ( ! $group_id ) $group_id = bp_get_group_id();

			$Blockchain = new Blockchain('9e9567d5-dd38-4513-ae2e-39d3f521156d');
			
			$Blockchain->Wallet->credentials( 
				get_user_meta( get_current_user_id( ), 'wallet_guid', true),
				get_user_meta( get_current_user_id( ), 'wallet_password', true)
			);

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
			<span>Your address is <?php echo get_user_meta( get_current_user_id( ), 'wallet_address', true); ?></span><br />
			Your ballance is <span id="balance"><?php echo $Blockchain->Wallet->getBalance(); ?></span> BTC
			<form class="form-inline">
				<div class="form-group">
					<select class="form-control" id="btc-amount" required>
						<option value="">Select amount of transaction</option>
						<option value="1">1 USD</option>
						<option value="2">2 USD</option>
					</select>
				</div>
				<button id="pay-btc">Pay</button>
			</form>
			<?php
		}
	}

	bp_register_group_extension( 'AlphaSSS_Donation' );

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
 
		// create http client instance
		$client = new Guzzle\Http\Client();
		 
		// create a request
		$request = $client->get($invoice->getUrl());
		 
		// send request / get response
		$response = $request->send();

		// crate crawler instance from body HTML code
		$crawler = new Symfony\Component\DomCrawler\Crawler($response->getBody(true));
		 
		// apply css selector filter
		$filter = $crawler->filterXpath('//code[@id="addressCode"]');

		foreach ($filter as $element) {
			$address = $element->nodeValue;
		}
		
		$Blockchain = new Blockchain('9e9567d5-dd38-4513-ae2e-39d3f521156d');
		$Blockchain->Wallet->credentials( 
			get_user_meta( get_current_user_id( ), 'wallet_guid', true),
			get_user_meta( get_current_user_id( ), 'wallet_password', true)
		);
		$Blockchain->Wallet->send($address, $invoice->getBtcPrice());

		// Prepare data
		$data = array(
			'data' => array(
				'amount' => $Blockchain->Wallet->getBalance()
			)
		);
		//--

		echo json_encode($data);

		wp_die();
	});
});