<?php


/**
 * Register the filtered PG Widgets
 */
function register_pg_widgets() {
    register_widget("pg_Forums_Widget");
    register_widget("pg_Topics_Widget");
    register_widget("pg_Replies_Widget");
	register_widget("pg_Activity_Widget");

}


add_action('widgets_init', 'register_pg_widgets');



// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * pg Forum Widget*
 * Adds a widget which displays the forum list
 *
 */
class pg_Forums_Widget extends WP_Widget {

    /**
     * primarily the code from the bbpress forum widget !
     */
    public function __construct() {
        $widget_ops = apply_filters('pg_forums_widget_options', array(
            'classname' => 'widget_display_forums',
            'description' => __('A list of forums with an option to set the parent.', 'bbp-private groups')
        ));

        parent::__construct(false, __('(PG) Forums List', 'bbp-private-groups'), $widget_ops);
    }

    /**
     * Register the widget
     *
     * @since bbPress (r3389)
     *
     * @uses register_widget()
     */
    public static function register_widget() {
        register_widget('pg_Forums_Widget');
    }

    /**
     * Displays the output, the forum list
     *
     * @since bbPress (r2653)
     *
     * @param mixed $args Arguments
     * @param array $instance Instance
     * @uses apply_filters() Calls 'bbp_forum_widget_title' with the title
     * @uses get_option() To get the forums per page option
     * @uses current_user_can() To check if the current user can read
     *                           private() To resety name
     * @uses bbp_has_forums() The main forum loop
     * @uses bbp_forums() To check whether there are more forums available
     *                     in the loop
     * @uses bbp_the_forum() Loads up the current forum in the loop
     * @uses bbp_forum_permalink() To display the forum permalink
     * @uses bbp_forum_title() To display the forum title
     */
    public function widget($args, $instance) {

        // Get widget settings
        $settings = $this->parse_settings($instance);

        // Typical WordPress filter
        $settings['title'] = apply_filters('widget_title', $settings['title'], $instance, $this->id_base);

        // bbPress filter
        $settings['title'] = apply_filters('pg_widget_title', $settings['title'], $instance, $this->id_base);

        // Note: private and hidden forums will be excluded via the
        // bbp_pre_get_posts_exclude_forums filter and function.
        $query_data = array(
            'post_type' => bbp_get_forum_post_type(),
            'post_parent' => $settings['parent_forum'],
            'post_status' => bbp_get_public_status_id(),
            'posts_per_page' => get_option('_bbp_forums_per_page', 50),
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );

        //PRIVATE GROUPS Get an array of IDs which the current user has permissions to view
        $allowed_posts = private_groups_get_permitted_post_ids(new WP_Query($query_data));
        // The default forum query with allowed forum ids array added
        $query_data['post__in'] = $allowed_posts;

        $widget_query = new WP_Query($query_data);

        // Bail if no posts
        if (!$widget_query->have_posts()) {
            return;
        }

		echo $args['before_widget'];
        if (!empty($settings['title'])) {
            echo $args['before_title'] . $settings['title'] . $args['after_title'];
        }
        ?>

        <ul>

            <?php while ($widget_query->have_posts()) : $widget_query->the_post(); ?>

                <li><a class="bbp-forum-title" href="<?php bbp_forum_permalink($widget_query->post->ID); ?>" title="<?php bbp_forum_title($widget_query->post->ID); ?>"><?php bbp_forum_title($widget_query->post->ID); ?></a></li>

            <?php endwhile; ?>

        </ul>

        <?php
        echo $args['after_widget'];

        // Reset the $post global
        wp_reset_postdata();
    }

    /**
     * Update the forum widget options
     *
     * @since bbPress (r2653)
     *
     * @param array $new_instance The new instance options
     * @param array $old_instance The old instance options
     */
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['parent_forum'] = $new_instance['parent_forum'];

        // Force to any
        if (!empty($instance['parent_forum']) && !is_numeric($instance['parent_forum'])) {
            $instance['parent_forum'] = 'any';
        }

