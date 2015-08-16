<?php


if (is_admin()) {
    add_action('admin_enqueue_scripts', 'wpdm_admin_enqueue_scripts');
    add_action("admin_menu", "fmmenu");
    add_action('wp_ajax_wdm_settings', 'wdm_ajax_settings');

    add_action("admin_head", "wpdm_adminjs");
    add_action('admin_head', "addusercolumn");



    add_action('wp_ajax_wpdm-activate-shop', 'wpdm_activate_shop');


    add_action("wp_ajax_quick_add_package","wpdm_save_new_package");
    add_action('wp_ajax_wpdm_category_dropdown', 'wpdm_print_cat_dropdown');

    add_action('wp_ajax_photo_gallery_upload', 'wpdm_check_upload');
    add_action('wp_ajax_icon_upload', 'wpdm_upload_icon');
    add_action('wp_ajax_wpdm-install-addon', 'wpdm_install_addon');


    add_action('admin_init', 'wpdm_meta_boxes', 0);

    add_filter('manage_posts_columns', 'wpdm_columns_th');
    add_action('manage_posts_custom_column', 'wpdm_columns_td', 10, 2);
    add_filter( 'request', 'wpdm_dlc_orderby' );
    add_filter( 'manage_edit-wpdmpro_sortable_columns', 'wpdm_dlc_sortable' );
    add_action('activated_plugin','wpdm_welcome_redirect');

    //Check add-on updates
    add_action('wp_ajax_wpdm_check_update', 'wpdm_check_update');
    add_action('admin_footer', 'wpdm_newversion_check');

    add_action("admin_init", "wpdm_initiate_settings");




} else {

    /** Short-Codes */
    add_shortcode('wpdm_direct_link', 'wpdm_hotlink');
    add_shortcode("wpdm_package", "wpdm_package_link");
    add_shortcode("wpdm_file", "wpdm_package_link_old");
    add_shortcode("wpdm_category", "wpdm_category");

    add_shortcode('wpdm-all-packages', 'wpdm_all_packages');
    add_shortcode('wpdm_all_packages', 'wpdm_all_packages');
    add_shortcode('wpdm_hotlink', 'wpdm_hotlink');

    /** Actions */
    add_action('wp_enqueue_scripts', 'wpdm_enqueue_scripts');

    add_action("init", "wpdm_DownloadNow");
    add_action("wp", "wpdm_ajax_call_exec");


    /** Filters */


    if (get_option('_wpdm_custom_template') == 0)
        add_filter('the_content', 'wpdm_downloadable', 99999);
    else
        add_filter('the_content', 'wpdm_downloadable', 0);

    add_filter('widget_text', 'do_shortcode');


    if (isset($_GET['mode']) && $_GET['mode'] == 'popup')
        add_action("init", "DownloadPageContent");

    add_action('init', 'wpdm_check_invpass');

    add_action('wp_loaded', 'wpdm_do_login');
    add_action('wp_loaded', 'wpdm_do_register');




}

add_action( 'plugins_loaded', 'wpdm_load_textdomain' );
add_action("init", "wpdm_common_actions");
add_action("init", "wpdm_upload_file");
add_action( 'admin_init', 'wpdm_sfb_access');
add_action('save_post', 'wpdm_save_package_data');
