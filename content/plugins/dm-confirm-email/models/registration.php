<?php
/**
 * Class DmConfirmEmail_Models_Registration
 *
 * Handles new registration
 */
class DmConfirmEmail_Models_Registration {
    /**
     * @var string
     */
    private $userLogin;

    /**
     * @var string
     */
    private $userEmail;

    /**
     * @var string
     */
    private $userKey;

    /**
     * @var string
     */
    private $isHtml;

    function __construct($userLogin, $userEmail) {
        // Set the properties
        $this->userLogin = $userLogin;
        $this->userEmail = $userEmail;
    }

    /**
     * Make a temp registration
     */
    public function register() {
        $options = get_option(DmConfirmEmail::PLUGIN_ALIAS);

        // Attempty to save the data
        $tempData = $this->saveTempDate($options['expiry_time']);

        // If something went wrong
        if($tempData == false)
            return;

        $sendEmail = $this->sendEmail($options['email_subject'], $options['email_text'], $options['email_ishtml']);
    }

    /**
     * Save the data
     * @param string $expiryTime
     */
    private function saveTempDate($expiryTime) {
        global $wpdb;

        // Generate a unique key
        $this->userKey = $this->generateUniqueKeys();

        // Set the expiry time
        $expiryTime = $this->setExpiryTime($expiryTime);

        // Save
        $save = $wpdb->insert(
            $wpdb->prefix . DmConfirmEmail::PLUGIN_ALIAS,
            array(
                'user_login' => $this->userLogin,
                'user_email' => $this->userEmail,
                'user_key' => $this->userKey,
                'expiry_date' => $expiryTime
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        return $save;
    }

    /**
     * Generates a unique key
     * @return string
     */
    private function generateUniqueKeys() {
        global $wpdb;

        $tableName = $wpdb->prefix . DmConfirmEmail::PLUGIN_ALIAS;

        // Create keys until it is unique
        $unique = false;
        while(!$unique) {
            // Create key
            $key = wp_generate_password(20, false);

            // Check if key already exist
            $check = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id FROM {$tableName} WHERE user_key = %s",
                    $key
                )
            );

            // Null means unique
            if(is_null($check))
                $unique = true;
        }

        return $key;
    }

    /**
     * Set the expiry time of the key
     * @param string $expiryTime
     * @return string
     */
    private function setExpiryTime($expiryTime = '0000-00-00') {
        if(empty($expiryTime) || $expiryTime == 0 || $expiryTime == false)
            $expiryTime = '0000-00-00';
        else
            $expiryTime = date('Y-m-d',strtotime("+{$expiryTime} days"));

        return $expiryTime;
    }

    /**
     * Send the email
     * @param $emailSubject
     * @param $emailText
     * @param $emailType
     * @return bool
     */
    public function sendEmail($emailSubject, $emailText, $emailType) {
        // Set the email content type
        $this->isHtml = $emailType;
        add_filter('wp_mail_content_type', array($this, 'emailContentType'));

        // Convert the tags
        $parsedText = html_entity_decode($this->parseTags($emailText));

        // Send wp_mail
        $send = wp_mail(
            $this->userEmail,
            $emailSubject,
            $parsedText
        );

        // Reset filter after sending email
        remove_filter( 'wp_mail_content_type', array($this, 'emailContentType'));

        return $send;
    }

    /**
     * Convert the tags to valuable data
     * @param string $emailText
     * @return string
     */
    private function parseTags($emailText) {
        // Parse message
        $message = DmConfirmEmail::parser($emailText, $this->userKey, $this->userLogin);

        return $message;
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

    /**
     * Userkey setter
     * @param string $userKey
     */
    public function setUserKey($userKey) {
        $this->userKey = $userKey;
    }
}