        return $instance;
    }

    /**
     * Output the forum widget options form
     *
     * @since bbPress (r2653)
     *
     * @param $instance Instance
     * 
     */
    public function form($instance) {

        // Get widget settings
        $settings = $this->parse_settings($instance);
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'bbpress'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($settings['title']); ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('parent_forum'); ?>"><?php _e('Parent Forum ID:', 'bbpress'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('parent_forum'); ?>" name="<?php echo $this->get_field_name('parent_forum'); ?>" type="text" value="<?php echo esc_attr($settings['parent_forum']); ?>" />
            </label>

            <br />

            <small><?php _e('"0" to show only root - "any" to show all', 'bbp-private-groups'); ?></small>
        </p>

        <?php
    }

    /**
     * Merge the widget settings into defaults array.
     *
     * @since bbPress (r4802)
     *
     * @param $instance Instance
     * @uses bbp_parse_args() To merge widget settings into defaults
     */
    public function parse_settings($instance = array()) {
        return bbp_parse_args($instance, array(
            'title' => __('Forums', 'bbpress'),
            'parent_forum' => 0
                ), 'forum_widget_settings');
    }

}

/**
 * PRIVATE GROUPS Topic Widget
 *
 * Adds a widget which displays the topic list
 *
 * @since bbPress (r2653)
 *
 * @uses WP_Widget
 */
class pg_Topics_Widget extends WP_Widget {

    /**
     * bbPress Topic Widget
     *
     * Registers the topic widget
     *
     * @since bbPress (r2653)
     *
     * @uses apply_filters() Calls 'bbp_topics_widget_options' with the
     *                        widget options
     */
    public function __construct() {
        $widget_ops = apply_filters('pg_topics_widget_options', array(
            'classname' => 'widget_display_topics',
            'description' => __('A list of recent topics, sorted by popularity or freshness.', 'bbp-private-groups')
        ));

        parent::__construct(false, __('(PG) Recent Topics', 'bbp-private-groups'), $widget_ops);
    }

    /**
     * Register the widget
     *
     * @since bbPress (r3389)
     *
     * @uses register_widget()
     */
    public static function register_widget() {
        register_widget('pg_Topics_Widget');
    }

    /**
     * Displays the output, the topic list
     *
     * @since bbPress (r2653)
     *
     * @param mixed $args
     * @param array $instance
     * @uses apply_filters() Calls 'bbp_topic_widget_title' with the title
     * @uses bbp_topic_permalink() To display the topic permalink
     * @uses bbp_topic_title() To display the topic title
     * @uses bbp_get_topic_last_active_time() To get the topic last active
     *                                         time
     * @uses bbp_get_topic_id() To get the topic id
     */
    public function widget($args = array(), $instance = array()) {

        // Get widget settings
        $settings = $this->parse_settings($instance);

        // Typical WordPress filter
        $settings['title'] = apply_filters('widget_title', $settings['title'], $instance, $this->id_base);

        // bbPress filter
        $settings['title'] = apply_filters('pg_topic_widget_title', $settings['title'], $instance, $this->id_base);

        // How do we want to order our results?
        switch ($settings['order_by']) {

            // Order by most recent replies
            case 'freshness' :
                $topics_query = array(
                    'post_type' => bbp_get_topic_post_type(),
                    'post_parent' => $settings['parent_forum'],
                    'posts_per_page' => (int) $settings['max_shown'],
                    'post_status' => array(bbp_get_public_status_id(), bbp_get_closed_status_id()),
                    'show_stickies' => false,
                    'meta_key' => '_bbp_last_active_time',
                    'orderby' => 'meta_value',
                    'order' => 'DESC',
                );
                break;

            // Order by total number of replies
            case 'popular' :
                $topics_query = array(
                    'post_type' => bbp_get_topic_post_type(),
                    'post_parent' => $settings['parent_forum'],
                    'posts_per_page' => (int) $settings['max_shown'],
                    'post_status' => array(bbp_get_public_status_id(), bbp_get_closed_status_id()),
                    'show_stickies' => false,
                    'meta_key' => '_bbp_reply_count',
                    'orderby' => 'meta_value',
                    'order' => 'DESC'
                );
                break;

            // Order by which topic was created most recently
            case 'newness' :
            default :
                $topics_query = array(
                    'post_type' => bbp_get_topic_post_type(),
                    'post_parent' => $settings['parent_forum'],
                    'posts_per_page' => (int) $settings['max_shown'],
                    'post_status' => array(bbp_get_public_status_id(), bbp_get_closed_status_id()),
                    'show_stickies' => false,
                    'order' => 'DESC'
                );
                break;
        }

        //PRIVATE GROUPS Get an array of IDs which the current user has permissions to view
        //set posts per page to 200 to ensure we get a full list
		$topics_query['posts_per_page'] =200;
		$allowed_posts = private_groups_get_permitted_post_ids(new WP_Query($topics_query));
        // The default forum query with allowed forum ids array added
		$topics_query['post__in'] = $allowed_posts;
		//reset the max to be shown
		$topics_query['posts_per_page'] =(int) $settings['max_shown'] ;
		
        // Note: private and hidden forums will be excluded via the
        // bbp_pre_get_posts_exclude_forums filter and function.
        $widget_query = new WP_Query($topics_query);

        // Bail if no topics are found
        if (!$widget_query->have_posts()) {
            return;
        }

        echo $args['before_widget'];

        if (!empty($settings['title'])) {
            echo $args['before_title'] . $settings['title'] . $args['after_title'];
        }
        ?>

        <ul>

            <?php
            while ($widget_query->have_posts()) :

                $widget_query->the_post();
                $topic_id = bbp_get_topic_id($widget_query->post->ID);
                $author_link = '';

                // Maybe get the topic author
                if ('on' == $settings['show_user']) :
                    $author_link = bbp_get_topic_author_link(array('post_id' => $topic_id, 'type' => 'both', 'size' => 14));
                endif;
                ?>

                <li>
                    <a class="bbp-forum-title" href="<?php echo esc_url(bbp_get_topic_permalink($topic_id)); ?>" title="<?php echo esc_attr(bbp_get_topic_title($topic_id)); ?>"><?php bbp_topic_title($topic_id); ?></a>

                    <?php if (!empty($author_link)) : ?>

                        <?php printf(_x('by %1$s', 'widgets', 'bbpress'), '<span class="topic-author">' . $author_link . '</span>'); ?>

                    <?php endif; ?>

                    <?php if ('on' == $settings['show_date']) : ?>

                        <div><?php bbp_topic_last_active_time($topic_id); ?></div>

                    <?php endif; ?>

                </li>

            <?php endwhile; ?>

        </ul>

        <?php
        echo $args['after_widget'];

        // Reset the $post global
        wp_reset_postdata();
    }

