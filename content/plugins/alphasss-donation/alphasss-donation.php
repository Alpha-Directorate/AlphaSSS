<?php
/**
 * Plugin Name: AlphaSSS Donation
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss micro donation plugin
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 */

use AlphaSSS\Helpers\Arr;

add_action( 'bp_loaded', function(){

	load_plugin_textdomain( 'alphasss-donation', false, basename( dirname( __FILE__ ) ) . '/languages' );

	class AlphaSSS_Donation extends BP_Group_Extension {

		public function __construct() {
			parent::init( [
				'slug'   => 'micro-donate',
				'name'   => __( 'micro-Donate', 'alphasss-donation' ),
				'access' => 'non-admin'
			] );
		}

		/**
		 * Method checks whether the current user meets an access condition.
		 * Added one custom option 'non-admin'
		 *
		 * @param string $access_condition 'anyone', 'loggedin', 'member',
		 *        'mod', 'non-admin', 'admin' or 'noone'.
		 * @return bool
		 */
		protected function user_meets_access_condition( $access_condition ) {
			$group = groups_get_group( array(
				'group_id' => $this->group_id,
			) );

			switch ( $access_condition ) {
				case 'admin' :
					$meets_condition = groups_is_user_admin( bp_loggedin_user_id(), $this->group_id );
					break;

				case 'non-admin':
					$meets_condition = ! groups_is_user_admin( bp_loggedin_user_id(), $this->group_id );
					break;

				case 'mod' :
					$meets_condition = groups_is_user_mod( bp_loggedin_user_id(), $this->group_id );
					break;

				case 'member' :
					$meets_condition = groups_is_user_member( bp_loggedin_user_id(), $this->group_id );
					break;

				case 'loggedin' :
					$meets_condition = is_user_logged_in();
					break;

				case 'noone' :
					$meets_condition = false;
					break;

				case 'anyone' :
				default :
					$meets_condition = true;
					break;
			}

			return $meets_condition;
		}

		/**
		* Method contains the markup that will be displayed on the main
		* plugin tab
		*/
		public function display( $group_id = null )
		{
			$group_id = bp_get_group_id();
			echo 'What a cool plugin!';
		}

		/**
		* settings_screen() is the catch-all method for displaying the content
		* of the edit, create, and Dashboard admin panels
		*/
		public function settings_screen( $group_id = null )
		{
			$setting = groups_get_groupmeta( $group_id, 'group_extension_example_1_setting' );

			/**
			* Save your plugin setting here: <input type="text" name="group_extension_example_1_setting" value="<?php echo esc_attr( $setting ) ?>" />
			*/
		}

		/**
		* settings_sceren_save() contains the catch-all logic for saving
		* settings from the edit, create, and Dashboard admin panels
		*/
		public function settings_screen_save( $group_id = null )
		{
			$setting = Arr::get( $_POST, 'group_extension_example_1_setting', '' ); // input var okay

			groups_update_groupmeta( $group_id, 'group_extension_example_1_setting', $setting );
		}
	}

	bp_register_group_extension( 'AlphaSSS_Donation' );
});