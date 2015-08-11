<?php
global $wpdm_message, $btnclass;


function wpdm_print_cat_dropdown()
{
    echo "<option value=''>" . __('Top Level Category', 'wpdmpro') . "</option>";
    wpdm_cat_dropdown_tree('', 0, '');
    die();
}

/**
 * Setup wpdm pro custom post type and taxonomy
 */
function wpdm_common_actions()
{
    $labels = array(
        'name' => __('Downloads', 'wpdmpro'),
        'singular_name' => __('File', 'wpdmpro'),
        'add_new' => __('Add New', 'wpdmpro'),
        'add_new_item' => __('Add New File', 'wpdmpro'),
        'edit_item' => __('Edit File', 'wpdmpro'),
        'new_item' => __('New File', 'wpdmpro'),
        'all_items' => __('All Files', 'wpdmpro'),
        'view_item' => __('View File', 'wpdmpro'),
        'search_items' => __('Search Files', 'wpdmpro'),
        'not_found' => __('No File Found', 'wpdmpro'),
        'not_found_in_trash' => __('No Files found in Trash', 'wpdmpro'),
        'parent_item_colon' => '',
        'menu_name' => __('Downloads', 'wpdmpro')

    );
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'download', 'with_front' => true),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'menu_icon' => 'dashicons-download',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail')

    );
    register_post_type('wpdmpro', $args);


    $labels = array(
        'name' => __('Categories', 'wpdmpro'),
        'singular_name' => __('Category', 'wpdmpro'),
        'search_items' => __('Search Categories', 'wpdmpro'),
        'all_items' => __('All Categories', 'wpdmpro'),
        'parent_item' => __('Parent Category', 'wpdmpro'),
        'parent_item_colon' => __('Parent Category:', 'wpdmpro'),
        'edit_item' => __('Edit Category', 'wpdmpro'),
        'update_item' => __('Update Category', 'wpdmpro'),
        'add_new_item' => __('Add New Category', 'wpdmpro'),
        'new_item_name' => __('New Category Name', 'wpdmpro'),
        'menu_name' => __('Categories', 'wpdmpro'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'download-category'),
    );

    register_taxonomy('wpdmcategory', array('wpdmpro'), $args);


}

/**
 * Download contents as a file
 * @param $filename
 * @param $content
 */
function wpdm_download_data($filename, $content)
{
    @ob_end_clean();
    header("Content-Description: File Transfer");
    header("Content-Type: text/plain");
    header("Content-disposition: attachment;filename=\"$filename\"");
    header("Content-Transfer-Encoding: text/plain");
    header("Content-Length: " . strlen($content));
    echo $content;
}


/**
 * Cache remote file to local directory and return local file path
 * @param mixed $url
 * @param mixed $filename
 * @return string $path
 */
function wpdm_cache_remote_file($url, $filename = '')
{
    $filename = $filename ? $filename : end($tmp = explode('/', $url));
    $path = WPDM_CACHE_DIR . $filename;
    $fp = fopen($path, 'w');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    $data = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return $path;
}

/**
 * @usage Download Given File
 * @param $filepath
 * @param $filename
 * @param int $speed
 * @param int $resume_support
 * @param array $extras
 */
function wpdm_download_file($filepath, $filename, $speed = 0, $resume_support = 1, $extras = array())
{

    if (isset($extras['package']))
        $package = $extras['package'];
    $mdata = wp_check_filetype($filename);
    $content_type = $mdata['type'];

    $buffer = $speed ? $speed : 1024;

    $buffer *= 1024; // in byte

    $bandwidth = 0;

    if( function_exists('ini_set') )
        @ini_set( 'display_errors', 0 );

    @session_write_close();

    //if ( function_exists( 'apache_setenv' ) )
    //    @apache_setenv( 'no-gzip', 1 );

    if( function_exists('ini_set') )
        @ini_set('zlib.output_compression', 'Off');


    @set_time_limit(0);
    @session_cache_limiter('none');

    if ( get_option( '__wpdm_support_output_buffer', 1 ) == 1 ) {

        do {
            @ob_end_clean();
        } while ( ob_get_level() > 0 );
    }

    if (strpos($filepath, '://'))
        $filepath = wpdm_cache_remote_file($filepath, $filename);

    if (file_exists($filepath))
        $fsize = filesize($filepath);
    else
        $fsize = 0;

    nocache_headers();
    header( "X-Robots-Tag: noindex, nofollow", true );
    header("Robots: none");
    header("Content-type: $content_type");
    if(get_option('__wpdm_open_in_browser', 0))
    header("Content-disposition: inline;filename=\"{$filename}\"");
    else
    header("Content-disposition: attachment;filename=\"{$filename}\"");
    header("Content-Transfer-Encoding: binary");

    if( ( isset($_REQUEST['play']) && strpos($_SERVER['HTTP_USER_AGENT'],"Safari") ) || get_option('__wpdm_download_resume',1)==2 ) {
        readfile($filepath);
        die();
    }

    $file = @fopen($filepath, "rb");

    //check if http_range is sent by browser (or download manager)
    if (isset($_SERVER['HTTP_RANGE']) && $fsize > 0) {
        list($bytes, $http_range) = explode("=", $_SERVER['HTTP_RANGE']);
        $set_pointer = intval(array_shift($tmp = explode('-', $http_range)));

        $new_length = $fsize - $set_pointer;

        header("Accept-Ranges: bytes");
        header("HTTP/1.1 206 Partial Content");

        header("Content-Length: $new_length");
        header("Content-Range: bytes $http_range$fsize/$fsize");

        fseek($file, $set_pointer);

    } else {
        header("Content-Length: " . $fsize);
    }
    $packet = 1;

    if ($file) {
        while (!(connection_aborted() || connection_status() == 1) && $fsize > 0) {
            if ($fsize > $buffer)
                echo fread($file, $buffer);
            else
                echo fread($file, $fsize);
            ob_flush();
            flush();
            $fsize -= $buffer;
            $bandwidth += $buffer;
            if ($speed > 0 && ($bandwidth > $speed * $packet * 1024)) {
                sleep(1);
                $packet++;
            }


        }
        $package['downloaded_file_size'] = $fsize;
        //add_action('wpdm_download_completed', $package);
        @fclose($file);
    }

    die();

}


/**
 * @usage Generate downlad link of a package
 * @param $package
 * @param int $embed
 * @param array $extras
 * @return string
 */
