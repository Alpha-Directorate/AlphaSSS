<?php namespace AlphaSSS\HTTP;

if ( ! defined( 'ABSPATH' ) ) exit;

class HTTP {

	/**
	 * This function returns the protocol name
	 * 
	 * @uses is_ssl() checks if SSL is being used
	 * @see is_ssl() http://codex.wordpress.org/Function_Reference/is_ssl
	 * 
	 * @return string
	 */
	public static function protocol()
	{
		return is_ssl() ? 'https' : 'http';
	}

	/**
	 * This function checks is a current request method is POST
	 * 
	 * @return boolean
	 */
	public static function isPost()
	{
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	} 
}