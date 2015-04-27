<?php
class DmConfirmEmail_Models_PluginPage {
    function __construct() {
        add_action('admin_init', array($this, 'adminInit'));
        add_action('admin_menu', array($this, 'pluginPage'));
    }

    public function adminInit() {
        register_setting('dm_confirm_email_options', DmConfirmEmail::PLUGIN_ALIAS, array($this, 'validate'));
    }

    public function pluginPage() {
        add_options_page('DM Confirm Email', 'DM Confirm Email', 'manage_options',
            'dm_confirm_email_options', array($this, 'pluginPageContent'));
    }

    public function validate($input) {
        $valid = array();

        $valid['email_subject'] = sanitize_text_field($input['email_subject']);
        $valid['email_text'] = esc_textarea(wpautop($input['email_text']));
        $valid['email_ishtml'] = sanitize_text_field($input['email_ishtml']);
        $valid['expiry_time'] = sanitize_text_field($input['expiry_time']);
        $valid['success_message'] = esc_html($input['success_message']);
        $valid['confirmed_message'] = esc_html($input['confirmed_message']);
        $valid['failed_message'] = esc_html($input['failed_message']);
        // Welcome image
        $valid['send_welcome'] = esc_html($input['send_welcome']);
        $valid['welcome_email_subject'] = sanitize_text_field($input['welcome_email_subject']);
        $valid['welcome_message'] = esc_textarea(wpautop($input['welcome_message']));
        $valid['send_welcome_html'] = esc_html($input['send_welcome_html']);

        // User Pass Email
        $valid['user_pass_subject'] = sanitize_text_field($input['user_pass_subject']);
        $valid['user_pass_message'] = esc_textarea(wpautop($input['user_pass_message']));

        // Set ishtml false if not checked
        if(!isset($input['email_ishtml']))
            $valid['email_ishtml'] = 'false';

        // Set send_welcome false if not checked
        if(!isset($input['send_welcome']))
            $valid['send_welcome'] = 'false';

        // Check if welcome message is html
        if(!isset($input['send_welcome_html']))
            $valid['send_welcome_html'] = 'false';

        foreach($valid as $k => $v) {
            // Ignore
            if($k == 'welcome_email_subject' || $k == 'welcome_message')
                continue;
            // Check if empty
            if(strlen($v) == 0) {
                add_settings_error(
                    $k,
                    $k . '_texterror',
                    'Please enter a valid ' . $k,
                    'error'
                );
            }
        }

        return $valid;
    }

