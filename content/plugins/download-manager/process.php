<?php

    if(!defined('ABSPATH')) exit();

    do_action("wpdm_onstart_download", $package);

    global $current_user, $dfiles;
    
    $speed = get_option('__wpdm_download_speed',4096); //in KB - default 4 MB
    $speed = apply_filters('wpdm_download_speed', $speed);
     
    get_currentuserinfo();
    if(wpdm_is_download_limit_exceed($package['ID'])) die(__msg('DOWNLOAD_LIMIT_EXCEED'));
    $files = $package['files'];
//    $dir = isset($package['package_dir'])?$package['package_dir']:'';
//    if($dir!=''){
//    $dfiles = array();
//    wpmp_get_files($dir);
//    }
    $log = new Stats();
        
    $oid = isset($_GET['oid'])?addslashes($_GET['oid']):'';

    if(!isset($_GET['ind'])&&!isset($_GET['nostat']))
    $log->NewStat($package['ID'], $current_user->ID,$oid);

    if(count($files)==0) {
      if(isset($package['sourceurl'])&&$package['sourceurl']!='') {
                
        if(!isset($package['url_protect'])||$package['url_protect']==0&&strpos($package['sourceurl'],'://')){
            header('location: '.$package['sourceurl']);
            die();
        }
        
        $r_filename = basename($package['sourceurl']);
        $r_filename = explode("?", $r_filename);
        $r_filename = $r_filename[0];
        wpdm_download_file($package['sourceurl'],$r_filename, $speed, 1, $package);
        die();
      } 
    
       wpdm_download_data('download-not-available.txt','Sorry! Download is not available yet.');
       die();
    
    }
     
    $idvdl = isset($package['individual_file_download'])&&isset($_GET['ind'])?1:0;

    
    //Individual file or single file download section

        $ind = 0;




    $files[$ind] = trim($files[$ind]);

    if(strpos($files[$ind], "://")) {
        header("location: ". $files[$ind]);
        die();
    }

    if(file_exists(UPLOAD_DIR.$files[$ind]) && $files[$ind]!='')
    $filepath = UPLOAD_DIR.$files[$ind];
    else if(file_exists($files[$ind]) && $files[$ind]!='')
    $filepath = $files[$ind];
    else if(file_exists(WP_CONTENT_DIR.end($tmp = explode("wp-content",$files[$ind]))) && $files[$ind]!='') //path fix on site move
    $filepath = WP_CONTENT_DIR.end($tmp = explode("wp-content",$files[$ind]));
    else {
        wpdm_download_data('file-not-found.txt','File not found or deleted from server');
        die();        
    }

    //$plock = get_wpdm_meta($file['id'],'password_lock',true);
    //$fileinfo = get_wpdm_meta($package['id'],'fileinfo');
     
    $filename = basename($filepath);
    $filename = preg_replace("/([0-9]+)[wpdm]*_/","",$filename);
    
    wpdm_download_file($filepath, $filename, $speed, 1, $package);    
    //@unlink($filepath);


do_action("after_downlaod", $package);
die();
?>
