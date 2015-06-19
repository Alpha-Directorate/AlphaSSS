<?php

require 'vendor/autoload.php';

global $wpdb;
try {

	// Get POST data from BitPay
	$post = file_get_contents("php://input");

	if (true === empty($post)) {
		//@todo Add record to logs
	}

	$json = json_decode($post, true);

	if (true === is_string($json)) {
    	//@todo Add record to logs
	}

    if (false === array_key_exists('posData', $json)) {
    	//@todo Add record to logs
    }

	if (false === array_key_exists('id', $json)) {
    	//@todo Add record to logs
	}

	// Use invoice ID from the $json in  getInvoice($invoice_id) and get status from that.
	$client  = new \Bitpay\Client\Client();
	$adapter = new \Bitpay\Client\Adapter\CurlAdapter();

	// Detect BitPay environment
	$network = (strpos($json['url'], 'test') === false) 
		? new \Bitpay\Network\Livenet() 
		: new \Bitpay\Network\Testnet();

    $client->setAdapter($adapter);
    $client->setNetwork($network);

    // Checking invoice is valid...
    $response  = $client->getInvoice($json['id']);
    $sessionid = $response->getPosData();

    switch ($response->getStatus()) {
        //For low and medium transaction speeds, the order status is set to "Order Received". The customer receives an initial email stating that the transaction has been paid.
        case 'paid':
            if (true === is_numeric($sessionid)) {
                $sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `processed`= '2' WHERE `sessionid`=" . $sessionid;
                $wpdb->query($sql);

                $sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `notes`= 'The payment has been received, but the transaction has not been confirmed on the bitcoin network. This will be updated when the transaction has been confirmed.' WHERE `sessionid`=" . $sessionid;
                $wpdb->query($sql);
            }
            break;
        //For low and medium transaction speeds, the order status will not change. For high transaction speed, the order
        //status is set to "Order Received" here. For all speeds, an email will be sent stating that the transaction has
        //been confirmed.
        case 'confirmed':
            if (true === is_numeric($sessionid)) {
                $sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `processed`= '2' WHERE `sessionid`=" . $sessionid;
                $wpdb->query($sql);
            }
            break;
        //The purchase receipt email is sent upon the invoice status changing to "complete", and the order
        //status is changed to Accepted Payment
        case 'complete':
            if (true === is_numeric($sessionid)) {
                $sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `processed`= '3' WHERE `sessionid`=" . $sessionid;
                $wpdb->query($sql);
                $message = 'Your transaction is now complete! Thank you for using BitPay!';
                $sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `notes`= 'The transaction is now complete.' WHERE `sessionid`=" . $sessionid;
                $wpdb->query($sql);
                if (wp_mail($email, 'Transaction Complete', $message)) {
                    $mail_sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `email_sent`= '1' WHERE `sessionid`=" . $sessionid;
                    $wpdb->query($mail_sql);
                }
                //false because this is just for email notification
                transaction_results($sessionid, false);
            }
            break;
        }
} catch (\Exception $e) {
	//@todo Add multiple emails
    wp_mail( getenv( 'NOTIFICATION_EMAIL' ), 'Transaction Complete', $e->getMessage() );
}