function DownloadLink(&$package, $embed = 0, $extras = array())
{
    global $wpdb, $current_user, $wpdm_download_icon, $wpdm_download_lock_icon, $btnclass;
    extract($extras);
    $data = '';
    get_currentuserinfo();

    $package['link_url'] = home_url('/?download=1&');
    $package['link_label'] = !isset($package['link_label']) || $package['link_label'] == '' ? __("Download", "wpdmpro") : $package['link_label'];

    //Change link label using a button image
    $package['link_label'] = apply_filters('wpdm_button_image', $package['link_label'], $package);


    $package['download_url'] = wpdm_download_url($package);
    if (wpdm_is_download_limit_exceed($package['ID'])) {
        $package['download_url'] = '#';
        $package['link_label'] = __('Download Limit Exceeded','wpdmpro');
    }
    if (isset($package['expire_date']) && $package['expire_date'] > 0 && $package['expire_date'] < time()) {
        $package['download_url'] = '#';
        $package['link_label'] = __('Download was expired on', 'wpdmpro') . " " . date(get_option('date_format'), $package['expire_date']);
        $package['download_link'] = "<a href='#'>{$package['link_label']}</a>";
        return "<div class='alert alert-warning'><b>" . __('Download:', 'wpdmpro') . "</b><br/>{$package['link_label']}</div>";
    }

    if (isset($package['publish_date']) && $package['publish_date'] > 0 && $package['publish_date'] > time()) {
        $package['download_url'] = '#';
        $package['link_label'] = __('Download will be available from ', 'wpdmpro') . " " . date(get_option('date_format'), $package['publish_date']);
        $package['download_link'] = "<a href='#'>{$package['link_label']}</a>";
        return "<div class='alert alert-warning'><b>" . __('Download:', 'wpdmpro') . "</b><br/>{$package['link_label']}</div>";
    }

    $link_label = isset($package['link_label']) ? $package['link_label'] : __('Download', 'wpdmpro');

    $package['access'] = @maybe_unserialize($package['access']);
    $access = array();

    if (count($access) > 0) {
        foreach ($access as $role) {
            if (!@in_array($role, $package['access']))
                $package['access'][] = $role;
        }
    }
    if ($package['download_url'] != '#')
        $package['download_link'] = "<a class='wpdm-download-link wpdm-download-locked {$btnclass}' rel='noindex nofollow' href='{$package['download_url']}'><i class='$wpdm_download_icon'></i>{$link_label}</a>";
    else
        $package['download_link'] = "<div class='alert alert-warning'><b>" . __('Download:', 'wpdmpro') . "</b><br/>{$link_label}</div>";
    $caps = array_keys($current_user->caps);
    $role = array_shift($caps);
    $matched = @array_intersect($current_user->roles, @maybe_unserialize($package['access']));

    $skiplink = 0;
    if (is_user_logged_in() && count($matched) <= 0 && !@in_array('guest', @maybe_unserialize($package['access']))) {
        $package['download_url'] = "#";
        $package['download_link'] = stripslashes(get_option('wpdm_permission_msg'));
        $package = apply_filters('download_link', $package);
        if (get_option('_wpdm_hide_all', 0) == 1) $package['download_link'] = 'blocked';
        return $package['download_link'];
    }
    if (!@in_array('guest', @maybe_unserialize($package['access'])) && !is_user_logged_in()) {

        //$loginform = wp_login_form(array('echo' => 0));
        //$loginform = str_replace('class="', 'class="form-control ', $loginform);

        //$loginform = '<a class="wpdm-download-link wpdm-download-login ' . $btnclass . '" href="#wpdm-login-form" data-toggle="modal"><i class=\'glyphicon glyphicon-lock\'></i>' . __('Login', 'wpdmpro') . '</a><div id="wpdm-login-form" class="modal fade">' . $loginform . "</div>";
        $package['download_url'] = wp_login_url($_SERVER['REQUEST_URI']);
        $package['download_link'] = stripcslashes(get_option('wpdm_login_msg'));
        $package['download_link'] = $package['download_link'] == ''?"<a class='btn btn-danger btn-xs' href='{$package['download_url']}'>".__('Please login to download','wpdmpro')."</a>":$package['download_link'];
        return $package['download_link']; //get_option('__wpdm_login_form', 0) == 1 ? $loginform : $package['download_link'];die();

    }

    $package = apply_filters('download_link', $package);

    $unqid = uniqid();
    if (!isset($package['quota']) || (isset($package['quota']) && $package['quota'] > 0 && $package['quota'] > $package['download_count']) || $package['quota'] == 0) {

        $lock = 0;

        if ($package['password'] != '') {
            $lock = 'locked';
            $data = '
       
        <div id="msg_' .$unqid. '_'. $package['ID'] . '" style="display:none;" class="text-danger">processing...</div>
        <form id="wpdmdlf_' . $unqid . '_' . $package['ID'] . '" method=post action="' . home_url('/') . '" style="margin-bottom:0px;">
        <input type=hidden name="id" value="' . $package['ID'] . '" />
        <input type=hidden name="dataType" value="json" />
        <input type=hidden name="execute" value="wpdm_getlink" />
        <input type=hidden name="action" value="wpdm_ajax_call" />
        ';

            $data .= '
                <div class="input-group" style="max-width:250px;">
        <input type="password" class="form-control input-sm" placeholder="Enter Password" size="10" id="password_' . $unqid . '_' . $package['ID'] . '" name="password" />
        <span class="input-group-btn"><input style="margin-left:5px" id="wpdm_submit_' . $unqid . '_' . $package['ID'] . '" class="wpdm_submit btn btn-sm btn-warning" type="submit" value="' . __('Download', 'wpdmpro') . '" /></span>
            </div>
        </form>        
        
        <script type="text/javascript">
        jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").submit(function(){

            jQuery("#msg_' .$unqid. '_' . $package['ID'] . '").html("Processing...").show();
            jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").hide();    

            jQuery(this).removeClass("wpdm_submit").addClass("wpdm_submit_wait");

        jQuery(this).ajaxSubmit({
        success: function(res){

                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").hide();
                jQuery("#msg_' .$unqid. '_' . $package['ID'] . '").html("Verifying...").css("cursor","pointer").show().click(function(){ jQuery(this).hide();jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").show(); });
                if(res.downloadurl!=""&&res.downloadurl!=undefined) {
                location.href=res.downloadurl;
                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").html("<a style=\'color:#ffffff !important\' class=\'btn btn-success\' href=\'"+res.downloadurl+"\'>Download</a>");
                jQuery("#msg_' .$unqid. '_' . $package['ID'] . '").html("processing...").hide();
                jQuery("#wpdmdlf_' . $unqid . '_' . $package['ID'] . '").show();
                } else {

                    jQuery("#msg_' .$unqid. '_' . $package['ID'] . '").html(""+res.error+"&nbsp;<span class=\'label label-primary\'>Retry</span>").show();;
                }
        }
        });
        return false;
        });
        </script> 
         
        ';
        }


        if ($lock === 'locked') {
            $popstyle = isset($popstyle) && in_array($popstyle, array('modal', 'pop-over')) ? $popstyle : 'pop-over';
            if ($embed == 1)
                $adata = "" . $data . "";
            else {
                $adata = '<a href="#pkg_' . $package['ID'] . '" data-title="Download ' . $package['title'] . '" class="wpdm-download-link wpdm-download-locked ' . $popstyle . ' ' . $btnclass . '"><i class=\'' . $wpdm_download_lock_icon . '\'></i>' . $package['link_label'] . '</a>';
                if ($popstyle == 'pop-over')
                    $adata .= '<div class="modal fade" id="pkg_' . $package['ID'] . '"><div class="row all-locks">' . $data . '</div></div>';
                else
                    $adata .= '<div class="modal fade" id="pkg_' . $package['ID'] . '"> <div class="modal-header"><strong style="margin:0px;font-size:12pt">' . __('Download') . '</strong></div><div class="modal-body" styl>' . $data . '</div><div class="modal-footer">' . __('Please take any of the actions above to start download') . '</div></div>';
            }

            $data = $adata;
        }
        if ($lock !== 'locked') {

            $data = $package['download_link'];


        }
    } else {
        $data = "Download limit exceeded!";
    }
    if(!isset($package['files'][0]) || trim($package['files'][0])=='') return "<div class='alert alert-danger' style='margin: 0;border-radius: 2px'>". __('No file attached!', 'wpdmpro')."</div>";
    //return str_replace(array("\r","\n"),"",$data);
    return $data;

}

/**
 * @usage Check if a package is locked or public
 * @param $id
 * @return bool
 */
function wpdm_is_locked($id){
    $package = array();
    $package['ID'] = $id;
    $package = array_merge($package, wpdm_custom_data($package['ID']));
    $lock = '';

    if (isset($package['password_lock']) && $package['password_lock'] == 1) $lock = 'locked';

    if ($lock !== 'locked')
        $lock = apply_filters('wpdm_check_lock', $id, $lock);

    return ($lock=='locked');


}


function wpdm_addonslist(){

    if(!isset($_SESSION['wpdm_addon_store_data'])){
        $data = remote_get('http://www.wpdownloadmanager.com/?wpdm_api_req=getPackageList');
        $cats = remote_get('http://www.wpdownloadmanager.com/?wpdm_api_req=getCategoryList');
        $_SESSION['wpdm_addon_store_data'] = $data;
        $_SESSION['wpdm_addon_store_cats'] = $cats;
    }
    else {
        $data = $_SESSION['wpdm_addon_store_data'];
        $cats = $_SESSION['wpdm_addon_store_cats'];
    }

    include(WPDM_BASE_DIR."/tpls/wpdm-addons-list.php");
}



//Direct Download button
function wpdm_ddl_button($package, $icononly = false)
{
    global $wpdb, $current_user;
    $label = $icononly ? "" : "Download Now";
    //print_r($package);     
    $download_url = home_url("/?file={$package['ID']}");
    return "<a class='wpdm-gh-button wpdm-gh-icon arrowdown wpdm-gh-big' href='$download_url'>$label</a>";

}


/**
 * return download link after verifying password
 * data format: json
 */
function wpdm_getlink()
{
    global $wpdb;
    if (!isset($_POST['id'])) return;
    $id = (int)$_POST['id'];
    $password = isset($_POST['password']) ? addslashes($_POST['password']) : '';
    $file = get_post($id, ARRAY_A);
    $file['ID'] = $file['ID'];
    $file = wpdm_setup_package_data($file);

    $file1 = $file;
    // and( password='$password' or password like '%[$password]%')

    $plock = $file['password']!='' ? 1 : 0;

    $data = array('error' => '', 'downloadurl' => '');


    if ($plock == 1 && $password != $file['password']) {
        $data['error'] = 'Wrong Password!';
        $file = array();
    }
    if ($plock == 1 && $password == '') {
        $data['error'] = 'Wrong Password!';
        $file = array();
    }

    if ($plock == 1 && $data['error']=='') {
        $key = uniqid();
        update_post_meta($file['ID'], $key, 3);
    }


    //if (isset($_COOKIE['unlocked_' . $file['ID']])) {
    //    $data['error'] = '';
    //    $file = $file1;
    // }

    if ($data['error'] == '') $data['downloadurl'] = wpdm_download_url($file, "_wpdmkey={$key}"); // home_url('/?downloadkey='.md5($file['files']).'&file='.$id.$ux);
    $adata = apply_filters("wpdmgetlink", $data, $file);
    $data = is_array($adata) ? $adata : $data;
    header("Content-type: application/json");
    die(json_encode($data));
}

/**
 * callback function for shortcode [wpdm_package id=pid]
 *
 * @param mixed $params
 * @return mixed
 */
function wpdm_package_link($params)
{
    global $wpdb, $current_user;
    extract($params);
    $postlink = site_url('/');
    if (isset($pagetemplate) && $pagetemplate == 1)
        return DownloadPageContent($id);
    $data = get_post($id, ARRAY_A);
    $data = wpdm_setup_package_data($data);

    if ($data['ID'] == '') {
        return '';
    }


    if(isset($template) && in_array($template, array('link-template-default','link-template-default-wdc','link-template-default-ext','link-template-button')))
        $template = "{$template}.php";
    else
        $template = "link-template-default.php";
    if(isset($color))
        $data['color'] = $color;

    return "<div class='w3eden'>" . FetchTemplate($template, $data, 'link') . "</div>";
}

