
 <style>
 .frm td{
     padding:5px;
     border-bottom: 1px solid #eeeeee;
    
     font-size:10pt;
     
 }
 h4{
     color: #336699;
     margin-bottom: 0px;
 }
 em{
     color: #888;
 }
 .input-group .form-control:first-child{
     border-top-right-radius: 0 !important;
     border-bottom-right-radius: 0 !important;
 }
 .input-group *:last-child{
     border-top-left-radius: 0 !important;
     border-bottom-left-radius: 0 !important;
 }
 </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">



                <div class="panel panel-default">
                    <div class="panel-heading"><?php echo __('Messages','wpdmpro'); ?></div>
                    <div class="panel-body">


                        <div class="form-group">
                            <label><?php echo __('Permission Denied Message for Packages:','wpdmpro'); ?></label>
                                <input type=text class="form-control" name="wpdm_permission_msg" value="<?php echo htmlspecialchars(stripslashes(get_option('wpdm_permission_msg','Access Denied'))); ?>" />
                         </div>





                         <div class="form-group">
                                    <label><?php echo __('Login Required Message:','wpdmpro'); ?></label>
                             <textarea class="form-control" cols="70" rows="6" name="wpdm_login_msg"><?php echo get_option('wpdm_login_msg')?stripslashes(get_option('wpdm_login_msg')):"<a href='".wp_login_url()."' >Please login to download</a>"; ?></textarea><br>

                         </div>
                       </div>
                </div> 
                
                <div class="panel panel-default">
                    <div class="panel-heading"><?php echo __('Server File Browser','wpdmpro'); ?></div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label><?php echo __('Server File Browser Base Dir:','wpdmpro'); ?></label>
                            <div class="input-group">
                            <input type=text class="form-control" id="_wpdm_file_browser_root" name="_wpdm_file_browser_root" value="<?php echo htmlspecialchars(stripslashes(get_option('_wpdm_file_browser_root',ABSPATH))); ?>" />
                                <span class="input-group-btn">
                                    <button class="btn btn-default ttip" title="<?php _e('Reset Base Dir'); ?>" type="button" onclick="jQuery('#_wpdm_file_browser_root').val('<?php echo rtrim(ABSPATH,'/'); ?>');"><i class="fa fa-repeat"></i></button>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo __('File Browser Access:','wpdmpro');  ?></label><br/>
                            <select style="width: 100%" name="_wpdm_file_browser_access[]" multiple="multiple" data-placeholder="<?php _e('Who will have access to server file browser','wpdmpro'); ?>">
                                <?php

                                $currentAccess = maybe_unserialize(get_option( '_wpdm_file_browser_access', array('administrator')));
                                $selz = '';

                                ?>

                                <?php
                                global $wp_roles;
                                $roles = array_reverse($wp_roles->role_names);
                                foreach( $roles as $role => $name ) {

                                    $ro = get_role($role);

                                    if(isset($ro->capabilities['edit_posts']) && $ro->capabilities['edit_posts']==1){

                                    if(  $currentAccess ) $sel = (in_array($role,$currentAccess))?'selected=selected':'';
                                    else $sel = '';



                                    ?>
                                    <option  value="<?php echo $role; ?>" <?php echo $sel  ?>> <?php echo $name; ?></option>
                                <?php }} ?>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading"><?php echo __('Upload Settings','wpdmpro'); ?></div>
                    <div class="panel-body">
                        <div class="form-group">
                            <input type="hidden" value="0" name="__wpdm_sanitize_filename" />
                            <label><input style="margin: 0 10px 0 0" <?php checked(1, get_option('__wpdm_sanitize_filename',0)); ?> type="checkbox" value="1" name="__wpdm_sanitize_filename"><?php _e('Sanitize Filename','wpdmpro'); ?></label><br/>
                            <em><?php _e('Check the option if you want to sanitize uploaded file names to remove illegal chars','wpdmpro'); ?></em>
                            <br/>

                        </div>
                    </div>
                </div>
                
                <div class="panel panel-default">
                    <div class="panel-heading"><?php echo __('File Download','wpdmpro'); ?></div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label><?php echo __('Download Speed:','wpdmpro'); ?></label>
                            <div class="input-group">
                            <input type=text class="form-control" name="__wpdm_download_speed" value="<?php echo intval(get_option('__wpdm_download_speed',4096)); ?>" />
                            <span class="input-group-addon">KB</span>
                            </div>
                        </div>
                        <hr/>
                        <em class="note"><?php _e('If you get broken download, then try enabling/disabling following options, as sometimes server may not support output buffering or partial downloads','wpdmpro'); ?>:</em>
                        <hr/>
                        <div class="form-group">
                            <label><?php _e('Resumable Downloads','wpdmpro'); ?></label><br/>
                            <select name="__wpdm_download_resume">
                                        <option value="1"><?php _e("Enabled","wpdmpro"); ?></option>
                                        <option value="2" <?php selected(get_option('__wpdm_download_resume'), 2); ?>><?php _e("Disabled","wpdmpro"); ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?php _e('Output Buffering','wpdmpro'); ?></label><br/>
                            <select name="__wpdm_support_output_buffer">
                                        <option value="1"><?php _e("Enabled","wpdmpro"); ?></option>
                                        <option value="0" <?php selected(get_option('__wpdm_support_output_buffer'), 0); ?>><?php _e("Disabled","wpdmpro"); ?></option>
                            </select>
                        </div>

                        <div class="form-group"><hr/>
                            <input type="hidden" value="0" name="__wpdm_open_in_browser" />
                            <label><input style="margin: 0 10px 0 0" <?php checked(1, get_option('__wpdm_open_in_browser',0)); ?> type="checkbox" value="1" name="__wpdm_open_in_browser"><?php _e('Open in Browser','wpdmpro'); ?></label><br/>
                            <em><?php _e('Try to Open in Browser instead of download when someone clicks on download link','wpdmpro'); ?></em>
                            <br/>

                        </div>
                    </div>
                </div>


                <div class="panel panel-default">
                    <div class="panel-heading"><?php _e("Misc Settings","wpdmpro"); ?></div>
                    <div class="panel-body">

                        <table cellpadding="5" cellspacing="0" class="frm" width="100%">

                             

                            <tr>
                                <td>
                                    <?php _e("Twitter Bootstrap","wpdmpro"); ?></td><td>
                                    <select name="__wpdm_twitter_bootstrap">
                                        <option value="active"><?php _e("Active","wpdmpro"); ?></option>
                                        <option value="djs" <?php selected(get_option('__wpdm_twitter_bootstrap'), 'djs'); ?>><?php _e("Disable JS","wpdmpro"); ?></option>
                                        <option value="dcss" <?php selected(get_option('__wpdm_twitter_bootstrap'), 'dcss'); ?>><?php _e("Disable CSS","wpdmpro"); ?></option>
                                        <option value="dall" <?php selected(get_option('__wpdm_twitter_bootstrap'), 'dall'); ?>><?php _e("Disable Both","wpdmpro"); ?></option>
                                    </select>

                                </td>
                            </tr>

                            <?php do_action('basic_settings'); ?>

                        </table>

                    </div>
                    <div class="panel-footer">

                    </div>
                </div>



            </div>
        </div>
    </div>



