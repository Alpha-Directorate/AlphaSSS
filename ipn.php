<?php

global $wpdb;

try {
    if (isset($_GET['bitpay_callback'])) {
        $post = file_get_contents("php://input");
        if (true === empty($post)) {
            return array('error' => 'No post data');
        }
        $json = json_decode($post, true);
        if (true === is_string($json)) {
            return array('error' => $json);
        }
        if (false === array_key_exists('posData', $json)) {
            return array('error' => 'no posData');
        }
        if (false === array_key_exists('id', $json)) {
            return 'Cannot find invoice ID';
        }
        // Don't trust parameters from the scary internet.
        // Use invoice ID from the $json in  getInvoice($invoice_id) and get status from that.
        $client  = new \Bitpay\Client\Client();
        $adapter = new \Bitpay\Client\Adapter\CurlAdapter();
        $network = (strpos($json['url'], 'test') === false) ? new \Bitpay\Network\Livenet() : new \Bitpay\Network\Testnet();
        $client->setAdapter($adapter);
        $client->setNetwork($network);
        // Checking invoice is valid...
        $response  = $client->getInvoice($json['id']);
        $sessionid = $response->getPosData();
        // get buyer email
        $sql          = "SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`=" . $sessionid;
        $purchase_log = $wpdb->get_results($sql, ARRAY_A);
        $email_form_field = $wpdb->get_var("SELECT `id` FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `checkout_order` ASC LIMIT 1");
        $email            = $wpdb->get_var($wpdb->prepare("SELECT `value` FROM `" . WPSC_TABLE_SUBMITTED_FORM_DATA . "` WHERE `log_id` = %d AND `form_id` = %d LIMIT 1", $purchase_log[0]['id'], $email_form_field));
        // get cart contents
        $sql           = "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid`=" . $purchase_log[0]['id'];
        $cart_contents = $wpdb->get_results($sql, ARRAY_A);
        // get currency symbol
        $currency_id     = get_option('currency_type');
        $sql             = "SELECT * FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `id`=" . $currency_id;
        $currency_data   = $wpdb->get_results($sql, ARRAY_A);
        $currency_symbol = $currency_data[0]['symbol'];
        // list products and individual prices in the email
        $message_product = "\r\n\r\nTransaction Details:\r\n\r\n";
        $pnp      = 0.0;
        $subtotal = 0.0;
        foreach ($cart_contents as $product) {
            // shipping for each item
            $pnp             += $product['pnp'];
            $message_product .= 'x' . $product['quantity'] . ' ' . $product['name'] . ' - ' . $currency_symbol . ($product['price'] * $product['quantity']) . "\r\n";
            $subtotal        += $product['price'] * $product['quantity'];
        }
        //list subtotal
        $subtotal         = number_format($subtotal, 2, '.', ',');
        $message_product .= "\r\n" . 'Subtotal: ' . $currency_symbol . $subtotal . "\r\n";
        //list total taxes and total shipping costs in the email
        $message_product .= 'Taxes: '    . $currency_symbol . $purchase_log[0]['wpec_taxes_total']       . "\r\n";
        $message_product .= 'Shipping: ' . $currency_symbol . ($purchase_log[0]['base_shipping'] + $pnp) . "\r\n\r\n";
        //display total price in the email
        $message_product .= 'Total Price: ' . $currency_symbol . $purchase_log[0]['totalprice'];
        switch ($response->getStatus()) {
            //For low and medium transaction speeds, the order status is set to "Order Received" . The customer receives
            //an initial email stating that the transaction has been paid.
            case 'paid':
                if (true === is_numeric($sessionid)) {
                    $sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `processed`= '2' WHERE `sessionid`=" . $sessionid;
                    $wpdb->query($sql);
                    $message  = 'Thank you! Your payment has been received, but the transaction has not been confirmed on the bitcoin network. You will receive another email when the transaction has been confirmed.';
                    $message .= $message_product;
                    $sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `notes`= 'The payment has been received, but the transaction has not been confirmed on the bitcoin network. This will be updated when the transaction has been confirmed.' WHERE `sessionid`=" . $sessionid;
                    $wpdb->query($sql);
                    if (wp_mail($email, 'Payment Received', $message)) {
                        $mail_sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `email_sent`= '1' WHERE `sessionid`=" . $sessionid;
                        $wpdb->query($mail_sql);
                    }
                    transaction_results($sessionid, false);    //false because this is just for email notification
                }
                break;
            //For low and medium transaction speeds, the order status will not change. For high transaction speed, the order
            //status is set to "Order Received" here. For all speeds, an email will be sent stating that the transaction has
            //been confirmed.
            case 'confirmed':
                if (true === is_numeric($sessionid)) {
                    $sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `processed`= '2' WHERE `sessionid`=" . $sessionid;
                    $wpdb->query($sql);
                    $mail_sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `email_sent`= '1' WHERE `sessionid`=" . $sessionid;
                    //display initial "thank you" if transaction speed is high, as the 'paid' status is skipped on high speed
                    if (get_option('bitpay_transaction_speed') == 'high') {
                        $message  = 'Thank you! Your payment has been received, and the transaction has been confirmed on the bitcoin network. You will receive another email when the transaction is complete.';
                        $message .= $message_product;
                        $sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `notes`= 'The payment has been received, and the transaction has been confirmed on the bitcoin network. This will be updated when the transaction has been completed.' WHERE `sessionid`=" . $sessionid;
                        $wpdb->query($sql);
                        if (wp_mail($email, 'Payment Received', $message)) {
                            $wpdb->query($mail_sql);
                        }
                    } else {
                        $message = 'Your transaction has now been confirmed on the bitcoin network. You will receive another email when the transaction is complete.';
                        $sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `notes`= 'The payment has been received, and the transaction has been confirmed on the bitcoin network. This will be updated when the transaction has been completed.' WHERE `sessionid`=" . $sessionid;
                        $wpdb->query($sql);
                        if (wp_mail($email, 'Transaction Confirmed', $message)) {
                            $wpdb->query($mail_sql);
                        }
                    }
                    //false because this is just for email notification
                    transaction_results($sessionid, false);
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
        // END OF switch ($response->getStatus())
        }
    }
} catch (\Exception $e) {
    debuglog('[Error] In Bitpay plugin, form_bitpay() function on line ' . $e->getLine() . ', with the error "' . $e->getMessage() . '".');
    throw $e;
}
