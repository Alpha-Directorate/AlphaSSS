<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Media
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'BuddyBoss_Media_Type_Photo' ) ):
/**
 *
 * BuddyBoss Media Photo Type BuddyPress Component
 * ***********************************************
 *
 *
 */
class BuddyBoss_Media_Type_Photo extends BP_Component
{
	/**
	 * SHOW INLINE COMMENTS PIC PAGE
	 *
	 * @since BuddyBoss Media (1.0.0)
	 */
	public $redirect_single = false;
	public $show_single = false;

	/**
	 * PICTURE GRID TEMPLATE VARIABLS
	 *
	 * @since BuddyBoss Media (1.0.0)
	 */
	public $grid_has_pics = false;
	public $grid_num_pics = 0;
	public $grid_current_pic = null;
	public $grid_photo_index = 0;
	public $grid_data = array();
	public $grid_html = null;
	public $grid_has_run = false;
	public $grid_pagination = null;
	public $grid_num_pages = 0;
	public $grid_current_page = 1;
	//@todo, pics_per_page have to be filterable. E.g: for use on 'all media page'
	//jp: these variables could all have filters/admin options, just make sure to leave
	//    a default here and then in the class' global function or somewhere during
	//    instantiation filter it there like:
	//    $this->grid_current_page = apply_filters( 'buddyboss_media_grid_pics_per_page', $this->grid_current_page )
	public $grid_pics_per_page = 15;

	/**
	* STORAGE
	*
	* @since BuddyBoss Media (1.0.0)
	*/
	public $cache;

	/**
	* FILTERS
	*
	* @since BuddyBoss Media (1.0.0)
	*/
	public $filters;
	public $hooks;

	/**
	* INITIALIZE CLASS
	*
	* @since BuddyBoss Media (1.0.0)
	*/
	public function __construct()
	{
		$component_slug = $this->option('component-slug');
		if( !$component_slug )
			$component_slug = buddyboss_media_default_component_slug();

		$slug = $this->slug = apply_filters( 'buddyboss_media_type_photo_slug', $component_slug );

		$this->hooks = new BuddyBoss_Media_Photo_Hooks();

		parent::start(
			$slug,
			__( 'Photos', 'buddyboss-media' ),
			dirname( __FILE__ )
		);
		
		// register our component as an active component in BP
		/* slug is configurable, but we'll keep the component name same,
		 * otherwise, all notifications(by this plugin) will be rendered inactive as soon as slug is changed.
		 */
		buddypress()->active_components[buddyboss_media_default_component_slug()] = '1';
	}

	/**
	 * Convenince method for getting main plugin options.
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 */
	public function option( $key )
	{
		return buddyboss_media()->option( $key );
	}

	/**
	 * SETUP GLOBAL OPTIONS
	 */
	public function setup_globals( $args = array() )
	{
		parent::setup_globals( array(
			'has_directory'			=> false,
			'notification_callback' => 'buddyboss_media_format_notifications',
		) );
	}

	/**
	 * SETUP ACTIONS
	 *
	 * @since  BuddyBoss Media (1.0.0)
	 */
	public function setup_actions()
	{
		// Add body class
		add_filter( 'body_class', array( $this, 'body_class' ) );

		/* FILTERS */
		if ( $this->option( 'enabled' ) )
		{
			add_action( 'bp_activity_after_save', array( $this->hooks, 'bp_activity_after_save' ) );
			add_filter( 'bp_get_activity_action', array( $this->hooks, 'bp_get_activity_action' ), 11 );
			add_filter( 'bp_get_activity_content_body', array( $this->hooks, 'bp_get_activity_content_body' ) );
			add_filter( 'bp_get_member_latest_update', array( $this->hooks, 'bp_get_member_latest_update' ) );
			add_action( 'wp_ajax_buddyboss_delete_media', array($this,'delete_media_ajax') );
		}
		else {
			add_filter( 'bp_get_activity_content_body', array( $this->hooks, 'off_bp_get_activity_content_body' ) );
		}

		// Globals
		// add_action( 'bp_setup_globals',  array( $this, 'setup_globals' ) );

		// Theme
		add_action( 'after_setup_theme', array( $this, 'setup_theme' ) );

		// Menu
		add_action( 'bp_setup_nav', array( $this, 'setup_bp_menu' ), 100 );

		// Front End Assets
		if ( ! is_admin() && ! is_network_admin() )
		{
			add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );

			// Script templates
			add_action( 'wp_footer', array( $this, 'script_templates' ) );
		}

