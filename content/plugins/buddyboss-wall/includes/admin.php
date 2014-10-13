<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Wall
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'BuddyBoss_Wall_Admin' ) ):
/**
 *
 * BuddyBoss Wall Admin
 * ********************
 *
 *
 */
class BuddyBoss_Wall_Admin
{
	/* Options/Load
	 * ===================================================================
	 */

	/**
	 * Plugin options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Empty constructor function to ensure a single instance
	 */
	public function __construct()
	{
		// ... leave empty, see Singleton below
	}


	/* Singleton
	 * ===================================================================
	 */

	/**
	 * Admin singleton
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @param  array  $options [description]
	 *
	 * @uses BuddyBoss_Wall_Admin::setup() Init admin class
	 *
	 * @return object Admin class
	 */
	public static function instance()
	{
		static $instance = null;

		if ( null === $instance )
		{
			$instance = new BuddyBoss_Wall_Admin;
			$instance->setup();
		}

		return $instance;
	}


	/* Utility functions
	 * ===================================================================
	 */

	/**
	 * Get option
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @param  string $key Option key
	 *
	 * @uses BuddyBoss_Wall_Plugin::option() Get option
	 *
	 * @return mixed      Option value
	 */
	public function option( $key )
	{
		$value = buddyboss_wall()->option( $key );
		return $value;
	}

	/* Actions/Init
	 * ===================================================================
	 */

	/**
	 * Setup admin class
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses buddyboss_wall() Get options from main BuddyBoss_Wall_Plugin class
	 * @uses is_admin() Ensures we're in the admin area
	 * @uses curent_user_can() Checks for permissions
	 * @uses add_action() Add hooks
	 */
	public function setup()
	{
		if ( ( ! is_admin() && ! is_network_admin() ) || ! current_user_can( 'manage_options' ) )
		{
			return;
		}

		$actions = array(
			'admin_init',
			'admin_menu',
			'network_admin_menu'
		);

		if ( isset( $_GET['page'] ) && ( $_GET['page'] == 'buddyboss-wall/includes/admin.php' ) )
		{
			$actions[] = 'admin_enqueue_scripts';
		}

		foreach( $actions as $action )
		{
			add_action( $action, array( $this, $action ) );
		}
	}

	/**
	 * Register admin settings
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses register_setting() Register plugin options
	 * @uses add_settings_section() Add settings page option sections
	 * @uses add_settings_field() Add settings page option
	 */
	public function admin_init()
	{
		register_setting( 'buddyboss_wall_plugin_options', 'buddyboss_wall_plugin_options', array( $this, 'plugin_options_validate' ) );
		add_settings_section( 'general_section', __( 'General Settings', 'buddyboss-wall' ), array( $this, 'section_general' ), __FILE__ );
		// add_settings_section( 'style_section', 'Style Settings', array( $this, 'section_style' ), __FILE__ );

		//general options
		add_settings_field( 'enabled', __( 'Enable Wall Component', 'buddyboss-wall' ), array( $this, 'setting_enabled' ), __FILE__, 'general_section' );
		add_settings_field( 'all-members', __( 'Available to all members', 'buddyboss-wall' ), array( $this, 'setting_available_to_allmembers' ), __FILE__, 'general_section' );
		// add_settings_field('touch-icon', 'Homescreen Icon', array( $this, 'setting_touch_icon' ), __FILE__, 'general_section');
		// add_settings_field('ipad-theme', 'Mobile iPad Theme', array( $this, 'setting_ipad_theme' ), __FILE__, 'general_section');

		//style options
		// add_settings_field('theme', 'Mobile Theme', array( $this, 'setting_theme' ), __FILE__, 'style_section');
		//add_settings_field('theme-style', 'Theme Style', array( $this, 'setting_theme_style' ), __FILE__, 'style_section');
		// add_settings_field('toolbar-color', 'Toolbar Color', array( $this, 'setting_toolbar_color' ), __FILE__, 'style_section');
		// add_settings_field('background-color', 'Background Color', array( $this, 'setting_background_color' ), __FILE__, 'style_section');
	}

	/**
	 * Add plugin settings page
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses add_options_page() Add plugin settings page
	 */
	public function admin_menu()
	{
		add_options_page( 'BuddyBoss Wall', 'BuddyBoss Wall', 'manage_options', __FILE__, array( $this, 'options_page' ) );
	}

	/**
	 * Add plugin settings page
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses BuddyBoss_Wall_Admin::admin_menu() Add settings page option sections
	 */
	public function network_admin_menu()
	{
		return $this->admin_menu();
	}

	/**
	 * Register admin scripts
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses wp_enqueue_script() Enqueue admin script
	 * @uses wp_enqueue_style() Enqueue admin style
	 * @uses buddyboss_wall()->assets_url Get plugin URL
	 */
	public function admin_enqueue_scripts()
	{
		$js  = buddyboss_wall()->assets_url . '/js/';
		$css = buddyboss_wall()->assets_url . '/css/';
	}

	/* Settings Page + Sections
	 * ===================================================================
	 */

	/**
	 * Render settings page
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses do_settings_sections() Render settings sections
	 * @uses settings_fields() Render settings fields
	 * @uses esc_attr_e() Escape and localize text
	 */
	public function options_page()
	{
	?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2>BuddyBoss Wall</h2>
			<form action="options.php" method="post">
			<?php settings_fields('buddyboss_wall_plugin_options'); ?>
			<?php do_settings_sections(__FILE__); ?>

			<p class="submit">
				<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
			</p>
			</form>
		</div>

	<?php
	}

