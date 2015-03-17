<?php

/**
 * This function returns the protocol name
 * 
 * @uses is_ssl() checks if SSL is being used
 * @see is_ssl() http://codex.wordpress.org/Function_Reference/is_ssl
 * 
 * @return string
 */
function detect_protocol_name()
{
	return is_ssl() ? 'https' : 'http';
}

?>