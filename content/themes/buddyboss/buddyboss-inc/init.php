<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss
 * @since BuddyBoss 2.0
 */


/****************************** MAIN BUDDYBOSS THEME CLASS ******************************/

Class BuddyBoss_Theme
{
	/**
	 * BuddyBoss parent/main theme path
	 * @var string
	 */
	public $tpl_dir;

	/**
	 * BuddyBoss parent theme url
	 * @var string
	 */
	public $tpl_url;

	/**
	 * BuddyBoss includes path
	 * @var string
	 */
	public $inc_dir;

	/**
	 * BuddyBoss includes url
	 * @var string
	 */
	public $inc_url;

	/**
	 * BuddyBoss options array
	 * @var array
	 */
	public $opt;

	/**
	 * BuddyBoss modules array
	 * @var array
	 */
	public $mods;

	/**
	 * Check if BuddyPress is active
	 */
	public $buddypress_active;

	/**
	 * Check if BBPress is active
	 */
	public $bbpress_active;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		/**
		 * Globals, constants, theme path etc
		 */
		$this->globals();

		/**
		 * Load BuddyBoss options
		 */
		$this->options();

		/**
		 * Load required theme files
		 */
		$this->includes();

		/**
		 * Actions/filters
		 */
		$this->actions_filters();

		/**
		 * Assets
		 */
		$this->assets();
	}

	/**
	 * Global variables
	 */
	public function globals()
	{
		global $bp, $buddyboss_debug_log, $buddyboss_js_params;

		// Get theme path
		$this->tpl_dir = get_template_directory();

		// Get theme url
		$this->tpl_url = get_template_directory_uri();

		// Get includes path
		$this->inc_dir = $this->tpl_dir . '/buddyboss-inc';

		// Get includes url
		$this->inc_url = $this->tpl_url . '/buddyboss-inc';

		if ( ! defined( 'BUDDYBOSS_DEBUG' ) ) define( 'BUDDYBOSS_DEBUG', false );

		// Set BuddyPress and BBPress as inactive by default, then we hook into
		// their init actions to set these variables to true when they're active
		$this->buddypress_active = false;
		$this->bbpress_active = false;

		// A variable to hold the event log
		$buddyboss_debug_log = "";

		// Child themes can add variables to this array for JS on the front end
		if ( empty( $buddyboss_js_params ) )
		{
			$buddyboss_js_params = array();
		}
	}

	/**
	 * Load options
	 */
	public function options()
	{
		$opt = get_option( 'buddyboss_theme_options' );
	}

	/**
	 * Includes
	 */
	public function includes()
	{
		// Admin options
		require_once( $this->inc_dir . '/admin.php' );

		// Theme setup
		require_once( $this->inc_dir . '/theme-functions.php' );

		// Theme customizer
		require_once( $this->inc_dir . '/buddyboss-customizer/buddyboss-customizer-loader.php' );

		// Slides
		require_once( $this->inc_dir . '/buddyboss-slides/buddyboss-slides-loader.php' );

		// BuddyPress legacy plugin support
		require_once( $this->inc_dir . '/buddyboss-bp-legacy/bp-legacy-loader.php' );

		// Debug functions
		require_once( $this->inc_dir . '/debug.php' );

		// BuddyPress Modules
		if ( class_exists( 'BP_Component' ) )
		{
			// Widgets
			require_once( $this->inc_dir . '/buddyboss-widgets/buddyboss-profile-widget-loader.php' );
		}

		// Allow automatic updates via the WordPress dashboard
		require_once( $this->inc_dir . '/wp-updates-theme.php' );
		new WPUpdatesThemeUpdater_799( 'http://wp-updates.com/api/2/theme', basename( get_template_directory() ) );
	}

	/**
	 * Actions and filters
	 */
	public function actions_filters()
	{
		if ( BUDDYBOSS_DEBUG )
		{
			add_action( 'bp_footer', 'buddyboss_dump_log' );
		}

		// If BuddyPress or BBPress is active we'll update our
		// global variable (theme uses this later on)
		add_action( 'bp_init', array( $this, 'set_buddypress_active' ) );
		add_action( 'bbp_init', array( $this, 'set_bbpress_active' ) );
	}

	/**
	 * Assets
	 */
	public function assets()
	{
		if ( ! class_exists( 'BP_Legacy' ) )
		{
			return false;
		}
	}

	/**
	 * Set BuddyPress global variable to true
	 */
	public function set_buddypress_active()
	{
		$this->buddypress_active = true;
	}

	/**
	 * Set BBPress global variable to true
	 */
	public function set_bbpress_active()
	{
		$this->bbpress_active = true;
	}

	/**
	 * Utility function for loading modules
	 */
	public function add_mod( $mod_info )
	{
		if ( ! isset( $mod_info['name'] ) )
		{
			wp_die( __( 'Module does not have the proper info array', 'buddyboss' ) );
		}

		$this->mods[ $mod_info['name'] ] = $mod_info;

		return true;
	}

	/**
	 * Check if a module is active
	 */
	public function is_active( $name )
	{
		$active = false;

		// Check for active module
		if ( isset( $this->mods[$name] )
			   && isset( $this->mods[$name]['active'] )
			   && $this->mods[$name]['active'] == true )
		{
			$active = true;
		}

		// Check for active module (old way, soon to be deprecated)
		if ( isset( $this->opt['mod_'.$name] ) )
		{
			return $this->opt['mod_'.$name];
		}

		return $active;
	}
}

$GLOBALS['buddyboss'] = new BuddyBoss_Theme;

function buddyboss() {
	return $GLOBALS['buddyboss'];
}
?>