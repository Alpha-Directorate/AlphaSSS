<?php
/**
 * @package WordPress
 * @subpackage Alphasss Gf Finances
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \AlphaSSS\Repositories\User;
use \AlphaSSS\Helpers\Arr;

if ( ! class_exists( 'Alphasss_Gf_Finances_BP_Component' ) ):

	class Alphasss_Gf_Finances_BP_Component extends BP_Component
	{
		public function __construct()
		{
			parent::start(
				'gf-finances',
				__( 'Financials' , 'alphasss' ),
				dirname( __FILE__ )
			);
		}

		public function option( $key )
		{
			return alphasss_gf_finances()->option( $key );
		}

		public function setup_globals( $args = array() )
		{
			add_action( 'bp_setup_nav', array($this, 'update_bp_menus'), 100 );
			add_action('bp_before_member_header_meta', array($this, 'setup_text_below_activity'));
			wp_enqueue_script( 'tablesorter', ALPHASSS_GF_FINANCES_PLUGIN_URL . 'assets/js/tablesorter/jquery.tablesorter.min.js',array('jquery') );
			wp_enqueue_script( 'jstz', ALPHASSS_GF_FINANCES_PLUGIN_URL . 'assets/js/jstz.min.js', array('jquery') );
			wp_enqueue_script( 'mustache', ALPHASSS_GF_FINANCES_PLUGIN_URL . 'assets/js/mustache.min.js', array('jquery') );
			wp_enqueue_style( 'tablesorter-css', ALPHASSS_GF_FINANCES_PLUGIN_URL.'assets/js/tablesorter/themes/blue/style.css' );

			parent::setup_globals();
		}

		public function setup_actions()
		{
			parent::setup_actions();
		}

		public function setup_text_below_activity()
		{
			global $bp;
			
			$permitted_pages_for_activity_text = array_map(function($url){
				$url = parse_url($url);

				return $url['path'];
			},
			[
				$bp->displayed_user->domain . 'accounting/',
				$bp->displayed_user->domain . 'accounting/',
				$bp->displayed_user->domain . 'accounting/'
			]);
			
			if ( in_array( Arr::get( $_SERVER, 'REQUEST_URI' ), $permitted_pages_for_activity_text) ) {
				alphasss_gf_finances_load_template('members/single/alphasss_gf_finances_activity_text');
			}
		}

		/**
		 * Method add new intem in profile menu
		 */
		public function update_bp_menus()
		{
			global $bp;

			if ( 
				! is_user_logged_in() || 
				! User::hasRole('gf') || 
				( $bp->displayed_user->domain != $bp->loggedin_user->domain ) ) return;

			bp_core_new_nav_item( array(
				'name'                => __( 'Financials', 'alphasss' ),
				'slug'                => 'accounting',
				'position'            => 200,
				'screen_function'     => 'alphasss_gf_finances_screen_grid',
				'default_subnav_slug' => 'my-accounting'
			) );

			$alphasss_gf_finances_link = $bp->displayed_user->domain;

			bp_core_new_subnav_item( array(
				'name'            => __( 'Accounting', 'alphasss' ),
				'slug'            => 'my-accounting',
				'parent_slug'     => 'accounting',
				'parent_url'      => $alphasss_gf_finances_link,
				'screen_function' => 'alphasss_gf_finances_screen_grid',
				'position'        => 10
			) );

			bp_core_new_subnav_item( array(
				'name'            => __( 'Time Value', 'alphasss' ),
				'slug'            => 'my-time-value',
				'parent_slug'     => 'accounting',
				'parent_url'      => $alphasss_gf_finances_link,
				'screen_function' => 'alphasss_gf_finances_screen_grid',
				'position'        => 20
			) );

			bp_core_new_subnav_item( array(
				'name'            => __( 'Levels', 'alphasss' ),
				'slug'            => 'my-levels',
				'parent_slug'     => 'accounting',
				'parent_url'      => $alphasss_gf_finances_link,
				'screen_function' => 'alphasss_gf_finances_screen_grid',
				'position'        => 30
			) );
		}

		public function setup_admin_bar( $wp_admin_nav = array() ) {
			parent::setup_admin_bar($wp_admin_nav);
		}
	}

	function alphasss_gf_finances_screen_grid() {
		add_action( 'bp_template_content', function() {
			alphasss_gf_finances_load_template( 'members/single/alphasss-gf-finances-accounting' );
		} );
		bp_core_load_template( apply_filters( 'alphasss_finances_screen_grid', 'members/single/plugins' ) );
	}

	function alphasss_gf_finances_load_template($template) {
		$template .= '.php';

		include_once alphasss_gf_finances()->templates_dir.'/'.$template;
	}
endif;
?>