/**
 * callback function for shortcode [wpdm_file id=pid]
 *
 * @param mixed $params
 * @return mixed
 */
function wpdm_package_link_old($params)
{
    global $wpdb, $current_user;
    extract($params);
    if(!isset($id)) return '';
    $args = array(
        'meta_key' => '__wpdm_legacy_id',
        'meta_value' => $id,
        'post_type' => 'wpdmpro',
        'posts_per_page' => 1
    );
    $posts = get_posts($args);
    if(is_array($posts) && isset($posts[0])) {
        $data = (array)$posts[0];
        $data = wpdm_setup_package_data($data);
    }
    if (!isset($data['ID']) || $data['ID'] == '') {
        return '';
    }


    $template = "link-template-default.php";
    if(isset($style) && $style =='button')
        $template = "link-template-button.php";

    $data['color'] = isset($params['color'])?$params['color']:'light';
    return "<div class='w3eden'>" . FetchTemplate($template, $data, 'link') . "</div>";
}

/**
 * callback function for shortcode [wpdm_package id=pid]
 *
 * @param mixed $params
 * @return mixed
 */
function wpdm_hotlink($params)
{

    extract($params);

    if (isset($pagetemplate) && $pagetemplate == 1)
        return DownloadPageContent($id);
    $data = get_post($id, ARRAY_A);
    $data = wpdm_setup_package_data($data);

    if ($data['ID'] == '') {
        return '';
    }

    if(isset($link_label)) $data['link_label'] = $link_label;
    return  DownloadLink($data, 1);
}

/**
 * Parse shortcode
 *
 * @param mixed $content
 * @return mixed
 */
function wpdm_downloadable($content)
{
    if(defined('WPDM_THEME_SUPPORT')&&WPDM_THEME_SUPPORT==true) return $content;
    global $wpdb, $current_user, $post, $wp_query, $wpdm_package;
    if (isset($wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')]) && $wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')] != '')
        return wpdm_embed_category(array("id" => $wp_query->query_vars[get_option('__wpdm_curl_base', 'downloads')]));
    $postlink = site_url('/');
    get_currentuserinfo();
    $permission_msg = get_option('wpdm_permission_msg') ? stripslashes(get_option('wpdm_permission_msg')) : "<div  style=\"background:url('" . get_option('siteurl') . "/wp-content/plugins/download-manager/images/lock.png') no-repeat;padding:3px 12px 12px 28px;font:bold 10pt verdana;color:#800000\">Sorry! You don't have suffient permission to download this file!</div>";
    $login_msg = get_option('wpdm_login_msg') ? stripcslashes(get_option('wpdm_login_msg')) : "<a href='" . get_option('siteurl') . "/wp-login.php'  style=\"background:url('" . get_option('siteurl') . "/wp-content/plugins/download-manager/images/lock.png') no-repeat;padding:3px 12px 12px 28px;font:bold 10pt verdana;\">Please login to access downloadables</a>";
    $user = new WP_User(null);
    if (isset($_GET[get_option('__wpdm_purl_base', 'download')]) && $_GET[get_option('__wpdm_purl_base', 'download')] != '' && $wp_query->query_vars[get_option('__wpdm_purl_base', 'download')] == '')
        $wp_query->query_vars[get_option('__wpdm_purl_base', 'download')] = $_GET[get_option('__wpdm_purl_base', 'download')];
    $wp_query->query_vars[get_option('__wpdm_purl_base', 'download')] = isset($wp_query->query_vars[get_option('__wpdm_purl_base', 'download')]) ? urldecode($wp_query->query_vars[get_option('__wpdm_purl_base', 'download')]) : '';

    if (is_singular('wpdmpro')) {
        if (get_option('_wpdm_custom_template') == 1 || file_exists(get_template_directory().'/single-wpdmpro.php')) return $content;

        return DownloadPageContent();
    }

    return $content;


}



/**
 * @usage Validate and sanitize input data
 * @param $var
 * @param array $params
 * @return int|null|string|void
 */
function wpdm_query_var($var, $params = array())
{
    $val = isset($_REQUEST[$var]) ? $_REQUEST[$var] : null;
    $validate = is_string($params) ? $params : '';
    $validate = is_array($params) && isset($params['validate']) ? $params['validate'] : $validate;

    switch ($validate) {
        case 'num':
            $val = intval($val);
            break;
        case 'txt':
            $val = esc_attr($val);
            break;
    }

    return $val;
}
        
        
function wpdm_validate_newpass_sk()
{
    global $wp_query, $wpdb;
    if ($wp_query->query_vars['minimaxtask'] != 'new-password') return false;
    $reminder = sanitize_text_field($_REQUEST['u']);
    $userdata = $wpdb->get_row("select * from {$wpdb->prefix}users where user_login='$reminder' or user_email='$reminder'");
    $usk = get_user_meta($userdata->ID, 'remind_pass_sk', true);
    if ($usk != $_REQUEST['sk']) return false;
    return true;
}

function wpdm_update_password()
{
    global $wpdb;
    if (!isset($_POST['user_pass'])) return;
    $reminder = sanitize_text_field($_REQUEST['u']);
    $userdata = $wpdb->get_row("select * from {$wpdb->prefix}users where user_login='$reminder' or user_email='$reminder'");
    $usk = get_user_meta($userdata->ID, 'remind_pass_sk', true);
    if ($usk != $_REQUEST['sk']) return;
    $pid = uniqid();
    update_user_meta($userdata->ID, 'remind_pass_sk', $pid);
    wp_update_user(array('ID' => $userdata->ID, 'user_pass' => $_POST['user_pass']));
    header("location: " . home_url('/members/'));
    die();
}

function wpdm_do_logout()
{
    global $wp_query;
    if (isset($_GET['task']) && $_GET['task'] == 'logout') {
        wp_logout();
        header("location: " . home_url('/'));
        die();
    }
}


function wpdm_category($params)
{
    $params['order_field'] = isset($params['order_by'])?$params['order_by']:'publish_date';
    unset($params['order_by']);
    if (isset($params['item_per_page']) && !isset($params['items_per_page'])) $params['items_per_page'] = $params['item_per_page'];
    unset($params['item_per_page']);
    return wpdm_embed_category($params);

}


function wpdm_page_links($urltemplate, $total, $page = 1, $items_per_page = 10)
{
    if ($items_per_page <= 0) $items_per_page = 10;
    $page = $page ? $page : 1;
    $pages = ceil($total / $items_per_page);
    $start = ($page - 1) * $items_per_page;
    $pag = new wpdm_pagination();
    $pag->items($total);
    $pag->nextLabel(' <i class="icon icon-forward"></i> ');
    $pag->prevLabel(' <i class="icon icon-backward"></i> ');
    $pag->limit($items_per_page);
    $pag->urlTemplate($urltemplate);
    $pag->currentPage($page);
    return $pag->show();
}