    /**
     * Update the topic widget options
     *
     * @since bbPress (r2653)
     *
     * @param array $new_instance The new instance options
     * @param array $old_instance The old instance options
     */
    public function update($new_instance = array(), $old_instance = array()) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['order_by'] = strip_tags($new_instance['order_by']);
		$instance['parent_forum'] = sanitize_text_field( $new_instance['parent_forum'] );
        $instance['show_date'] = (bool) $new_instance['show_date'];
        $instance['show_user'] = (bool) $new_instance['show_user'];
        $instance['max_shown'] = (int) $new_instance['max_shown'];

        // Force to any
        if ( !empty( $instance['parent_forum'] ) && !is_numeric( $instance['parent_forum'] ) ) {
			$instance['parent_forum'] = 'any';
		}
         

        return $instance;
    }

    /**
     * Output the topic widget options form
     *
     * @since bbPress (r2653)
     *
     * @param $instance Instance
     *
     */
    public function form($instance = array()) {

        // Get widget settings
        $settings = $this->parse_settings($instance);
        ?>

        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'bbpress'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($settings['title']); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('max_shown'); ?>"><?php _e('Maximum topics to show:', 'bbpress'); ?> <input class="widefat" id="<?php echo $this->get_field_id('max_shown'); ?>" name="<?php echo $this->get_field_name('max_shown'); ?>" type="text" value="<?php echo esc_attr($settings['max_shown']); ?>" /></label></p>

        <p>
            <label for="<?php echo $this->get_field_id('parent_forum'); ?>"><?php _e('Parent Forum ID:', 'bbpress'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('parent_forum'); ?>" name="<?php echo $this->get_field_name('parent_forum'); ?>" type="text" value="<?php echo esc_attr($settings['parent_forum']); ?>" />
            </label>

            <br />

            <small><?php _e('"0" to show only root - "any" to show all', 'bbpress'); ?></small>
        </p>

        <p><label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Show post date:', 'bbpress'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" <?php checked( true, $settings['show_date'] ); ?> value="1" /></label></p>
        <p><label for="<?php echo $this->get_field_id('show_user'); ?>"><?php _e('Show topic author:', 'bbpress'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('show_user'); ?>" name="<?php echo $this->get_field_name('show_user'); ?>" <?php checked( true, $settings['show_user'] ); ?> value="1" /></label></p>
        <p>
            <label for="<?php echo $this->get_field_id('order_by'); ?>"><?php _e('Order By:', 'bbpress'); ?></label>
            <select name="<?php echo $this->get_field_name('order_by'); ?>" id="<?php echo $this->get_field_name('order_by'); ?>">
                <option <?php selected($settings['order_by'], 'newness'); ?> value="newness"><?php _e('Newest Topics', 'bbpress'); ?></option>
                <option <?php selected($settings['order_by'], 'popular'); ?> value="popular"><?php _e('Popular Topics', 'bbpress'); ?></option>
                <option <?php selected($settings['order_by'], 'freshness'); ?> value="freshness"><?php _e('Topics With Recent Replies', 'bbpress'); ?></option>
            </select>
        </p>

        <?php
    }

    /**
     * Merge the widget settings into defaults array.
     *
     * @since bbPress (r4802)
     *
     * @param $instance Instance
     * @uses bbp_parse_args() To merge widget options into defaults
     */
    public function parse_settings($instance = array()) {
        return bbp_parse_args($instance, array(
            'title' => __('Recent Topics', 'bbpress'),
            'max_shown' => 5,
            'show_date' => false,
            'show_user' => false,
            'parent_forum' => 'any',
            'order_by' => false
                ), 'topic_widget_settings');
    }

}
/********************************************/
/**
 * PRIVATE GROUPS Replies Widget
 *
 * Adds a widget which displays the replies list
 *
 *
 * @uses WP_Widget
 */
class pg_Replies_Widget extends WP_Widget {

    /**
     * pg Replies Widget
     *
     * Registers the replies widget
     *
     *
     * @uses apply_filters() Calls 'bbp_replies_widget_options' with the
     *                        widget options
     */
    public function __construct() {
        $widget_ops = apply_filters('pg_replies_widget_options', array(
            'classname' => 'widget_display_replies',
            'description' => __('A list of the most recent replies.', 'bbp-private-groups')
        ));

        parent::__construct(false, __('(PG) Recent Replies', 'bbp-private-groups'), $widget_ops);
    }

    /**
     * Register the widget
     *
     * @since bbPress (r3389)
     *
     * @uses register_widget()
     */
    public static function register_widget() {
        register_widget('pg_Replies_Widget');
    }

    /**
     * Displays the output, the replies list
     *
     * @since bbPress (r2653)
     *
     * @param mixed $args
     * @param array $instance
     * @uses apply_filters() Calls 'bbp_reply_widget_title' with the title
     * @uses bbp_get_reply_author_link() To get the reply author link
     * @uses bbp_get_reply_author() To get the reply author name
     * @uses bbp_get_reply_id() To get the reply id
     * @uses bbp_get_reply_url() To get the reply url
     * @uses bbp_get_reply_excerpt() To get the reply excerpt
     * @uses bbp_get_reply_topic_title() To get the reply topic title
     * @uses get_the_date() To get the date of the reply
     * @uses get_the_time() To get the time of the reply
     */
    public function widget($args, $instance) {

        // Get widget settings
        $settings = $this->parse_settings($instance);

        // Typical WordPress filter
        $settings['title'] = apply_filters('widget_title', $settings['title'], $instance, $this->id_base);

        // bbPress filter
        $settings['title'] = apply_filters('pg_replies_widget_title', $settings['title'], $instance, $this->id_base);

        // Note: private and hidden forums will be excluded via the
        // bbp_pre_get_posts_exclude_forums filter and function.
        $query_data = array(
            'post_type' => bbp_get_reply_post_type(),
            'post_status' => array(bbp_get_public_status_id(), bbp_get_closed_status_id()),
             'posts_per_page' => '50',
			
        );

        //PRIVATE GROUPS Get an array of IDs which the current user has permissions to view
        $allowed_posts = private_groups_get_permitted_post_ids(new WP_Query($query_data));
        // The default forum query with allowed forum ids array added
        $query_data['post__in'] = $allowed_posts;

		//now set max posts
		$query_data ['posts_per_page'] = (int) $settings['max_shown'] ;
		
        $widget_query = new WP_Query($query_data);

        // Bail if no replies
        if (!$widget_query->have_posts()) {
            return;
        }

        echo $args['before_widget'];

        if (!empty($settings['title'])) {
            echo $args['before_title'] . $settings['title'] . $args['after_title'];
        }
        ?>

        <ul>

            <?php while ($widget_query->have_posts()) : $widget_query->the_post(); ?>

                <li>

                    <?php
                    // Verify the reply ID
                    $reply_id = bbp_get_reply_id($widget_query->post->ID);
                    $reply_link = '<a class="bbp-reply-topic-title" href="' . esc_url(bbp_get_reply_url($reply_id)) . '" title="' . esc_attr(bbp_get_reply_excerpt($reply_id, 50)) . '">' . bbp_get_reply_topic_title($reply_id) . '</a>';

                    // Only query user if showing them
                    if ('on' == $settings['show_user']) :
                        $author_link = bbp_get_reply_author_link(array('post_id' => $reply_id, 'type' => 'both', 'size' => 14));
                    else :
                        $author_link = false;
                    endif;

                    // Reply author, link, and timestamp
                    if (( 'on' == $settings['show_date'] ) && !empty($author_link)) :

                        // translators: 1: reply author, 2: reply link, 3: reply timestamp
                        printf(_x('%1$s on %2$s %3$s', 'widgets', 'bbpress'), $author_link, $reply_link, '<div>' . bbp_get_time_since(get_the_time('U')) . '</div>');

                    // Reply link and timestamp
                    elseif ('on' == $settings['show_date']) :

                        // translators: 1: reply link, 2: reply timestamp
                        printf(_x('%1$s %2$s', 'widgets', 'bbpress'), $reply_link, '<div>' . bbp_get_time_since(get_the_time('U')) . '</div>');

                    // Reply author and title
                    elseif (!empty($author_link)) :

                        // translators: 1: reply author, 2: reply link
                        printf(_x('%1$s on %2$s', 'widgets', 'bbpress'), $author_link, $reply_link);

                    // Only the reply title
                    else :

                        // translators: 1: reply link
                        printf(_x('%1$s', 'widgets', 'bbpress'), $reply_link);

                    endif;
                    ?>

                </li>

            <?php endwhile; ?>

        </ul>

        <?php
        echo $args['after_widget'];

        // Reset the $post global
        wp_reset_postdata();
    }

    /**
     * Update the reply widget options
     *
     * @since bbPress (r2653)
     *
     * @param array $new_instance The new instance options
     * @param array $old_instance The old instance options
     */
    public function update($new_instance = array(), $old_instance = array()) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['show_date'] = (bool) $new_instance['show_date'];
        $instance['show_user'] = (bool) $new_instance['show_user'];
        $instance['max_shown'] = (int) $new_instance['max_shown'];

        return $instance;
    }

    /**
     * Output the reply widget options form
     *
     * @since bbPress (r2653)
     *
     * @param $instance Instance
     *
     */
    public function form($instance = array()) {

        // Get widget settings
        $settings = $this->parse_settings($instance);
        ?>

        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'bbpress'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($settings['title']); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('max_shown'); ?>"><?php _e('Maximum replies to show:', 'bbpress'); ?> <input class="widefat" id="<?php echo $this->get_field_id('max_shown'); ?>" name="<?php echo $this->get_field_name('max_shown'); ?>" type="text" value="<?php echo esc_attr($settings['max_shown']); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Show post date:', 'bbpress'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" <?php checked( true, $settings['show_date'] ); ?> value="1" /></label></p>
        <p><label for="<?php echo $this->get_field_id('show_user'); ?>"><?php _e('Show reply author:', 'bbpress'); ?> <input type="checkbox" id="<?php echo $this->get_field_id('show_user'); ?>" name="<?php echo $this->get_field_name('show_user'); ?>" <?php checked( true, $settings['show_user'] ); ?> value="1" /></label></p>

        <?php
    }

    /**
     * Merge the widget settings into defaults array.
     *
     * @since bbPress (r4802)
     *
     * @param $instance Instance
     * @uses bbp_parse_args() To merge widget settings into defaults
     */
    public function parse_settings($instance = array()) {
        return bbp_parse_args($instance, array(
            'title' => __('Recent Replies', 'bbp-private-groups'),
            'max_shown' => 5,
            'show_date' => false,
            'show_user' => false
                ), 'replies_widget_settings');
    }

}


