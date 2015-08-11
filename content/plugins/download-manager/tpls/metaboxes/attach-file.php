<input type="hidden" name="file[files][]" value="<?php $afiles = maybe_unserialize(get_post_meta(get_the_ID(), "__wpdm_files", true)); echo $afiles[0]; ?>" id="wpdmfile" />
<div class="cfile" id="cfl" style="padding: 10px;margin-bottom:10px;border:1px solid #ddd;background: #fafafa">
    <?php
    $filesize = "<em style='color: darkred'>( ".__("attached file is missing/deleted","wpdmpro")." )</em>";
    $afile = is_array($afiles)&&isset($afiles[0])?$afiles[0]:'';

    if($afile !=''){

        if(strpos($afile, "://")){
            $fparts = parse_url($afile);
            $filesize = "<span class='w3eden'><span class='text-primary'><i class='fa fa-link'></i> {$fparts['host']}</span></span>";
        }
        else {
            if (file_exists(UPLOAD_DIR . '/' . $afile))
                $filesize = number_format(filesize(UPLOAD_DIR . '/' . $afile) / 1025, 2) . " KB";
            else if (file_exists($afile))
                $filesize = number_format(filesize($afile) / 1025, 2) . " KB";
        }

        if(strpos($afile, "#")) {
            $afile = explode("#", $afile);
            $afile = $afile[1];
        }

        ?>

        <div style="position: relative;"><strong><?php echo  basename($afile); ?></strong><br/><?php echo $filesize; ?> <a href='#' id="dcf" title="Delete Current File" style="position: absolute;right:0;top:0;height:32px;"><img src="<?php echo plugins_url('/download-manager/images/error.png'); ?>" /></a></div>
    <?php } else echo "<span style='font-weight:bold;color:#ddd'>No file uploaded yet!</span>"; ?>
    <div style="clear: both;"></div>
</div>

<div id="ftabs">
<ul>
    <li><a href="#upload">Upload</a></li>
    <?php  if(current_user_can('access_server_browser')){ ?>
    <li><a href="#browse">Browse</a></li>
    <?php } ?>
</ul>

<div id="upload">
<div id="plupload-upload-ui" class="hide-if-no-js" style="margin-top: 10px">
        <div id="drag-drop-area">
            <div class="drag-drop-inside" style="width: 100% !important;margin-top: 40px">
                <p class="drag-drop-info"><?php _e('Drop files here','wpdmpro'); ?></p>
                <p><?php _ex('or', 'Uploader: Drop files here - or - Select Files','wpdmpro'); ?></p>
                <p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files','wpdmpro'); ?>" class="button" /><br/><br/>
                Max: <?php echo number_format(wp_max_upload_size()/1048576,2); ?> MB<br/>
                </p>
            </div>
        </div>
    </div>

    <?php

    $plupload_init = array(
        'runtimes'            => 'html5,silverlight,flash,html4',
        'browse_button'       => 'plupload-browse-button',
        'container'           => 'plupload-upload-ui',
        'drop_element'        => 'drag-drop-area',
        'file_data_name'      => 'async-upload',
        'multiple_queues'     => true,
        'max_file_size'       => wp_max_upload_size().'b',
        'url'                 => admin_url('admin-ajax.php'),
        'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
        'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
        'filters'             => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
        'multipart'           => true,
        'urlstream_upload'    => true,

        // additional post data to send to our ajax hook
        'multipart_params'    => array(
            '_ajax_nonce' => wp_create_nonce('photo-upload'),
            'action'      => 'photo_gallery_upload',            // the ajax action name
        ),
    );

    // we should probably not apply this filter, plugins may expect wp's media uploader...
    $plupload_init = apply_filters('plupload_init', $plupload_init); ?>

    <script type="text/javascript">

        jQuery(document).ready(function($){

            $('body').on('click','#dcf', function(){
                if(!confirm("Are you sure?")) return;
                $('#cfl').html('<span style="font-weight:bold;color:#ddd">No file uploaded yet!</span>');
                $('#wpdmfile').val("");
            });

            // create the uploader and pass the config from above
            var uploader = new plupload.Uploader(<?php echo json_encode($plupload_init); ?>);

            // checks if browser supports drag and drop upload, makes some css adjustments if necessary
            uploader.bind('Init', function(up){
                var uploaddiv = jQuery('#plupload-upload-ui');

                if(up.features.dragdrop){
                    uploaddiv.addClass('drag-drop');
                    jQuery('#drag-drop-area')
                        .bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
                        .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });

                }else{
                    uploaddiv.removeClass('drag-drop');
                    jQuery('#drag-drop-area').unbind('.wp-uploader');
                }
            });

            uploader.init();

            // a file was added in the queue
            uploader.bind('FilesAdded', function(up, files){
                //var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);



                plupload.each(files, function(file){
                    jQuery('#filelist').append(
                        '<div class="file" id="' + file.id + '"><b>' +

                            file.name + '</b> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') ' +
                            '<div class="progress progress-success progress-striped active"><div class="bar fileprogress"></div></div></div>');
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

                jQuery('#' + file.id ).remove();
                var d = new Date();
                var ID = d.getTime();
                response = response.response;
                var data = response.split("|||");
                jQuery('#wpdmfile').val(data[0]);
                jQuery('#cfl').html('<div><strong>'+data[0]+'</strong> <a href="#" id="dcf" title="Delete Current File" style="position: absolute;right:0;top:0;height:32px;"><img src="<?php echo plugins_url('/download-manager/images/error.png'); ?>" /></a>').slideDown();



            });

        });

    </script>
    <div id="filelist"></div>

    <div class="clear"></div>