    public function pluginPageContent() {
        // Get options
        $options = get_option(DmConfirmEmail::PLUGIN_ALIAS);
        // For the is_html checkbox
        if($options['email_ishtml'] == 'true')
            $isHtmlChecked = ' checked=checked';
        else
            $isHtmlChecked = '';
        // For the send_welcome checkbox
        if($options['send_welcome'] == 'true')
            $isSendWelcome = ' checked=checked';
        else
            $isSendWelcome = '';
        // if welcome message is html
        if($options['send_welcome_html'] == 'true')
            $isSendWelcomeHtml = ' checked=checked';
        else
            $isSendWelcomeHtml = '';

        // Set the subject for the user pass email
        if(isset($options['user_pass_subject']) && !empty($options['user_pass_subject']))
            $userPassSubject = $options['user_pass_subject'];
        else
            $userPassSubject = DmConfirmEmail::getPluggableUserPassSubject();

        // Check if the new pluggable message is set
        if(isset($options['user_pass_message']) && !empty($options['user_pass_message']))
            $userPassMessage = $options['user_pass_message'];
        else
            $userPassMessage = DmConfirmEmail::getPluggableUserPassMessage();
        ?>
        <div id="icon-options-general" class="icon32"><br></div>
        <div class="wrap">
            <h1>DM Confirm Email Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('dm_confirm_email_options'); ?>
                <h2 class="nav-tab-wrapper">
                    <a href="#" class="nav-tab nav-tab-active" id="dmec-general">Email Content</a>
                    <a href="#" class="nav-tab" id="dmec-email">Email Welcome Message</a>
                    <a href="#" class="nav-tab" id="dmec-email-reg">Email Registration Message</a>
                    <a href="#" class="nav-tab" id="dmec-settings">Notification Messages</a>
                </h2>

                <div id="dmec-general-div" class="dmec-div" style="display: inline">
                    <table class="form-table">
                        <!-- Email Subject -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Email Subject"); ?>
                            </th>
                            <td>
                                <input name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[email_subject]"; ?>" type="text"
                                       value="<?php echo $options['email_subject']; ?>"/>
                                <p class="description">
                                    Subject of the email to be sent.
                                </p>
                            </td>
                        </tr>
                        <!-- Confirm Email Text -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Confirmation Email Text"); ?>
                            </th>
                            <td>
                                <textarea rows="7" cols="100" name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[email_text]"; ?>"><?php echo $options['email_text']; ?></textarea>
                                <p class="description">
                                    More info about the available tags <a href="https://donmhi.co/projects/dm-confirm-email/#emailtags">here</a>.
                                </p>
                            </td>
                        </tr>
                        <!-- Is Email HTML -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Send as HTML?"); ?>
                            </th>
                            <td>
                                <input type="checkbox"<?php echo $isHtmlChecked; ?> name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[email_ishtml]"; ?>" value="true"/>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="dmec-email-div" class="dmec-div">
                    <table class="form-table">
                        <!-- Will Send welcome message -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Send Welcome Message?"); ?>
                            </th>
                            <td>
                                <input type="checkbox"<?php echo $isSendWelcome; ?> name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[send_welcome]"; ?>" value="true"/>
                            </td>
                        </tr>
                        <!-- Welcome Email Subject -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Welcome Email Subject"); ?>
                            </th>
                            <td>
                                <input name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[welcome_email_subject]"; ?>" type="text"
                                       value="<?php echo $options['welcome_email_subject']; ?>"/>
                            </td>
                        </tr>
                        <!-- Welcome email message -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Welcome email message"); ?>
                            </th>
                            <td>
                                <textarea rows="3" cols="60" name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[welcome_message]"; ?>"><?php echo esc_html($options['welcome_message']); ?></textarea>
                                <p class="description">
                                    Will be sent to user only once (after the account was confirmed).
                                </p>
                            </td>
                        </tr>
                        <!-- Is HTML -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Send Welcome Message as HTML?"); ?>
                            </th>
                            <td>
                                <input type="checkbox"<?php echo $isSendWelcomeHtml; ?> name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[send_welcome_html]"; ?>" value="true"/>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="dmec-email-reg-div" class="dmec-div">
                    <table class="form-table">
                        <!-- Email Subject -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Subject"); ?>
                            </th>
                            <td>
                                <input name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[user_pass_subject]"; ?>" type="text"
                                       size="50" value="<?php echo $userPassSubject; ?>"/>
                                <p class="description">
                                    Override the email subject
                                </p>
                            </td>
                        </tr>

                        <!-- Email Message -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Message"); ?>
                            </th>
                            <td>
                                <textarea rows="3" cols="60" name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[user_pass_message]"; ?>"><?php echo esc_html($userPassMessage); ?></textarea>
                                <p class="description">
                                    Override the email message containing the password. <a href="http://donmhi.co">(?)</a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="dmec-settings-div" class="dmec-div">
                    <table class="form-table">
                        <!-- Registration Success Message -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Success registration message"); ?>
                            </th>
                            <td>
                                <textarea rows="3" cols="60" name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[success_message]"; ?>"><?php echo esc_html($options['success_message']); ?></textarea>
                                <p class="description">
                                    If the registration is success.
                                </p>
                            </td>
                        </tr>

                        <!-- Email Confirmed Message -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Email confirmed message"); ?>
                            </th>
                            <td>
                                <textarea rows="3" cols="60" name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[confirmed_message]"; ?>"><?php echo esc_html($options['confirmed_message']); ?></textarea>
                                <p class="description">
                                    If email was confirmed.
                                </p>
                            </td>
                        </tr>

                        <!-- Invalid / Expired key Message -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Invalid / Expired key message"); ?>
                            </th>
                            <td>
                                <textarea rows="3" cols="60" name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[failed_message]"; ?>"><?php echo esc_html($options['failed_message']); ?></textarea>
                                <p class="description">
                                    If key isn't valid.
                                </p>
                            </td>
                        </tr>

                        <!-- Expiry date -->
                        <tr valign="top">
                            <th scope="row">
                                <?php _e("Expiry Time"); ?>
                            </th>
                            <td>
                                <input name="<?php echo DmConfirmEmail::PLUGIN_ALIAS . "[expiry_time]"; ?>" type="text"
                                       size="2" value="<?php echo $options['expiry_time']; ?>"/> days.
                                <p class="description">
                                    Insert number in days. (0 if no time limit)
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>"/>
                </p>
            </form>
        </div>
    <?php }
}