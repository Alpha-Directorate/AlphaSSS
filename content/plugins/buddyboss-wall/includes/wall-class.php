<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Wall
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'BuddyBoss_Wall_BP_Component' ) ):
/**
 *
 * BuddyBoss Wall BuddyPress Component
 * ***********************************
 */
class BuddyBoss_Wall_BP_Component extends BP_Component {
	/**
	 * BUDDYPRESS ACTIVITIES
	 *
	 * @since BuddyBoss Wall 1.0
	 */
	public $activities;
	public $activity_count = 0;
	public $filter_qs = false;
	public $like_users = array();

	/**
	 * POST FORM
	 */
	public $form_displayed;

	/**
	 * LIKES
	 *
	 * @since BuddyBoss Wall 1.0
	 */
	public $likes_store = array();

	/**
	 * INITIALIZE CLASS
	 *
	 * @since BuddyBoss Wall 1.0
	 */
	public function __construct()
	{
		parent::start(
			'wall',
			__( 'Wall' , 'buddyboss-wall' ),
			dirname( __FILE__ )
		);
	}

	/**
	 * Convenince method for getting main plugin options.
	 *
	 * @since BuddyBoss Wall (1.0.0)
	 */
	public function option( $key )
	{
		return buddyboss_wall()->option( $key );
	}

	/**
	 * SETUP BUDDYPRESS GLOBAL OPTIONS
	 *
	 * @since	BuddyBoss Wall 1.0
	 */
	public function setup_globals( $args = array() )
	{
		$update_menus = $this->option( 'UPDATE_MENUS' );

		// Update menu text, this needs to be in the global
		// setup function because these actions will have ran already
		if ( $update_menus )
		{
			add_action( 'wp_before_admin_bar_render', array($this, 'update_wp_menus'), 99 );
			add_action( 'bp_setup_nav', array($this, 'update_bp_menus'), 98 );
			add_action( 'bp_setup_nav', array($this, 'bbg_remove_activity_friends_subnav'), 99 );
			add_filter( 'bp_get_displayed_user_nav_activity', array($this, 'bbg_replace_activity_link') );
		}

		parent::setup_globals();
	}

	/**
	 * SETUP ACTIONS
	 *
	 * @since  BuddyBoss Wall 1.0
	 */
	public function setup_actions()
	{
		// Inject "Whats new" area
		add_action( 'wp_footer', array( $this, 'script_template_greeting' ) );

		// Inject post form fallback if the action
		// "bp_before_member_activity_post_form" is missing
		// from members/single/activity.php
		add_action( 'wp_footer', array( $this, 'script_template_form' ) );

		// Add inline script templates
		add_action( 'bp_before_member_activity_post_form', array( $this, 'post_form' ) );
		add_action( 'bp_before_activity_entry_comments', array( $this, 'bp_before_activity_entry_comments' ) );

		// Front End Assets
		if ( ! is_admin() && ! is_network_admin() )
		{
			add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		}

		// BP filters & actions
		if ( buddyboss_wall()->is_enabled() )
		{
			add_action( 'bp_before_activity_comment', 'buddyboss_wall_add_likes_comments' );
			if( !is_admin() || ( defined('DOING_AJAX') && DOING_AJAX ) ){
				//global $activity_template used by the method is not available in backend(wp-admin)
				//and it generates notices
				//temporary fix
				add_filter( 'bp_get_activity_action', 'buddyboss_wall_read_filter' );
				//the second parameter requred by function is not passed in the action call in admin
				//so this function doesn't work in wp-admin
				//temporary fix
				add_filter( 'bp_get_activity_action', 'buddyboss_wall_replace_placeholders_with_url', 11, 2 );
			}
			add_filter( 'bp_activity_after_save', 'buddyboss_wall_input_filter' );
			add_filter( 'bp_ajax_querystring', 'buddyboss_wall_qs_filter', 111 );
			add_filter( 'bp_activity_multiple_at_mentions_notification', 'buddyboss_wall_format_mention_notification', 10, 5 );
			add_filter( 'bp_activity_single_at_mentions_notification', 'buddyboss_wall_format_mention_notification', 10, 5 );
			add_action( 'bp_activity_screen_my_activity', 'bp_activity_reset_my_new_mentions' );
			add_filter( 'bp_has_activities', 'buddyboss_wall_prepare_likes_filter', 10, 3 );
			add_filter( 'wp_head', 'buddyboss_wall_inline_styles', 10, 3 );
		}

		parent::setup_actions();
	}

