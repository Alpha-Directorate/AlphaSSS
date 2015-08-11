<link rel="stylesheet" href="<?php echo plugins_url('/download-manager/css/chosen.css'); ?>" />
<script language="JavaScript" src="<?php echo plugins_url('/download-manager/js/chosen.jquery.min.js'); ?>"></script>
<script language="JavaScript" src="<?php echo plugins_url('/download-manager/bootstrap/js/bootstrap.min.js');?>" ></script>
<script type="text/javascript" src="<?php echo plugins_url();?>/download-manager/js/jquery.form.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url('/download-manager/bootstrap/css/bootstrap.css');?>" />
<link href='http://fonts.googleapis.com/css?family=Roboto:400,300,100' rel='stylesheet' type='text/css'>
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
<style>
    .w3eden .lead,
    .w3eden .btn,
    .w3eden p{
        font-family: 'Roboto', sans-serif;
        font-weight: 100;
    }

    .r{
        font-family: 'Roboto', sans-serif;
        font-weight: 300;
        font-size: 11pt;
    }

    .r b{
        display: block;
        clear: both;
        margin-bottom: 5px;
    }

    input{
        padding: 7px;
    }
    #wphead{
        border-bottom:0px;
    }
    #screen-meta-links{
        display: none;
    }
    .wrap{
        margin: 0px;
        padding: 0px;
    }
    #wpbody{
        margin-left: -19px;
    }
    select{
        min-width: 150px;
    }
    .media-body b{
        font-size: 11pt;
        margin-bottom: 5px;
    }

    .media .btn-success{
        margin-top: 3px;
    }

    .wpdm-loading {
        background: url('<?php  echo plugins_url('download-manager/images/wpdm-settings.png'); ?>') center center no-repeat;
        width: 16px;
        height: 16px;
        /*border-bottom: 2px solid #2a2dcb;*/
        /*border-left: 2px solid #ffffff;*/
        /*border-right: 2px solid #c30;*/
        /*border-top: 2px solid #3dd269;*/
        /*border-radius: 100%;*/

    }

    .w3eden .btn{
        border-radius: 0.2em !important;
    }
    .well{ box-shadow: none !important; background: #FFFFFF !important; } .btn{ border: 0 !important; }

    .w3eden .nav-pills a{
        background: #f5f5f5;
    }

    #addonmodal{ background: rgba(0,0,0,0.7); z-index: 9999; }

    #addonmodal .modal-dialog{
        margin-top: 100px;

    }

    .w3eden .form-control,
    .w3eden .nav-pills a{
        border-radius: 0.2em !important;
        box-shadow: none !important;
        font-size: 9pt !important;
    }

    .wpdm-spin{
        -webkit-animation: spin 2s infinite linear;
        -moz-animation: spin 2s infinite linear;
        -ms-animation: spin 2s infinite linear;
        -o-animation: spin 2s infinite linear;
        animation: spin 2s infinite linear;
    }

    @keyframes "spin" {
        from {
            -webkit-transform: rotate(0deg);
            -moz-transform: rotate(0deg);
            -o-transform: rotate(0deg);
            -ms-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -webkit-transform: rotate(359deg);
            -moz-transform: rotate(359deg);
            -o-transform: rotate(359deg);
            -ms-transform: rotate(359deg);
            transform: rotate(359deg);
        }

    }

    @-moz-keyframes spin {
        from {
            -moz-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -moz-transform: rotate(359deg);
            transform: rotate(359deg);
        }

    }

    @-webkit-keyframes "spin" {
        from {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -webkit-transform: rotate(359deg);
            transform: rotate(359deg);
        }

    }

    @-ms-keyframes "spin" {
        from {
            -ms-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -ms-transform: rotate(359deg);
            transform: rotate(359deg);
        }

    }

    @-o-keyframes "spin" {
        from {
            -o-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        to {
            -o-transform: rotate(359deg);
            transform: rotate(359deg);
        }

    }

    .panel-heading h3.h{
        font-size: 11pt;
        font-weight: 700;
        margin: 0;
        padding: 5px 10px;
        font-family: 'Open Sans';
    }

    .panel-heading .btn.btn-primary{
        margin-top: -4px;
        margin-right: -6px;
        border-radius: 3px;
        border:1px solid rgba(255,255,255,0.8);
        -webkit-transition: all 400ms ease-in-out;
        -moz-transition: all 400ms ease-in-out;
        -o-transition: all 400ms ease-in-out;
        transition: all 400ms ease-in-out;
    }

    .panel-heading .btn.btn-primary:hover{
        margin-top: -4px;
        margin-right: -6px;
        border-radius: 3px;
        border:1px solid rgba(255,255,255,1);

    }

    .alert-info {
        background-color: #DFECF7 !important;
        border-color: #B0D1EC !important;
    }

    ul.nav li a:active,
    ul.nav li a:focus,
    ul.nav li a{
        outline: none !important;
    }

    .w3eden .nav-pills li.active a,
    .btn-primary,
    .w3eden .panel-primary > .panel-heading{
        background-image: linear-gradient(to bottom, #2081D5 0px, #1B6CB2 100%) !important;
    }
    .w3eden .panel-default > .panel-heading {
        background-image: linear-gradient(to bottom, #F5F5F5 0px, #E1E1E1 100%);
        background-repeat: repeat-x;
    }

    #modalcontents .wrap h2{ display: none; }

</style>

<div class="wrap w3eden">


    <div class="container" style="margin-top: 10px">
         <div class="row">

             <div class="col-md-12">
                 <div class="well text-center">
                     <img src="<?php echo WPDM_BASE_URL; ?>images/wpdm-welcome.png" />
                 </div>

             </div>

             <?php
             global $wpdb;
             $tf = 0;
             if($wpdb->get_var("SHOW TABLES LIKE 'ahm_files'") == 'ahm_files')
             $tf = $wpdb->get_var("select count(*) from `ahm_files`");
             if($tf>0){
                 ?>
                 <div class="col-md-12">

                     <div class="alert alert-success" style="border-radius: 3px">
                         <b>Migrate Data</b><br/>
                         Looks like you were using v2.6 earlier and there are some data already, anyhow you can migrate those data to 2.7 from <a href="<?php echo admin_url('edit.php?post_type=wpdmpro&page=settings&tab=m24x'); ?>"><b>Admin Menu <i class="fa fa-angle-double-right"></i> Downloads <i class="fa fa-angle-double-right"></i> Settings <i class="fa fa-angle-double-right"></i> Migrate Tab</b></a>
                         <br/>You also can get back to v2.6. To do that, delete current installation of v2.7, <a href="https://downloads.wordpress.org/plugin/download-manager.2.6.96.zip">Download v2.6.96</a> and install.
                     </div>


                 </div>
             <?php } ?>

             <?php if(!function_exists('wpdm_tinymce')){ ?>
             <div class="col-md-12 lead text-center">Lets install following add-ons to make your WordPress Download Manager more awesome<br/></div>
                 <div class="col-md-4 col-sm-6 col-xs-12">
                     <div class="panel panel-default">
                         <div style="min-height: 130px;height: 130px;overflow: hidden" class="panel-body">
                             <div class="media">
                                 <a class="pull-left" href="#">
                                     <img width="100px" src="http://cdn.wpdownloadmanager.com/wp-content/plugins/download-manager/cache//tinymce-button-200x200.png" class="img-rounded">
                                 </a>
                                 <div class="media-body">
                                     <b><a target="_blank" href="http://www.wpdownloadmanager.com/download/advanced-tinymce-button/">Advanced TinyMCE button</a></b><br>
                                     <p>Advanced TinyMCE button add-on will add a button with tinymce editor from where ...</p>
                                 </div>
                             </div>
                         </div>
                         <div style="line-height: 30px;" class="panel-footer">
                             <div style="margin-top: -2px" class="pull-right">
                                 <a style="border: 0;border-radius: 2px" href="#" data-target="#addonmodal" data-backdrop="true" rel="15665" data-toggle="modal" class="btn btn-sm btn-primary btn-install"><i class="fa fa-download"></i> &nbsp;Download &amp; Install</a>
                             </div>
                              <span style="font-size: 11px;" class="label label-info"><i class="fa fa-th"></i> &nbsp;642.87 KB</span>
                         </div>
                     </div>
                 </div>

                 <div class="col-md-4 col-sm-6 col-xs-12">
                     <div class="panel panel-default">
                         <div style="min-height: 130px;height: 130px;overflow: hidden" class="panel-body">
                             <div class="media">
                                 <a class="pull-left" href="#">
                                     <img width="100px" src="http://www.wpdownloadmanager.com/wp-content/plugins/download-manager/cache//wpdm-extended-shortcode-200x200.png" class="img-rounded">
                                 </a>
                                 <div class="media-body">
                                     <b><a target="_blank" href="http://www.wpdownloadmanager.com/download/wpdm-extended-short-codes/">WPDM Extended Short-codes</a></b><br>
                                     <p>WPDM Extended Short-codes add-on will give you better experience in using WordPr...</p>
                                 </div>
                             </div>
                         </div>
                         <div style="line-height: 30px;" class="panel-footer">
                             <div style="margin-top: -2px" class="pull-right">
                                 <a style="border: 0;border-radius: 2px" href="#" data-target="#addonmodal" data-backdrop="true" rel="15691" data-toggle="modal" class="btn btn-sm btn-primary btn-install"><i class="fa fa-download"></i> &nbsp;Download &amp; Install</a>
                             </div>
                              <span style="font-size: 11px;" class="label label-info"><i class="fa fa-th"></i> &nbsp;310.17 KB</span>
                         </div>
                     </div>
                 </div>


                 <div class="col-md-4 col-sm-6 col-xs-12">
                     <div class="panel panel-default">
                         <div style="min-height: 130px;height: 130px;overflow: hidden" class="panel-body">
                             <div class="media">
                                 <a class="pull-left" href="#">
                                     <img width="100px" src="http://www.wpdownloadmanager.com/wp-content/plugins/download-manager/cache//wpdm-page-templates1-200x200.png" class="img-rounded">
                                 </a>
                                 <div class="media-body">
                                     <b><a target="_blank" href="http://www.wpdownloadmanager.com/download/wpdm-page-templates/">WPDM Page Templates</a></b><br>
                                     <p>WPDM Page Templates add-on will help you to apply page type templates ( like ful...</p>
                                 </div>
                             </div>
                         </div>
                         <div style="line-height: 30px;" class="panel-footer">
                             <div style="margin-top: -2px" class="pull-right">
                                 <a style="border: 0;border-radius: 2px" href="#" data-target="#addonmodal" data-backdrop="true" rel="15739" data-toggle="modal" class="btn btn-sm btn-primary btn-install"><i class="fa fa-download"></i> &nbsp;Download &amp; Install</a>
                             </div>
                              <span style="font-size: 11px;" class="label label-info"><i class="fa fa-th"></i> &nbsp;26.51 KB</span>
                         </div>
                     </div>
                 </div>



             <?php } ?>
            <div style="clear:both"></div>
             <div class="col-md-12">
                 <div class="well">
                     <div class="media">
                         <div class="pull-right">
                             <a href="http://www.wpdownloadmanager.com/downloads/free-add-ons/" class="btn btn-success">Explore Free Add-ons <i class="fa fa-angle-double-right"></i></a>
                         </div>
                         <div class="media-body">
                             <b>Free Add-ons</b><br/>
                             There are more free add-ons
                         </div> </div>
                 </div>
             </div>

             <div class="col-md-12 lead">
                 <h3>What's New?</h3>
                 What new with WordPress Download Manager v2.7:
             </div>

             <div class="col-md-4 r">

                     <b>Custom Post Type</b>
                     WordPress Download Manager 2.7 using custom post type now, so you can enjoy all build-in features of WordPress.

             </div>
             <div class="col-md-4 r">

                 <b>Add-On Support</b>
                 Now you can use free or pro add-ons with WordPress Download Manager v2.7, earlier which were only available for wpdm pro.
             </div>

             <div class="col-md-4 r">

                 <b>Theme Support</b>
                 As WordPress Download Manager 2.7 using custom post type now, you will not need any extra work to use it with any WordPress Theme.
             </div>
            
            <div class="col-md-12"><br style="clear:both"/><hr/>
                 <h3>v2.7.95</h3>
                 <p class="lead">What new with WordPress Download Manager v2.7.95:</p>
                 <ul style="list-style:square !important">
                     <li>Added new option for sanitizing file names to avoid illegal chars</li>
                     <li>Fixed issue with open with browser option</li>
                     <li>Fixed issue with file type icons selection for all files short-codet</li>
                     <li>Fixed issue with order expiration check</li>
                     <li>Fixed issue with purchased items access at front-end</li>
                     <li>Added new option to recalculate total sales of a product</li>
                 </ul>
             </div>

             <div class="col-md-12 lead">
                 <br/>
                 <hr/><br/>
                 Lets start: Admin Menu <i class="fa fa-angle-double-right"></i> <a href="<?php echo admin_url('edit.php?post_type=wpdmpro'); ?>">Downloads</a> <i class="fa fa-angle-double-right"></i> <a href="<?php echo admin_url('post-new.php?post_type=wpdmpro'); ?>">Add New</a>
             </div>

         </div>

    </div>
    <div class="modal fade" id="addonmodal" tabindex="-1" role="dialog" aria-labelledby="addonmodalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">Add-On Installer</h4>
                </div>
                <div class="modal-body" id="modalcontents">
                    <i class="fa fa-spinner fa-spin"></i> Please Wait...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <a type="button" id="prcbtn" target="_blank" href="http://www.wpdownloadmanager.com/cart/" class="btn btn-success" style="display: none" onclick="jQuery('#addonmodal').modal('hide')">Checkout</a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    jQuery(function(){


        jQuery(".btn-install, .btn-purchase").click(function(){
            jQuery('#modalcontents').html('<i class="fa fa-spinner fa-spin"></i> Please Wait...');
        });
        jQuery('#addonmodal').on('shown.bs.modal', function (e) {
            if(jQuery(e.relatedTarget).hasClass('btn-install')){
                jQuery('.modal-dialog').css('width','500px');
                jQuery('.modal-footer .btn-danger').html('Close');
                jQuery('#modalcontents').css('padding','20px').css('background','#ffffff');
                jQuery.post(ajaxurl,{action:'wpdm-install-addon', addon: e.relatedTarget.rel}, function(res){
                    notice = "<div class='alert alert-info'>For any reason, if auto installation failed, close this popup, click on add-on title, download the add-on from our site, then install manually as you do for regular plugins.</div>"
                    jQuery('#modalcontents').html(res.replace('Return to Plugin Installer','')+notice);
                })
            }

            if(jQuery(e.relatedTarget).hasClass('btn-purchase')){
                jQuery('.modal-dialog').css('width','800px');
                jQuery('.modal-footer').css('margin',0);
                jQuery('.modal-footer .btn-danger').html('<i class="fa fa-spinner fa-spin"></i> Please Wait...');
                jQuery('#modalcontents').css('padding',0).css('background','#f2f2f2').html("<iframe onload=\"jQuery('.modal-footer .btn-danger').html('Continue Shopping...');jQuery('#prcbtn').show();\" style='width: 100%;padding-top: 20px; background: #f2f2f2;height: 300px;border: 0' src='http://www.wpdownloadmanager.com/?addtocart="+e.relatedTarget.rel+"'></iframe>");
            }
        })


    });
</script>


