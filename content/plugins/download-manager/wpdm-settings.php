<link rel="stylesheet" type="text/css" href="<?php echo plugins_url('/download-manager/bootstrap/css/bootstrap.css');?>" />


<style>

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



    .w3eden .nav-pills a{
        background: #f5f5f5;
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
        border:1px solid #18578E;
    }

    .panel-heading .btn.btn-primary:hover{
        margin-top: -4px;
        margin-right: -6px;
        border-radius: 3px;
        border:1px solid #18578E;
        background-image: linear-gradient(to bottom, #F5F5F5 0px, #E1E1E1 100%);

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

    .w3eden .panel-primary {
        border-color: #2080D3;
    }

    .w3eden .nav-pills li.active a,
    .btn-primary,
    .w3eden .panel-primary > .panel-heading{
        background-image: linear-gradient(to bottom, #2081D5 0px, #1B6CB2 100%) !important;
    }
    .w3eden .panel-default > .panel-heading {
        background-image: linear-gradient(to bottom, #F5F5F5 0px, #E1E1E1 100%);
        background-repeat: repeat-x;
        border-bottom-color: #cccccc !important;
    }

    ul#navigation {
        border-bottom: 1px solid #999999;
    }

    #tabs a{
        border-radius: 3px !important;
    }

    .form-control:focus{
        -webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,0.075),0 0 3px rgba(102,175,233,0.6) !important;
        box-shadow:inset 0 1px 1px rgba(0,0,0,0.075),0 0 3px rgba(102,175,233,0.6) !important;
    }
    .chzn-drop{
        width: 100% !important;
    }


</style>

<script type="text/javascript" src="<?php echo plugins_url();?>/download-manager/js/jquery.form.js"></script>
<link rel="stylesheet" href="<?php echo plugins_url('/download-manager/css/chosen.css'); ?>" />


<div class="wrap w3eden">






<style>
ul#navigation {
border-bottom: 1px solid #999999;
}
</style>

<div style="clear: both;"></div>
<div style="max-width: 800px;margin:-3px 30px 0 30px;">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success text-center" style="border-radius: 3px">
             <a class="text-success" href='https://wordpress.org/support/view/plugin-reviews/download-manager?rate=5#postform' title="Please consider it when you get some free moments" target="_blank">A 5* rating will inspire me a lot. Thanks :)</a>
         </div>
        </div>
    </div>
</div>   
    <form method="post" id="wdm_settings_form">
       <?php wp_nonce_field('wpdm-'.NONCE_KEY,'wpdmsettingsnonce'); ?>
        
 <div style="max-width: 800px;margin:0 30px" class="panel panel-primary">
     <div class="panel-heading"><button type="submit" class="btn btn-primary pull-right"><span class="pull-left wpdm-loading wpdm-ssb" style="margin: 0.1em 5px 0 0"></span> Save Settings</button><h3 class="h">&nbsp;&nbsp;Download Manager Settings <div class="pull-left wpdm-loading" id="wdms_loading"></div></h3>

     </div>
<div class="panel-body">
<div class="container-fluid">
 
<div class="row"><div class="col-md-3">
     <ul id="tabs" class="nav nav-pills nav-stacked">
         <?php render_settings_tabs($tab=isset($_GET['tab'])?esc_attr($_GET['tab']):'basic'); ?>
     </ul>
        
         
        </div><div class="col-md-9">
     <div class="tab-content">
<div onclick="jQuery(this).slideUp();" class="alert alert-info" style="display: none" id="message"></div>

<input type="hidden" name="task" id="task" value="wdm_save_settings" />
<input type="hidden" name="action" id="action" value="wdm_settings" />
<input type="hidden" name="section" id="section" value="basic" />
<div id="fm_settings">
<?php include('settings/basic.php'); ?>
</div> <br>
<br>

         <button type="submit" class="btn btn-primary"><span class="pull-left wpdm-loading wpdm-ssb" style="margin: 0.1em 5px 0 0"></span> Save Settings</button>

<br>
 
</div>
    </div>

</div>
</div>
</div>
        
 </div>

    </form>

<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('select').chosen();
    jQuery("ul#tabs li").click(function() {

    });
    jQuery('#message').removeClass('hide').hide();
    jQuery("ul#tabs li a").click(function() {
        ///jQuert("ul#tabs li").removeClass('active')
        jQuery("ul#tabs li").removeClass("active");
        jQuery(this).parent('li').addClass('active');
        jQuery('#wdms_loading').addClass('wpdm-spin');
        jQuery(this).append('<span class="wpdm-loading wpdm-spin pull-right" id="wpdm-lsp"></span>')
        var section = this.id;
        jQuery.post(ajaxurl,{action:'wdm_settings',section:this.id},function(res){
            jQuery('#fm_settings').html(res);
            jQuery('#section').val(section)
            jQuery('#wdms_loading').removeClass('wpdm-spin');
            jQuery('select').chosen();
            window.history.pushState({"html":res,"pageTitle":"response.pageTitle"},"", "edit.php?post_type=wpdmpro&page=settings&tab="+section);
            jQuery('#wpdm-lsp').fadeOut(function(){
                jQuery(this).remove();
            });
        });
        return false;
    });
    
    window.onpopstate = function(e){
    if(e.state){
        jQuery("#fm_settings").html(e.state.html);
        //document.title = e.state.pageTitle;
    }
    };
    
    <?php if(isset($_GET['tab'])&&$_GET['tab']!=''){ ?>
        jQuery("ul#tabs li").removeClass("active");
        jQuery('#wdms_loading').addClass('wpdm-spin');
        jQuery('#<?php echo esc_attr($_GET['tab']); ?>').parents().addClass("active");
        var section = '<?php echo esc_attr($_GET['tab']);?>';
        jQuery.post(ajaxurl,{action:'wdm_settings',section:section},function(res){
            jQuery('#fm_settings').html(res);
            jQuery('#section').val(section)
            jQuery('#wdms_loading').removeClass('wpdm-spin');
        });
    <?php } ?>
    
    jQuery('#wdm_settings_form').submit(function(){
       
       jQuery(this).ajaxSubmit({
        url:ajaxurl,
        beforeSubmit: function(formData, jqForm, options){
          jQuery('.wpdm-ssb').addClass('wpdm-spin');
          jQuery('#wdms_loading').addClass('wpdm-spin');
        },
        success: function(responseText, statusText, xhr, $form){
          jQuery('#message').html("<p>"+responseText+"</p>").slideDown();
          //setTimeout("jQuery('#message').slideUp()",4000);
          jQuery('.wpdm-ssb').removeClass('wpdm-spin');
          jQuery('#wdms_loading').removeClass('wpdm-spin');
        }   
       });
        
       return false; 
    });
    
   
});
 
</script>