	/**
	 * Prepare array with translated messages/strings to use in JS
	 *
	 * @return array Localized BuddyBoss Wall messages
	 */
	public function get_js_translations()
	{
		$js_translations = array(
			'person'              => __( 'person', 'buddyboss-wall' ),
			'people'              => __( 'people', 'buddyboss-wall' ),
			'like'                => __( 'like', 'buddyboss-wall' ),
			'likes'               => __( 'likes', 'buddyboss-wall' ),
			'mark_as_fav'         => __( 'Like', 'buddyboss-wall' ),
			'my_favs'             => __( 'My Likes', 'buddyboss-wall' ),
			'remove_fav'          => __( 'Unlike', 'buddyboss-wall' )
		);

		return apply_filters( 'buddyboss_wall_js_translations', $js_translations );
	}

	/**
	 * Prepare array with current state that needs to be passed to JS
	 *
	 * @return array Current app state
	 */
	public function get_js_app_state()
	{
		$app_state = buddyboss_wall()->options;

		return apply_filters( 'buddyboss_wall_app_state', $app_state );
	}

	/**
	 * Load CSS/JS
	 * @return void
	 */
	public function assets()
	{
		$load_css      = $this->option( 'LOAD_CSS' );
		$load_tooltips = $this->option( 'LOAD_TOOLTIPS' );

		if ( $load_css )
		{
			// Wall stylesheet.
			wp_enqueue_style( 'buddyboss-wall-main', buddyboss_wall()->assets_url . '/css/buddyboss-wall.min.css', array(), '1.0.7', 'all' );
		}

		// Scripts
		if ( $load_tooltips )
		{
			wp_enqueue_script( 'buddyboss-wall-tooltip', buddyboss_wall()->assets_url . '/js/jquery.tooltipster.min.js', array( 'jquery' ), '3.0.5', true );
		}

		wp_enqueue_script( 'buddyboss-wall-main', buddyboss_wall()->assets_url . '/js/buddyboss-wall.min.js', array( 'jquery', 'buddyboss-wall-tooltip' ), '1.0.7', true );

		// Localization
		$js_vars_array = array_merge(
			(array) $this->get_js_translations(),
			(array) $this->get_js_app_state()
		);

		$js_vars = apply_filters( 'buddyboss_wall_js_vars', $js_vars_array );

		wp_localize_script( 'buddyboss-wall-main', 'BuddyBoss_Wall_Appstate', $js_vars );
	}

	/**
	 * Prints inline script template - greeting label
	 * @return void
	 */
	public function script_template_greeting()
	{
		if ( is_user_logged_in() ){
			$greeting = '';
			if ( bp_is_group() ){
				$greeting = sprintf( __( "What's new in %s, %s?", 'buddyboss-wall' ), bp_get_current_group_name(), bp_get_user_firstname() );
			} elseif( !bp_is_my_profile() && bp_is_user_activity() ) {
				$greeting = sprintf( __( "Write something to %s", 'buddyboss-wall' ), bp_get_displayed_user_fullname() ) ;
			} else {
				$greeting = sprintf( __( "What's new, %s?", 'buddyboss-wall' ), bp_get_user_firstname() );
			}

			$greeting = apply_filters( 'buddyboss_wall_greeting_template', $greeting );
			?>
			<script type="text/html" id="buddyboss-wall-tpl-greeting">
				<?php echo $greeting; ?>
			</script>
			<?php
		}
	}

