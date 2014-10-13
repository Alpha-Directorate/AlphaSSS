<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Wall
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('widgets_init', create_function('', 'return register_widget("BuddyBoss_Most_Liked_Activity_Widget");') );
class BuddyBoss_Most_Liked_Activity_Widget extends WP_Widget{
	/**
	 * @var int how many words of activity to display 
	 */
	protected $word_count=10;
	
	public function __construct() {
		parent::__construct( 'BuddyBoss_Most_Liked_Activity_Widget', __( '(BuddyBoss Wall) Most Liked Activity', 'buddyboss-wall' ), array(
			'classname'   => 'widget_most_liked_activities buddypress',
			'description' => __( 'Display a list of most liked activities site-wide.', 'buddyboss-wall' ),
		) );
	}

    function form($instance){
        $instance = wp_parse_args( (array) $instance, array( 'title' => 'Most Liked Activity', 'count' => 5, 'wordcount' => 10 ) );
        $title		= $instance['title'];
		$count		= $instance['count'];
		$wordcount	= $instance['wordcount'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title','buddyboss-wall');?>: 
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                    name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                    value="<?php echo attribute_escape($title); ?>" />
            </label>
        </p>
		<p>
            <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Max activity posts to show','buddyboss-wall');?>: 
                <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" 
                    name="<?php echo $this->get_field_name('count'); ?>" type="number" 
                    value="<?php echo attribute_escape($count); ?>" style="width: 30%" />
            </label>
        </p>
		<p>
            <label for="<?php echo $this->get_field_id('wordcount'); ?>"><?php _e('Max words per activity post','buddyboss-wall');?>: 
                <input class="widefat" id="<?php echo $this->get_field_id('wordcount'); ?>" 
                    name="<?php echo $this->get_field_name('wordcount'); ?>" type="number" 
                    value="<?php echo attribute_escape($wordcount); ?>" style="width: 30%" />
            </label>
        </p>
        <?php
    }
	
	function update($new_instance, $old_instance){
        $instance = $old_instance;
        $instance['title']		= $new_instance['title'];
		$instance['count']		= $new_instance['count'];
		$instance['wordcount']	= $new_instance['wordcount'];
        return $instance;
    }
	
	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		if (!empty($title)){
			echo $before_title . $title . $after_title;
		}
		
		add_filter( 'bp_activity_paged_activities_sql', array( $this, 'activity_orderby_query' ), 11 );
		add_filter( 'bp_activity_get_user_join_filter', array( $this, 'activity_orderby_query' ), 11 );
		
		add_filter( 'bp_get_activity_content_body',		array( $this, 'trim_activity_content' ), 11, 2 );
		$this->word_count = $instance['wordcount'];
		
		$params = array(
			'per_page'			=> $instance['count'],
			'display_comments'	=> false,
			'meta_query'		=> array(
				array(
					'key'		=> 'favorite_count'
				)
			),
		);
		
		if ( bp_has_activities( $params ) ) :?>
			<ul id="most-liked-activities" class="item-list">
			<?php while ( bp_activities() ) : bp_the_activity();?>
				
				<li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>">
					
					<div class="item-avatar">
						<a href="<?php bp_activity_user_link(); ?>">
							<?php bp_activity_avatar(); ?>
						</a>
					</div>
					
					<div class="item">

						<?php if ( bp_activity_has_content() ) : ?>

							<div class="item-title fn">
								<a href="<?php bp_activity_thread_permalink(); ?>"><?php bp_activity_content_body(); ?></a>
							</div>

							<div class="item-meta">
								<span class="activity">
									<?php echo bp_activity_get_meta( bp_get_activity_id(), 'favorite_count', true) ?> Likes
								</span>
							</div>

						<?php endif; ?>
						
					</div>
					
				</li>

			<?php endwhile; ?>
			</ul>
		<?php endif;
		
		remove_filter( 'bp_get_activity_content_body',		array( $this, 'trim_activity_content' ), 11, 2 );
		
		remove_filter( 'bp_activity_paged_activities_sql',	array( $this, 'activity_orderby_query' ), 11 );
		remove_filter( 'bp_activity_get_user_join_filter',	array( $this, 'activity_orderby_query' ), 11 );
		
		echo $after_widget;
	}
	
	function activity_orderby_query( $sql ){
		/*
		 SELECT DISTINCT a.id  FROM wp_bp_activity a  INNER JOIN wp_bp_activity_meta ON (a.id = wp_bp_activity_meta.activity_id) 
		 WHERE a.is_spam = 0 AND a.hide_sitewide = 0 AND  
		 (wp_bp_activity_meta.meta_key = 'favorite_count' ) AND a.type != 'activity_comment' AND a.type != 'last_activity' 
		 ORDER BY a.date_recorded DESC LIMIT 0, 5
		 */
		global $wpdb;
		return str_replace( 'ORDER BY a.date_recorded', 'ORDER BY '. $wpdb->prefix .'bp_activity_meta.meta_value', $sql );
	}
	
	function trim_activity_content( $activity_content, $activity ){
		$new_c1 = strip_tags($activity_content);
		
		$content = explode(" ", $new_c1, $this->word_count);
		if (count($content)>=$this->word_count) {
			array_pop($content);
			
			$more_content = "...";
			$content[] = $more_content;
		}
		$activity_content = implode(" ",$content);
		
		return $activity_content;
	}
}