<div style="max-width: 300px;margin: 0 auto">
<?php

if(isset($_GET['redirect_to'])) $redirect = $_GET['redirect_to'];

if(is_user_logged_in()){

    do_action("wpdm_user_logged_in","<div class='alert alert-success'>".__("You are already logged in.","wpdmpro")." <a href='".wp_logout_url()."'>".__("Logout","wpdmpro")."</a></div>");

} else { ?>

<form name="loginform" id="loginform" action="" method="post" class="login-form" style="margin: 0">

<input type="hidden" name="permalink" value="<?php the_permalink(); ?>" />
    <div class="panel panel-default">
        <div class="panel-heading"><b><?php _e("LogIn","wpdmpro");?></b></div>
        <div class="panel-body">
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
            <div class="row">
            <p class="login-remember col-md-8"><label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="39" /> <?php _e('Remember Me','wpdmpro'); ?></label></p>
            <p class="login-submit col-md-4 text-right">
                <button type="submit" name="wp-submit" id="wp-submit" tabindex="40" class="btn btn-primary btn-sm"><i class="fa fa-lock"></i> &nbsp; <?php _e('Log In','wpdmpro'); ?></button>
                <input type="hidden" name="redirect_to" value="<?php echo isset($redirect)?$redirect:$_SERVER['REQUEST_URI']; ?>" />
                
            </p></div>
            </div><div class="panel-footer">
            <?php _e('Forgot Password?','wpdmpro'); ?> <a href="<?php echo site_url('/wp-login.php?action=lostpassword'); ?>"><?php _e('Request New Password.','wpdmpro'); ?></a>
        </div>
            </div>
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