		parent::setup_actions();
	}

	public function setup_theme()
	{
		add_image_size( 'buddyboss_media_photo_tn', 150, 150, true );
		//add_image_size( 'buddyboss_media_photo_med', 501, 9999 );//not used anywhere
		add_image_size( 'buddyboss_media_photo_wide', 750, 9999 );
		add_image_size( 'buddyboss_media_photo_large', 1300, 9999 );
	}

	/**
	 * Prepare array with translated messages/strings to use in JS
	 *
	 * @return array Localized BuddyBoss Media Pics messages
	 */
	public function get_js_translations()
	{
		$firstname = '';

		if ( is_user_logged_in() && function_exists( 'bp_get_user_firstname' ) )
		{
			$firstname = bp_get_user_firstname();
		}

		$js_translations = array(
			'error_photo_is_uploading' => __( 'Picture upload currently in progress, please wait until completed.', 'buddyboss-media' ),
			'error_uploading_photo'    => __( 'Error uploading photo.', 'buddyboss-media' ),
			'one_moment'               => __( 'One moment...', 'buddyboss-media' ),
			'resizing'                 => __( 'Resizing...', 'buddyboss-media' ),
			'file_browse_title'        => __( 'Upload a Picture', 'buddyboss-media' ),
			'cancel'                   => __( 'Cancel', 'buddyboss-media' ),
			'failed'                   => __( 'Failed', 'buddyboss-media' ),
			'add_photo'                => __( 'Add Photo', 'buddyboss-media' ),
			'user_add_photo'           => sprintf( __( "Add a photo, %s", 'buddyboss-media' ), $firstname ),
			'photo_uploading'          => __( 'Photo is currently uploading, please wait!', 'buddyboss-media' ),
			'sure_delete_photo'          => __( 'Sure you want to delete this photo?', 'buddyboss-media' )
		);

		return apply_filters( 'buddyboss_media_js_translations', $js_translations );
	}

	/**
	 * Prepare array with current state that needs to be passed to JS
	 *
	 * @return array Current app state
	 */
	public function get_js_app_state()
	{
		$swf_url = buddyboss_media()->assets_url . '/vendor/plupload2/Moxie.swf';
		$xap_url = buddyboss_media()->assets_url . '/vendor/plupload2/Moxie.xap';

		// TODO: These should be admin options
		//
		$app_state = array(
			'uploader_filesize'    => apply_filters( 'buddyboss-media-uploader-filesize', '15mb' ),
			'uploader_filetypes'   => apply_filters( 'buddyboss-media-uploader-filetypes', 'jpg,jpeg,gif,png,bmp' ),
			'uploader_runtimes'    => apply_filters( 'buddyboss-media-uploader-runtimes', 'html5,flash,silverlight,html4' ),
			'uploader_multiselect' => apply_filters( 'buddyboss-media-uploader-multiselect', false ),
			'uploader_swf_url'     => apply_filters( 'buddyboss-media-uploader-swf-url', $swf_url ),
			'uploader_xap_url'     => apply_filters( 'buddyboss-media-uploader-xap-url', $xap_url ),
			'uploader_embed_panel' => apply_filters( 'buddyboss-media-uploader-embed-panel', true )
		);

		return apply_filters( 'buddyboss_media_js_app_state', $app_state );
	}

	public function minified_assets()
	{
		$assets = buddyboss_media()->assets_url;

    	// FontAwesome icon fonts. If browsing on a secure connection, use HTTPS.
		wp_register_style('fontawesome', "//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css", false, null);
		wp_enqueue_style( 'fontawesome' );

		// CSS > Main
		//wp_enqueue_style( 'buddyboss-media-main', $assets . '/css/buddyboss-media.css', array( 'fontawesome' ), '2.0.4', 'all' );
		wp_enqueue_style( 'buddyboss-media-main', $assets . '/css/buddyboss-media.min.css', array( 'fontawesome' ), '2.0.4', 'all' );

		// JS > PhotoSwipe
		wp_enqueue_script( 'buddyboss-media-klass', $assets . '/vendor/photoswipe/klass.min.js', array( 'jquery' ), '1.0', false );
		wp_enqueue_script( 'buddyboss-media-popup', $assets . '/vendor/photoswipe/code.photoswipe.jquery-3.0.5.min.js', array( 'jquery' ), '3.0.5', false );

		// JS > Plupload
		wp_deregister_script( 'moxie' );
		wp_deregister_script( 'plupload' );
		wp_enqueue_script( 'moxie', $assets . '/vendor/plupload2/moxie.js', array( 'jquery' ), '1.2.1' );
		wp_enqueue_script( 'plupload', $assets . '/vendor/plupload2/plupload.dev.js', array( 'jquery', 'moxie' ), '2.1.2' );

		if( bp_is_active( 'friends' ) && buddyboss_media()->option( 'enable_tagging' )=='yes' ){
			//tooltip is only required if friends tagging is enabled.
			wp_enqueue_script( 'jquery-tooltipster', $assets . '/js/jquery.tooltipster.min.js', array( 'jquery' ), '3.0.5', true );
		}
		
		// JS > Main
		//wp_enqueue_script( 'buddyboss-media-main', $assets . '/js/buddyboss-media.js', array( 'jquery', 'plupload' ), '2.0.6', true );
		wp_enqueue_script( 'buddyboss-media-main', $assets . '/js/buddyboss-media.min.js', array( 'jquery', 'plupload' ), '2.0.6', true );
		
		$data = array(
			'is_media_page'	=> ( buddyboss_media()->option('all-media-page') && is_page( buddyboss_media()->option('all-media-page') ) ) ? true : false,
		);
		wp_localize_script( 'buddyboss-media-main', 'BBOSS_MEDIA', $data );
	}

	/**
	 * Load CSS/JS
	 * @return void
	 */
	public function assets()
	{
		// Minified Assets
		$this->minified_assets();

		// Localization
		$js_vars_array = array_merge(
			(array) $this->get_js_translations(),
			(array) $this->get_js_app_state()
		);
		
		if( bp_is_active( 'friends' ) && buddyboss_media()->option( 'enable_tagging' ) == 'yes' ){
			$js_vars_array['enable_tagging'] = true;
			
			/**
			 * The following jquery selector is used to update activity action with ajax when tagged users are updated.
			 * Standard structure is :
			 * <div class="activity-header">
			 *	<?php bp_activity_action(); ?>
			 * </div>
			 * 
			 * But if your theme uses a different classname/strucure, you should modify the jquery selector here,
			 * to make the activity action udpate automatically when users tagged in photo is updated.
			 */
			$js_vars_array['activity_header_selector'] = '.activity-header';
		}
		
		$js_vars = apply_filters( 'buddyboss_media_js_vars', $js_vars_array );

		wp_localize_script( 'buddyboss-media-main', 'BuddyBoss_Media_Appstate', $js_vars );
	}

	/**
	 * Print inline templates
	 * @return void
	 */
	public function script_templates()
	{
		?>
		<script type="text/html" id="buddyboss-media-tpl-add-photo">
			<div id="buddyboss-media-add-photo">
				<button type="button" id="buddyboss-media-add-photo-button"><?php _e( 'Add Photo', 'buddyboss-media' ); ?></button>
				<div class="buddyboss-media-progress">
					<div class="buddyboss-media-progress-value">0%</div>
					<progress class="buddyboss-media-progress-bar" value="0" max="100"></progress>
				</div>
				<div id="buddyboss-media-photo-uploader"></div>
			</div><!-- #buddyboss-media-add-photo -->
		</script>

		<script type="text/html" id="buddyboss-media-tpl-preview">
			<div class="clearfix" id="buddyboss-media-preview">
				<div id="buddyboss-media-preview-inner"></div>
				<div id="buddyboss-media-preview-details">
					<?php _e( 'Say something about this photo...', 'buddyboss-media' );?>
				</div>
			</div><!-- #buddyboss-media-preview -->
		</script>
		
		<?php if( is_user_logged_in() ):?>
		<div class="activity-comments buddyboss-media-form-wrapper" style="display:none">
			<form id="frm_buddyboss-media-move-media" method="POST" onsubmit="return buddyboss_media_submit_media_move();">
				<?php $is_single_album = buddyboss_media_is_single_album() ? 'yes' : 'no'; ?>
				<input type="hidden" name="is_single_album" value="<?php echo $is_single_album;?>" >
				<input type="hidden" name="action" value="buddyboss_media_move_media" >
				<input type="hidden" name="bboss_media_move_media_nonce" value="<?php echo wp_create_nonce( 'bboss_media_move_media');?>" >
				<input type="hidden" name="activity_id" value="">
				
				<div class="clearfix" id="buddyboss-media-move-media">
					<div class="field">
						<label><?php _e( 'In photo album:', 'buddyboss-media' );?></label>
						<select id="buddyboss_media_move_media_albums" name="buddyboss_media_move_media_albums" >
							<?php 
							global $wpdb;
							$user_albums = $wpdb->get_results( $wpdb->prepare( "SELECT id, title FROM {$wpdb->prefix}buddyboss_media_albums WHERE user_id=%d ", bp_loggedin_user_id() ) );
							if( !empty( $user_albums ) && !is_wp_error( $user_albums ) ){
								echo "<option value=''>" . __( '[None]', 'buddyboss-media' ) . "</option>";
								foreach( $user_albums as $album ){
									echo "<option value='{$album->id}'>" . stripslashes( $album->title ) . "</option>";
								}
							} else {
								echo "<option value=''>" . __( 'You have not created any albums yet!', 'buddyboss-media' ) . "</option>";
							}
							?>
						</select>
					</div>
					<div class="field submit">
						<input type="submit" id="buddyboss-media-move-media-submit" value="<?php _e( 'Save', 'buddyboss-media' );?>" > &nbsp; 
						<a class='buddyboss_media_move_media_cancel' href='#' onclick='return buddyboss_media_move_media_close();'>
							<?php _e( 'Cancel', 'buddyboss-media' );?>
						</a>
					</div>
				</div><!-- #buddyboss-media-move-media -->
				<div id="message"></div>
			</form>
		</div>
		<?php endif; ?>
		
		<?php
	}

	/**
	 * SETUP MENU, ADD NAVIGATION OPTIONS
	 *
	 * @since	BuddyBoss Media (1.0.0)
	 * @todo: cache the amount of pics
	 */
	public function setup_bp_menu()
	{
		global $wpdb, $bp;

		if ( ! isset( $bp->displayed_user->id ) )
		{
			return;
		}

		$photos_user_id      = $bp->displayed_user->id;
		$activity_table      = bp_core_get_table_prefix() . 'bp_activity';
		$activity_meta_table = bp_core_get_table_prefix() . 'bp_activity_meta';
		$groups_table        = bp_core_get_table_prefix() . 'bp_groups';

		// Prepare a SQL query to retrieve the activity posts
		// that have pictures associated with them
		$sql = "SELECT COUNT(*) as photo_count FROM $activity_table a
						INNER JOIN $activity_meta_table am ON a.id = am.activity_id
  					LEFT JOIN (SELECT id FROM $groups_table WHERE status != 'public' ) grp ON a.item_id = grp.id
						WHERE a.user_id = %d
						AND (am.meta_key = 'buddyboss_media_aid' OR am.meta_key = 'buddyboss_pics_aid' OR am.meta_key = 'bboss_pics_aid')
						AND (a.component != 'groups' || a.item_id != grp.id)";
		$sql = $wpdb->prepare( $sql, $photos_user_id );

		buddyboss_media_log( ' MENU PHOTO COUNT SQL ' );
		buddyboss_media_log( $sql );
		$photos_cnt = $wpdb->get_var( $sql );

		/* Add 'Photos' to the main user profile navigation */
		bp_core_new_nav_item( array(
			'name' => sprintf( __( 'Photos <span>%d</span>', 'buddyboss-media' ), $photos_cnt),
			'slug' => $this->slug,
			'position' => 80,
			'screen_function' => 'buddyboss_media_screen_photo_grid',
			'default_subnav_slug' => 'my-gallery'
		) );

		$buddyboss_media_link = $bp->displayed_user->domain . $this->slug . '/';

		bp_core_new_subnav_item( array(
			'name' => __( 'Uploads', 'buddyboss-media' ),
			'slug' => 'my-gallery',
			'parent_slug' => $this->slug,
			'parent_url' => $buddyboss_media_link,
			'screen_function' => 'buddyboss_media_screen_photo_grid',
			'position' => 10
		) );
		
		bp_core_new_subnav_item( array(
			'name' => __( 'Albums', 'buddyboss-media' ),
			'slug' => 'albums',
			'parent_slug' => $this->slug,
			'parent_url' => $buddyboss_media_link,
			'screen_function' => 'buddyboss_media_screen_albums',
			'position' => 11
		) );
	}
	
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		if (is_user_logged_in()) {
			global $bp;
			$buddyboss_media_link = $bp->loggedin_user->domain . $this->slug . '/';
			
			$wp_admin_nav[] = array(
				'parent' => buddypress()->my_account_menu_id,
				'id'     => 'my-account-photos',
				'title'  => __( 'Photos', 'buddyboss-media' ),
				'href'   => $buddyboss_media_link
			);

			$wp_admin_nav[] = array(
				'parent' => 'my-account-photos',
				'id'     => 'my-account-photos-view',
				'title'  => __( 'Uploads', 'buddyboss-media' ),
				'href'   => $buddyboss_media_link
			);
			
			$wp_admin_nav[] = array(
				'parent' => 'my-account-photos',
				'id'     => 'my-account-photos-albums',
				'title'  => __( 'Albums', 'buddyboss-media' ),
				'href'   => $buddyboss_media_link . 'albums/'
			);
		}

		parent::setup_admin_bar($wp_admin_nav);
	}

	/**
	* Add active wall class
	*
	* @since BuddyBoss Media (1.0.0)
	*/
	public function body_class( $classes )
	{
		$classes[] = apply_filters( 'buddyboss_media_photos_body_class', 'buddyboss-media-has-photos-type' );
		return $classes;
	}

	public function single_photo_remove_confirmation_js()
	{
		remove_action( 'wp_head', 'bp_core_confirmation_js', 100 );
	}
	
	/**
	 * Ajax for deleting photo media.
	 * @since BuddyBoss Media (1.1)
	 **/
	public function delete_media_ajax() {
		error_reporting(0);
		$activity_id = intval($_POST["media"]);
		if(empty($activity_id)) {
			_e("Photo does not exists.","buddyboss-media");
			exit;
		}
		
		$activity_array = bp_activity_get_specific( array(
			'activity_ids'     => $activity_id,
			'display_comments' => 'stream'
		   ) );
	  
		$activity = ! empty( $activity_array['activities'][0] ) ? $activity_array['activities'][0] : false;
		
		if($activity->user_id == get_current_user_id()) {
			bp_activity_delete(array('id'=>$activity_id));
			echo "done";
		} else {
			_e("You don't have permission to delete this photo.","buddyboss-media");
		}
		exit;
	}

} // BuddyBoss_Media_Type_Photo

endif;

?>