</div>

<div id="browse">
    <?php  if(current_user_can('access_server_browser')) wpdm_file_browser(); ?>
</div>

</div>

<script>
jQuery(function(){
        jQuery( "#ftabs" ).tabs();

        jQuery('#rmta').click(function(){
        var ID = 'file_' + parseInt(Math.random()*1000000);
        var file = jQuery('#rurl').val();
        var filename = file;
            jQuery('#rurl').val('');
        if(/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/|www\.)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test(file)==false){
                alert("Invalid url");
            return false;
            }

        jQuery('#wpdm-files').dataTable().fnAddData( [
            "<input type='hidden' id='in_"+ID+"' name='file[files][]' value='"+file+"' /><img id='del_"+ID+"' src='<?php echo plugins_url(); ?>/download-manager/images/minus.png' rel='del' align=left />",
            file,
            "<input style='width:99%' type='text' name='file[fileinfo]["+file+"][title]' value='"+filename+"' onclick='this.select()'>",
            "<input size='10' type='text' id='indpass_"+ID+"' name='file[fileinfo]["+file+"][password]' value=''> <img style='cursor: pointer;float: right;margin-top: -3px' class='genpass' onclick=\"return generatepass('indpass_"+ID+"')\" title='Generate Password' src=\"<?php echo plugins_url('download-manager/images/generate-pass.png'); ?>\" />"
        ] );
        jQuery('#wpdm-files tbody tr:last-child').attr('id',ID).addClass('cfile');

        jQuery("#wpdm-files tbody").sortable();

        jQuery('#'+ID).fadeIn();
        jQuery('#del_'+ID).click(function(){
            if(jQuery(this).attr('rel')=='del'){
                jQuery('#'+ID).removeClass('cfile').addClass('dfile');
                jQuery('#in_'+ID).attr('name','del[]');
                jQuery(this).attr('rel','undo').attr('src','<?php echo plugins_url(); ?>/download-manager/images/add.png').attr('title','Undo Delete');
            } else if(jQuery(this).attr('rel')=='undo'){
                jQuery('#'+ID).removeClass('dfile').addClass('cfile');
                jQuery('#in_'+ID).attr('name','file[files][]');
                jQuery(this).attr('rel','del').attr('src','<?php echo plugins_url(); ?>/download-manager/images/minus.png').attr('title','Delete File');
            }


        });


    });

});
 
</script>
<?php

do_action("wpdm_attach_file_metabox");