<?php
require_once('registration.php');
/**
 * Class RegForm
 *
 * Handles the registration form changes
 *
 * @package DmConfirmEmail\Models
 */
class DmConfirmEmail_Models_RegisterForm {
    /**
     * @var array
     */
    private $message;

    function __construct() {
        add_action('login_form_register', array($this, 'formRegister'));
        add_action('dm_ec_reg', array($this, 'unsetError'), 10, 1);
        add_filter('registration_errors', array($this, 'forceError'), 1, 3);
        add_action('login_form_dmec', array($this, 'displayCustomMessage'));

        // Get message
        $this->message = get_option(DmConfirmEmail::PLUGIN_ALIAS);
    }
    /**
     * Replace the built-in register form. Only added one line code
     */
    public function formRegister() {
        $errors = new WP_Error();

        $http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
        $interim_login = isset($_REQUEST['interim-login']);
        if ( is_multisite() ) {
            // Multisite uses wp-signup.php
            wp_redirect( apply_filters( 'wp_signup_location', network_site_url('wp-signup.php') ) );
            exit;
        }

        if ( !get_option('users_can_register') ) {
            wp_redirect( site_url('wp-login.php?registration=disabled') );
            exit();
        }

        $user_login = '';
        $user_email = '';
        if ( $http_post ) {
            $user_login = $_POST['user_login'];
            $user_email = $_POST['user_email'];
            $errors = register_new_user($user_login, $user_email);
            if ( !is_wp_error($errors) ) {
                $redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : 'wp-login.php?checkemail=registered';
                wp_safe_redirect( $redirect_to );
                exit();
            }
        }

        // The only code added.
        do_action('dm_ec_reg', $errors);

        $redirect_to = apply_filters( 'registration_redirect', !empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '' );
        login_header(__('Registration Form'), '<p class="message register">' . __('Register For This Site') . '</p>', $errors);
        ?>

        <form name="registerform" id="registerform" action="<?php echo esc_url( site_url('wp-login.php?action=register', 'login_post') ); ?>" method="post">
            <p>
                <label for="user_login"><?php _e('Username') ?><br />
                    <input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr(wp_unslash($user_login)); ?>" size="20" /></label>
            </p>
            <p>
                <label for="user_email"><?php _e('E-mail') ?><br />
                    <input type="text" name="user_email" id="user_email" class="input" value="<?php echo esc_attr(wp_unslash($user_email)); ?>" size="25" /></label>
            </p>
            <?php do_action('register_form'); ?>
            <p id="reg_passmail"><?php _e('A password will be e-mailed to you.') ?></p>
            <br class="clear" />
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
            <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Register'); ?>" /></p>
        </form>

        <p id="nav">
            <a href="<?php echo esc_url( wp_login_url() ); ?>"><?php _e( 'Log in' ); ?></a> |
            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" title="<?php esc_attr_e( 'Password Lost and Found' ) ?>"><?php _e( 'Lost your password?' ); ?></a> |
            <a href="<?php echo site_url('wp-login.php?action=resendec'); ?>" title="<?php esc_attr_e('Resend Confirmation Link'); ?>"><?php _e('Resend Confirmation Link'); ?></a>
        </p>

        <?php
        login_footer('user_login');
        die();
    }

    /**
     * Unsets the custom error and redirect to a custom action=dmec
     * @param object $errors
     */
    public function unsetError($errors) {
        if(!empty($errors->errors)) {
            // Check if the only error is the force error
            if(count($errors->errors) == 1 && isset($errors->errors['dm_ec_force_error'])) {
                // Remove the custom error
                unset($errors->errors['dm_ec_force_error']);

                // Save the reg data in the db
                $register = new DmConfirmEmail_Models_Registration($_POST['user_login'], $_POST['user_email']);
                $register->register();

                // Redirect to a custom action dmec
                $redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : 'wp-login.php?action=dmec';
                wp_safe_redirect( $redirect_to );
                exit();
            }
            // Remove the custom error
            unset($errors->errors['dm_ec_force_error']);
        }
    }

    /**
     * Forcefull add an error to prevent the registration to be saved immediately in the database
     * @param $errors
     * @param $sanitized_user_login
     * @param $user_email
     */
    public function forceError($errors, $sanitized_user_login, $user_email) {
        $errors->add('dm_ec_force_error', __(''));

        // If user data exists
        $userLogin = $this->checkExist('user_login', $sanitized_user_login);
        $userEmail = $this->checkExist('user_email', $user_email);

        if($userLogin != 0)
            $errors->add('dm_ec_userlogin_exists', __('<strong>ERROR</strong>: This username is already registered. Please choose another one.'));

        if($userEmail != 0)
            $errors->add('dm_ec_useremail_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.'));

        return $errors;
    }

    /**
     * @param string $user Either user_login or user_email only
     * @param string $data
     * @return mixed
     */
    private function checkExist($user, $data) {
        global $wpdb;

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}" . DmConfirmEmail::PLUGIN_ALIAS . " WHERE {$user} = %s",
                $data
            )
        );

        return $result;
    }

    /**
     * Display the custom message on our custom action=dmec
     */
    public function displayCustomMessage() {
        add_filter('wp_login_errors', array($this, 'customMessage'), 10, 2);
    }

    /**
     * Add a custom message telling the user to confirm his / her email
     * @param $errors
     * @param $redirect_to
     * @return mixed
     */
    public function customMessage($errors, $redirect_to) {
        $errors->add('confirm', esc_html($this->message['success_message']), 'message');

        return $errors;
    }
}