function wpdm_embed_category($params = array('id' => '', 'items_per_page' => 10, 'title' => false, 'desc' => false, 'order_field' => 'create_date', 'order' => 'desc', 'paging' => false, 'toolbar' => 1, 'template' => ''))
{
    extract($params);
    if(!isset($id)) return;
    if(!isset($items_per_page)) $items_per_page = 10;
    if(!isset($template)) $template = 'link-template-panel.php';
    $cols = 1;
    if(!isset($toolbar)) $toolbar = 1;
    $cwd_class = "col-md-".(int)(12/$cols);

    $id = trim($id, ", ");
    $cids = explode(",", $id);

    global $wpdb, $current_user, $post, $wp_query;

    $order_field = isset($order_field) ? $order_field : 'publish_date';
    $order_field = isset($_GET['orderby']) ? $_GET['orderby'] : $order_field;
    $order = isset($order) ? $order : 'desc';
    $order = isset($_GET['order']) ? $_GET['order'] : $order;

    $params = array(
        'post_type' => 'wpdmpro',
        'posts_per_page' => $items_per_page,
        'tax_query' => array(array(
            'taxonomy' => 'wpdmcategory',
            'field' => 'slug',
            'terms' => $cids
        ))
    );

    $params['orderby'] = $order_field;
    $params['order'] = $order;
    $page = isset($_GET['cp']) ? $_GET['cp'] : 1;
    if(intval(($page))>1)
    $params['paged'] = $page;


    $packs = new WP_Query($params);
    $total = $packs->found_posts;

    $pages = ceil($total / $items_per_page);
    $start = ($page - 1) * $items_per_page;

    if (!isset($paging) || $paging == 1) {
        $pag = new wpdm_pagination();
        $pag->items($total);
        $pag->nextLabel(' &#9658; ');
        $pag->prevLabel(' &#9668; ');
        $pag->limit($items_per_page);
        $pag->currentPage($page);
    }

    $burl = get_permalink();
    $url = get_permalink();
    $url = strpos($url, '?') ? $url . '&' : $url . '?';
    $url = preg_replace("/[\&]*cp=[0-9]+[\&]*/", "", $url);
    $url = strpos($url, '?') ? $url . '&' : $url . '?';
    if (!isset($paging) || $paging == 1)
        $pag->urlTemplate($url . "cp=[%PAGENO%]");


    $html = '';

    //$template = "<div class='media'><div class='pull-left'>[icon]</div><div class='media-body'><b>[title]</b><br/>[download_link]</div></div>";
    if(isset($template) && in_array($template, array('link-template-default','link-template-default-wdc','link-template-default-ext','link-template-button')))
        $template = "{$template}.php";
    else
        $template = "link-template-default.php";
    global $post;
    while($packs->have_posts()) { $packs->the_post();

        $pack = (array)$post;
        $repeater = "<div class='{$cwd_class}'>".FetchTemplate($template, $pack)."</div>";
        $html .=  $repeater;

    }

    $html = "<div class='row'>{$html}</div>";
    $cname = array();
    foreach($cids as $cid){
        $cat = get_term_by('slug', $cid, 'wpdmcategory');
        if(is_object($cat))
        $cname[] = $cat->name;
    }
    $cats = implode(", ", $cname);
    //$category['title'] = stripcslashes($category['title']);
    //$category['content'] = stripcslashes($category['content']);
    $cimg = '';
    $desc = '';
    //if ($title == 1 && count($cids) == 1) $title = "<h3 style='margin:0px;font-size:11pt;line-height:normal'>$category[title]</h3>";
    //if (get_option('__wpdm_cat_img', 0) == 1) $cimg = "<img src='{$category[icon]}' />";
    //if ($desc == 1 && count($cids) == 1 || get_option('__wpdm_cat_desc', 0) == 1) $desc = wpautop($category['content']);


    $subcats = '';
    if (function_exists('wpdm_ap_categories') && $subcats == 1) {
        $schtml = wpdm_ap_categories(array('parent' => $id));
        if ($schtml != '') {
            $subcats = "<fieldset class='cat-page-tilte'><legend>" . __('Sub-Categories', 'wpdmpro') . "</legend>" . $schtml . "<div style='clear:both'></div></fieldset>" . "<fieldset class='cat-page-tilte'><legend>" . __('Downloads', 'wpdmpro') . "</legend>";
            $efs = '</fieldset>';
        }
    }

    if (!isset($paging) || $paging == 1)
        $pgn = "<div style='clear:both'></div>" . $pag->show() . "<div style='clear:both'></div>";
    else
        $pgn = "";
    global $post;

    $sap = get_option('permalink_structure') ? '?' : '&';
    $burl = $burl . $sap;
    if (isset($_GET['p']) && $_GET['p'] != '') $burl .= 'p=' . $_GET['p'] . '&';
    if (isset($_GET['src']) && $_GET['src'] != '') $burl .= 'src=' . $_GET['src'] . '&';
    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'create_date';
    $order = ucfirst($order);
    $order_field = " " . ucwords(str_replace("_", " ", $order_field));
    $ttitle = __('Title', 'wpdmpro');
    $tdls = __('Downloads', 'wpdmpro');
    $tcdate = __('Publish Date', 'wpdmpro');
    $tudate = __('Update Date', 'wpdmpro');
    $tasc = __('Asc', 'wpdmpro');
    $tdsc = __('Desc', 'wpdmpro');
    $tsrc = __('Search', 'wpdmpro');
    if ($toolbar || get_option('__wpdm_cat_tb') == 1)
        $toolbar = <<<TBR
                  <br/>
                  <div class="row">
                  <div class="col-md-6">
                    <span class="label label-success" style="font-size:14pt;font-weight:700">$cats</span>
                  </div>
                  <div class="col-md-6">
                 <ul class="nav nav-pills pull-right">
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Order By {$order_field} <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                         <li><a href="{$burl}orderby=title&order=asc">{$ttitle}</a></li>
                         <!-- li><a href="{$burl}orderby=download_count&order=desc">{$tdls}</a></li -->
                         <li><a href="{$burl}orderby=publish_date&order=desc">{$tcdate}</a></li>
                        </ul>
                     </li>
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">$order <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                         <li><a href="{$burl}orderby={$orderby}&order=asc">{$tasc}</a></li>
                         <li><a href="{$burl}orderby={$orderby}&order=desc">{$tdsc}</a></li>
                        </ul>
                     </li>

                 </ul>

                 </div>
                 </div><br/>
TBR;
    else
        $toolbar = '';

    wp_reset_query();
    return "<div class='w3eden'><div class='wpdm-category'>" . $cimg . $desc . $toolbar . $subcats . $html  . $pgn . "<div style='clear:both'></div></div></div>";
}

/**
 * @usage Generate thumbnail dynamically
 * @param $path
 * @param $size
 * @return mixed
 */

function wpdm_dynamic_thumb($path, $size)
{
    $path = str_replace(site_url('/'), ABSPATH, $path);

    if (!file_exists($path)) return;
    $name_p = explode(".", $path);
    $ext = "." . end($name_p);
    $thumbpath = str_replace($ext, "-" . implode("x", $size) . $ext, $path);
    if (file_exists($thumbpath)) {
        $thumbpath = str_replace(ABSPATH, site_url('/'), $thumbpath);
        return $thumbpath;
    }
    $image = wp_get_image_editor($path);
    if (!is_wp_error($image)) {
        $image->resize($size[0], $size[1], true);
        $image->save($thumbpath);
    }
    $thumbpath = str_replace(ABSPATH, site_url('/'), $thumbpath);
    return $thumbpath;
}


function wpdm_option_field($data) {
    switch($data['type']):
        case 'text':
            return "<input type='text' name='$data[name]' class='form-control' id='$data[id]' value='$data[value]' placeholder='{$data['placeholder']}'  />";
            //echo "<div class='note'>{$data['description']}</div>";
            break;
        case 'select':
        case 'dropdown':
            $html = "<select name='{$data['name']}'  id='{$data['id']}' style='width:100%;min-width:150px;' >";
            foreach($data['options'] as $value => $label){

                $html .= "<option value='{$value}' ".selected($data['selected'],$value,false).">$label</option>";
            }
            $html .= "</select>";
            return $html;
            break;
        case 'textarea':
            return "<textarea name='$data[name]' id='$data[id]' class='form-control' style='min-height: 100px'>$data[value]</textarea>";
            //echo "<div class='note'>{$data['description']}</div>";
            break;
        case 'checkbox':
            return "<input type='checkbox' name='$data[name]' id='$data[id]' value='$data[value]' ".checked($data['checked'], $data['value'], false)." />";
            //echo "<div class='note'>{$data['description']}</div>";
            break;
        case 'callback':
            return call_user_func($data['dom_callback'], $data['dom_callback_params']);
            //echo "<div class='note'>{$data['description']}</div>";
            break;
        case 'heading':
            return "<h3>".$data['label']."</h3>";
            break;
    endswitch;
}

function wpdm_option_page($options){
    $html = "<table class='table table-striped table-v' style='margin: 0'>";
    foreach($options as $id => $option){
        $html .= "<tr><td>{$option['label']}</td><td>".wpdm_option_field($option)."</td></tr>";
    }
    $html .="</table>";
    return $html;
}

/**
 * @usage Get All Custom Data of a File
 * @param $pid
 * @return array
 */
function wpdm_custom_data($pid)
{
    $cdata = get_post_custom($pid);
    $data = array();
    foreach ($cdata as $k => $v) {
        $k = str_replace("__wpdm_", "", $k);
        $data[$k] = maybe_unserialize($v[0]);
    }
    //$defaults = array('email_lock'=>0,'password_lock'=>0,'tweet_lock'=>0,'facebooklike_lock'=>0,'linkedin_lock'=>0,'gplusone_lock'=>0,'individual_file_download'=>0);
    //$data = array_merge($defaults, $data);
    return $data;
}