	/**
	 * General settings section
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 */
	public function section_general()
	{
		_e( 'Make sure BuddyPress <strong>Activity Streams</strong> are enabled for the Wall to function. Go to <em>Settings &rarr; BuddyPress &rarr; Components</em>', 'buddyboss-wall' );
	}

	/**
	 * Style settings section
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 */
	public function section_style()
	{

	}

	/**
	 * Validate plugin option
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 */
	public function plugin_options_validate( $input )
	{
		$input['enabled'] = sanitize_text_field( $input['enabled'] );

		return $input; // return validated input
	}

	/* Settings Page Options
	 * ===================================================================
	 */

	/**
	 * Setting > BuddyBoss Wall Enabled
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses BuddyBoss_Wall_Admin::option() Get plugin option
	 */
	public function setting_enabled()
	{
		$value = buddyboss_wall()->is_enabled();

		$checked = '';

		if ( $value )
		{
			$checked = ' checked="checked" ';
		}

		echo "<input ".$checked." id='enabled' name='buddyboss_wall_plugin_options[enabled]' type='checkbox' />  ";

		_e( 'Enable Wall Component.', 'buddyboss-wall' );
	}

	/**
	 * Setting > all members
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses BuddyBoss_Wall_Admin::option() Get plugin option
	 */
	public function setting_available_to_allmembers()
	{
		$value = $this->option( 'all-members' );

		$checked = '';

		if ( $value )
		{
			$checked = ' checked="checked" ';
		}

		echo "<input ".$checked." id='all-members' name='buddyboss_wall_plugin_options[all-members]' type='checkbox' />  ";

		_e('Allow Wall posting for all members (not just friends).', 'buddyboss-wall');
	}
	
	/**
	 * Setting > iPad Theme
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses BuddyBoss_Wall_Admin::option() Get plugin option
	 */
	public function setting_ipad_theme()
	{
		$value = $this->option( 'ipad-theme' );

		$checked = '';

		if ( $value )
		{
			$checked = ' checked="checked" ';
		}

		echo "<input ".$checked." id='ipad-theme' name='buddyboss_wall_plugin_options[ipad-theme]' type='checkbox' />  ";

		_e('Enable mobile theme on iPad.', 'buddyboss-wall');
	}

	/**
	 * Setting > Choose Theme
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses BuddyBoss_Wall_Admin::option() Get plugin option
	 * @uses wp_get_themes() Get themes
	 * @uses _e() Echo and localize text
	 */
	public function setting_theme()
	{
		$themeop = $this->option( 'theme' );

		$themes = wp_get_themes();

		$base = array( 'iphone', 'bootpress' );

		foreach ( $themes as $index => $data )
		{
			if ( !in_array( $data['Template'], $base ) )
			{
				unset($themes[$index]);
			}
		}

		$data = json_decode( $themeop );
		$themer = $data->theme;

		echo "<select id='theme' name='buddyboss_wall_plugin_options[theme]'>";

		foreach( $themes as $theme => $data  )
		{
			$id = $theme;

			$ar = array(
				'theme' => $theme,
				'template' => $data['Template']
			);

			$val = json_encode($ar);

			$selected = ( $themer == $id ) ? 'selected="selected"' : '';

			echo "<option value=$val $selected>$theme</option>" ;
		}
		echo "</select>  ";

		_e( 'Choose a theme for mobile phones.', 'buddyboss-wall' );
	}

	/**
	 * Setting > Theme Style
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses BuddyBoss_Wall_Admin::option() Get plugin option
	 */
	public function setting_theme_style()
	{
		$value = $this->option( 'theme-style' );

		$checked  = '';
		$checked2 = '';

		if ( $value === 'default' )
		{
			$checked  = ' checked="checked" ';
		}

		if ( $value === 'dark' )
		{
			$checked2 = ' checked="checked" ';
		}

		echo "<input ". $checked  ." type='radio' id='theme-style-default' name='buddyboss_wall_plugin_options[theme-style]' value='default' />   Default      ";
		echo "<input ". $checked2 ." type='radio' id='theme-style-dark' name='buddyboss_wall_plugin_options[theme-style]' value='dark' />   Dark";
	}

	/**
	 * Setting > Toolbar Color
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses BuddyBoss_Wall_Admin::option() Get plugin option
	 */
	public function setting_toolbar_color()
	{
		$value = $this->option( 'toolbar-color' );

		echo "<input id='toolbar-color' name='buddyboss_wall_plugin_options[toolbar-color]' size='20' type='text' value='$value' />";
	}

	/**
	 * Setting > Theme Style
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses BuddyBoss_Wall_Admin::option() Get plugin option
	 */
	public function setting_background_color()
	{
		$value = $this->option( 'background-color' );

		echo "<input id='background-color' name='buddyboss_wall_plugin_options[background-color]' size='20' type='text' value='$value' />";
	}

	/**
	 * Setting > Theme Style
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 *
	 * @uses BuddyBoss_Wall_Admin::option() Get plugin option
	 * @uses wp_enqueue_media() Enqueue WP media attachment libs
	 * @uses admin_url() Get WP admin URL
	 * @uses _e() Echo and localize text
	 */
	public function setting_touch_icon()
	{
		wp_enqueue_media();

		$text = $this->option( 'touch-icon' );

		$admin = admin_url() . 'media-new.php';

		echo "<input id='touch-icon' name='buddyboss_wall_plugin_options[touch-icon]' size='40' type='text' value='$text' />  ";
		echo "<input type='button' class='button' name='buddyboss-wall-touch-icon' id='buddyboss-wall-touch-icon' value='Upload' />";
		_e('   image size must be 114 x 114 px', 'buddyboss-wall');
	}
}
// End class BuddyBoss_Wall_Admin

endif;

?>