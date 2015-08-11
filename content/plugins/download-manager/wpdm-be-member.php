<?php

    $invoice = wpdm_query_var('invoice','txt')?wpdm_query_var('invoice','txt'):'';
    if($invoice!=''){
    $oorder = new Order();
    $order = $oorder->GetOrder($invoice);
    if($order->uid!=0) $invoice = '';
    }
?>



<div class="w3eden" style="max-width: 350px;margin: 0 auto">


    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs nav-justified">
                <li class="active"><a href="#wpdmlogin" data-toggle="tab"><?php _e('Log In','wpdmpro'); ?></a></li>
                <li><a href="#wpdmregister" data-toggle="tab"><?php _e('Register','wpdmpro'); ?></a></li>
            </ul>
            <div class="tab-content">
            <div class="tab-pane active" id="wpdmlogin">
                <?php if(isset($_SESSION['reg_warning'])&&$_SESSION['reg_warning']!=''): ?>  <br>

                        <div class="alert alert-warning" align="center" style="font-size:10pt;">
                            <?php echo $_SESSION['reg_warning']; unset($_SESSION['reg_warning']); ?>
                        </div>

                <?php endif; ?>

                <?php if(isset($_SESSION['sccs_msg'])&&$_SESSION['sccs_msg']!=''): ?><br>

                        <div class="alert alert-success" align="center" style="font-size:10pt;">
                            <?php echo $_SESSION['sccs_msg'];  unset($_SESSION['sccs_msg']); ?>
                        </div>

                <?php endif; ?>
                <?php if(is_user_logged_in()){

                    do_action("wpdm_user_logged_in","<div class='alert alert-success'>".__("You are already logged in.","wpdmpro")." <a href='".wp_logout_url()."'>".__("Logout","wpdmpro")."</a></div>");

                } else {


                    ?>

                    <form name="loginform" id="loginform" action="" method="post" class="login-form" style="margin: 0">

                        <input type="hidden" name="permalink" value="<?php the_permalink(); ?>" />

                                <?php global $wp_query; if(isset($_SESSION['login_error'])&&$_SESSION['login_error']!='') {  ?>
                                    <div class="error alert alert-danger" >
                                        <b><?php _e('Login Failed!','wpdmpro'); ?></b><br/>
                                        <?php echo preg_replace("/<a.*?<\/a>\?/i","",$_SESSION['login_error']); $_SESSION['login_error']=''; ?>
                                    </div>
                                <?php } ?>
                                <p class="login-username">
                                    <label for="user_login"><?php _e('Username','wpdmpro'); ?></label>
                                    <input type="text" name="wpdm_login[log]" id="user_login" class="form-control input required text" value="" size="20" tabindex="38" />
                                </p>
                                <p class="login-password">
                                    <label for="user_pass"><?php _e('Password','wpdmpro'); ?></label>
                                    <input type="password" name="wpdm_login[pwd]" id="user_pass" class="form-control input required password" value="" size="20" tabindex="39" />
                                </p>

                                <?php do_action("wpdm_login_form"); ?>
                                <?php do_action("login_form"); ?>

                                <p class="login-remember"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="39" /> <?php _e('Remember Me','wpdmpro'); ?></label></p>
                                <p class="login-submit">
                                    <input type="submit" name="wp-submit" id="wp-submit" value="Log In" tabindex="40" class="btn btn-primary" />
                                    <input type="hidden" name="redirect_to" value="<?php echo isset($redirect)?$redirect:$_SERVER['REQUEST_URI']; ?>" />

                                </p>

                                <?php _e('Forgot Password?','wpdmpro'); ?> <a href="<?php echo site_url('/wp-login.php?action=lostpassword'); ?>"><?php _e('Request New Password.','wpdmpro'); ?></a>

                    </form>


                    <script language="JavaScript">
                        <!--
                        jQuery(function(){
                            jQuery('#loginform').validate({
                                highlight: function(label) {
                                    jQuery(label).closest('.control-group').addClass('error');
                                },
                                success: function(label) {
                                    label
                                        .addClass('valid')
                                        .closest('.control-group').addClass('success');
                                }
                            });
                        });
                        //-->
                    </script>

                <?php } ?></div>
            <div class="tab-pane" id="wpdmregister">
                <?php if(isset($_SESSION['reg_warning'])&&$_SESSION['reg_warning']!=''): ?>  <br>

                        <div class="alert alert-warning" align="center" style="font-size:10pt;">
                            <?php echo $_SESSION['reg_warning']; unset($_SESSION['reg_warning']); ?>
                        </div>

                <?php endif; ?>

                <?php if(isset($_SESSION['sccs_msg'])&&$_SESSION['sccs_msg']!=''): ?><br>

                        <div class="alert alert-success" align="center" style="font-size:10pt;">
                            <?php echo $_SESSION['sccs_msg'];  unset($_SESSION['sccs_msg']); ?>
                        </div>

                <?php endif; ?>
                <?php include("wpdm-reg-form.php"); ?></div>
            </div>
        </div>



</div>
<?php if(isset($_REQUEST['reseted'])): ?>
<div class="row">
<div class="col-md-12">
<div class="alert alert-success"><?php echo $_COOKIE['global_success'];?></div>
</div>
</div>
<?php unset($_COOKIE['global_success']); endif; ?>

</div>

<style>
    .tab-content{
        padding: 20px;
        border: 1px solid #ddd;
        border-top: 0;
    }
</style>