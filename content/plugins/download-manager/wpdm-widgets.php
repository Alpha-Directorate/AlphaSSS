<?php


class wpdm_search_widget extends WP_Widget {
    /** constructor */


    function __construct() {
        parent::__construct(
            'wpdm_search_widget', // Base ID
            __( 'WPDM Search', 'wpdmpro' ), // Name
            array( 'description' => __( 'WordPress Download Manager Search Widget', 'wpdmpro' ), ) // Args
        );
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        extract($instance);
        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;
        echo "<div class='w3eden'><form action='".home_url('/')."'><input type='hidden' name='post_type[]' value='wpdmpro' />";
        echo "<input class='form-control input-lg' id='s' name='s' >";
        echo '</form></div>';
        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['promo'] = $new_instance['promo'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $title = isset($instance['title'])?esc_attr($instance['title']):"";
        $promo = isset($instance['promo'])?$instance['promo']:"The best plugin to manage your files & documents from your WordPress site";
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />

        </p>

    <?php
    }

}
class wpdm_affiliate_widget extends WP_Widget {
    /** constructor */


    function __construct() {
        parent::__construct(
            'wpdm_affiliate_widget', // Base ID
            __( 'WPDM Pro Affiliate', 'text_domain' ), // Name
            array( 'description' => __( 'Earn 20% from each sale referred by you', 'text_domain' ), ) // Args
        );
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        extract($instance);
        echo $before_widget;

        echo "<div class='w3eden'><div class='panel panel-primary'>";
        echo "<div class='panel-heading' style='font-size: 10pt'>Best File & Document Management Plugin</div><div class='panel-body' style='padding-bottom:0;background:#F2F2F2;'><a href='http://www.wpdownloadmanager.com/pricing/?affid={$title}'><img src='http://cdn.wpdownloadmanager.com/wp-content/uploads/images/wpdm-main-banner-v4x.png' style='max-width: 100%' alt='WordPress Download Manager' /></a><div class='text-center' style='margin:10px 0'>{$promo}</div></div>";
        echo "<div class='panel-footer' style='line-height: 30px'><a class='pull-right btn btn-sm btn-danger' style='color: #ffffff;font-weight:900' href='http://www.wpdownloadmanager.com/pricing/?affid={$title}'>Buy Now <i class='fa fa-angle-right'></i></a><span class='text-success' style='line-height: 30px;font-weight:900;font-size: 10pt;border-radius: 2px;'>$45.00</span></div></div></div>";
        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['promo'] = $new_instance['promo'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $title = isset($instance['title'])?esc_attr($instance['title']):"";
        $promo = isset($instance['promo'])?$instance['promo']:"The best plugin to manage your files & documents from your WordPress site";
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('WPDM Affiliate ID:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            <em>It is your account <b>username</b> at www.wpdownloadmanager.com. You will get up to 20% from each sale referred by you</em>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('promo'); ?>"><?php _e('Promo Text:'); ?></label>
            <textarea class="widefat" id="<?php echo $this->get_field_id('promo'); ?>" name="<?php echo $this->get_field_name('promo'); ?>"><?php echo htmlspecialchars($promo); ?></textarea>
            <em>It is your account <b>username</b> at www.wpdownloadmanager.com. You will get up to 20% from each sale referred by you</em>
        </p>
    <?php
    }

}

class wpdm_download_button_widget extends WP_Widget {
    /** constructor */
    function wpdm_download_button_widget() {
        parent::WP_Widget(false, 'WPDM Download Button');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; 
                echo "<ul>";        
                wpdm_list_categories();
                echo "</ul>";
               echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $title = esc_attr($instance['title']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php 
    }

} 



class wpdm_topdls_widget extends WP_Widget {
    /** constructor */
    function wpdm_topdls_widget() {
        parent::WP_Widget(false, 'WPDM Top Downloads');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        global $post;
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $sdc = $instance['sdc'];
        $nop = $instance['nop'];
        $sdc = $sdc==''?'link-template-default-wdc.php':$sdc;
        $newp = new WP_Query(array('post_type'=>'wpdmpro','posts_per_page'=>$nop, 'order_by'=>'publish_date','order'=>'desc','orderby' => 'meta_value_num','meta_key'=>'__wpdm_download_count','order'=>'desc'));

        ?>
        <?php echo $before_widget; ?>
        <?php if ( $title )
            echo $before_title . $title . $after_title;
        echo "<div class='w3eden wpdm-category'>";
        while($newp->have_posts()){
            $newp->the_post();

            $pack = (array)$post;
            echo FetchTemplate($sdc, $pack);
        }
        echo "</div>";
        echo $after_widget;
        wp_reset_query();
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['sdc'] = strip_tags($new_instance['sdc']);
    $instance['nop'] = strip_tags($new_instance['nop']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $title = isset($instance['title'])?esc_attr($instance['title']):"";
        $sdc = isset($instance['sdc'])?esc_attr($instance['sdc']):"link-template-default.php";
        $nop = isset($instance['nop'])?esc_attr($instance['nop']):5;
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('nop'); ?>"><?php _e('Number of packages to show:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('nop'); ?>" name="<?php echo $this->get_field_name('nop'); ?>" type="text" value="<?php echo $nop; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('sdc'); ?>"><?php _e('Link Template:'); ?></label>           
          <?php
                $tdr =  str_replace("modules","templates",dirname(__FILE__)).'/';
                $ptemplates = scandir($tdr);
                        
            ?>
          <select id="<?php echo $this->get_field_id('sdc'); ?>" name="<?php echo $this->get_field_name('sdc'); ?>">           
            <?php
            $ctpls = scandir(WPDM_BASE_DIR.'/templates/');
                              array_shift($ctpls);
                              array_shift($ctpls);
                              $ptpls = $ctpls;
                              foreach($ctpls as $ctpl){
                                  $tmpdata = file_get_contents(WPDM_BASE_DIR.'/templates/'.$ctpl);
                                  if(preg_match("/WPDM[\s]+Link[\s]+Template[\s]*:([^\-\->]+)/",$tmpdata, $matches)){                                 
                
            ?>
            <option value="<?php echo $ctpl; ?>"  <?php echo $sdc==$ctpl?'selected=selected':''; ?>><?php echo $matches[1]; ?></option>
            <?php    
            }  
            } 
            if($templates = unserialize(get_option("_fm_link_templates",true))){ 
              foreach($templates as $id=>$template) {  
            ?>
            <option value="<?php echo $id; ?>"  <?php echo ( $sdc==$id )?' selected ':'';  ?>><?php echo $template['title']; ?></option>
            <?php } } ?>
          </select> 
          
        </p>
        <?php 
    }

} 

class wpdm_newpacks_widget extends WP_Widget {
    /** constructor */
    function wpdm_newpacks_widget() {
        parent::WP_Widget(false, 'WPDM New Files');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        global $post;
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $sdc = $instance['sdc'];
        $nop = $instance['nop1'];
        $sdc = $sdc==''?'link-template-default.php':$sdc;
        $newp = new WP_Query(array('post_type'=>'wpdmpro','posts_per_page'=>$nop, 'order_by'=>'publish_date','order'=>'desc'));
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title;
        echo "<div class='w3eden wpdm-category'>";
                while($newp->have_posts()){
                    $newp->the_post();

                    $pack = (array)$post;
                    echo FetchTemplate($sdc, $pack);
                }
        echo "</div>";
               echo $after_widget;
        wp_reset_query();
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['sdc'] = strip_tags($new_instance['sdc']);
    $instance['nop1'] = strip_tags($new_instance['nop1']);
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $title = isset($instance['title'])?esc_attr($instance['title']):"";
        $sdc = isset($instance['sdc'])?esc_attr($instance['sdc']):'link-template-default.php';
        $nop = isset($instance['nop1'])?esc_attr($instance['nop1']):5;
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
         <p>
          <label for="<?php echo $this->get_field_id('nop1'); ?>"><?php _e('Number of packages to show:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('nop1'); ?>" name="<?php echo $this->get_field_name('nop1'); ?>" type="text" value="<?php echo $nop; ?>" />
        </p>
        <p>
        <?php
                $tdr =  str_replace("modules","templates",dirname(__FILE__)).'/';
                $ptemplates = scandir($tdr);
                        
            ?>
          <label for="<?php echo $this->get_field_id('sdc'); ?>"><?php _e('Link Template:'); ?></label>           
          <select id="<?php echo $this->get_field_id('sdc'); ?>" name="<?php echo $this->get_field_name('sdc'); ?>">
                                   
            <?php
            $ctpls = scandir(WPDM_BASE_DIR.'/templates/');
                              array_shift($ctpls);
                              array_shift($ctpls);
                              $ptpls = $ctpls;
                              foreach($ctpls as $ctpl){
                                  $tmpdata = file_get_contents(WPDM_BASE_DIR.'/templates/'.$ctpl);
                                  if(preg_match("/WPDM[\s]+Link[\s]+Template[\s]*:([^\-\->]+)/",$tmpdata, $matches)){                                 
                
            ?>
            <option value="<?php echo $ctpl; ?>"  <?php echo $sdc==$ctpl?'selected=selected':''; ?>><?php echo $matches[1]; ?></option>
            <?php    
            }  
            } 
            if($templates = unserialize(get_option("_fm_link_templates",true))){ 
              foreach($templates as $id=>$template) {  
            ?>
            <option value="<?php echo $id; ?>"  <?php echo ( $sdc==$id )?' selected ':'';  ?>><?php echo $template['title']; ?></option>
            <?php } } ?>
          </select> 
          
        </p>
        <?php 
    }

} 


add_action('widgets_init', create_function('', 'return register_widget("wpdm_topdls_widget");'));
add_action('widgets_init', create_function('', 'return register_widget("wpdm_newpacks_widget");'));
add_action('widgets_init', create_function('', 'return register_widget("wpdm_affiliate_widget");'));
add_action('widgets_init', create_function('', 'return register_widget("wpdm_search_widget");'));

