<?php

class DmConfirmEmail_Models_WelcomeMessage {
    /**
     * @var string
     */
    private $isHtml;

    function __construct() {
        add_action('user_register', array($this, 'sendWelcomeMessage'));
    }

    public function sendWelcomeMessage($userId) {
        $options = get_option(DmConfirmEmail::PLUGIN_ALIAS);
        // Check if send welcome message is activated
        if($options['send_welcome'] != 'true')
            return;

        // Set the email content type
        $this->isHtml = $options['send_welcome_html'];
        add_filter('wp_mail_content_type', array($this, 'emailContentType'));

        // Get user info
        $user = get_userdata($userId);

        // Parse the messages
        $parsedSubject = DmConfirmEmail::parser($options['welcome_email_subject'], '', $user->user_login);
        $parsedMessage = DmConfirmEmail::parser($options['welcome_message'], '', $user->user_login);
        // Clean the message
        $subject = html_entity_decode($parsedSubject);
        $message = html_entity_decode($parsedMessage);

        // Send email
        $send = wp_mail(
            $user->user_email,
            $subject,
            $message
        );

        // Reset filter after sending email
        remove_filter( 'wp_mail_content_type', array($this, 'emailContentType'));
    }

    /**
     * Set the content type of email
     * @return string
     */
    public function emailContentType() {
        // Make sure we have bool
        $isHtml = (bool)$this->isHtml;

        if($isHtml)
            return 'text/html';
        else
            return 'text/plain';
    }
}