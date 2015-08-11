<?php

//error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
 

global $stabs, $package, $wpdm_package;
/**
 * @usage Initiate Settings Tabs
 */
function wpdm_initiate_settings()
{
    global $stabs;
    $tabs = array();
    $tabs['basic'] = array('id' => 'basic', 'link' => 'edit.php?post_type=wpdmpro&page=settings', 'title' => 'Basic', 'callback' => 'basic_settings');

    if (function_exists('bp_is_active')) {
        $tabs['buddypress'] = array('id' => 'buddypress', 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=buddypress', 'title' => 'BuddyPress', 'callback' => 'buddypress_addon_settings');
    }

    if(defined('WPDM_CLOUD_STORAGE')){
        $tabs['cloud-storage'] = array('id' => 'cloud-storage', 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=cloud-storage', 'title' => 'Cloud Storage', 'callback' => 'wpdm_cloud_storage_settings');
    }

    if(!$stabs) $stabs = array();

    $stabs = $tabs + $stabs;

    $stabs = apply_filters("add_wpdm_settings_tab", $stabs);


}

/**
 * @param $tablink
 * @param $newtab
 * @param $func
 * @deprecated Deprecated from v2.7.6, use filter hook 'add_wpdm_settings_tab'
 * @usage Deprecated: From v2.7.6, use filter hook 'add_wpdm_settings_tab'
 */
function add_wdm_settings_tab($tablink, $newtab, $func)
{
    global $stabs;
    $stabs["{$tablink}"] = array('id' => $tablink, 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=' . $tablink, 'title' => $newtab, 'callback' => $func);
}

function wpdm_create_settings_tab($tablink, $tabtitle, $callback)
{
    return array('id' => $tablink, 'link' => 'edit.php?post_type=wpdmpro&page=settings&tab=' . $tablink, 'title' => $tabtitle, 'callback' => $callback);
}

function render_settings_tabs($sel = '')
{
    global $stabs;
    foreach ($stabs as $tab) {
        if ($sel == $tab['id'])
            echo "<li class='active'><a id='{$tab['id']}' href='{$tab['link']}'>{$tab['title']}</a></li>";
        else
            echo "<li class=''><a id='{$tab['id']}' href='{$tab['link']}'>{$tab['title']}</a></li>";
        if (isset($tab['func']) && function_exists($tab['func'])) {
            add_action('wp_ajax_' . $tab['func'], $tab['func']);
        }
    }
}


function buddypress_addon_settings(){
    if(isset($_POST['section']) && $_POST['section']=='buddypress' && isset($_POST['task']) && $_POST['task']=='wdm_save_settings' && current_user_can('manage_options')){
        foreach($_POST as $k => $v){
            if(strpos($k, '_wpdm_')){
                update_option($k, $v);
            }
        }
        die('Settings Saved Successfully!');
    }
    include(WPDM_BASE_DIR."settings/buddypress.php");
}

function wpdm_cloud_storage_settings(){
    if(isset($_POST['section']) && $_POST['section']=='cloud-storage' && isset($_POST['task']) && $_POST['task']=='wdm_save_settings' && current_user_can('manage_options')){
        foreach($_POST as $k => $v){
            if(strpos($k, '_wpdm_')){
                update_option($k, $v);
            }
        }
        die('Settings Saved Successfully!');
    }
    include(WPDM_BASE_DIR."settings/cloud-storage.php");
}



function wpdm_is_download_limit_exceed($id)
{
    return false;
    global $wpdb, $current_user;
    get_currentuserinfo();
    $cond[] = "pid='$id'";
    if (is_user_logged_in())
        $cond[] = "uid='{$current_user->ID}'";
    else
        $cond[] = "ip='{$_SERVER['REMOTE_ADDR']}'";
    $td = $wpdb->get_var("select count(*) from {$wpdb->prefix}ahm_download_stats where " . implode(" and ", $cond));
    $mx = get_post_meta($id, '__wpdm_download_limit_per_user');
    if ($mx > 0 && $td >= $mx) return true;
    return false;
}


function DownloadPageTitle($title)
{
    global $wpdb, $wp_query;
    if ($wp_query->query_vars['wpdm_page'] != '') {
        $id = (int)$wp_query->query_vars['wpdm_page'];
        $data = $wpdb->get_row("select title from {$wpdb->prefix}ahm_files where id='$id'", ARRAY_A);
        return $data['title'];
    }
    return $title;

}

function DownloadPageContent($embedid = 0)
{
    global $wpdb, $wp_query, $wpdm_package, $post;
    if (is_singular('wpdmpro') || $embedid > 0) {
        if ($embedid > 0)

            $linktemplates = maybe_unserialize(get_option("_fm_link_templates"));
        $pagetemplates = maybe_unserialize(get_option("_fm_page_templates"));
        if (!isset($wpdm_package['ID']))
            $wpdm_package = get_post(get_the_ID(), ARRAY_A);
        $wpdm_package['id'] = get_the_ID();

        $wpdm_package = wpdm_setup_package_data($wpdm_package);

        $wpdm_package['template'] = 'link-template-default.php'; //isset($wpdm_package['template']) ? $wpdm_package['template'] : 'link-template-default.php';
        $wpdm_package['page_template'] = 'page-template-default.php'; //isset($wpdm_package['page_template']) ? $wpdm_package['page_template'] : 'page-template-default.php';

        if (file_exists(dirname(__FILE__) . '/templates/' . $wpdm_package['template'])) $wpdm_package['template'] = @file_get_contents(dirname(__FILE__) . '/templates/' . $wpdm_package['template']);
        else
            $wpdm_package['template'] = $linktemplates[$wpdm_package['template']]['content'] ? $linktemplates[$wpdm_package['template']]['content'] : $wpdm_package['template'];

        if (file_exists(dirname(__FILE__) . '/templates/' . $wpdm_package['page_template'])) $wpdm_package['page_template'] = @file_get_contents(dirname(__FILE__) . '/templates/' . $wpdm_package['page_template']);
        else
            $wpdm_package['page_template'] = $pagetemplates[$wpdm_package['page_template']]['content'] ? $pagetemplates[$wpdm_package['page_template']]['content'] : $wpdm_package['page_template'];

        $wpdm_package = apply_filters('wdm_pre_render_page', $wpdm_package);
        if (isset($_GET['mode']) && $_GET['mode'] == 'popup') {
            echo "<div class='w3eden'>";
            echo FetchTemplate($wpdm_package['page_template'], $wpdm_package, 'popup');
            echo '<br><div style="clear: both;"></div><br></div> ';
        } else {
            $wpdm_package['page_template'] = stripcslashes($wpdm_package['page_template']);

            $data = FetchTemplate($wpdm_package['page_template'], $wpdm_package, 'page');
            $siteurl = site_url('/');
            $data .= "<script type='text/javascript' language='JavaScript'> jQuery('.inddl').click(function(){ var tis = this; jQuery.post('{$siteurl}',{wpdmfileid:'{$wpdm_package['id']}',wpdmfile:jQuery(this).attr('file'),actioninddlpvr:jQuery(jQuery(this).attr('pass')).val()},function(res){ res = res.split('|'); var ret = res[1]; if(ret=='error') jQuery(jQuery(tis).attr('pass')).addClass('error'); if(ret=='ok') location.href=jQuery(tis).attr('rel')+'&_wpdmkey='+res[2];});}); </script> ";
            return "<div class='w3eden'>" . $data . "<div style='clear:both'></div></div>";
        }
    }

}

function wpdm_download_url($package, $ext = '')
{
    if ($ext) $ext = '&' . $ext;
    $ID = is_array($package)?$package['ID']:$package;
    return site_url("/?wpdmdl={$ID}{$ext}");
}


function wpdm_upload_file()
{
    if (!isset($_FILES['Filedata']) || !isset($_GET['task'])) return;
    if (is_uploaded_file($_FILES['Filedata']['tmp_name']) && is_admin() && $_GET['task'] == 'wpdm_upload_files' && current_user_can("edit_posts")) {
        $tempFile = $_FILES['Filedata']['tmp_name'];
        $targetFile = UPLOAD_DIR . time() . 'wpdm_' . $_FILES['Filedata']['name'];
        move_uploaded_file($tempFile, $targetFile);
        echo basename($targetFile);
        die();
    }
}



function CreateDir(){

    if (!file_exists(UPLOAD_BASE)) {
        @mkdir(UPLOAD_BASE, 0755);
    }
    @chmod(UPLOAD_BASE, 0755);
    @mkdir(UPLOAD_DIR, 0755);
    @chmod(UPLOAD_DIR, 0755);
    setHtaccess();
    if ($_GET[re] == 1) {
        if (file_exists(UPLOAD_DIR)) $s = 1;
        else $s = 0;
        echo "<script>
        location.href='{$_SERVER[HTTP_REFERER]}&success={$s}';
        </script>";
        die();
    }
}

function FMSettings()
{

    if (isset($_POST['access']) && $_POST['access'] != '') {
        update_option('access_level', $_POST[access]);
    }

    $access = get_option('access_level');
    include('wpdm-settings.php');
}

function basic_settings()
{
    
    if (isset($_POST['task']) && $_POST['task'] == 'wdm_save_settings' && current_user_can('manage_options')) {
        
        if ( ! isset( $_POST['wpdmsettingsnonce'] ) || ! wp_verify_nonce( $_POST['wpdmsettingsnonce'], 'wpdm-'.NONCE_KEY ) ) die('Invalid Request!');    
        
        foreach ($_POST as $optn => $optv) {
            if(strpos("__".$optn, "wpdm")) //Option must have "wpdm" in its name to avoid any type ambiguity  
            update_option($optn, $optv);
        }
        if (!isset($_POST['__wpdm_login_form'])) delete_option('__wpdm_login_form');



        die(__('Settings Saved Successfully','wpdmpro'));
    }
    include('settings/basic.php');
}

function wdm_ajax_settings()
{
    global $stabs;
    
    if(current_user_can('manage_options'))
    call_user_func($stabs[$_POST['section']]['callback']);
    die();
}


function wpdm_save_package_data($post)
{
    global $wpdb, $current_user;
    get_currentuserinfo();


    if (get_post_type() != 'wpdmpro' || !isset($_POST['file'])) return;

    $cdata = get_post_custom($post);
    foreach ($cdata as $k => $v) {
        $tk = str_replace("__wpdm_", "", $k);
        if (!isset($_POST['file'][$tk]) && $tk != $k)
            delete_post_meta($post, $k);

    }

    foreach ($_POST['file'] as $meta_key => $meta_value) {
        $key_name = "__wpdm_" . $meta_key;
        update_post_meta($post, $key_name, $meta_value);
    }

    update_post_meta($post, '__wpdm_masterkey', uniqid());

    if (isset($_POST['reset_key']) && $_POST['reset_key'] == 1)
        update_post_meta($post, '__wpdm_masterkey', uniqid());

    //do_action('after_update_package',$post, $_POST['file']);


}



/**
 * @usage Render Download Manager Category List with ul/li hirarchy
 * @param int $parent
 * @param int $level
 * @param bool $recur
 */
function wpdm_list_categories($parent = 0, $level = 0, $recur = true)
{
    $parent = isset($parent)?$parent:0;
    $args = array(
        'orderby'       => 'name',
        'order'         => 'ASC',
        'hide_empty'    => false,
        'exclude'       => array(),
        'exclude_tree'  => array(),
        'include'       => array(),
        'number'        => '',
        'fields'        => 'all',
        'slug'          => '',
        'parent'         => $parent,
        'hierarchical'  => true,
        'child_of'      => 0,
        'get'           => '',
        'name__like'    => '',
        'pad_counts'    => false,
        'offset'        => '',
        'search'        => '',
        'cache_domain'  => 'core'
    );
    $cats = get_terms('wpdmcategory',$args);
    if ($parent  && $level > 0 && wpdm_cat_has_child($parent)) echo "<ul>";
    foreach ($cats as $id => $cat) {
        $pres = str_repeat("&mdash;", $level);
            $catlink = get_term_link($cat);

            echo "<li><a href='{$catlink}'>{$cat->name}</a>\n";
            if ($recur)
                wpdm_list_categories($id, $level + 1, $recur);

        echo "</li>\n";
    }
    if ($parent && $level > 0 && wpdm_cat_has_child($parent)) echo "</ul>";
}

/**
 * @usage Check if a download manager category has child
 * @param $parent
 * @return bool
 */

function wpdm_cat_has_child($parent)
{
    $termchildren = get_term_children( $parent, 'wpdmcategory' );
    if(count($termchildren)>0) return true;
    return false;
}

function wpdm_cblist_categories($parent = 0, $level = 0, $sel = array())
{
    $cats = get_terms('wpdmcategory', array('hide_empty' => false, 'parent' => $parent));
    if (!$cats) $cats = array();
    if ($parent != '') echo "<ul>";
    foreach ($cats as $cat) {
        $id = $cat->slug;
        $pres = $level * 5;

            if (in_array($id, $sel))
                $checked = 'checked=checked';
            else
                $checked = '';
            echo "<li style='margin-left:{$pres}px;padding-left:0'><label for='c$id'><input id='c$id' type='checkbox' name='file[category][]' value='$id' $checked /> ".$cat->name."</label></li>\n";
            wpdm_cblist_categories($cat->term_id, $level + 1, $sel);

    }
    if ($parent != '') echo "</ul>";
}

function wpdm_dropdown_categories($name = '', $selected = '', $id = '')
{
    wp_dropdown_categories('show_option_none=Select category&show_count=0&orderby=name&echo=1&taxonomy=wpdmcategory&hide_empty=0&name=' . $name . '&id=' . $id . '&selected=' . $selected);

}



function setHtaccess()
{
    $cont = 'RewriteEngine On
    <Files *>
    Deny from all
    </Files> 
       ';
    @file_put_contents(UPLOAD_DIR . '.htaccess', $cont);
}

function remote_post($url, $data)
{
    $fields_string = "";
    foreach ($data as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');
    //open connection
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    //execute post
    $result = curl_exec($ch);
    //close connection
    curl_close($ch);
    return $result;
}

function remote_get($url)
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => false, // don't return headers
        CURLOPT_ENCODING => "", // handle all encodings
        CURLOPT_USERAGENT => "spider", // who am i
        CURLOPT_AUTOREFERER => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
        CURLOPT_TIMEOUT => 120, // timeout on response
        CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    $header = curl_getinfo($ch);
    curl_close($ch);
    return $content;
}

 

function addusercolumn()
{
    ?>
    <script type="text/javascript">
        jQuery(function () {

            /*jQuery('#role').after('<th>WPDM Stats</th>');
             jQuery('tfoot .column-role').after('<th>WPDM Stats</th>');*/

            jQuery('table.users tbody tr').each(function (index) {
                var uid = this.id.split('-')[1];
                var cell = jQuery(this).find('td.sports_data');
                jQuery('#' + this.id + ' .row-actions').append(' | <a href="edit.php?post_type=wpdmpro&page=stats&type=pvdpu&uid=' + uid + '">Download Stats</a>');
            });

        });
    </script>
<?php
}

function wpdm_remove_tinymce()
{
    if ($_GET['page'] != 'file-manager/add-new-package') return false;
    ?>
    <script language="JavaScript">
        <!--
        tinyMCE.execCommand('mceRemoveControl', false, 'file[description]');
        //-->
    </script>
<?php
}

function wpdm_adminjs()
{
    remove_submenu_page( 'index.php', 'wpdm-welcome' );
    ?>
    <script language="JavaScript">
        <!--
        jQuery(function () {
            jQuery('#TB_closeWindowButton').click(function () {
                tb_remove();
            });

            var title = '';
            var edge = 'left';
            jQuery('.infoicon').css('cursor', 'pointer').mouseover(function () {
                title = this.title;
                this.title = '';
                if (jQuery(this).attr('edge')) edge = jQuery(this).attr('edge');
                else edge = 'left';
                var options = {"content": "<h3>Quick Help!<\/h3><p style=\"font-family:'Segoe UI','Lucida Sans'\">" + title + "<\/p>", "position": {"edge": edge, "align": "center"}};

                if (!options)
                    return;

                options = jQuery.extend(options, {
                    close: function () {
                        
                    }
                });

                jQuery(this).pointer(options).pointer('open');

            });
            jQuery('.infoicon').mouseout(function () {
                this.title = title;
                jQuery(this).pointer('close');

            });

        });


        //-->
    </script>

<?php
}



function wpdm_plugin_data($dir){
    $plugins = get_plugins();
    foreach($plugins as $plugin => $data){
        $plugin = explode("/", $plugin);
        if($plugin[0]==$dir) return $data;
    }
}

function wpdm_check_update()
{

    if(!current_user_can('manage_options')) return;

    $latest = '';//get_option('wpdm_latest');
    $latest_check = get_option('wpdm_latest_check');
    $time = time() - intval($latest_check);
    $plugins = get_plugins();

    $latest_v_url = 'http://www.wpdownloadmanager.com/versions.php';

    if ($latest == '' || $time > 86400) {
        $latest = remote_get($latest_v_url);
        update_option('wpdm_latest', $latest);
        update_option('wpdm_latest_check', time());

    }
    $latest = maybe_unserialize($latest);
    array_shift($latest);
    $page = isset($_REQUEST['page'])?esc_attr($_REQUEST['page']):'';
    $plugin_info_url = isset($_REQUEST['plugin_url'])?$_REQUEST['plugin_url']:'http://www.wpdownloadmanager.com/purchases/';
    foreach($latest as $plugin_dir => $latestv){
        $plugin_data = wpdm_plugin_data($plugin_dir);

        if (version_compare($plugin_data['Version'], $latestv, '<') == true ) {
            $plugin_name = $plugin_data['Name'];
            $plugin_info_url = $plugin_data['PluginURI'];
            $trid = sanitize_title($plugin_name);
            if($trid!=''){
                if ($page == 'plugins') {
                    echo <<<NOTICE
     <script type="text/javascript">
      jQuery(function(){
        jQuery('tr#{$trid}').addClass('update').after('<tr class="plugin-update-tr"><td colspan=3 class="plugin-update colspanchange"><div style="background:#D54E21;border-top:0px;padding:5px 15px;color:#fff">There is a new version of {$plugin_name} available. <b><a href="{$plugin_info_url}#{$latestv}" style="color:#fff;float: right;background: rgba(0,0,0,0.2);padding: 5px 15px;margin-top:-5px;margin-right: -15px" target=_blank>Download v{$latestv}  <i class="fa fa-long-arrow-right"></i></a></b></div></td></tr>');
      });
      </script>
NOTICE;
                } else {
                    echo <<<NOTICE
     <script type="text/javascript">
      jQuery(function(){
        jQuery('.wrap > h2').after('<div class="updated error" style="margin:10px 0px;padding:10px;border:2px solid #dd3d36;border-radius:4px;background: #ffffff"><div style="float:left;"><b style="color:#dd3d36;">Important!</b><br/>There is a new version of <u>{$plugin_name}</u> available.</div> <a style="border-radius:2px;float:right;display:inline-table;color:#ffffff;background:#D54E21;padding:10px 15px" href="{$plugin_info_url}#{$latestv}"  target=_blank>Download v{$latestv}  <i class="fa fa-long-arrow-right"></i></a><div style="clear:both"></div></div>');
         });
         </script>
NOTICE;
                }}
        }
    }
    if(wpdm_is_ajax()) die();
}

function wpdm_newversion_check(){

    if(!current_user_can('manage_options')) return;

    $tmpvar = explode("?", basename($_SERVER['REQUEST_URI']));
    $page = array_shift($tmpvar);
    $page = explode(".", $page);
    $page = array_shift($page);

    if (get_option('wpdm_update_notice') == 'disabled' || !($page == 'plugins' || get_post_type()=='wpdmpro') ) return;

    $page = $page == 'plugins'?$page:get_post_type();

    ?>
    <script type="text/javascript">
        jQuery(function(){

            jQuery.post(ajaxurl, {
                action:         'wpdm_check_update',
                page:           '<?php echo $page; ?>'
            }, function(res){
                jQuery('#wpfooter').after(res);
            });


        });
    </script>
<?php
}



function wpdm_ajax_call_exec()
{
    if (isset($_POST['action']) && $_POST['action'] == 'wpdm_ajax_call') {
        if ($_POST['execute']=='wpdm_getlink')
            wpdm_getlink();
        else
            echo "function not defined!";
        die();
    }
}








    include(dirname(__FILE__) . "/hooks.php");


 


  
