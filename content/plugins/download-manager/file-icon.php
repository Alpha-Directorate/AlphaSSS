<?php //print_r( $fileinfo );  ?>
<style type="text/css">
.wdmiconfile{    
    -webkit-border-radius: 6px;
-moz-border-radius: 6px;
border-radius: 6px;
}
</style>


<div class="postbox " id="iconimage">
<div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><span><?php echo __("Select Icon",'wpdmpro'); ?> </span></h3>
<div class="inside" style="height: 200px;overflow: auto;"> 
<div id="icon-plupload-upload-ui" class="hide-if-no-js">
     <div id="icon-drag-drop-area">
       <div class="icon-drag-drop-inside">       
        <input id="icon-plupload-browse-button" type="button" class="button-secondary" value="<?php echo __('Upload New Icon','wpdmpro'); ?>" class="btn" />
      </div>
     </div>
  </div>

  <?php

  $plupload_init = array(
    'runtimes'            => 'html5,silverlight,flash,html4',
    'browse_button'       => 'icon-plupload-browse-button',
    'container'           => 'icon-plupload-upload-ui',
    'drop_element'        => 'icon-drag-drop-area',
    'file_data_name'      => 'icon-async-upload',            
    'multiple_queues'     => true,
   /* 'max_file_size'       => wp_max_upload_size().'b',*/
    'url'                 => admin_url('admin-ajax.php'),
    'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
    'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
    'filters'             => array(array('title' => __('Allowed Files','wpdmpro'), 'extensions' => 'png, jpg, gif')),
    'multipart'           => true,
    'urlstream_upload'    => true,

    // additional post data to send to our ajax hook
    'multipart_params'    => array(
      '_ajax_nonce' => wp_create_nonce('icon-upload'),
      'action'      => 'icon_upload',            // the ajax action name
    ),
  );

  // we should probably not apply this filter, plugins may expect wp's media uploader...
  $plupload_init = apply_filters('plupload_init', $plupload_init); ?>

  <script type="text/javascript">

    jQuery(document).ready(function($){

      // create the uploader and pass the config from above
      var uploader = new plupload.Uploader(<?php echo json_encode($plupload_init); ?>);

      // checks if browser supports drag and drop upload, makes some css adjustments if necessary
      uploader.bind('Init', function(up){
        var uploaddiv = jQuery('#icon-plupload-upload-ui');

        if(up.features.dragdrop){
          uploaddiv.addClass('drag-drop');
            jQuery('#icon-drag-drop-area')
              .bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
              .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });

        }else{
          uploaddiv.removeClass('drag-drop');
          jQuery('#icon-drag-drop-area').unbind('.wp-uploader');
        }
      });

      uploader.init();

      // a file was added in the queue
      uploader.bind('FilesAdded', function(up, files){
        //var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
        
        jQuery('#icon-loading').slideDown();
           

        plupload.each(files, function(file){
          jQuery('#icon-filelist').html(
                        '<div class="file" id="' + file.id + '"><b>' +
 
                        file.name + '</b> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') ' +
                        '<div class="fileprogress"></div></div>');
        });

        up.refresh();
        up.start();
      });
      
      uploader.bind('UploadProgress', function(up, file) {                      
                jQuery('#' + file.id + " .fileprogress").width(file.percent + "%");
                jQuery('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
            });
 

      // a file was uploaded 
      uploader.bind('FileUploaded', function(up, file, response) {

        // this is your ajax response, update the DOM with it or something...
        var jres = jQuery.parseJSON(response.response);
        console.log(jres);
        //response
        jQuery('#' + file.id ).remove();
        var d = new Date();
        var ID = d.getTime();        
        jQuery('#icon-loading').hide(); 
        jQuery('#w-icons').prepend("<img class='wdmiconfile' id='"+jres.fid+"' src='"+jres.url+"' style='padding:5px; margin:1px; float:left; border:#fff 2px solid;height: 32px;width:auto; ' /><input rel='wdmiconfile' style='display:none' type='radio'  name='file[icon]'  class='checkbox'  value='"+jres.rpath+"' ></label>");
                            

      });    
    
    });   

  </script>
<br clear="all" />
<div id="w-icons">
<img  id="icon-loading" src="<?php  echo plugins_url('download-manager/images/loading.gif'); ?>" style=";display:none;padding:5px; margin:1px; float:left; border:#fff 2px solid;height: 32px;width:auto; " />
<?php 
$img = array('jpg','gif','jpeg','png');
foreach($fileinfo as $index=>$value): $tmpvar = explode(".",$value['file']); $ext = strtolower(end($tmpvar)); if(in_array($ext,$img)): ?>
<label>
<img class="wdmiconfile" id="<?php echo md5($value['file']) ?>" src="<?php  echo plugins_url().'/'.$value['file'] ?>" alt="<?php echo $value['name'] ?>" style="padding:5px; margin:1px; float:left; border:#fff 2px solid;height: 32px;width:auto; " />
<input rel="wdmiconfile" style="display:none" <?php if($file['icon']==$value['file']) echo ' checked="checked" ' ?> type="radio"  name="file[icon]"  class="checkbox"  value="<?php echo $value['file'] ?>"></label>
<?php endif; endforeach; ?>
</div>
<script type="text/javascript">
//border:#CCCCCC 2px solid


jQuery('#<?php echo md5($file['icon']) ?>').css('border','#008000 2px solid').css('background','#F2FFF2');

jQuery('img.wdmiconfile').on('click',function(){

jQuery('img.wdmiconfile').css('border','#fff 2px solid').css('background','transparent');
jQuery(this).css('border','#008000 2px solid').css('background','#F2FFF2');



});

</script>

 <div class="clear"></div>
</div>
</div>
 