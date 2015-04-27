<?php
/**
 * Plugin Name: AlphaSSS SMTP
 * Plugin URI:  http://alphasss.com/
 * Description: AlphaSSS email sending
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

add_action( 'plugins_loaded', function(){
	add_action( 'phpmailer_init', function( $phpmailer ){

		// Define that we are sending with SMTP
		$phpmailer->isSMTP();

		// The hostname of the mail server
		$phpmailer->Host = getenv( 'SMTP_HOST' );

		// Use SMTP authentication (true|false)
		$phpmailer->SMTPAuth = true;

		// SMTP port number - likely to be 25, 465 or 587
		$phpmailer->Port = getenv( 'SMTP_PORT' );

		// Username to use for SMTP authentication
		$phpmailer->Username = getenv( 'SMTP_USERNAME' );

		// Password to use for SMTP authentication
		$phpmailer->Password = getenv( 'SMTP_PASSWORD' );

		// Encryption system to use - ssl or tls
		$phpmailer->SMTPSecure = getenv( 'SMTP_ENCRYPTION' );
	} );


} );

?>