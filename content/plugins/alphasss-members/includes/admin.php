<?php
/**
 * @package WordPress
 * @subpackage Alphasss Members
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Alphasss_Members_Admin' ) ):

class Alphasss_Members_Admin
{
	/**
	 * Plugin options
	 *
	 * @var array
	 */
	public $options = array();

	private function __construct(){}

	public static function instance()
	{
		static $instance = null;

		if ( null === $instance ) {
			$instance = new Alphasss_Members_Admin;
			$instance->setup();
		}

		return $instance;
	}

	/**
	 * Get option
	 *
	 * @param  string $key Option key
	 *
	 * @return mixed Option value
	 */
	public function option( $key )
	{
		return alphasss_members()->option( $key );
	}

	/**
	 * Setup admin class
	 */
	public function setup()
	{
		if ( ( ! is_admin() && ! is_network_admin() ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$actions = array('admin_init', 'admin_menu', 'network_admin_menu');

		foreach( $actions as $action ) {
			add_action( $action, array( $this, $action ) );
		}
	}

	/**
	 * Register admin settings
	 */
	public function admin_init()
	{
		register_setting( 'alpahsss_members_plugin_options', 'alpahsss_members_plugin_options', array( $this, 'plugin_options_validate' ) );
		add_settings_section( 'general_section', __( 'General Settings', 'alpahsss-members' ), array( $this, 'section_general' ), __FILE__ );

		add_settings_field( 'publish-key', __( 'Publish key', 'alpahsss-members' ), array( $this, 'setting_publish_key' ), __FILE__, 'general_section' );
		add_settings_field( 'subscribe-key', __( 'Subscribe key', 'alpahsss-members' ), array( $this, 'setting_subscribe_key' ), __FILE__, 'general_section' );
		add_settings_field( 'channel-prefix', __( 'Channel prefix', 'alpahsss-members' ), array( $this, 'setting_channel_prefix' ), __FILE__, 'general_section' );
	}

	public function setting_publish_key()
	{
		if ( ! $value = $this->option( 'publish-key' ) ) {
			$value = '';
		}

		printf( '<input id="publish-key" name="alpahsss_members_plugin_options[publish-key]" value="%s" /> ', $value);

		_e('Add publish key', 'alpahsss-members');
	}


	public function setting_subscribe_key()
	{
		if ( ! $value = $this->option( 'subscribe-key' ) ) {
			$value = '';
		}

		printf( '<input id="subscribe-key" name="alpahsss_members_plugin_options[subscribe-key]" value="%s" /> ', $value);

		_e('Add subscribe key', 'alpahsss-members');
	}

	public function setting_channel_prefix()
	{
		if ( ! $value = $this->option( 'channel-prefix' ) ) {
			$value = '';
		}

		printf( '<input id="channel-prefix" name="alpahsss_members_plugin_options[channel-prefix]" value="%s" /> ', $value);

		_e('Add channel prefix', 'alpahsss-members');
	}

	
	/**
	 * Add plugin settings page
	 */
	public function admin_menu()
	{
		add_options_page( 'Alpahsss Members', 'Alpahsss Members', 'manage_options', __FILE__, array( $this, 'options_page' ) );
	}

	/**
	 * Add plugin settings page
	 */
	public function network_admin_menu()
	{
		return $this->admin_menu();
	}

	/**
	 * Render settings page
	 */
	public function options_page()
	{
	?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2>Alpahsss Members</h2>
			<form action="options.php" method="post">
			<?php settings_fields('alpahsss_members_plugin_options'); ?>
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
	 */
	public function section_general()
	{
		_e( 'Some dummy text here', 'alpahsss-members' );
	}

	/**
	 * Validate plugin option
	 *
	 * @return array
	 */
	public function plugin_options_validate( $input )
	{
		$input['publish-key']   = esc_html ( $input['publish-key'] );
		$input['subscribe-key'] = esc_html ( $input['subscribe-key'] );
		$input['channel-prefix'] = esc_html ( $input['channel-prefix'] );

		return $input;
	}
}
// End class Alpahsss_Members_Admin

endif;

?>