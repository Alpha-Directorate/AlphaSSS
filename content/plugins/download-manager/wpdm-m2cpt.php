<?php


function wpdm_m24x(){
    global $wpdb;
    $ccn = get_option('__wpdm_category_converted',0);
    if($ccn==0){
    $allcs = maybe_unserialize(get_option('_fm_categories'));

    $term_templates = array();
    foreach($allcs as $id=>$wpdmc){
        if($wpdmc['parent']!=''){
            $parent = term_exists($wpdmc['parent'], 'wpdmcategory');
            $parent_id = $parent['term_id'];
        }
        else $parent_id = 0;
        $term = wp_insert_term(
            $wpdmc['title'], // the term
            'wpdmcategory',  // the taxonomy
            array(
                'description'=> $wpdmc['content'],
                'slug' => $id,
                'parent'=> $parent_id
            )
        );
        if(!is_wp_error($term))
        $term_templates[$term['term_id']] = isset($wpdmc['link_template'])?$wpdmc['link_template']:'link-template-default.php';
        }
        update_option("__wpdm_category_link_templates",$term_templates);
        update_option("__wpdm_category_converted",1);
    }
    $ids = get_option('_wpdm_m24x_ids',true);
    if(isset($_POST['task'])&&$_POST['task']=='wdm_save_settings'){
        if(!is_array($ids)) $ids = array();
        if(!is_array($_POST['id'])) $_POST['id'] = array();
        foreach($_POST['id'] as $fid){
            //if(!in_array($fid, $ids)){
            $file = $wpdb->get_row("select * from ahm_files where id='$fid'", ARRAY_A);
            $file['files'] = array();
            $file['files'] = array($file['file']);
            unset($file['file']);

            $file['access'] = $file['access']=='guest'?array('guest'):array('subscriber','administrator');
            if(isset($file['sourceurl']) && $file['sourceurl']!='')
            $file['files'][] = $file['sourceurl'];

            foreach($file['files'] as $filepath){
                $fileinfo[$filepath] = array('title'=>basename($filepath), 'password'=>'');
            }



            $cats = maybe_unserialize($file['category']);
            $id = wp_insert_post(array(
                'post_type' => 'wpdmpro',
                'post_title'=>$file['title'],
                'post_content' => $file['description'],
                'post_status' => 'publish',
                'tax_input' => array('wpdmcategory'=>$cats),
                'post_date' => date("Y-m-d H:i:s", time()),
                'comment_status' => 'open'
            ));

            /** media */
            /*
            $filename = $file['preview'];
            $filename = str_replace(site_url('/'), ABSPATH.'/', $filename);
            $wp_filetype = wp_check_filetype(basename($filename), null );
            //$wp_upload_dir = wp_upload_dir();
            $attachment = array(
                'guid' => $file['preview'],
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $filename, $id );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            set_post_thumbnail( $id, $attach_id );
            */
            $file['legacy_id'] = $file['id'];
            unset($file['title']);
            unset($file['description']);
            unset($file['id']);
            unset($file['uid']);
            unset($file['create_date']);
            unset($file['preview']);
            unset($file['sourceurl']);

            foreach($file as $meta_key => $meta_value ){
                $meta_value = maybe_unserialize($meta_value);
                update_post_meta($id, '__wpdm_'.$meta_key, $meta_value);
            }
//            $allmeta = $wpdb->get_results("select * from {$wpdb->prefix}ahm_filemeta where pid='{$file['id']}'", ARRAY_A);
//            foreach($allmeta as $wmeta){
//                $wmeta['value'] = maybe_unserialize($wmeta['value']);
//                update_post_meta($id, '__wpdm_'.$wmeta['name'], $wmeta['value']);
//            }
//            update_post_meta($id, '__wpdm_fileinfo', $fileinfo);


        }
        if(is_array($ids))
        $ids = array_unique(array_merge($ids, $_POST['id']));
        else
        $ids = $_POST['id'];
        /*foreach($_POST as $optn=>$optv){
            update_option($optn, $optv);
        }                                      */

        update_option('_wpdm_m24x_ids',$ids);
        die('Copied successfully');
    }

    $res = $wpdb->get_results("select * from ahm_files", ARRAY_A);

    ?>
    <div class="clear"></div>

    <div class="update-nag" style="margin: 10px 0">Please don't select more then 100 packages at a time</div><Br/>
<div class="clear"></div>

<table cellspacing="0" class="widefat fixed">
    <thead>
    <tr>
    <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input class="call m" type="checkbox"></th>
    <th style="" class="manage-column column-media" id="media" scope="col">WPDM 3 Package</th>
    <th style="" class="manage-column column-parent" id="parent" scope="col">Migrated</th>
    </tr>
    </thead>

    <tfoot>
    <tr>
    <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input class="call m" type="checkbox"></th>
    <th style="" class="manage-column column-media" id="media" scope="col">WPDM 3 Package</th>
    <th style="" class="manage-column column-parent" id="parent" scope="col">Migrated</th>
    </tr>
    </tfoot>

    <tbody class="list:post" id="the-list">
    <?php $altr = 'alternate'; foreach($res as $media) {   $copied = @in_array($media['id'],$ids)?'<span style="color: #008800">Yes</span>':'No'; $altr = $altr == ''?'alternate':'';  ?>
    <tr valign="top" class="<?php echo $altr;  ?> author-self status-inherit" id="post-8">

                <th class="check-column" scope="row"><input type="checkbox" value="<?php echo $media['id'];?>" class="m" name="id[]"></th>

                <td class="media column-media">
                    <strong><?php echo $media['title']?></strong>
                </td>
                <td class="parent column-parent"><b><?php echo $copied; ?></b></td>

     </tr>
     <?php } ?>
    </tbody>
</table>

 <script language="JavaScript">
 <!--
   jQuery('.call').click(function(){
       if(this.checked)
       jQuery('.m').attr('checked','checked');
       else
       jQuery('.m').removeAttr('checked');
   });
 //-->
 </script>

    <?php
}


global $wpdb;
if($wpdb->get_var("SHOW TABLES LIKE 'ahm_files'") == 'ahm_files') {
    $tf = $wpdb->get_var("select count(*) from `ahm_files`");
    if (function_exists('add_wdm_settings_tab') && $tf > 0)
        add_wdm_settings_tab("m24x", "Migrate", 'wpdm_m24x');
}
 