function wpdm_setup_package_data($vars)
{
    if (isset($vars['formatted'])) return $vars;

    global $wp_query, $post;

    if (!isset($vars['ID'])) return $vars;

    $vars['title'] = stripcslashes($vars['post_title']);
    $vars['description'] = stripcslashes($vars['post_content']);
    $vars['description'] = wpautop(stripslashes($vars['description']));
    $vars['description'] = do_shortcode(stripslashes($vars['description']));
    $vars['excerpt'] = stripcslashes(strip_tags($vars['post_excerpt']));
    $src = wp_get_attachment_image_src(get_post_thumbnail_id($vars['ID']), 'full', false, '');
    $vars['preview'] = $src['0'];
    $vars['create_date'] = get_the_date();
    $vars['update_date'] = date(get_option('date_format'), strtotime($post->post_modified));

    //print_r($vars); die();
    $data = wpdm_custom_data($vars['ID']);
    $vars = array_merge($vars, $data);


    //$vars['description'] = apply_filters('the_content',stripslashes($wpdm_package['description']));
    $vars['files'] = get_post_meta($vars['ID'], '__wpdm_files', true);
    $vars['file_count'] = count($vars['files']);
    $vars['link_label'] = isset($vars['link_label']) ? $vars['link_label'] : __('Download', 'wpdmpro');
    $vars['page_link'] = "<a href='" . get_permalink($vars['ID']) . "'>{$vars['title']}</a>";
    $vars['page_url'] = get_permalink($vars['ID']);

    $size = 0;
    if (is_array($vars['files'])) {
        foreach ($vars['files'] as $f) {
            if (file_exists($f))
                $size += @filesize($f);
            else
                $size += @filesize(UPLOAD_DIR . $f);
        }
    }
    if (count($vars['files']) > 1) $vars['file_ext'] = 'zip';
    $tmpdata = array();
    if(isset($vars['files'][0]))
    $tmpdata = explode(".", $vars['files'][0]);
    if (is_array($vars['files']) && count($vars['files']) == 1) $vars['file_ext'] = end($tmpdata);
    $vars['file_size'] = $size / 1024;
    if ($vars['file_size'] > 1024) $vars['file_size'] = number_format($vars['file_size'] / 1024, 2) . ' MB';
    else $vars['file_size'] = number_format($vars['file_size'], 2) . ' KB';

    //$vars['create_date'] = $vars['create_date']?@date(get_option('date_format'),$vars['create_date']):@date(get_option('date_format'),get_wpdm_meta($vars['ID'],'create_date'));
    //$vars['update_date'] = $vars['update_date']?@date(get_option('date_format'),$vars['update_date']):@date(get_option('date_format'),get_wpdm_meta($vars['ID'],'update_date'));

    $type = (get_post_type() != 'wpdmpro' || !array_key_exists(get_option('__wpdm_purl_base', 'download'), $wp_query->query_vars)) ? 'link' : 'page';

    if(isset($vars['icon']) && strpos($vars['icon'], "://") === false && !file_exists(WP_PLUGIN_DIR.'/'.$vars['icon']))
        $vars['icon'] = "download-manager/file-type-icons/blank.png";

    if (!isset($vars['icon']) || $vars['icon'] == '')
        $vars['icon'] = '<img class="wpdm_icon" src="' . plugins_url('download-manager/file-type-icons/') . (@count($vars['files']) <= 1 ? @end(@explode('.', @end($vars['files']))) : 'zip') . '.png" onError=\'this.src="' . plugins_url('download-manager/file-type-icons/_blank.png') . '";\' />';
    else if (!strpos($vars['icon'], '://'))
        $vars['icon'] = '<img class="wpdm_icon"   src="' . plugins_url($vars['icon']) . '" />';
    else if (!strpos($vars['icon'], ">"))
        $vars['icon'] = '<img class="wpdm_icon"   src="' . $vars['icon'] . '" />';

    if (isset($vars['preview']) && $vars['preview'] != '') {
        $vars['thumb'] = "<img class='wpdm-thumb' src='" . plugins_url() . '/download-manager/timthumb.php?w=' . get_option('_wpdm_thumb_w') . '&h=' . get_option('_wpdm_thumb_h') . '&zc=1&src=' . $vars['preview'] . "'/>";
    } else
        $vars['thumb'] = $vars['thumb_page'] = $vars['thumb_gallery'] = $vars['thumb_widget'] = "";

    $k = 1;
    $vars['additional_previews'] = isset($vars['more_previews']) ? $vars['more_previews'] : array();
    $img = "<img id='more_previews_{$k}' title='' class='more_previews' src='" . wpdm_dynamic_thumb($vars['preview'], array(575, 170)) . "'/>\n";
    $tmb = "<a href='#more_previews_{$k}' class='spt'><img title='' src='" . wpdm_dynamic_thumb($vars['preview'], array(100, 45)) . "'/></a>\n";
    if ($vars['additional_previews']) {
        foreach ($vars['additional_previews'] as $p) {
            ++$k;
            $img .= "<img style='display:none;position:absolute' id='more_previews_{$k}' class='more_previews' title='' src='" . plugins_url() . '/download-manager/timthumb.php?w=575&h=170&zc=1&src=wp-content/plugins/download-manager/preview/' . $p . "'/>\n";
            $tmb .= "<a href='#more_previews_{$k}' class='spt'><img id='more_previews_{$k}' title='' src='" . plugins_url() . '/download-manager/timthumb.php?w=100&h=45&zc=1&src=wp-content/plugins/download-manager/preview/' . $p . "'/></a>\n";
        }
    }
    $vars['slider-previews'] = "<div class='slider' style='height:180px;'>" . $img . "</div><div class='tmbs'>$tmb</div>";
    $vars['all-previews'] = "<div class='slider' style='height:180px;'>" . $img . "</div><div class='tmbs'>$tmb</div>";


    //WPMS fix
    global $blog_id;
    if (defined('MULTISITE')) {
        $vars['thumb'] = str_replace(home_url('/files'), ABSPATH . 'wp-content/blogs.dir/' . $blog_id . '/files', $vars['thumb']);
    }


    if (!isset($vars['download_link_called'])) {
        $tmpvar = DownloadLink($vars, 0, array('btnclass' => '[btnclass]'));
        $tmpvar1 = DownloadLink($vars, 1);
        $vars['download_link'] = $tmpvar;
        $vars['download_link_extended'] = $tmpvar1;
        $vars['download_link_called'] = 1;
    }
    $vars = apply_filters("wdm_before_fetch_template", $vars);
    if (!isset($vars['formatted'])) $vars['formatted'] = 0;
    ++$vars['formatted'];

    return $vars;
}


function FetchTemplate($template, $vars, $type = 'link')
{
    if ($vars['ID'] == '') return '';


    $default['link'] = file_get_contents(dirname(__FILE__) . '/templates/link-template-default.php');
    $default['popup'] = file_get_contents(dirname(__FILE__) . '/templates/page-template-default.php');
    $default['page'] = file_get_contents(dirname(__FILE__) . '/templates/page-template-default.php');

    $vars = wpdm_setup_package_data($vars);

    if ($template == '') {
        $template = $type == 'page' ? $vars['page_template'] : $vars['template'];
    }

    if ($template == '')
        $template = $default[$type];


    if (file_exists(TEMPLATEPATH . '/' . $template)) $template = file_get_contents(TEMPLATEPATH . '/' . $template);
    else if (file_exists(dirname(__FILE__) . '/templates/' . $template)) $template = file_get_contents(dirname(__FILE__) . '/templates/' . $template);
    else if (file_exists(dirname(__FILE__) . '/templates/' . $template . '.php')) $template = file_get_contents(dirname(__FILE__) . '/templates/' . $template . '.php');

    preg_match_all("/\[thumb_([0-9]+)x([0-9]+)\]/", $template, $matches);
    preg_match_all("/\[thumb_url_([0-9]+)x([0-9]+)\]/", $template, $umatches);
    preg_match_all("/\[thumb_gallery_([0-9]+)x([0-9]+)\]/", $template, $gmatches);
    preg_match_all("/\[excerpt_([0-9]+)\]/", $template, $xmatches);
    //preg_match_all("/\[download_link ([^\]]+)\]/", $template, $cmatches);

    $thumb = wp_get_attachment_image_src(get_post_thumbnail_id($vars['ID']), 'full');
    $vars['preview'] = $thumb['0'];

    if(isset($vars['quota']) && $vars['quota']==0) $vars['quota'] = __('Unlimited','wpdm');

    foreach ($matches[0] as $nd => $scode) {
        $keys[] = $scode;
        $values[] = $vars['preview'] != '' ? "<img src='" . wpdm_dynamic_thumb($vars['preview'], array($matches[1][$nd], $matches[2][$nd])) . "' alt='{$vars['title']}' />" : '';
    }

    foreach ($umatches[0] as $nd => $scode) {
        $keys[] = $scode;
        $values[] = $vars['preview'] != '' ? wpdm_dynamic_thumb($vars['preview'], array($umatches[1][$nd], $umatches[2][$nd])) : '';
    }

    foreach ($gmatches[0] as $nd => $scode) {
        $keys[] = $scode;
        $values[] = wpdm_get_additional_preview_images($vars, $gmatches[1][$nd], $gmatches[2][$nd]);
    }

    foreach ($xmatches[0] as $nd => $scode) {
        $keys[] = $scode;
        $ss = substr(strip_tags($vars['description']), 0, intval($xmatches[1][$nd]));
        $tmp = explode(" ", substr(strip_tags($vars['description']), intval($xmatches[1][$nd])));
        $bw = array_shift($tmp);
        $ss .= $bw;
        $values[] = $ss . '...';
    }

    if ($type == 'page' && (strpos($template, '[similar_downloads]') || strpos($vars['description'], '[similar_downloads]')))
        $vars['similar_downloads'] = wpdm_similar_packages($vars, 5);


    foreach ($vars as $key => $value) {
        $keys[] = "[$key]";
        $values[] = $value;
    }

    if ($vars['download_link'] == 'blocked' && $type == 'link') return "";
    if ($vars['download_link'] == 'blocked' && $type == 'page') return get_option('wpdm_permission_msg');
    //if($vars['password']=='') $template = $template."<script>jQuery('body').on('click','.wpdm-link-tpl', function(){ var durl = jQuery(this).data('durl'); location.href=durl; });</script>";
    $data = @str_replace($keys, $values, @stripcslashes($template));
    $data = str_replace(array("\r","\n"),"", $data);
    return $data;
}

/**
 * @usage WPDM Add-on Installer
 */
function wpdm_install_addon(){
    if(isset($_REQUEST['addon']) && current_user_can('manage_options')){
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        $upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
        if(strpos($_REQUEST['addon'], '.zip'))
            $downloadlink = $_REQUEST['addon'];
        else
            $downloadlink = 'http://www.wpdownloadmanager.com/?wpdmdl='.$_REQUEST['addon'];
        $upgrader->install($downloadlink);
        die();
    } else {
        die("Only site admin is authorized to install add-on");
    }
}