/*******************************************latest activity widget*/

class pg_Activity_Widget extends WP_Widget {

	/**
	 * bbPress Topic Widget
	 *
	 * Registers the topic widget
	 *
	 * @since bbPress (r2653)
	 *
	 * @uses apply_filters() Calls 'bbp_topics_widget_options' with the
	 *                        widget options
	 */
	public function __construct() {
		$widget_ops = apply_filters( 'pg_topics_widget_options', array(
			'classname'   => 'widget_display_topics',
			'description' => __( 'A list of latest activity, sorted by popularity or freshness with latest author.', 'bbpress' )
		) );

		parent::__construct( false, __( '(PG) Latest Activity', 'bbpress' ), $widget_ops );
	}

	/**
	 * Register the widget
	 *
	 * @since bbPress (r3389)
	 *
	 * @uses register_widget()
	 */
	public static function register_widget() {
		register_widget( 'pg_Activity_Widget' );
	}

	/**
	 * Displays the output, the topic list
	 *
	 * @since bbPress (r2653)
	 *
	 * @param mixed $args
	 * @param array $instance
	 * @uses apply_filters() Calls 'bbp_topic_widget_title' with the title
	 * @uses bbp_topic_permalink() To display the topic permalink
	 * @uses bbp_topic_title() To display the topic title
	 * @uses bbp_get_topic_last_active_time() To get the topic last active
	 *                                         time
	 * @uses bbp_get_topic_id() To get the topic id
	 */
	public function widget( $args = array(), $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance );

