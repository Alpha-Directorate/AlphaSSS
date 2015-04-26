<?php
/*
Plugin Name: DM Confirm Email
Plugin URI: http://donmhi.co/projects/dm-confirm-email/
Description: Protect your wordpress site with spam registration. DM Confirm Email makes sure that user trying to register is real by requiring them to confirm their email address.
Version: 1.4
Author: donMhico
Author URI: http://donmhi.co
License: GPLv2
*/

require_once('models/login-register.php');
require_once('models/login-confirm.php');
require_once('models/login-resend.php');
require_once('models/plugin-page.php');
require_once('models/welcome_message.php');
require_once('inc/pluggable.php');

class DmConfirmEmail {
    /**
     * Folder name
     */
    const PLUGIN_FOLDER = 'dm-confirm-email';

    /**
     * Table name
     */
    const PLUGIN_ALIAS = 'dmec';

    /**
     * DB Version of the plugin
     */
    const DB_VERSION = '1.0';

    function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Options page
        new DmConfirmEmail_Models_PluginPage();

        // Wordpress native registration alteration
        new DmConfirmEmail_Models_RegisterForm();
        new DmConfirmEmail_Models_ConfirmForm();
        new DmConfirmEmail_Models_ResendEmailConfirmForm();

        // Welcome email
        new DmConfirmEmail_Models_WelcomeMessage();

        // Enqueue admin script
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    /**
     * Enqueue both scripts
     *
     * @param string $hook
     */
    public function enqueueScripts($hook) {
        if($hook != 'settings_page_dm_confirm_email_options')
            return;

        // Register style
        wp_register_style('dmec_general_style', plugins_url(self::PLUGIN_FOLDER . '/css/general.css'), array(), '1.4');

        // Register script
        wp_register_script('dmec_general_script', plugins_url(self::PLUGIN_FOLDER . '/js/general.js'), array('jquery'), '1.4');

        // Enqueue
        wp_enqueue_script('dmec_general_script');
        wp_enqueue_style('dmec_general_style');
    }

    /**
     * Create database upon activation
     */
    public function activate() {
        global $wpdb;

        // Plugin options
        update_option(self::PLUGIN_ALIAS . '_dbversion', self::DB_VERSION);
        update_option(self::PLUGIN_ALIAS, array(
            'email_subject' => 'Email Confirmation',
            'email_text' => '&lt;p&gt;Hello {user_login},&lt;/p&gt;
&lt;p&gt;   Welcome to {site_link}.&lt;/p&gt;
&lt;p&gt;   To confirm your email, please click the link below&lt;/p&gt;
&lt;p&gt;   {confirm_link}&lt;/p&gt;',
            'email_ishtml' => 'true',
            'expiry_time' => '30',
            'success_message' => 'Check your e-mail for the confirmation link.',
            'user_pass_subject' => self::getPluggableUserPassSubject(),
            'user_pass_message' => self::getPluggableUserPassMessage(),
            'confirmed_message' => 'Your email is now confirmed. Your password was sent to your email',
            'failed_message' => 'Key is not valid or already expired.',
            'send_welcome' => 'false',
            'welcome_email_subject' => 'Welcome to our site',
            'welcome_message' => '',
            'send_welcome_html' => 'true'
        ));

        // Create new table
        $tableName = $wpdb->prefix . self::PLUGIN_ALIAS;

        $sql = "CREATE TABLE {$tableName} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_login varchar(60) NOT NULL,
            user_email varchar(100) NOT NULL,
            user_key varchar(60) NOT NULL,
            expiry_date date DEFAULT '0000-00-00' NOT NULL,
            UNIQUE KEY id (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Get default pluggable user pass message
     *
     * @return string
     */
    public static function getPluggableUserPassMessage() {
        // Default pluggable message
        // see wp-includes/pluggable.php
        // function wp_new_user_notification()
        $message  = "<p>" . sprintf(__('Username: %s'), '{user_login}') . "</p>" . "\r\n";
        $message .= "<p>" . sprintf(__('Password: %s'), '{password}') . "</p>" . "\r\n";
        $message .= "<p>" . '{login_url}' . "</p>" . "\r\n";

        return $message;
    }

    /**
     * Get the default subject for the user pass email
     *
     * @return string
     */
    public static function getPluggableUserPassSubject() {
        // Default pluggable message
        // see wp-includes/pluggable.php
        // function wp_new_user_notification()
        $title  = '{site_title} Your username and password';

        return $title;
    }

    /**
     * Text Parser for the DM Email Confirm.
     *
     * @param string $message
     * @param string $userKey
     * @param string $userLogin
     * @param string $password
     * @return mixed
     */
    public static function parser($message, $userKey = '', $userLogin = '', $password = '') {
        $siteUrl = site_url();
        $loginUrl = wp_login_url();
        $keyLink = '<a href="' . esc_url("{$loginUrl}/?action=confirm&eckey={$userKey}") .'" target="_blank">' .
            esc_url("{$loginUrl}/?action=confirm&eckey={$userKey}") . '</a>';
        $keyUrl = esc_url("{$loginUrl}/?action=confirm&eckey={$userKey}");
        $siteTitle = get_bloginfo('name');
        $siteLink = '<a href="' . esc_url($siteUrl) . '" target="_blank">' . $siteTitle . '</a>';

        // Parsable tags
        $tags = array(
            '{confirm_key}', // Unique key from user_key
            '{confirm_link}', // Link with <a> tag
            '{confirm_url}', // Key url without the <a> tag
            '{site_title}',
            '{site_link}',
            '{site_url}',
            '{user_login}',
            '{password}',
            '{login_url}'
        );

        // Corresponding values.
        $values = array(
            $userKey, $keyLink, $keyUrl, $siteTitle, $siteLink, $siteUrl, $userLogin, $password, $loginUrl
        );

        // Replace the tags
        return str_ireplace($tags, $values, $message);
    }

}
new DmConfirmEmail();