function wpdm_activate_shop(){
    if( current_user_can('manage_options')){
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        $upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
        $downloadlink = 'http://www.wpdownloadmanager.com/?wpdmdl=15671';
        ob_start();
        echo "<div id='acto'>";
        if(file_exists(dirname(dirname(__FILE__)).'/wpdm-premium-packages/'))
            $upgrader->upgrade($downloadlink);
        else
            $upgrader->install($downloadlink);
        echo '</div><style>#acto .wrap { display: none; }</style>';
        @ob_clean();
        activate_plugin( 'wpdm-premium-packages/wpdm-premium-packages.php' );
        echo "Congratulation! Your Digital Store is Activated. <a href='' class='btn btn-warning'>Refresh The Page!</a>";
        die();
    } else {
        die("Only site admin is authorized to install add-on");
    }
}

/**
 * Process Download Request
 *
 */

function wpdm_downloadnow()
{

    global $wpdb, $current_user, $wp_query;
    get_currentuserinfo();
    if (!isset($wp_query->query_vars['wpdmdl']) && !isset($_GET['wpdmdl'])) return;
    $id = isset($_GET['wpdmdl']) ? (int)$_GET['wpdmdl'] : (int)$wp_query->query_vars['wpdmdl'];
    if ($id <= 0) return;
    $key = array_key_exists('_wpdmkey', $_GET) ? $_GET['_wpdmkey'] : '';
    $key = $key == '' && array_key_exists('_wpdmkey', $wp_query->query_vars) ? $wp_query->query_vars['_wpdmkey'] : $key;
    $key = preg_replace("/[^_a-z|A-Z|0-9]/i", "", $key);
    $package = get_post($id, ARRAY_A);
    $package['ID'] = $package['ID'];
    $package = array_merge($package, wpdm_custom_data($package['ID']));
    if (isset($package['files']))
        $package['files'] = maybe_unserialize($package['files']);
    else
        $package['files'] = array();
    //$package = wpdm_setup_package_data($package);

    if (is_array($package)) {
        $role = @array_shift(@array_keys($current_user->caps));
        $cpackage = apply_filters('before_download', $package);
        $lock = '';
        $package = $cpackage ? $cpackage : $package;
        if (isset($package['password']) && $package['password'] != '') $lock = 'locked';

        if ($lock !== 'locked')
            $lock = apply_filters('wpdm_check_lock', $id, $lock);

        if (isset($_GET['masterkey']) && esc_attr($_GET['masterkey']) == $package['masterkey']) {
            $lock = 0;
        }


        $limit = $key ? (int)trim(get_post_meta($package['ID'], $key, true)) : 0;


        if ($limit <= 0 && $key != '') delete_post_meta($package['ID'], $key);
        else if ($key != '')
            update_post_meta($package['ID'], $key, $limit - 1);
        $matched = array_intersect($current_user->roles, @maybe_unserialize($package['access']));

        if (($id != '' && is_user_logged_in() && count($matched) < 1 && !@in_array('guest', $package['access'])) || (!is_user_logged_in() && !@in_array('guest', $package['access']) && $id != '')) {
            wpdm_download_data("permission-denied.txt", __("You don't have permission to download this file", 'wpdmpro'));
            die();
        } else {

            if ($lock === 'locked' && $limit <= 0) {
                if ($key != '')
                    wpdm_download_data("link-expired.txt", __("Download link is expired. Please get new download link.", 'wpdmpro'));
                else
                    wpdm_download_data("invalid-link.txt", __("Download link is expired or not valid. Please get new download link.", 'wpdmpro'));
                die();
            } else
                if ($package['ID'] > 0)
                    include("process.php");

        }
    } else
        wpdm_notice(__("Invalid download link.", 'wpdmpro'));
}



function wpdm_is_ajax()
{
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        return true;
    return false;
}


function __msg($key)
{
    include("messages.php");
    return $msgs[$key] ? $msgs[$key] : $key;
}

/**
 * function to list all packages
 *
 */
function wpdm_all_packages($params = array())
{
    global $wpdb, $current_user, $wp_query;
    $items = isset($params['items_per_page']) && $params['items_per_page'] > 0 ? $params['items_per_page'] : 20;
    $cp = isset($wp_query->query_vars['paged']) && $wp_query->query_vars['paged'] > 0 ? $wp_query->query_vars['paged'] : 1;
    $terms = isset($params['categories']) ? explode(",", $params['categories']) : array();
    if (isset($_GET['wpdmc'])) $terms = array(esc_attr($_GET['wpdmc']));
    $offset = ($cp - 1) * $items;
    $total_files = wp_count_posts('wpdmpro')->publish;
    if (count($terms) > 0) {
        $tax_query = array(array(
            'taxonomy' => 'wpdmcategory',
            'field' => 'slug',
            'terms' => $terms,
            'operator' => 'IN'
        ));
    }

    //foreach($files as $file){
    //$users = explode(',',get_option("wpdm_package_selected_members_only_".$file['ID']));
    //$roles = unserialize($file['access']);
    //$myrole = $current_user->roles[0];
    //if(@in_array($current_user->user_login,$users)||@in_array($myrole, $roles))
    //$myfiles[] = $file;
    //}
    ob_start();
    include("wpdm-all-downloads.php");
    $data = ob_get_contents();
    ob_clean();
    return $data;
}

/**
 * Check if loggen in user is authorise admin
 *
 */
function wpdm_is_custom_admin()
{
    global $current_user, $add_new_page;
    $admins = explode(",", get_option('__wpdm_custom_admin', ''));
    return in_array($current_user->user_login, $admins) ? true : false;
}


function wpdm_add_help_tab()
{
    global $add_new_page;
    $screen = get_current_screen();
    $tmpvar = explode('/', $_GET['page']);
    $page = array_shift($tmpvar);
    if ($page != 'file-manager') return;
    // Add my_help_tab if current screen is My Admin Page
    $screen->add_help_tab(array(
        'ID' => 'my_help_tab_0',
        'title' => __('Legends'),
        'content' => '<p>' . "<img align=left src='" . plugins_url('/download-manager/images/add-image.gif') . "' hspace=10 />" . __(" Click on the icon to launch media manager to select or upload preview images") . "<br/><img align=left src='" . plugins_url('/download-manager/images/reload.png') . "' hspace=10 />" . __(" Reload link or page templates.") . '</p>',
    ));

    $screen->add_help_tab(array(
        'ID' => 'my_help_tab_1',
        'title' => __('File Settings'),
        'content' => '<p>' . __("<b>Link Label:</b> Label to show with download link, like: download now, get it now<br/>
                                    <b>Password:</b> You can set single or multiple password for a package. In case of multiple password, each password have to be inside `[]`, like: [1234][456][789sf] and user will be able to download package using any one of them<br/>
                                    <b>PW Usage Limit:</b> When you are using multiple password, then you may want set a limit, how many time user will be able to use a password, you can set the numeric value here, suppose `n`. So each password will expire after it used for `n` times.<br/>
                                    <b>Stock Limit:</b> Should be a numeric value, suppose `9`. After package dowloaded for `9` times, the no one will able to download it anymore, will show 'out of stock' message<br/>
                                    <b>Download Limit/user:</b> Set a numeric value here if you want to block user after a certain times of download for this package.<br/>
                                    <b>Access</b>: Check the user roles, you want to enable to download this package, `All Visitors` will enable every one to download this package<br/>
                                    <b>Link Template:</b> Shortcode will be rendered based on select link template.<br/>
                                    <b>Page Template:</b> File details page will be rendered based on selected page template<br/>
                                ",'wpdmpro') . '</p>',
    ));

}

/**
 * @param $id
 * @return bool|mixed|null|void|WP_Post
 */
function wpdm_get_package($id)
{
    global $wpdb, $wpdm_package;
    $id = (int)$id;
    if ($id <= 0) return false;
    if ($id == $wpdm_package['ID']) return $wpdm_package;
    $data = get_post($id, ARRAY_A);
    $data = apply_filters('wpdm_data_init', $data);
    $data = wpdm_setup_package_data($data);
    return $data;
}


/**
 * @usage Get download manager package data
 * @param $ID
 * @param $meta
 * @return mixed
 */
function get_package_data($ID, $key){
    $data = get_post_meta($ID, "__wpdm_{$key}", true);
    return $data;
}


function wpdm_check_invpass()
{
    if (isset($_POST['actioninddlpvr']) && $_POST['actioninddlpvr'] != '') {
        $fileid = intval($_POST['wpdmfileid']);
        $data = get_post_meta($_POST['wpdmfileid'], '__wpdm_fileinfo', true);
        $data = $data ? $data : array();
        $package = get_post($fileid);
        $packagemeta = wpdm_custom_data($fileid);
        $password = $data[$_POST['wpdmfile']]['password'] != "" ? $data[$_POST['wpdmfile']]['password'] : $packagemeta['password'];
        if ($password == $_POST['actioninddlpvr'] || strpos($password, "[" . $_POST['actioninddlpvr'] . "]") !== FALSE) {
            $id = "__wpu_" . uniqid();
            update_post_meta($fileid, $id, 3);
            die("|ok|$id|");
        } else
            die('|error|');
    }
}



// Function that output's the contents of the dashboard widget
function wpdm_dashboard_widget_function()
{
    global $wpdb;
    echo "<img height='30px' src='" . plugins_url('/download-manager/images/wpdm-logo.png') . "' /><br/>";
    ?>
    <link href="<?php echo plugins_url('/download-manager/bootstrap/css/bootstrap.css'); ?>" rel='stylesheet'
          type='text/css'>
    <script language="JavaScript"
            src="<?php echo plugins_url('/download-manager/bootstrap/js/bootstrap.min.js'); ?>"></script>
    <link href='http://fonts.googleapis.com/css?family=Open+Sans|Open+Sans+Condensed:700,300,300italic' rel='stylesheet'
          type='text/css'>
    <style type="text/css">
        .nav-tabs {
            margin-bottom: 0px !important;
        }

        .tab-content {
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-top: 0px;
            -webkit-border-bottom-right-radius: 5px;
            -webkit-border-bottom-left-radius: 5px;
            -moz-border-radius-bottomright: 5px;
            -moz-border-radius-bottomleft: 5px;
            border-bottom-right-radius: 5px;
            border-bottom-left-radius: 5px;
        }

        .tab-content * {
            font-family: 'Open Sans';
            font-size: 10pt;
            font-weight: 400;
        }

        .nav-tabs a {
            font-family: 'Open Sans';
            font-size: 9pt;
            font-weight: 200;
        }

        .tab-content * {
            font-size: 10pt;
            font-weight: 400;
        }

    </style>


    <div class="w3eden">
        <ul class="nav nav-tabs" id="myTab">
            <li class="active"><a href="#settings">News Updates</a></li>
        </ul>

        <div class="tab-content">

            <div class="tab-pane active" id="settings">
                <iframe id="excelz" src="" style="height: 350px;width:100%;border:0px;overflow: hidden"></iframe>
            </div>
        </div>

        <script>
            jQuery(function () {

                jQuery('#excelz').attr('src','http://cdn.wpdownloadmanager.com/notice.php?wpdmvarsion=<?php echo WPDM_Version; ?>');

            })
        </script>


    </div>

<?php
}

// Function that beeng used in the action hook
function wpdm_add_dashboard_widgets()
{
    wp_add_dashboard_widget('wpdm_dashboard_widget', 'WordPress Download Manager', 'wpdm_dashboard_widget_function');
    global $wp_meta_boxes;
    $side_dashboard = $wp_meta_boxes['dashboard']['side']['core'];
    $wpdm_widget = array('wpdm_dashboard_widget' => $wp_meta_boxes['dashboard']['normal']['core']['wpdm_dashboard_widget']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['wpdm_dashboard_widget']);
    $sorted_dashboard = array_merge($wpdm_widget, $side_dashboard);
    $wp_meta_boxes['dashboard']['side']['core'] = $sorted_dashboard;
}


// Register the new dashboard widget into the 'wp_dashboard_setup' action
add_action('wp_dashboard_setup', 'wpdm_add_dashboard_widgets');




function wpdm_enqueue_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-form');

    wp_enqueue_style('icons', plugins_url() . '/download-manager/css/front.css');

    if (get_option('__wpdm_twitter_bootstrap') != 'dall' && get_option('__wpdm_twitter_bootstrap') != 'dcss')
        wp_enqueue_style('wpdm-bootstrap', plugins_url('/download-manager/bootstrap/css/bootstrap.css'));

    if (get_option('__wpdm_twitter_bootstrap') != 'dall' && get_option('__wpdm_twitter_bootstrap') != 'djs')
        wp_enqueue_script('wpdm-bootstrap', plugins_url('/download-manager/bootstrap/js/bootstrap.min.js'), array('jquery'));

    wp_enqueue_script('frontjs', plugins_url('/download-manager/js/front.js'), array('jquery'));
    wp_enqueue_script('jquery-choosen', plugins_url('/download-manager/js/chosen.jquery.min.js'), array('jquery'));
    wp_enqueue_style('font-awesome', WPDM_BASE_URL.'font-awesome/css/font-awesome.min.css');


}