	/**
	 * Prints inline script template - post form
	 * @return void
	 */
	public function script_template_form()
	{
		if ( ! $this->form_displayed && buddyboss_wall()->is_enabled() && is_user_logged_in() && ! bp_is_my_profile() && ( ! bp_current_action() || 'just-me' === bp_current_action() ) )
		{
		  ?>
			<script type="text/html" id="buddyboss-wall-tpl-form">
				<?php $this->post_form(); ?>
			</script>
			<?php
		}
	}
	public function post_form()
	{
		global $bp;

		// If:
		// Wall is enabled
		// User is logged in
		// We're not on the logged in user's profile
		// But we are on a profile/wall ( bp_current_action() )
		if ( buddyboss_wall()->is_enabled() && is_user_logged_in() && ! bp_is_my_profile() && bp_is_user() )
		{
			$this->form_displayed = true;
			?>

			<?php if ( !is_user_logged_in() ) : ?>

				<div id="message">
					<p><?php printf( __( 'You need to <a href="%s" title="Log in">log in</a>', 'buddyboss-wall' ), wp_login_url() ); ?><?php if ( bp_get_signup_allowed() ) : ?><?php printf( __( ' or <a class="create-account" href="%s" title="Create an account">create an account</a>', 'buddyboss-wall' ), bp_get_signup_page() ); ?><?php endif; ?><?php _e( ' to post to this user\'s Wall.', 'buddyboss-wall' ); ?></p>
				</div>

			<?php elseif (!bp_is_my_profile() && (!is_super_admin() && !buddyboss_wall_is_admin()) && (bp_is_user() && (buddyboss_wall()->is_enabled() && !$this->option('all-members') && !$this->is_friend($bp->displayed_user->id)) )):?>

				<div id="message" class="info">
					<p><?php printf( __( "You and %s are not friends. Request friendship to post to their Wall.", 'buddyboss-wall' ), bp_get_displayed_user_fullname() ) ?></p>
				</div>

			<?php else:?>

				<?php if ( isset( $_GET['r'] ) ) : ?>
					<div id="message" class="info">
						<p><?php printf( __( 'You are mentioning %s in a new update, this user will be sent a notification of your message.', 'buddyboss-wall' ), bp_get_mentioned_user_display_name( $_GET['r'] ) ) ?></p>
					</div>
				<?php endif; ?>

				<?php bp_get_template_part( 'activity/post-form' ); ?>

			<?php endif; ?>

			<?php
		}
	}
	public function bp_before_activity_entry_comments()
	{
		$has_likes  = $this->has_likes( bp_get_activity_id() );
		$has_access = is_user_logged_in() && bp_activity_can_comment();
		$count      = bp_activity_get_comment_count();

		if ( $has_likes && ! $count ): ?>

			<script type="text/html" class="buddyboss-wall-tpl-activity-comments" id="buddyboss-wall-tpl-activity-comments-<?php echo bp_get_activity_id(); ?>">
				<?php buddyboss_wall_add_likes_comments(); ?>
			</script>

		<?php endif;
	}

	/**
	 * RENAME ACTIVITY LINK ON PROFILE SIDEBAR MENU
	 *
	 * @since BuddyBoss Wall 1.0
	 */
	public function bbg_replace_activity_link( $value )
	{
		$menu_name = $this->option( "MENU_NAME" );
		return str_replace( 'Activity', $menu_name, $value );
	}

	/**
	 * REMOVE TABS FROM PROFILE HEADER
	 *
	 * @since BuddyBoss Wall 1.0
	 */
	public function bbg_remove_activity_friends_subnav()
	{
		global $bp;

		bp_core_remove_subnav_item( 'activity', 'friends' );
		bp_core_remove_subnav_item( 'activity', 'mentions' );
		bp_core_remove_subnav_item( 'activity', 'groups' );

		if ( ! bp_is_my_profile() )
			bp_core_remove_subnav_item( 'activity', 'favorites' );
	}

