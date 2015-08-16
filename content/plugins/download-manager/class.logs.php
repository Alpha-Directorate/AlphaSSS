<?php
class Stats{
    
    function Stats(){
        
    }
    
    function NewStat($pid, $uid, $oid){
        global $wpdb;
        $ip = $_SERVER['REMOTE_ADDR'];
        $wpdb->insert("{$wpdb->prefix}ahm_download_stats",array('pid'=>(int)$pid, 'uid'=>(int)$uid,'oid'=>$oid, 'year'=> date("Y"), 'month'=> date("m"), 'day'=> date("d"), 'timestamp'=> time(),'ip'=>$ip));        
        update_post_meta($pid, '__wpdm_download_count',intval(get_post_meta($pid, '__wpdm_download_count', true))+1);
         
    }

    
    
}