function wpdm_admin_enqueue_scripts()
{
    if(get_post_type()=='wpdmpro' || in_array(wpdm_query_var('page'),array('settings','emails','wpdm-stats','templates','importable-files','wpdm-addons','orders'))){
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-form');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-sortable');
        //wp_enqueue_script('jquery-ui-timepicker', WPDM_BASE_URL.'/js/jquery-ui-timepicker-addon.js',array('jquery','jquery-ui-core','jquery-ui-datepicker','jquery-ui-slider') );
        wp_enqueue_style('icons', plugins_url() . '/download-manager/css/icons.css');
        wp_enqueue_script('wp-pointer');
        wp_enqueue_style('wp-pointer');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('media-upload');
        wp_enqueue_media();

        wp_enqueue_script('jquery-choosen', plugins_url('/download-manager/js/chosen.jquery.min.js'), array('jquery'));
        wp_enqueue_style('choosen-css', plugins_url('/download-manager/css/chosen.css'));
        wp_enqueue_style('jqui-css', plugins_url('/download-manager/jqui/theme/jquery-ui.css'));
        //if(isset($_GET['page']) && $_GET['page']== 'settings' && get_post_type()=='wpdmpro')

        wp_enqueue_style('wpdm-bootstrap', plugins_url('/download-manager/bootstrap/css/bootstrap.css'));
        wp_enqueue_style('wpdm-bootstrap-theme', plugins_url('/download-manager/bootstrap/css/bootstrap-theme.min.css'));
        wp_enqueue_script('wpdm-bootstrap', plugins_url('/download-manager/bootstrap/js/bootstrap.min.js'), array('jquery'));
        wp_enqueue_style('font-awesome', plugins_url('/download-manager/font-awesome/css/font-awesome.min.css'));
    }

}




function quote_all_array($values)
{
    foreach ($values as $key => $value)
        if (is_array($value))
            $values[$key] = quote_all_array($value);
        else
            $values[$key] = quote_all($value);
    return $values;
}

function quote_all($value)
{
    if (is_null($value))
        return "NULL";

    $value = mysql_real_escape_string($value);
    return $value;
}

function wpdm_dashboard()
{
    require_once(dirname(__FILE__) . '/wpdm-dashboard.php');
}


/** download manager new **/
function wpdm_meta_boxes()
{
    $settings = maybe_unserialize(get_option('_wpdmpp_settings'));
    $meta_boxes = array(
        'wpdm-settings' => array('title' => __('File Settings', "wpdmpro"), 'callback' => 'wpdm_meta_box_package_settings', 'position' => 'normal', 'priority' => 'low'),
        'wpdm-upload-file' => array('title' => __('Attach File', "wpdmpro"), 'callback' => 'wpdm_meta_box_upload_file', 'position' => 'side', 'priority' => 'core'),
        'wpdm-items'=>array('title'=>__('Other Items',"wpdm"),'callback'=>'wpdm_meta_box_other_items','position'=>'side','priority'=>'low'),
    );


    $meta_boxes = apply_filters("wpdm_meta_box", $meta_boxes);
    foreach ($meta_boxes as $id => $meta_box) {
        extract($meta_box);
        add_meta_box($id, $title, $callback, 'wpdmpro', $position, $priority);
    }
}

function wpdm_admin_notice() {
    global $wp_query;
    if(get_post_type()=='wpdmpro' && isset($wp_query->query_vars['posts_per_page'])){
    ?>
    <div class="updated" style="border: 2px solid #1E8CBE !important;border-radius: 3px;">
        <p>
            <strong>Download Manager Pro!</strong><br/>
            <i><a href="http://www.wpdownloadmanager.com/?affid=admin&amp;domain=localhost" target="_blank">Get Download Manager Pro Version Now! </a></i>
            <a style="float:right;margin-top: -23px;margin-right: -6px;border: 0 none;border-radius: 2px;box-shadow: none;" class="button button-primary button-hero" href="http://www.wpdownloadmanager.com/?affid=admin&amp;domain=localhost#features" target="_blank">Checkout The Features Here &rarr;</a>
        </p>
    </div>
<?php
    }
}
add_action( 'admin_notices', 'wpdm_admin_notice' );



function wpdm_meta_box_package_settings($post)
{
    $file = get_post_meta($post->ID, "_filedata", true);
    include("tpls/metaboxes/package-settings.php");
}

function wpdm_meta_box_upload_file($post)
{
    $file = get_post_meta($post->ID, "_filedata", true);
    include("tpls/metaboxes/attach-file.php");
}

function wpdm_meta_box_other_items($post)
{
    $file = get_post_meta($post->ID, "_filedata", true);
    include("tpls/metaboxes/items.php");
}

function wpdm_tag_query($query)
{

    if (is_tag()) {

        $post_type = get_query_var('post_type');
        if (!is_array($post_type))
            $post_type = array('post', 'wpdmpro', 'nav_menu_item');
        else
            $post_type = array_merge($post_type, array('post', 'wpdmpro', 'nav_menu_item'));
        $query->set('post_type', $post_type);
        return $query;
    }
}


function wpdm_array_splice_assoc(&$input, $offset, $length, $replacement) {
    $replacement = (array) $replacement;
    $key_indices = array_flip(array_keys($input));
    if (isset($input[$offset]) && is_string($offset)) {
        $offset = $key_indices[$offset];
    }
    if (isset($input[$length]) && is_string($length)) {
        $length = $key_indices[$length] - $offset;
    }

    $input = array_slice($input, 0, $offset, TRUE)
        + $replacement
        + array_slice($input, $offset + $length, NULL, TRUE);
}

function wpdm_columns_th($defaults) {
    if(get_post_type()!='wpdmpro') return $defaults;
    $img['wpdm-image'] = "<i class='fa fa-image'></i>";
    wpdm_array_splice_assoc( $defaults, 1, 0, $img );
    $otf['download_count'] = "<i class='fa fa-download'></i>";
    $otf['wpdm-shortcode'] = 'Short-code';
    wpdm_array_splice_assoc( $defaults, 3, 0, $otf );
    return $defaults;
}

