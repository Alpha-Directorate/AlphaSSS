<?php if(!defined('ABSPATH')) die('!');
if(get_option('users_can_register')){
?>
<script language="JavaScript" src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js"></script>

<form method="post" action="" id="registerform" name="registerform" class="login-form">
<input type="hidden" name="permalink" value="<?php the_permalink(); ?>" />
    <!-- div class="panel panel-primary">
<div class="panel-heading"><b>Register</b></div>
<div class="panel-body" -->
<?php global $wp_query; if(isset($_SESSION['reg_error'])&&$_SESSION['reg_error']!='') {  ?>
<div class="error alert alert-danger">
<b>Registration Failed!</b><br/>
<?php echo $_SESSION['reg_error']; $_SESSION['reg_error']=''; ?>
</div>
<?php } ?>

    <p class="form-group">
        <label class="control-label"><?php _e('Full Name','wpdmpro'); ?></label>
        <input class="form-control" required="required" type="text" size="20" class="input" id="displayname" value="<?php echo isset($_SESSION['tmp_reg_info']['display_name'])?$_SESSION['tmp_reg_info']['display_name']:''; ?>" name="wpdm_reg[display_name]">
    </p>
    <p class="form-group">
        <label class="control-label" for="user_login"><?php _e('Username','wpdmpro'); ?></label>
        <input class="form-control" required="required" type="text" size="20" class="required" id="user_login" value="<?php echo isset($_SESSION['tmp_reg_info']['user_login'])?$_SESSION['tmp_reg_info']['user_login']:''; ?>" name="wpdm_reg[user_login]">
    </p>
    <p class="form-group">
        <label class="control-label" for="user_email"><?php _e('E-mail','wpdmpro'); ?></label>
        <input class="form-control" required="required" type="email" size="25" class="required email" id="user_email" value="<?php echo isset($_SESSION['tmp_reg_info']['user_email'])?$_SESSION['tmp_reg_info']['user_email']:''; ?>" name="wpdm_reg[user_email]">
                      
    </p>

    <p class="form-group">
        <label class="control-label" for="user_login"><?php _e('Password','wpdmpro'); ?></label>
        <input class="form-control" required="required" type="password" size="20" class="required" id="password" value="" name="wpdm_reg[user_pass]">
    </p>

    <p class="form-group">
        <label class="control-label" for="user_login"><?php _e('Confirm Password','wpdmpro'); ?></label>
        <input class="form-control" required="required" type="password" size="20" class="required" equalto="#password" id="confirm_user_pass" value="" name="confirm_user_pass">
    </p>


    <?php do_action("wpdm_register_form"); ?>
    <?php do_action("register_form"); ?>


    <input type="hidden" value="" name="redirect_to">
    <p class=""><input type="submit" value="<?php _e('Join Now!','wpdmpro'); ?>" class="btn btn-success" id="wp-submit" name="wp-submit"></p>

    <!-- /div>
    </div -->
</form>

<script language="JavaScript">
<!--
  jQuery(function(){       
      jQuery('#registerform').validate({

            highlight: function(label) {
                jQuery(label).closest('.form-group').addClass('has-error');
            },
             success: function(label) {
                label.closest('.form-group').addClass('has-success');
                label.remove();

            }
      });
  });
//-->
</script>

<?php } else _e("Registration is disabled!", "wpdmpro"); ?>