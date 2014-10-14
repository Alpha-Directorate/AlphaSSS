<?php
/*
Plugin Name: JW Filter
Pluring URI: net.tutsplus.com
Description: Just for demo purposes.
Author: Jeffrey Way
Author URI: http://net.tutsplus.com
Version: 1.0
*/

//add_filter('the_title', ucwords);

add_filter('the_content', function($content) {
	return $content . ' ' . 'Hello World 1!' . ' ' . time();
});