function wpdm_columns_td($column_name, $post_ID) {
    if(get_post_type()!='wpdmpro') return;
    if ($column_name == 'download_count') {

        echo get_post_meta($post_ID, '__wpdm_download_count', true);

    }
    if ($column_name == 'wpdm-shortcode') {

        echo "<input style='font-family: Courier;font-size: 9pt;width:210px;padding: 10px 5px;text-align: center' readonly=readonly class='wpdm-scode' onclick='this.select();' value=\"[wpdm_package id='$post_ID']\" />";

    }
    if ($column_name == 'wpdm-image') {
//        if(has_post_thumbnail($post_ID))
//            echo get_the_post_thumbnail( $post_ID, 'thumbnail', array('class'=>'img60px') );
//        else {
            $icon = get_post_meta($post_ID,'__wpdm_icon', true);
            if($icon!=''){
                if(file_exists(WP_PLUGIN_DIR.'/'.$icon))
                    $icon = plugins_url('/').$icon;
                 //   $icon = "/download-manager/file-type-icons/blank.png";

                echo "<img src='$icon' class='img60px' alt='Icon' />";
            } else {
                echo "<img src='".WPDM_BASE_URL."/file-type-icons/blank.png' title='Default Icon' class='img60px' alt='Icon' />";
            }
        //}
    }
}

function wpdm_dlc_sortable( $columns ) {

    if(get_post_type()!='wpdmpro') return $columns;

    $columns['download_count'] = 'download_count';

    return $columns;
}

function wpdm_dlc_orderby( $vars ) {

    if ( isset( $vars['orderby'] ) && 'download_count' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => '__wpdm_download_count',
            'orderby' => 'meta_value_num'
        ) );
    }

    return $vars;
}

function wpdm_sfb_access(){

    global $wp_roles;

    $roleids = array_keys($wp_roles->roles);
    $roles = get_option('_wpdm_file_browser_access',array('administrator'));
    $naroles = array_diff($roleids, $roles);

    foreach($roles as $role) {
        $role = get_role($role);
        if(is_object($role))
        $role->add_cap('access_server_browser');
    }

    foreach($naroles as $role) {
        $role = get_role($role);
        $role->remove_cap('access_server_browser');
    }

}

/**
 * @usage Count files in a package
 * @param $id
 * @return int
 */
function wpdm_package_filecount($id){
    $files = get_post_meta($id, '__wpdm_files', true);
    $files = maybe_unserialize($files);
    return count($files);

}

/**
 * @usage Calculate file size
 * @param $id
 * @return float|int|mixed|string
 */
function wpdm_package_size($id){
    $size = get_post_meta($id, '__wpdm_package_size', true);

    if($size!="") return $size;

    $files = maybe_unserialize(get_post_meta($id, '__wpdm_files', true));

    $size = 0;
    if (is_array($files)) {
        foreach ($files as $f) {
            $f = trim($f);
            if (file_exists($f))
                $size += @filesize($f);
            else
                $size += @filesize(UPLOAD_DIR . $f);
        }
    }

    update_post_meta($id, '__wpdm_package_size_b', $size);
    $size = $size / 1024;
    if ($size > 1024) $size = number_format($size / 1024, 2) . ' MB';
    else $size = number_format($size, 2) . ' KB';
    update_post_meta($id, '__wpdm_package_size', $size);
    return $size;
}

/**
 * @usage Returns icons for package file types
 * @param $id
 * @param bool $img
 * @return array|string
 */
function wpdm_package_filetypes($id, $img = true){

    $files = maybe_unserialize(get_post_meta($id, '__wpdm_files', true));
    $ext = array();
    if (is_array($files)) {
        foreach ($files as $f) {
            $f = trim($f);
            $f = explode(".", $f);
            $ext[] = end($f);
        }
    }

    $ext = array_unique($ext);
    $exico = '';
    foreach($ext as $exi){
        if(file_exists(dirname(__FILE__).'/file-type-icons/16x16/'.$exi.'.png'))
        $exico .= "<img alt='{$exi}' title='{$exi}' class='ttip' src='".plugins_url('download-manager/file-type-icons/16x16/'.$exi.'.png')."' /> ";
    }
    if($img) return $exico;
    return $ext;
}


function wpdm_do_login()
{
    global $wp_query, $post, $wpdb;
    if (!isset($_POST['wpdm_login'])) return;
    unset($_SESSION['login_error']);
    $creds = array();
    $creds['user_login'] = $_POST['wpdm_login']['log'];
    $creds['user_password'] = $_POST['wpdm_login']['pwd'];
    $creds['remember'] = isset($_POST['rememberme']) ? $_POST['rememberme'] : false;
    $user = wp_signon($creds, false);


    if (is_wp_error($user)) {
        $_SESSION['login_error'] = $user->get_error_message();

        if(wpdm_is_ajax()) die('failed');

        header("location: " . $_SERVER['HTTP_REFERER']);
        die();
    } else {
        do_action('wp_login', $creds['user_login'], $user);

        if(wpdm_is_ajax()) die('success');

        header("location: " . $_POST['redirect_to']);
        die();
    }
}

function wpdm_do_register()
{
    global $wp_query, $wpdb;
    if (!isset($_POST['wpdm_reg']) || !get_option('users_can_register')) return;

    extract($_POST['wpdm_reg']);
    $_SESSION['tmp_reg_info'] = $_POST['wpdm_reg'];
    $user_id = username_exists($user_login);
    $loginurl = $_POST['permalink'];
    if ($user_login == '') {
        $_SESSION['reg_error'] = __('Username is Empty!','wpdmpro');

        if(wpdm_is_ajax()) die('Error: '.$_SESSION['reg_error']);


        header("location: " . $_POST['permalink']);
        die();
    }
    if (!isset($user_email) || !is_email($user_email)) {
        $_SESSION['reg_error'] = __('Invalid Email Address!','wpdmpro');

        if(wpdm_is_ajax()) die('Error: '.$_SESSION['reg_error']);

        header("location: " . $_POST['permalink']);
        die();
    }

    if (!$user_id) {
        $user_id = email_exists($user_email);
        if (!$user_id) {
            $auto_login = isset($user_pass) && $user_pass!=''?1:0;
            $user_pass = isset($user_pass) && $user_pass!=''?$user_pass:wp_generate_password(12, false);

            $user_id = wp_create_user($user_login, $user_pass, $user_email);
            $display_name = isset($display_name)?$display_name:$user_id;
            $headers = "From: " . get_option('sitename') . " <" . get_option('admin_email') . ">\r\nContent-type: text/html\r\n";
            $message = file_get_contents(dirname(__FILE__) . '/templates/wpdm-new-user.html');
            $loginurl = $_POST['permalink'];
            $message = str_replace(array("[#support_email#]", "[#homeurl#]", "[#sitename#]", "[#loginurl#]", "[#name#]", "[#username#]", "[#password#]", "[#date#]"), array(get_option('admin_email'), site_url('/'), get_option('blogname'), $loginurl, $display_name, $user_login, $user_pass, date("M d, Y")), $message);

            if ($user_id) {
                wp_mail($user_email, "Welcome to " . get_option('sitename'), $message, $headers);

            }
            unset($_SESSION['guest_order']);
            unset($_SESSION['login_error']);
            unset($_SESSION['tmp_reg_info']);
            //if(!isset($_SESSION['reg_warning']))
            $creds['user_login'] = $user_login;
            $creds['user_password'] = $user_pass;
            $creds['remember'] = true;
            $_SESSION['sccs_msg'] = "Your account has been created successfully and login info sent to your mail address.";
            if($auto_login==1) {
                $_SESSION['sccs_msg'] = "Your account has been created successfully and login now.";
                wp_signon($creds);
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);

            }

            if(wpdm_is_ajax()) die('success');

            header("location: " . $loginurl);
            die();
        } else {
            $_SESSION['reg_error'] = __('Email already exists.');
            $plink = $_POST['permalink'] ? $_POST['permalink'] : $_SERVER['HTTP_REFERER'];

            if(wpdm_is_ajax()) die('Error: '.$_SESSION['reg_error']);

            header("location: " . $loginurl);
            die();
        }
    } else {
        $_SESSION['reg_error'] = __('User already exists.');
        $plink = $_POST['permalink'] ? $_POST['permalink'] : $_SERVER['HTTP_REFERER'];

        if(wpdm_is_ajax()) die('Error: '.$_SESSION['reg_error']);

        header("location: " . $loginurl);
        die();
    }
    die();
}

function wpdm_update_profile()
{
    global $wp_query, $wpdb, $current_user;
    get_currentuserinfo();
    if (isset($_REQUEST['task']) && $_REQUEST['task'] == 'editprofile' && isset($_POST['profile'])) {
        extract($_POST);
        $error = 0;

        if ($password != $cpassword) {
            $_SESSION['member_error'][] = 'Password not matched';
            $error = 1;
        }
        if (!$error) {
            $profile['ID'] = $current_user->ID;
            if ($password != '')
                $profile['user_pass'] = $password;
            wp_update_user($profile);
            get_currentuserinfo();
            update_user_meta($current_user->ID, 'payment_account', $payment_account);
            $_SESSION['member_success'] = 'Profile data updated successfully.';
        }
        header("location: " . $_SERVER['HTTP_REFERER']);
        die();
    }
}