		// Typical WordPress filter
		$settings['title'] = apply_filters( 'widget_title',           $settings['title'], $instance, $this->id_base );

		// bbPress filter
		$settings['title'] = apply_filters( 'bbp_topic_widget_title', $settings['title'], $instance, $this->id_base );

		// How do we want to order our results?
		switch ( $settings['order_by'] ) {

			// Order by most recent replies
			case 'freshness' :
				$topics_query = array(
					'post_type'           => bbp_get_topic_post_type(),
					'post_parent'         => $settings['parent_forum'],
					'posts_per_page'      => (int) $settings['max_shown'],
					'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'meta_key'            => '_bbp_last_active_time',
					'orderby'             => 'meta_value',
					'order'               => 'DESC',
				);
				break;

			// Order by total number of replies
			case 'popular' :
				$topics_query = array(
					'post_type'           => bbp_get_topic_post_type(),
					'post_parent'         => $settings['parent_forum'],
					'posts_per_page'      => (int) $settings['max_shown'],
					'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'meta_key'            => '_bbp_reply_count',
					'orderby'             => 'meta_value',
					'order'               => 'DESC'
				);
				break;

			// Order by which topic was created most recently
			case 'newness' :
			default :
				$topics_query = array(
					'post_type'           => bbp_get_topic_post_type(),
					'post_parent'         => $settings['parent_forum'],
					'posts_per_page'      => (int) $settings['max_shown'],
					'post_status'         => array( bbp_get_public_status_id(), bbp_get_closed_status_id() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'order'               => 'DESC'
				);
				break;
		}
		
		 //PRIVATE GROUPS Get an array of IDs which the current user has permissions to view
        //set posts per page to 200 to ensure we get a full list
		$topics_query['posts_per_page'] =200;
		$allowed_posts = private_groups_get_permitted_post_ids(new WP_Query($topics_query));
        // The default forum query with allowed forum ids array added
		$topics_query['post__in'] = $allowed_posts;
		//reset the max to be shown
		$topics_query['posts_per_page'] =(int) $settings['max_shown'] ;

		// Note: private and hidden forums will be excluded via the
		// bbp_pre_get_posts_normalize_forum_visibility action and function.
		$widget_query = new WP_Query( $topics_query );
				// Bail if no topics are found
		if ( ! $widget_query->have_posts() ) {
			return;
		}

		echo $args['before_widget'];

		if ( !empty( $settings['title'] ) ) {
			echo $args['before_title'] . $settings['title'] . $args['after_title'];
		} ?>

		<ul>

			<?php while ( $widget_query->have_posts() ) :
			

				$widget_query->the_post();
				$topic_id    = bbp_get_topic_id( $widget_query->post->ID );
				$author_link = '';
				
				
				// Maybe get the topic author
				if ( ! empty( $settings['show_user'] ) ) {
				//check if this topic has a reply
				$reply = get_post_meta( $topic_id, '_bbp_last_reply_id',true);
				//do we display avatar?
					if (!empty ($settings['hide_avatar'])) $type='name' ;
					else $type='both' ;
				//if no reply the author
				if (empty ($reply)) $author_link = bbp_get_topic_author_link( array( 'post_id' => $topic_id, 'type' => $type, 'size' => 14 ) );
				//if has a reply then get the author of the reply
				else $author_link = bbp_get_reply_author_link( array( 'post_id' => $reply, 'type' => $type, 'size' => 14 ) );
				} ?>

				<li>
				<?php 
				//if no replies set the link to the topic
				if (empty ($reply)) {?>
					<a class="bbp-forum-title" href="<?php bbp_topic_permalink( $topic_id ); ?>"><?php bbp_topic_title( $topic_id ); ?></a>
				<?php } 
				//if replies then set link to the latest reply
				else { 
					echo '<a class="bbp-reply-topic-title" href="' . esc_url( bbp_get_reply_url( $reply ) ) . '" title="' . esc_attr( bbp_get_reply_excerpt( $reply, 50 ) ) . '">' . bbp_get_reply_topic_title( $reply ) . '</a>';
				} ?>
				
					<?php if ( ! empty( $author_link ) ) : ?>
						<div>
						<?php 
						
						if (empty($reply)) printf( _x( 'topic by %1$s', 'widgets', 'bbpress' ), '<span class="topic-author">' . $author_link . '</span>' ); 
						else printf( _x( 'reply by %1$s', 'widgets', 'bbpress' ), '<span class="topic-author">' . $author_link . '</span>' ); ?>
						</div>
					<?php endif; ?>
					

					<?php if ( ! empty( $settings['show_freshness'] ) ) : ?>
					<?php $output = bbp_get_topic_last_active_time( $topic_id ) ; 
						//shorten freshness?
						if ( ! empty( $settings['shorten_freshness'] ) ) $output = preg_replace( '/, .*[^ago]/', ' ', $output ); ?>
						<div><?php 
						echo $output ;
						//bbp_topic_last_active_time( $topic_id ); ?></div>
					
					<?php endif; ?>
					
					<?php if ( ! empty( $settings['show_forum'] ) ) : ?>
					<div>
						<?php
						$forum = bbp_get_topic_forum_id($topic_id);
						$forum1 = bbp_get_forum_title($forum) ;
						$forum2 = esc_url( bbp_get_forum_permalink( $forum )) ;
						echo 'in ' ; ?>
						<a class="bbp-forum-title" href="<?php echo $forum2; ?>"><?php echo $forum1 ; ?></a>
					</div>
					<?php endif; ?>
				
						

					

				</li>

			<?php endwhile; ?>

		</ul>

		<?php echo $args['after_widget'];

		// Reset the $post global
		wp_reset_postdata();
	}

