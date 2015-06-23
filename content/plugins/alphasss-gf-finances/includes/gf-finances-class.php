<?php
/**
 * @package WordPress
 * @subpackage Alphasss Gf Finances
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \AlphaSSS\Repositories\User;

if ( ! class_exists( 'Alphasss_Gf_Finances_BP_Component' ) ):

	class Alphasss_Gf_Finances_BP_Component extends BP_Component
	{
		public function __construct()
		{
			parent::start(
				'gf-finances',
				__( 'Finances' , 'alphasss' ),
				dirname( __FILE__ )
			);
		}

		public function option( $key )
		{
			return alphasss_gf_finances()->option( $key );
		}

		public function setup_globals( $args = array() )
		{
			if (true) {
				add_action( 'bp_setup_nav', array($this, 'update_bp_menus'), 100 );
			}

			parent::setup_globals();
		}

		public function setup_actions()
		{
			parent::setup_actions();
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

			$profile_link = $bp->loggedin_user->domain . $bp->activity->slug . '/';

			bp_core_new_nav_item( array(
				'name'                => __( 'Finances', 'alphasss' ),
				'slug'                => 'finances',
				'position'            => 200,
				'screen_function'     => 'alphasss_gf_finances_screen_grid',
				'default_subnav_slug' => 'my-finances'
			) );

			$alphasss_gf_finances_link = $bp->displayed_user->domain . $this->slug . '/';

			bp_core_new_subnav_item( array(
				'name'            => __( 'Finances', 'alphasss' ),
				'slug'            => 'finances',
				'parent_slug'     => $this->slug,
				'parent_url'      => $alphasss_gf_finances_link,
				'screen_function' => 'alphasss_gf_finances_screen_grid',
				'position'        => 10
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