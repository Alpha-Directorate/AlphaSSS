<?php



function myplugin_addbuttons() {
   // Don't bother doing this stuff if the current user lacks permissions
   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     return;
 
   // Add only in Rich Editor mode
   if ( get_user_option('rich_editing') == 'true') {
     add_filter("mce_external_plugins", "add_myplugin_tinymce_plugin");
     add_filter('mce_buttons', 'register_myplugin_button');
   }
}
 
function register_myplugin_button($buttons) {
   array_push($buttons, "separator", "donwloadmanager");
   return $buttons;
}
 
// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function add_myplugin_tinymce_plugin($plugin_array) {
   $plugin_array['donwloadmanager'] = home_url('wp-content/plugins/download-manager/js/tinymce.js');
   return $plugin_array;
}

function tinymce(){
    ?>
    <select id="fl">
    <?php
    $res = mysql_query("select * from ahm_files"); 
    while($row = mysql_fetch_assoc($res)){
    ?>
    
    <option value="<?php echo $row['id']; ?>"><?php echo $row['title']; ?></option>
    
    
    <?php    
        
    }
?>
    </select>
    <input type="submit" id="addtopost" class="button button-primary" name="addtopost" value="Insert into post" />

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo home_url('/wp-includes/js/tinymce/tiny_mce_popup.js'); ?>"></script>
                <script type="text/javascript">
                    /* <![CDATA[ */
                    jQuery('#wpbody').css('padding','5px').css('margin','0px');
                    jQuery('#adminmenu').remove();;
                    jQuery('#screen-meta').remove();;
                    jQuery('#wphead').remove();     
                    jQuery('body').removeClass('wp-admin').removeClass('js').css('overflow','hidden');
                    jQuery('#addtopost').click(function(){
                    var win = window.dialogArguments || opener || parent || top;                
                    win.send_to_editor('{filelink='+$('#fl').val()+'}');
                    tinyMCEPopup.close();
                    return false;                   
                    });
                    /* ]]> */
                </script>

</body>    
</html>
    
    <?php
    
    die();
}
 
// init process for button control
add_action('init', 'myplugin_addbuttons');