	/**
	 * Update the topic widget options
	 *
	 * @since bbPress (r2653)
	 *
	 * @param array $new_instance The new instance options
	 * @param array $old_instance The old instance options
	 */
	public function update( $new_instance = array(), $old_instance = array() ) {
		$instance                 = $old_instance;
		$instance['title']        = strip_tags( $new_instance['title'] );
		$instance['order_by']     = strip_tags( $new_instance['order_by'] );
		$instance['parent_forum'] = sanitize_text_field( $new_instance['parent_forum'] );
		$instance['show_freshness']    = (bool) $new_instance['show_freshness'];
		$instance['show_user']    = (bool) $new_instance['show_user'];
		$instance['show_forum']    = (bool) $new_instance['show_forum'];
		$instance['max_shown']    = (int) $new_instance['max_shown'];
		$instance['shorten_freshness']    = (int) $new_instance['shorten_freshness'];
		$instance['hide_avatar']    = (int) $new_instance['hide_avatar'];

		// Force to any
		if ( !empty( $instance['parent_forum'] ) && !is_numeric( $instance['parent_forum'] ) ) {
			$instance['parent_forum'] = 'any';
		}

		return $instance;
	}

	/**
	 * Output the topic widget options form
	 *
	 * @since bbPress (r2653)
	 *
	 * @param $instance Instance
	 * @uses BBP_Topics_Widget::get_field_id() To output the field id
	 * @uses BBP_Topics_Widget::get_field_name() To output the field name
	 */
	public function form( $instance = array() ) {

		// Get widget settings
		$settings = $this->parse_settings( $instance ); ?>

		<p><label for="<?php echo $this->get_field_id( 'title'     ); ?>"><?php _e( 'Title:',                  'bbpress' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title'     ); ?>" name="<?php echo $this->get_field_name( 'title'     ); ?>" type="text" value="<?php echo esc_attr( $settings['title']     ); ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'max_shown' ); ?>"><?php _e( 'Maximum topics to show:', 'bbpress' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_shown' ); ?>" name="<?php echo $this->get_field_name( 'max_shown' ); ?>" type="text" value="<?php echo esc_attr( $settings['max_shown'] ); ?>" /></label></p>

		<p>
			<label for="<?php echo $this->get_field_id( 'parent_forum' ); ?>"><?php _e( 'Parent Forum ID:', 'bbpress' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'parent_forum' ); ?>" name="<?php echo $this->get_field_name( 'parent_forum' ); ?>" type="text" value="<?php echo esc_attr( $settings['parent_forum'] ); ?>" />
			</label>

			<br />

			<small><?php _e( '"0" to show only root - "any" to show all', 'bbpress' ); ?></small>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'show_freshness' ); ?>"><?php _e( 'Show Freshness:',    'bbpress' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_freshness' ); ?>" name="<?php echo $this->get_field_name( 'show_freshness' ); ?>" <?php checked( true, $settings['show_freshness'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'shorten_freshness' ); ?>"><?php _e( 'Shorten freshness:',    'bbpress' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'shorten_freshness' ); ?>" name="<?php echo $this->get_field_name( 'shorten_freshness' ); ?>" <?php checked( true, $settings['shorten_freshness'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_user' ); ?>"><?php _e( 'Show topic author:', 'bbpress' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_user' ); ?>" name="<?php echo $this->get_field_name( 'show_user' ); ?>" <?php checked( true, $settings['show_user'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'hide_avatar' ); ?>"><?php _e( 'Hide Avatar',    'bbpress' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'hide_avatar' ); ?>" name="<?php echo $this->get_field_name( 'hide_avatar' ); ?>" <?php checked( true, $settings['hide_avatar'] ); ?> value="1" /></label></p>
		<p><label for="<?php echo $this->get_field_id( 'show_forum' ); ?>"><?php _e( 'Show Forum:',    'bbpress' ); ?> <input type="checkbox" id="<?php echo $this->get_field_id( 'show_forum' ); ?>" name="<?php echo $this->get_field_name( 'show_forum' ); ?>" <?php checked( true, $settings['show_forum'] ); ?> value="1" /></label></p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e( 'Order By:',        'bbpress' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'order_by' ); ?>" id="<?php echo $this->get_field_name( 'order_by' ); ?>">
				<option <?php selected( $settings['order_by'], 'freshness' ); ?> value="freshness"><?php _e( 'Topics With Recent Replies', 'bbpress' ); ?></option>
				<option <?php selected( $settings['order_by'], 'newness' );   ?> value="newness"><?php _e( 'Newest Topics',                'bbpress' ); ?></option>
				<option <?php selected( $settings['order_by'], 'popular' );   ?> value="popular"><?php _e( 'Popular Topics',               'bbpress' ); ?></option>
				
			</select>
		</p>

		<?php
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since bbPress (r4802)
	 *
	 * @param $instance Instance
	 * @uses bbp_parse_args() To merge widget options into defaults
	 */
	public function parse_settings( $instance = array() ) {
		return bbp_parse_args( $instance, array(
			'title'        => __( 'Latest Activity', 'bbpress' ),
			'max_shown'    => 5,
			'show_date'    => false,
			'show_user'    => false,
			'parent_forum' => 'any',
			'order_by'     => false
		), 'topic_widget_settings' );
	}
}






?>