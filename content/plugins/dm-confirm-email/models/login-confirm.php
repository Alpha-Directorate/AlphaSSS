<?php
class DmConfirmEmail_Models_ConfirmForm {
    /**
     * @var string
     */
    private $userLogin;

    /**
     * @var string
     */
    private $userEmail;

    /**
     * @var array
     */
    private $message;

    function __construct() {
        add_action('login_form_confirm', array($this, 'formConfirm'));
        // Get the messages
        $this->message = get_option(DmConfirmEmail::PLUGIN_ALIAS);
    }

    public function formConfirm() {
        // Sanitize
        $key = htmlspecialchars(strip_tags($_GET['eckey']), ENT_QUOTES);

        // Check key
        $confirmed = $this->checkUserKey($key);

        if($confirmed) {

            // Delete temp data
            $this->deleteTempData();

            add_filter('wp_login_errors', array($this, 'successMessage'), 10, 2);
        } else {
            add_filter('wp_login_errors', array($this, 'failMessage'), 10, 2);
        }
    }

    /**
     * Create a user with the same logic from wp-login.php
     * @return bool|int
     */
    private function createUser() {
        // Make sure we have userLogin and userEmail
        if($this->userLogin == null || $this->userLogin == '' || $this->userEmail == null || $this->userEmail == '')
            return false;

        $userPass = wp_generate_password(12, false);
        $userId = wp_create_user( $this->userLogin, $userPass, $this->userEmail);

        // If user creation failed
        if(!$userId)
            return false;

        update_user_option($userId, 'default_password_nag', true, true ); //Set up the Password change nag.

        wp_new_user_notification($userId, $userPass);

        return $userId;
    }

    /**
     * Delete temp data
     */
    private function deleteTempData() {
        global $wpdb;

        $wpdb->delete($wpdb->prefix . DmConfirmEmail::PLUGIN_ALIAS, array('user_email' => $this->userEmail), '%s');

        return;
    }
    /**
     * Check if user key exists in the temp db
     * @param string $key
     * @return bool
     */
    private function checkUserKey($key) {
        global $wpdb;

        // Retrieve data
        $results = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}" . DmConfirmEmail::PLUGIN_ALIAS . " WHERE user_key = %s",
                $key
            )
        );

        // No key found
        if($results === null)
            return false;

        // Check if key already expired
        $isExpired = $this->checkExpiry($results->expiry_date);
        if($isExpired)
            return false;

        // Set the properties
        $this->userLogin = $results->user_login;
        $this->userEmail = $results->user_email;

        // Check if key not yet expired
        return true;
    }

    /**
     * Check if key isn't expired
     * @param $expiryDate
     * @return bool
     */
    private function checkExpiry($expiryDate) {
        // Always true until proven
        $expired = true;

        // Get plugin option
        $option = get_option(DmConfirmEmail::PLUGIN_ALIAS);

        // If expiry time = 0, expiry will always be false
        if($option['expiry_time'] == 0 || empty($option['expiry_time']))
            $expired = false;

        // If registered while $options['expiry_time'] is 0 then false
        if($expiryDate == '0000-00-00')
            $expired = false;
        elseif($expiryDate > date('Y-m-d'))
            $expired = false;

        return $expired;
    }

    /**
     * Display if new user is created
     * @param $errors
     * @param $redirect
     * @return mixed
     * @todo add custom message "Your email is now confirmed. Your password has been sent to your email.""
     */
    public function successMessage($errors, $redirect) {
        $errors->add('dmec_key_success', esc_html($this->message['confirmed_message']), 'message');

        return $errors;
    }

    /**
     * Display if key is invalid or expired
     * @param $errors
     * @param $redirect
     * @return mixed
     */
    public function failMessage($errors, $redirect) {
        $errors->add('dmec_key_failed', esc_html($this->message['failed_message']));

        return $errors;
    }

    /**
     * Display when an unexpected occured
     * @param $errors
     * @param $redirect
     * @return mixed
     */
    public function failCreationMessage($errors, $redirect) {
        $errors->add('dmec_creation_failed', esc_html($this->message['failed_message']));

        return $errors;
    }
}