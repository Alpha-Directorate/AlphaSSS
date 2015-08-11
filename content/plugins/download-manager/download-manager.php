<?php 
 
/*
Plugin Name: Download Manager
Plugin URI: http://www.wpdownloadmanager.com/
Description: Manage, Protect and Track File Downloads from your WordPress site
Author: Shaon
Version: 2.7.95
Author URI: http://www.wpdownloadmanager.com/
*/

//error_reporting(E_ALL);
       
if(!isset($_SESSION))
session_start();

define('WPDM_Version','2.7.95');
        
include(dirname(__FILE__)."/functions.php");        
include(dirname(__FILE__)."/class.pack.php");
include(dirname(__FILE__)."/class.logs.php");
include(dirname(__FILE__)."/class.pagination.php");
include(dirname(__FILE__)."/server-file-browser.php");
include(dirname(__FILE__)."/wpdm-widgets.php");

    
$d = str_replace('\\','/',WP_CONTENT_DIR);

define('WPDM_BASE_DIR',dirname(__FILE__).'/');  
define('WPDM_BASE_URL',plugins_url('/download-manager/'));

define('UPLOAD_DIR',$d.'/uploads/download-manager-files/');  

define('WPDM_CACHE_DIR',dirname(__FILE__).'/cache/');  

define('_DEL_DIR',$d.'/uploads/download-manager-files');  

define('UPLOAD_BASE',$d.'/uploads/');  

if(function_exists('ini_set'))
@ini_set('upload_tmp_dir',UPLOAD_DIR.'/cache/');


if(!$_POST)    $_SESSION['download'] = 0;

