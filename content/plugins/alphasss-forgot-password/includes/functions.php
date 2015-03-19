<?php

/**
 * This function returns forgot password url
 * 
 * @uses detect_protocol_name() for detection protocol name
 * @uses site_url() retrieves the site url for the current site
 * @see site_url() http://codex.wordpress.org/Function_Reference/site_url
 * 
 * @return string
 */
function get_forgot_password_url()
{
	return str_replace( '/wp', '',  site_url( '/forgot-password/', detect_protocol_name() ) );
}

?>