	/**
	 * RENAME MENU TABS ON PROFILE
	 */
	public function update_bp_menus()
	{
		buddyboss_wall_log('Updating Menus');
		global $bp;

		$domain = (!empty($bp->displayed_user->id)) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;

		$profile_link = $domain . $bp->activity->slug . '/';

		// RENAME PERSONAL/WALL TAB
		bp_core_new_subnav_item( array(
			'name' => __( 'Wall', 'buddyboss-wall' ),
			'slug' => 'just-me',
			'parent_url' => $profile_link,
			'parent_slug' => $bp->activity->slug,
			'screen_function' =>
			'bp_activity_screen_my_activity' ,
			"position" => 10
		) );

		// ADD NEWS FEED TAB
		if ( bp_is_my_profile() )
		{
			bp_core_new_subnav_item( array(
				'name' => __( 'News Feed', 'buddyboss-wall' ),
				'slug' => 'news-feed',
				'parent_url' => $profile_link,
				'parent_slug' => $bp->activity->slug,
				'screen_function' =>
				'bp_activity_screen_my_activity' ,
				"position" => 11
			) );
		}

		// RENAME FAVORITES TAB
		bp_core_new_subnav_item( array(
			'name' => __( 'My Likes', 'buddyboss-wall' ),
			'slug' => 'favorites',
			'parent_url' => $profile_link,
			'parent_slug' => $bp->activity->slug,
			'screen_function' => 'bp_activity_screen_favorites',
			'position' => 12
		) );
	}

	/**
	 * REDIRECT LOGOUT FROM NEWSFEED
	 * @since BuddyBoss Wall 1.0
	 */
	public function newsfeed_logout_redirect_url()
	{
		global $bp;

		$action = $bp->current_action;

		if ( $action == 'news-feed' )
		{
			add_filter( 'logout_url', array( $this, 'set_newsfeed_logout_url' ) );
		}
	}
	public function set_newsfeed_logout_url( $logout_url )
	{
		global $bp;

		$parts = explode( 'redirect_to', $logout_url );

		if ( count( $parts ) > 1 )
		{
			$domain = (!empty($bp->displayed_user->id)) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;

			$profile_link = $domain . $bp->activity->slug . '/';

			$logout_url = $parts[0] . '&redirect_to=' . urlencode( $profile_link );
		}

		return $logout_url;
	}

	/**
	 * RENAME WORDPRESS MENU ITEMS
	 *
	 * @since BuddyBoss Wall 1.0
	 */
	public function update_wp_menus()
	{
		global $wp_admin_bar, $bp;

		$domain = $bp->loggedin_user->domain;

		$profile_link = $domain . $bp->activity->slug . '/';

		$activity_link = trailingslashit( $domain . $bp->activity->slug );

		// ADD ITEMS
		if ( is_user_logged_in() )
		{
			// REMOVE ITEMS
			$wp_admin_bar->remove_menu('my-account-activity-mentions');
			$wp_admin_bar->remove_menu('my-account-activity-personal');
			$wp_admin_bar->remove_menu('my-account-activity-favorites');
			$wp_admin_bar->remove_menu('my-account-activity-friends');
			$wp_admin_bar->remove_menu('my-account-activity-groups');

			// Change menus item to link to wall
			$user_info = $wp_admin_bar->get_node( 'user-info' );
			if ( ! is_object( $user_info ) ) $user_info = new stdClass();
			$user_info->href = trailingslashit( $activity_link );
			$wp_admin_bar->add_node( $user_info );

			$my_acct = $wp_admin_bar->get_node( 'my-account' );
			if ( ! is_object( $my_acct ) ) $my_acct = new stdClass();
			$my_acct->href = trailingslashit( $activity_link );
			$wp_admin_bar->add_node( $my_acct );


			// Change 'Activity' to 'Wall'
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-buddypress',
				'id'     => 'my-account-' . $bp->activity->id,
				'title'  => __( 'Wall', 'buddyboss-wall' ),
				'href'   => trailingslashit( $activity_link )
			) );