function wpdm_load_textdomain() {     
    load_plugin_textdomain( 'wpdmpro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

function wpdm_pro_Install(){
    global $wpdb;
      
      delete_option('wpdm_latest');  

      $sqls[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ahm_download_stats` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `pid` int(11) NOT NULL,
              `uid` int(11) NOT NULL,
              `oid` varchar(100) NOT NULL,
              `year` int(4) NOT NULL,
              `month` int(2) NOT NULL,
              `day` int(2) NOT NULL,
              `timestamp` int(11) NOT NULL,
              `ip` varchar(20) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
            
      
      
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      foreach($sqls as $sql){
      $wpdb->query($sql); 
      //dbDelta($sql); 
      }

   if(get_option('access_level',0)==0)              update_option('access_level','level_10');
   if(get_option('_wpdm_thumb_w',0)==0)             update_option('_wpdm_thumb_w','200');
   if(get_option('_wpdm_thumb_h',0)==0)             update_option('_wpdm_thumb_h','100');   
   if(get_option('_wpdm_pthumb_w',0)==0)            update_option('_wpdm_pthumb_w','400');   
   if(get_option('_wpdm_pthumb_h',0)==0)            update_option('_wpdm_pthumb_h','250');
   if(get_option('_wpdm_athumb_w',0)==0)            update_option('_wpdm_athumb_w','50');
   if(get_option('_wpdm_athumb_h',0)==0)            update_option('_wpdm_athumb_h','50');
   if(get_option('_wpdm_athumb_h',0)==0)            update_option('_wpdm_athumb_h','50');
   if(get_option('_wpdm_wthumb_h',0)==0)            update_option('_wpdm_wthumb_h','150');
   if(get_option('_wpdm_wthumb_h',0)==0)            update_option('_wpdm_wthumb_h','70');
   if(get_option('_wpdm_show_ct_bar',-1)==-1)       update_option('_wpdm_show_ct_bar','1');
   if(get_option('_wpdm_custom_template','')=='')   update_option('_wpdm_custom_template','page.php');
   
   update_option('wpdm_default_link_template',"[thumb_100x50]\r\n<br style='clear:both'/>\r\n<b>[popup_link]</b><br/>\r\n<b>[download_count]</b> downloads");
   update_option('wpdm_default_page_template',"[thumb_800x500]\r\n<br style='clear:both'/>\r\n[description]\r\n<fieldset class='pack_stats'>\r\n<legend><b>Package Statistics</b></legend>\r\n<table>\r\n<tr><td>Total Downloads:</td><td>[download_count]</td></tr>\r\n<tr><td>Stock Limit:</td><td>[quota]</td></tr>\r\n<tr><td>Total Files:</td><td>[file_count]</td></tr>\r\n</table>\r\n</fieldset><br>\r\n[download_link]");
    
   if(get_option('_wpdm_etpl')==''){
          update_option('_wpdm_etpl',array('title'=>'Your download link','body'=>file_get_contents(dirname(__FILE__).'/templates/wpdm-email-lock-template.html')));
   }
   
   wpdm_common_actions(); 
   flush_rewrite_rules();
   CreateDir();
       
}

include("wpdm-core.php");



register_activation_hook(__FILE__,'wpdm_pro_Install');
 
/** native upload code **/
function plu_admin_enqueue() {     
    wp_enqueue_script('plupload-all');    
    wp_enqueue_style('plupload-all');    
}

 
// handle uploaded file here
function wpdm_check_upload(){


  if(!current_user_can("edit_posts")) return;

  check_ajax_referer('photo-upload');

  $filename = get_option('__wpdm_sanitize_filename',0) == 1? sanitize_file_name($_FILES['async-upload']['name']):$_FILES['async-upload']['name'] ;

  if(file_exists(UPLOAD_DIR.$filename))
  $filename = time().'wpdm_'.$filename;
  //else
  //$filename = $filename;
  move_uploaded_file($_FILES['async-upload']['tmp_name'],UPLOAD_DIR.$filename);
  //@unlink($status['file']);
  echo $filename;
  exit;
}


function wpdm_upload_icon(){
  if(!current_user_can('manage_options')) return;
  check_ajax_referer('icon-upload');
    $filename = get_option('__wpdm_sanitize_filename',0) == 1? sanitize_file_name($_FILES['icon-async-upload']['name']):$_FILES['icon-async-upload']['name'] ;
    if(file_exists(dirname(__FILE__).'/file-type-icons/'.$filename))
    $filename = time().'wpdm_'.$filename;
  //else
  //$filename = $_FILES['icon-async-upload']['name'];

  $ext = explode(".", $filename);
  $ext = end($ext);
  $ext = strtolower($ext);
  if(!in_array($ext, array('png','jpg','jpeg'))) return; //Only Images!

  move_uploaded_file($_FILES['icon-async-upload']['tmp_name'],dirname(__FILE__).'/file-type-icons/'.$filename);
  $data = array('rpath'=>"download-manager/file-type-icons/$filename",'fid'=>md5("download-manager/file-type-icons/$filename"),'url'=>plugins_url("download-manager/file-type-icons/$filename"));
  header('HTTP/1.0 200 OK');
  header("Content-type: application/json");    
  echo json_encode($data);
  exit;
}


function wpdm_welcome(){
    remove_submenu_page( 'index.php', 'wpdm-welcome' );
    include(WPDM_BASE_DIR.'tpls/wpdm-welcome.php');
}

function fmmenu(){
    $access_level = 'manage_options';
    add_submenu_page( 'edit.php?post_type=wpdmpro', __('Add-Ons &lsaquo; Download Manager',"wpdmpro"), __('Add-Ons',"wpdmpro"), $access_level, 'wpdm-addons', 'wpdm_addonslist');
    add_submenu_page( 'edit.php?post_type=wpdmpro', __('Settings &lsaquo; Download Manager',"wpdmpro"), __('Settings',"wpdmpro"), $access_level, 'settings', 'FMSettings');
    add_dashboard_page('Welcome', 'Welcome', 'read', 'wpdm-welcome', 'wpdm_welcome');
}


function wpdm_skip_ngg_resource_manager($r){
    return false;
}

function wpdm_welcome_redirect($plugin)
{
    if($plugin=='download-manager/download-manager.php') {
        wp_redirect(admin_url('index.php?page=wpdm-welcome'));
        die();
    }
}

add_filter('run_ngg_resource_manager', 'wpdm_skip_ngg_resource_manager');

include(dirname(__FILE__)."/wpdm-m2cpt.php");