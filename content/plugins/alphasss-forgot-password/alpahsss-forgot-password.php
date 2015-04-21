<?php
/**
 * Plugin Name: Alphasss Forgot Password
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss Forgot Password
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

// Require helper functions
require_once('includes/functions.php');

add_action( 'plugins_loaded', function(){
	add_filter( 'lostpassword_url', function($lostpassword_url, $redirect){
		return get_forgot_password_url();
	}, 10, 2 );

	if ( ! class_exists('Forgot_Password_Plugin')) {

		class Forgot_Password_Plugin
		{
			public $_name;
			public $page_title;
			public $page_name;
			public $page_id;

			public function __construct()
			{
				$this->_name      = 'forgot_password';
				$this->page_title = 'Forgot password';
				$this->page_name  = $this->_name;
				$this->page_id    = '0';

				register_activation_hook(__FILE__, array($this, 'activate'));
				register_deactivation_hook(__FILE__, array($this, 'deactivate'));
				register_uninstall_hook(__FILE__, array($this, 'uninstall'));

				add_filter('parse_query', array($this, 'query_parser'));
				add_filter('the_posts', array($this, 'page_filter'));
			}

			public function activate()
			{
				global $wpdb;      

				delete_option($this->_name.'_page_title');
				add_option($this->_name.'_page_title', $this->page_title, '', 'yes');

				delete_option($this->_name.'_page_name');
				add_option($this->_name.'_page_name', $this->page_name, '', 'yes');

				delete_option($this->_name.'_page_id');
				add_option($this->_name.'_page_id', $this->page_id, '', 'yes');

				$the_page = get_page_by_title($this->page_title);

				if ( ! $the_page ) {
					// Create post object
					$_p = array();
					$_p['post_title']     = $this->page_title;
					$_p['post_content']   = '';
					$_p['post_status']    = 'publish';
					$_p['post_type']      = 'page';
					$_p['comment_status'] = 'closed';
					//$_p['ping_status']    = 'closed';
					$_p['post_category']  = array(1); // the default 'Uncatrgorised'
					$_p['page_template']  = 'forgot-password.php';
				}
				else
				{
					// the plugin may have been previously active and the page may just be trashed...
					$this->page_id = $the_page->ID;

					//make sure the page is not trashed...
					$the_page->post_status = 'publish';
					$this->page_id = wp_update_post($the_page);
				}

				delete_option($this->_name.'_page_id');
				add_option($this->_name.'_page_id', $this->page_id);
			}

			public function deactivate()
			{
				$this->deletePage();
				$this->deleteOptions();
			}

			public function uninstall()
			{
				$this->deletePage(true);
				$this->deleteOptions();
			}

			public function query_parser($q)
			{
				if (isset($q->query_vars['page_id']) AND (intval($q->query_vars['page_id']) == $this->page_id )) {
					$q->set($this->_name.'_page_is_called', true);
				}
				elseif (isset($q->query_vars['pagename']) AND (($q->query_vars['pagename'] == $this->page_name) OR ($_pos_found = strpos($q->query_vars['pagename'],$this->page_name.'/') === 0))) {
					$q->set($this->_name.'_page_is_called', true);
				}
				else {
					$q->set($this->_name.'_page_is_called', false);
				}
			}

			function page_filter($posts)
			{
				global $wp_query;

				if ($wp_query->get($this->_name.'_page_is_called')) {
					//$posts[0]->post_title = __('Forgot password');
					if ($posts[0])
						$posts[0]->post_content = __("<h1>Fear of a Bot Planet</h1> <p>So, how 'bout them Knicks? Ah, yes! John Quincy Adding Machine. He struck a chord with the voters when he pledged not to go on a killing spree. Whoa a real live robot; or is that some kind of cheesy New Year's costume?</p> <h2>Bender Should Not Be Allowed on TV</h2> <p>Look, everyone wants to be like Germany, but do we really have the pure strength of 'will'? No, I'm Santa Claus! You're going to do his laundry?</p> <ul> <li>We're also Santa Claus!</li> <li>Kids don't turn rotten just from watching TV.</li> <li>And I'd do it again! And perhaps a third time! But that would be it.</li> <li>Incidentally, you have a dime up your nose.</li> </ul> <h3>The Series Has Landed</h3> <p>Have you ever tried just turning off the TV, sitting down with your children, and hitting them? You don't know how to do any of those. When the lights go out, it's nobody's business what goes on between two consenting adults. No. We're on the top. What's with you kids? Every other day it's food, food, food. Alright, I'll get you some stupid food.</p> <h4>Three Hundred Big Boys</h4> <p>Bender, we're trying our best. You're going to do his laundry? Yeah. Give a little credit to our public schools.</p> <ol> <li>You're going to do his laundry?</li> <li>Morbo will now introduce tonight's candidates&hellip; PUNY HUMAN NUMBER ONE, PUNY HUMAN NUMBER TWO, and Morbo's good friend, Richard Nixon.</li> </ol> <h5>Xmas Story</h5> <p>Why am I sticky and naked? Did I miss something fun? Yes! In your face, Gandhi! Shut up and take my money!</p>", 'alphasss-forgot-password');
				}
				return $posts;
			}

			private function deletePage($hard = false)
			{
				global $wpdb;

				$id = get_option($this->_name.'_page_id');
				if($id && $hard == true)
					wp_delete_post($id, true);
				elseif($id && $hard == false)
					wp_delete_post($id);
			}

			private function deleteOptions()
			{
				delete_option($this->_name.'_page_title');
				delete_option($this->_name.'_page_name');
				delete_option($this->_name.'_page_id');
			}
		}
	}
	$forgot_password = new Forgot_Password_Plugin();

	register_activation_hook(__FILE__, function() use ($forgot_password){
		$forgot_password->activate();
	});

	register_deactivation_hook(__FILE__, function() use ($forgot_password){
		$forgot_password->deactivate();
	});
});

?>