			// Personal/Wall
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-' . $bp->activity->id,
				'id'     => 'my-account-' . $bp->activity->id . '-wall',
				'title'  => __( 'Wall', 'buddyboss-wall' ),
				'href'   => trailingslashit( $activity_link )
			) );

			// News Feed
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-' . $bp->activity->id,
				'id'     => 'my-account-' . $bp->activity->id . '-feed',
				'title'  => __( 'News Feed', 'buddyboss-wall' ),
				'href'   => trailingslashit( $activity_link . 'news-feed' )
			) );

			// Favorites
			$wp_admin_bar->add_menu( array(
				'parent' => 'my-account-' . $bp->activity->id,
				'id'     => 'my-account-' . $bp->activity->id . '-favorites',
				'title'  => __( 'My Likes', 'buddyboss-wall' ),
				'href'   => trailingslashit( $activity_link . 'favorites' )
			) );
		}
	}

	/**
	 * WRAPPER FUNCTION, WILL BE DEPRECATED
	 */
	public function is_friend( $id )
	{
		return buddyboss_wall_is_my_friend( $id );
	}

	/**
	 * GET WALL ACTIVITES
	 */
	public function get_wall_activities( $page = 0, $per_page=20 )
	{
		global $bp, $wpdb, $buddyboss_ajax_qs;

		$min = ($page>0)? ($page-1) * $per_page : 0;
		$max = ($page+1) * $per_page;
		$per_page = bp_get_activity_per_page();
		buddyboss_wall_log(" per page $per_page");

		if (isset($bp->loggedin_user) && isset($bp->loggedin_user->id) && $bp->displayed_user->id == $bp->loggedin_user->id)
		{
			$myprofile = true;
		}
		else {
			$myprofile = false;
		}
		// $wpdb->show_errors = BUDDYBOSS_DEBUG;
		$user_id = $bp->displayed_user->id;

		buddyboss_wall_log("Looking at $user_id" );
		$user_filter = $bp->displayed_user->domain;

		// buddyboss_wall_log($friend_id_list);
		$table = bp_core_get_table_prefix() . 'bp_activity';
		$table2 = bp_core_get_table_prefix() . 'bp_activity_meta';

		// Default WHERE
		$where = "WHERE ( $table.user_id = $user_id AND $table.type!='activity_comment' AND $table.type!='friends' )";

		// Add @mentions
		$mentions_modifier = "OR ( $table.content LIKE '%$user_filter%' AND $table.type!='activity_comment' ) ";

		// If we have a filter enabled, let's handle that
		$ajax_qs = ! empty( $buddyboss_ajax_qs )
						 ? wp_parse_args( $buddyboss_ajax_qs )
						 : false;

		if ( is_array( $ajax_qs ) && isset( $ajax_qs['action'] ) )
		{
			// Clear the @mentions modifier
			$mentions_modifier = '';

			$filter_qs = $ajax_qs['action'];

			// Check for commas and adjust
			if ( strpos( $filter_qs, ',' ) )
			{
				$filters = explode( ',', $filter_qs );
			}
			else {
				$filters = (array)$filter_qs;
			}

			// Clean each filter
			$filters_clean = array();

			foreach( $filters as $filter )
			{
				$filters_clean[] = $wpdb->escape( $filter );
			}

			$filter_sql = "AND ( $table.type='" . implode( "' OR $table.type='", $filters_clean ) . "' )";

			$where = "WHERE ( $table.user_id = $user_id $filter_sql )";
		}

		// Filter where SQL
		$where_filtered = apply_filters( 'buddyboss_wall_query_wall_activity_ids_where', $where );

		// Filter modifier SQL
		$mentions_filtered = apply_filters( 'buddyboss_wall_query_wall_activity_ids_mentions', $mentions_modifier );

		// Build Query
		$query_sql = "SELECT DISTINCT $table.id FROM $table LEFT JOIN $table2 ON $table.id=$table2.activity_id
		$where_filtered
		$mentions_filtered
		ORDER BY date_recorded DESC LIMIT $min, 40";

		// Filter full query SQL
		$query_filtered = apply_filters( 'buddyboss_wall_query_wall_activity_ids_full', $query_sql );

		// Run query
		$activities = $wpdb->get_results( $query_filtered, ARRAY_A );

		buddyboss_wall_log($query_filtered);
		buddyboss_wall_log($activities);

		if ( empty( $activities ) ) return null;

		$tmp = array();

		foreach ( $activities as $activity )
		{
			$tmp[] = $activity ["id"];
		}

		$activity_list = implode( ",", $tmp );

		return $activity_list;
	}


	/**
	 * GET FEED ACTIVITES
	 */
	public function get_feed_activities( $page = 0, $per_page = 20 )
	{
		global $bp, $wpdb, $buddyboss_ajax_qs;

		$min = ( $page > 0 ) ? ( $page - 1 ) * $per_page : 0;
		$max = ( $page + 1 ) * $per_page;
		$per_page = bp_get_activity_per_page();

		buddyboss_wall_log( "per page: $per_page" );

		if ( isset( $bp->loggedin_user ) && isset( $bp->loggedin_user->id )
				 && intval( $bp->displayed_user->id ) === intval( $bp->loggedin_user->id ) )
		{
			$myprofile = true;
		}
		else {
			$myprofile = false;
		}

		$wpdb->show_errors = $this->option( 'DEBUG' );

		$user_id = $bp->displayed_user->id;

		$user_name = $bp->displayed_user->userdata->user_login;

		$filter = $bp->displayed_user->domain;

		buddyboss_wall_log( "Looking at $user_id" );

		// Get friend's user IDs
		$user_ids = friends_get_friend_user_ids(	$user_id, false, false );

		// Add logged in user to news feed results
		// $user_ids[] = $user_id;

		$user_list = implode( ',', $user_ids );

		// buddyboss_wall_log( $friend_id_list );
		$table = bp_core_get_table_prefix() . 'bp_activity';
		$table2 = bp_core_get_table_prefix() . 'bp_activity_meta';

		// Default WHERE
		$where = "WHERE ( $table.user_id IN ($user_list) AND $table.type != 'activity_comment' )";

		// Add when user joined a group
		$group_modifier = "OR ( $table.user_id = $user_id AND $table.component = 'groups' ) ";

		// If we have a filter enabled, let's handle that
		$ajax_qs = ! empty( $buddyboss_ajax_qs )
						 ? wp_parse_args( $buddyboss_ajax_qs )
						 : false;

		if ( is_array( $ajax_qs ) && isset( $ajax_qs['action'] ) )
		{
			// Clear group modifier
			$group_modifier = '';

			$filter_qs = $ajax_qs['action'];

			// Check for commas and adjust
			if ( strpos( $filter_qs, ',' ) )
			{
				$filters = explode( ',', $filter_qs );
			}
			else {
				$filters = (array)$filter_qs;
			}

			// Clean each filter
			$filters_clean = array();

			foreach( $filters as $filter )
			{
				$filters_clean[] = $wpdb->escape( $filter );
			}

			$filter_sql = "AND ( $table.type='" . implode( "' OR $table.type='", $filters_clean ) . "' )";

			$where = "WHERE ( $table.user_id IN ($user_list) $filter_sql )";
		}

		// Filter where SQL
		$where_filtered = apply_filters( 'buddyboss_wall_query_feed_activity_ids_where', $where );

		// Filter modifier SQL
		$group_filtered = apply_filters( 'buddyboss_wall_query_feed_activity_ids_groups', $group_modifier );

		// Build Query
		$query_sql = "SELECT DISTINCT $table.id FROM $table LEFT JOIN $table2 ON $table.id = $table2.activity_id
		$where_filtered
		$group_filtered
		ORDER BY date_recorded DESC LIMIT $min, 40";

		// Filter full query SQL
		$query_filtered = apply_filters( 'buddyboss_wall_query_feed_activity_ids_full', $query_sql );

		// Run query
		$activities = $wpdb->get_results( $query_filtered, ARRAY_A );

		buddyboss_wall_log($query_filtered);
		buddyboss_wall_log($activities);

		if ( empty( $activities ) ) return null;

		$tmp = array();

		foreach ($activities as $activity )
		{
			$tmp[] = $activity["id"];
		}

		$activity_list = implode( ",", $tmp );

		return $activity_list;
	}

	/**
	 * Retrieve likes for current activity (within activity loop)
	 *
	 * @since 1.0
	 */
	public function has_likes( $activity_id = null )
	{
		if ( $activity_id === null ) $activity_id = bp_get_activity_id();

		return bp_activity_get_meta( $activity_id, 'favorite_count' );
	}
}
// End class BuddyBoss_Wall_BP_Component

endif;

?>
