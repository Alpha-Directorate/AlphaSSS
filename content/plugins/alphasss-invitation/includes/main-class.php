<?php
/**
 * @package WordPress
 * @subpackage Alphasss Invitation
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use \Carbon\Carbon;

if ( ! class_exists( 'Alphasss_Invitation_Plugin' ) ):

	class Alphasss_Invitation_Plugin
	{

		/**
		 * Alphasss Invitation uses many variables, most of which can be filtered to
		 * customize the way that it works. To prevent unauthorized access,
		 * these variables are stored in a private array that is magically
		 * updated using PHP 5.2+ methods. This is to prevent third party
		 * plugins from tampering with essential information indirectly, which
		 * would cause issues later.
		 *
		 * @see Alphasss_Invitation_Plugin::setup_globals()
		 * @var array
		 */
		private $data = array();

		private $main_includes = array(
			'invitation-class',
		);

		/**
		 * Admin includes
		 * @var array
		 */
		private $admin_includes = array(
			'admin'
		);

		/**
		 * Main Alphasss Invintation Instance.
		 *
		 * Insures that only one instance of Alphasss Invitation exists in memory at any
		 * one time. Also prevents needing to define globals all over the place.
		 *
		 * @static object $instance
		 * @uses Alphasss_Invitation_Plugin::setup_globals() Setup the globals needed.
		 * @uses Alphasss_Invitation_Plugin::setup_actions() Setup the hooks and actions.
		 * @uses Alphasss_Invitation_Plugin::setup_textdomain() Setup the plugin's language file.
		 * @see alphasss_invitation()
		 *
		 * @return Alphasss_Invitation_Plugin
		 */
		public static function instance()
		{
			static $instance = null;

			if ( null === $instance ) {
				$instance = new Alphasss_Invitation_Plugin();
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
		 * Magic method for getting Alphasss Invitation data varibles.
		 *
		 * @param string $key Data key
		 * @return mixed
		 */
		public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

		/**
		 * Magic method for setting Alphasss Invitation data varibles.
		 *
		 * @param string $key Data key
		 * @param mixed $value Data value
		 */
		public function __set( $key, $value ) { $this->data[$key] = $value; }

		/**
		 * Method retuns invitation codes received by user
		 * @param integer $user_id
		 * 
		 * @return array
		 */
		public function get_user_received_codes($user_id)
		{
			global $wpdb;

			$sort = array('ASC', 'DESC');
			
			$results = $wpdb->get_results( sprintf('
				SELECT * FROM 
					`%s` 
				WHERE 
					requested_member_id = %d 
				ORDER BY 
					created_date ASC ;', sanitize_text_field(ALPHASSS_INVITATION_TABLENAME), (int) $user_id), ARRAY_A );

			foreach ($results as &$result) {
				$user = get_user_by( 'id', (int) $result['member_id'] );

				$result['nickname']   = ($user) ? $user->display_name : null;
				$result['is_expired'] = Carbon::now()->timestamp > Carbon::parse( $result['expired_date'] )->timestamp;
				$result['date']       = str_replace('after', '', Carbon::now()->diffForHumans( Carbon::parse( $result['created_date'] ) ) );
			}

			return $results;
		}

		/**
		 * Method retuns unused registration code
		 * @param integer $requestor_id
		 * 
		 * @return string
		 */
		public function get_invitation_code($requestor_nickname = NULL)
		{
			global $wpdb;

			$sort = array('ASC', 'DESC');
			
			$results = $wpdb->get_results( sprintf('
				SELECT * FROM 
					`%s` 
				WHERE 
					member_id IS NULL AND is_active="NO" 
				ORDER BY 
					id %s 
				LIMIT 50;', sanitize_text_field(ALPHASSS_INVITATION_TABLENAME), $sort[ array_rand( $sort )] ), ARRAY_A );

			$result = $results[ array_rand( $results ) ];

			$data = array( 
				'member_id'    => get_current_user_id(),
				'is_active'    => 'YES',
				'created_date' => Carbon::now(),
				'expired_date' => Carbon::now()->addSeconds( $this->option( 'time-to-expire' ) )
			);

			if ($requestor_nickname = sanitize_text_field($requestor_nickname)) {

				// Is requestor exists?
				if ( $user = $wpdb->get_row( $wpdb->prepare(
					"SELECT `ID` FROM $wpdb->users WHERE `display_name` = %s LIMIT 1", $requestor_nickname
				) ) ) {
					$data['requested_member_id'] = $user->ID;
				}
			}

			$wpdb->update( 
				sanitize_text_field(ALPHASSS_INVITATION_TABLENAME), 
				$data, 
				array( 'id' => $result['id'] ) 
			);
			
			return $result['invitation_code'];
		}

		/**
		 * Method validate invitation code on errors
		 * 
		 * @return array
		 */
		public function validate_invitation_code($invitation_code)
		{
			global $wpdb;

			$result['is_success'] = true;

			$invitation_code = sanitize_text_field(strtoupper($invitation_code));

			$record = $wpdb->get_results( sprintf('
				SELECT * FROM 
					`%s`
				WHERE 
					member_id IS NOT NULL AND is_active="YES" AND invitation_code = "%s" AND activated_member_id IS NULL'
				, sanitize_text_field(ALPHASSS_INVITATION_TABLENAME), $invitation_code ), ARRAY_A );

			if ( $record ) {
				$record = $record[0];

				if ( Carbon::now()->timestamp > Carbon::parse($record['expired_date'])->timestamp ){
					$result['is_success'] = false;
					$result['message']    = __('This code is older than 24 hours, and is no longer valid. Simply request a new invitation code.', 'alphasss-invitation');
				}

			} else {
				$result['is_success'] = false;
				$result['message']    = __('This is not a valid invitation code. If you don\'t have one, just ask for it.', 'alphasss-invitation');
			}

			return $result;
		}

		public function update_invitation_code($invitation_code, $data)
		{
			global $wpdb;

			$wpdb->update( 
				sanitize_text_field(ALPHASSS_INVITATION_TABLENAME), 
				$data,
				array( 'invitation_code' => $invitation_code ) 
			);
		}

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
			$option = apply_filters( 'alphasss_invitation_option', $option );

			// Option specific filter name is converted to lowercase
			$filter_name = sprintf( 'alphasss_invitation_option_%s', strtolower( $key ) );
			$option      = apply_filters( $filter_name,  $option );

			return $option;
		}

		/**
		 * Include required admin files.
		 */
		public function load_admin()
		{
			$this->do_includes( $this->admin_includes );

			$this->admin = Alphasss_Invitation_Admin::instance();
		}

		/**
		 * Include required array of files in the includes directory
		 *
		 * @uses require_once() Loads include file
		 */
		public function do_includes( $includes = array() )
		{
			foreach( (array)$includes as $include ) {
				require_once( $this->includes_dir . '/' . $include . '.php' );
			}
		}

		/**
		 * Include required files.
		 */
		public function load_main()
		{
			$this->do_includes( $this->main_includes );

			$this->component = new Alphasss_Invitation_BP_Component();
		}

		/**
		 * Setup Alphasss Invitation plugin global variables
		 */
		private function setup_globals( $args = array() )
		{
			$saved_options = get_option( 'alphasss_invitation_plugin_options' );
			$saved_options = maybe_unserialize( $saved_options );

			$this->options = wp_parse_args( $saved_options, $this->default_options );

			// Normalize legacy uppercase keys
			foreach( $this->options as $key => $option ) {
				// Delete old entry
				unset( $this->options[$key] );

				// Override w/ lowercase key
				$this->options[ strtolower( $key) ] = $option;
			}

			$this->file       = ALPHASSS_INVITATION_PLUGIN_FILE;
			$this->basename   = plugin_basename( $this->file );
			$this->plugin_dir = ALPHASSS_INVITATION_PLUGIN_DIR;
			$this->plugin_url = ALPHASSS_INVITATION_PLUGIN_URL;

			// Languages
			$this->lang_dir = ALPHASSS_INVITATION_PLUGIN_DIR . '/languages/';

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
		 * Setup Alphasss Invitation main actions
		 */
		private function setup_actions()
		{
			// Admin
			if ( ( is_admin() || is_network_admin() ) && current_user_can( 'manage_options' ) ) {
				$this->load_admin();
			}

			// Hook into Alphasss init
			add_action( 'bp_loaded', array( $this, 'load_main' ) );
		}

		/**
		 * Method setup right localization
		 */
		private function setup_textdomain() {
			load_textdomain( 'alphasss-invitation', WP_LANG_DIR . '/plugins/alphasss/alphasss-' . get_locale() . '.mo' );
		}

		private function __construct() {}

		private function __clone() {}

		private function __wakeup() {}
	}

endif;
?>