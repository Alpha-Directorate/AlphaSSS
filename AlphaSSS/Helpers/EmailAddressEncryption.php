<?php namespace AlphaSSS\Helpers;

if ( ! defined( 'ABSPATH' ) ) exit;

use AlphaSSS\Helpers\Encryption;

class EmailAddressEncryption {

	/**
	 * This method descrypts user email
	 * 
	 * @param string $encrypted_email Encrypted email
	 * @return string
	 */
	public static function decode($encrypted_email)
	{
		return ( new Encryption )->decode( str_replace('@alphasss.com', '', $encrypted_email ) );
	}

	/**
	 * This method encrypt user email
	 * 
	 * @param string $user_email User email
	 * @return string
	 */
	public static function encode($user_email)
	{
		return ( new Encryption )->encode( $user_email ) . '@alphasss.com';
	}
}