<?php
/**
 * @package WordPress
 * @subpackage Alphasss Memebers
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alphasss_Members_Plugin' ) ):

	class Alphasss_Members_Plugin
	{

		/**
		 * BuddyBoss Invitation uses many variables, most of which can be filtered to
		 * customize the way that it works. To prevent unauthorized access,
		 * these variables are stored in a private array that is magically
		 * updated using PHP 5.2+ methods. This is to prevent third party
		 * plugins from tampering with essential information indirectly, which
		 * would cause issues later.
		 *
		 * @see Alphasss_Members_Plugin::setup_globals()
		 * @var array
		 */
		private $data = array();

		private $main_includes = array(
			'members-class',
		);

		/**
		 * Admin includes
		 * @var array
		 */
		private $admin_includes = array(
			'admin'
		);

		/**
		 * Main BuddyBoss Wall Instance.
		 *
		 * Insures that only one instance of BuddyBoss Invitation exists in memory at any
		 * one time. Also prevents needing to define globals all over the place.
		 *
		 * @static object $instance
		 * @uses Alphasss_Members_Plugin::setup_globals() Setup the globals needed.
		 * @uses Alphasss_Members_Plugin::setup_actions() Setup the hooks and actions.
		 * @uses Alphasss_Members_Plugin::setup_textdomain() Setup the plugin's language file.
		 * @see alphasss_members()
		 *
		 * @return Alphasss_Members_Plugin
		 */
		public static function instance()
		{
			static $instance = null;

			if ( null === $instance ) {
				$instance = new Alphasss_Members_Plugin();
				$instance->setup_globals();
				$instance->setup_actions();
				$instance->setup_textdomain();
			}

			return $instance;
		}

		/**
		 * Method checks is local data variable exists
		 *
		 * @param string $key Data key
		 * @return boolean
		 */
		public function __isset( $key ) { return isset( $this->data[$key] ); }

		/**
		 * Method delete data variable by key
		 *
		 * @param string $key Data key
		 */
		public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }

		/**
		 * Magic method for getting BuddyBoss Invitation data varibles.
		 *
		 * @param string $key Data key
		 * @return mixed
		 */
		public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

		/**
		 * Magic method for setting BuddyBoss Invitation data varibles.
		 *
		 * @param string $key Data key
		 * @param mixed $value Data value
		 */
		public function __set( $key, $value ) { $this->data[$key] = $value; }

		public function css_path()
		{
			return $this->assets_url . '/css/'; 
		}

		/**
		 * Convenience function to access plugin options, returns false by default
		 *
		 * @param string $key Option key
		 *
		 * @return mixed
		 */
		public function option( $key )
		{
			$key    = strtolower( $key );
			$option = isset( $this->options[$key] ) ? $this->options[$key] : null;

			// This filter is run for every option
			$option = apply_filters( 'alphasss_members_option', $option );

			// Option specific filter name is converted to lowercase
			$filter_name = sprintf( 'alphasss_members_option_%s', strtolower( $key ) );
			$option      = apply_filters( $filter_name,  $option );

			return $option;
		}

		/**
		 * Include required admin files.
		 */
		public function load_admin()
		{
			$this->do_includes( $this->admin_includes );

			$this->admin = Alphasss_Members_Admin::instance();
		}

		/**
		 * Include required array of files in the includes directory
		 *
		 * @uses require_once() Loads include file
		 */
		public function do_includes( $includes = array() )
		{
			foreach( (array)$includes as $include )
			{
				require_once( $this->includes_dir . '/' . $include . '.php' );
			}
		}

		/**
		 * Include required files.
		 */
		public function load_main()
		{
			$this->do_includes( $this->main_includes );

			// @todo code members functionality here
			//$this->component = new alphasss_members_BP_Component();
		}

		/**
		 * Setup BuddyBoss Invitation plugin global variables
		 */
		private function setup_globals( $args = array() )
		{
			$saved_options = get_option( 'alphasss_members_plugin_options' );
			$saved_options = maybe_unserialize( $saved_options );

			$this->options = wp_parse_args( $saved_options, $this->default_options );

			// Normalize legacy uppercase keys
			foreach( $this->options as $key => $option ) {
				// Delete old entry
				unset( $this->options[$key] );

				// Override w/ lowercase key
				$this->options[ strtolower( $key) ] = $option;
			}

			$this->file       = Alphasss_Members_Plugin_FILE;
			$this->basename   = plugin_basename( $this->file );
			$this->plugin_dir = ALPHASSS_MEMBERS_PLUGIN_DIR;
			$this->plugin_url = ALPHASSS_MEMBERS_PLUGIN_URL;

			// Languages
			$this->lang_dir = ALPHASSS_MEMBERS_PLUGIN_DIR . '/languages';

			// Includes
			$this->includes_dir = $this->plugin_dir . 'includes';
			$this->includes_url = $this->plugin_url . 'includes';
			//--

			// Templates
			$this->templates_dir = $this->plugin_dir . 'templates';
			$this->templates_url = $this->plugin_url . 'templates';
			//--

			// Assets
			$this->assets_dir = $this->plugin_dir . 'assets';
			$this->assets_url = $this->plugin_url . 'assets';
			//--
		}

		/**
		 * Setup BuddyBoss Invitation main actions
		 */
		private function setup_actions()
		{
			// Admin
			if ( ( is_admin() || is_network_admin() ) && current_user_can( 'manage_options' ) ) {
				$this->load_admin();
			}

			// Hook into BuddyPress init
			add_action( 'bp_loaded', array( $this, 'load_main' ) );
		}

		/**
		 * Method setup right localization
		 */
		private function setup_textdomain() {			
			//if not found, then load from buddboss-wall/languages/ directory
			load_plugin_textdomain( 'alphasss-members', false, $this->lang_dir );
		}

		private function __construct() {}

		private function __clone() {}

		private function __wakeup() {}
	}
endif;
?>