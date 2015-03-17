<?php

/**
 * Returns URI for non-member register page
 * 
 * @return string
 */
function get_non_member_register_uri()
{
	return '/register/';
}

/**
 * Returns URI for pre-member register page
 * 
 * @return string
 */
function get_pre_member_register_uri()
{
	return '/register-pre-member/';
}

/**
 * This function returns url for non-member register page
 * 
 * @uses detect_protocol_name() for detection protocol name
 * @uses get_pre_member_register_uri() get non-member register URI
 * @uses site_url() retrieves the site url for the current site
 * @see site_url() http://codex.wordpress.org/Function_Reference/site_url
 * 
 * @return string
 */
function get_non_member_register_url()
{
	return str_replace( '/wp', '',  site_url( get_non_member_register_uri(), detect_protocol_name() ) );
}

/**
 * This function returns url for pre-member register page
 * 
 * @uses detect_protocol_name() for detection protocol name
 * @uses get_non_member_register_uri() get pre-member register URI
 * @uses site_url() retrieves the site url for the current site
 * @see site_url() http://codex.wordpress.org/Function_Reference/site_url
 * 
 * @return string
 */
function get_pre_member_register_url()
{
	return str_replace( '/wp', '', site_url( get_pre_member_register_uri(), detect_protocol_name() ) );
}

/**
 * This function returns url for register page
 * 
 * @uses is_user_logged_in() function checks is user logged in
 * @uses get_non_member_register_url()
 * @uses get_pre_member_register_url()
 * 
 * @return string
 */
function get_register_url()
{
	return is_user_logged_in()
		? get_pre_member_register_url()
		: get_non_member_register_url();
}

?>