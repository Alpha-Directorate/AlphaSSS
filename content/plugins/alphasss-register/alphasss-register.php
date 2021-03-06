<?php
/*
Plugin Name: Alpha Register
Plugin URI:
Description: All filter and action hooks used for members register
Author: Fractal Overflow
Author URI:
Text Domain: alphasss-register
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use AlphaSSS\Repositories\User as UserRepository;
use AlphaSSS\Helpers\EmailAddressEncryption;
load_textdomain( 'alphasss-register', plugin_dir_path( __FILE__ ) . 'languages/alphasss-register-' . get_locale() . '.mo' );

// Require helper functions
require_once( 'includes/alphasss-register-functions.php' );

add_action( 'plugins_loaded', function(){
	// Gravity forms custom validation filter hook.
	add_filter( 'gform_validation_9', function( $validation_result ){

		$form = $validation_result['form'];

		foreach ( $form['fields'] as &$field ) {

			switch ( $field['id'] ) {

				// Invitation code validation
				case 20:
					if ( isset( $_POST['input_20'] ) && $invite_code = rgpost( 'input_20' ) ) {

						// Remove spaces from invitation code that user added
						$invite_code = str_replace( ' ', '', $invite_code );

						// Anti-Bruteforce protection
						if ( ! session_id() ) {
							session_start();
						}

						// Define guessing attempts if not exists
						if ( ! isset( $_SESSION['guessing-attempts'] ) ) {
							$_SESSION['guessing-attempts'] = 0;
						}

						if ( $_SESSION['guessing-attempts'] >= alphasss_invitation()->option( 'guessing-attempts-limit' ) ) {

							$confirmation = reset($form['confirmations']);

							$confirmation['message'] = '<h3>' . __('The Late Philip J. Fry', 'alphasss-register') .'</h3>';
							$confirmation['message'] .= '<h1>' . __('Your Invitation Code', 'alphasss-register') . '</h1>';
							$confirmation['message'] .= '<p>' . __('My fellow Earthicans, as I have explained in my book "Earth in Balance", and the much more popular "Harry Potter and the Balance of Earth" we need to defend our planet against pollution. Also <a href="/browse/">request a new code</a>', 'alphasss-register') . '</p>';
							$confirmation['message'] .= '<a class="button" href="/browse/">' . __('Request Invitation Code', 'alphasss-register') . '</a>';

							$form['confirmations'][key( $form['confirmations'] )] = $confirmation;

							$validation_result['is_valid'] = true;
							$field['failed_validation']    = false;
							$validation_result['form']     = $form;

							return $validation_result;
						}
						//--

						$invitation_validation_result = alphasss_invitation()->validate_invitation_code( $invite_code );

						if ( $invitation_validation_result['is_success'] ) {
							$validation_result['is_valid'] = true;
							$field['failed_validation']    = false;

							wp_update_user( array(
								'ID'   => get_current_user_id(), 
								'role' => 'member' ) 
							);

							bp_set_member_type(get_current_user_id(), 'member');

							alphasss_invitation()->update_invitation_code( $invite_code, array('activated_member_id' => $user->ID) );

							// Added custom action
							do_action('member_registered');
							
						} else {
							$_SESSION['guessing-attempts']++;

							$validation_result['is_valid'] = false;
							$field['failed_validation']    = true;
							$field['validation_message']   = $invitation_validation_result['message'];
						}
					}
				break;
			}
		}

		// Assign custom validation results back
		$validation_result['form'] = $form;

		return $validation_result;
	});

	// Gravity forms custom validation filter hook.
	add_filter( 'gform_validation_4', function($validation_result){

		$form = $validation_result['form'];

		foreach ( $form['fields'] as &$field ) {

			switch ( $field['id'] ) {

				// Username validation rules
				case 3:
					$is_username_validation_error = false;

					if ( $username = rgpost( 'input_3' ) ) {

						if ( ! preg_match( '/^[a-z0-9\'_.-]+$/i', $username ) ) {

							$is_username_validation_error = true;
							$field['validation_message']  = __("You may use only the following characters: letters (a-z), numbers (0-9), dashes (-), underscores (_), apostrophes ('), and periods (.). Try again please.", 'alphasss-register');
						}

						// User exists? Show validation error
						if ( username_exists( $username ) ) {

							$is_username_validation_error = true;
							$field['validation_message']  = __('This nickname is already taken. Please choose another one.', 'alphasss-register');
						}

					} else {
						$is_username_validation_error = true;
						$field['validation_message']  = __('Please choose your nickname.', 'alphasss-register');
					}

					// Mark form validation as failed
					if ( $is_username_validation_error ) {
						$validation_result['is_valid'] = false;
						$field['failed_validation']    = true;
					}
				break;

				case 22:
					if ( $email = trim( rgpost( 'input_22' ) ) ) {

						// Email format validation
						if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {

							// Check is email already in use
							if ( UserRepository::isEmailExists($email) ) {
								$is_username_validation_error = true;
								$field['validation_message']  = __('This email already in use. Please pick another one.', 'alphasss-register');
							}

						} else {
							// Incorrect email format
							$is_username_validation_error = true;
							$field['validation_message']  = __('Please enter the valid email address.', 'alphasss-register');
						}

					} else {
						// If email was passes as empty string
						$is_username_validation_error = true;
						$field['validation_message']  = __('Please enter your email.', 'alphasss-register');
					}

					// Mark form validation as failed
					if ( $is_username_validation_error ) {
						$validation_result['is_valid'] = false;
						$field['failed_validation']    = true;
					}
				break;

				// Password validation
				case 4:
					if ($password = rgpost( 'input_4' )) {
						$confirm_password  = rgpost( 'input_4_2' );
						$password_strength = rgpost( 'input_4_strength' );

						if ( $password != $confirm_password ) {
							$field['validation_message']   = __('The 2 passwords do not match. Please try again.', 'alphasss-register');
							$validation_result['is_valid'] = false;
							$field['failed_validation']    = true;
						}

						if ( isset( $field['validation_message'] ) && ! $field['validation_message'] && 'strong' != $password_strength ) {
							$field['validation_message']   = __('Your password must be strong. It\'s for your own protection.', 'alphasss-register');
							$validation_result['is_valid'] = false;
							$field['failed_validation']    = true;
						}
					} else {
							$field['validation_message']   = __('Please choose your password.', 'alphasss-register');
							$validation_result['is_valid'] = false;
							$field['failed_validation']    = true;
					}

				break;

				case 8:
					if (! rgpost( 'input_8_1' )) {
						$field['validation_message']   = __('Please confirm if you are at least 21-years of age?', 'alphasss-register');
						$validation_result['is_valid'] = false;
						$field['failed_validation']    = true;
					} else {
						$username        = sanitize_text_field ( rgpost( 'input_3' ) );
						$password        = sanitize_text_field ( rgpost( 'input_4' ) );
						$email           = sanitize_text_field ( rgpost( 'input_22' ) );
						$encrypted_email = EmailAddressEncryption::encode( $email );
						$hashed_email    = hash('sha512', $email);

						// Create a new user
						$user_id = wp_create_user( $username, $password, $encrypted_email );

						$user = get_userdata($user_id);

						// Sending email confirmation to newly created user
						( new DmConfirmEmail_Models_Registration( $username, $email ) )->register();
						
						// Set Pre Member Role to user
						wp_update_user( array('ID' => $user_id, 'role' => 'pre_member' ) ) ;

						update_user_meta($user_id, 'hashed_email', $hashed_email);
					}
				break;

				// Invitation code validation
				case 20:
					if ( isset( $_POST['input_20'] ) && $invite_code = rgpost( 'input_20' ) ) {

						// Remove spaces from invitation code that user added
						$invite_code = str_replace(" ", "", $invite_code);

						// Anti-Bruteforce protection
						if ( ! session_id() ) {
							session_start();
						}

						// Define guessing attempts if not exists
						if (! isset( $_SESSION['guessing-attempts'] ) ) {
							$_SESSION['guessing-attempts'] = 0;
						}

						if ( $_SESSION['guessing-attempts'] >= alphasss_invitation()->option( 'guessing-attempts-limit' ) ) {

							$confirmation = reset($form['confirmations']);

							$confirmation['message'] = '<h3>' . __('The Late Philip J. Fry', 'alphasss-register') .'</h3>';
							$confirmation['message'] .= '<h1>' . __('Your Invitation Code', 'alphasss-register') . '</h1>';
							$confirmation['message'] .= '<p>' . __('My fellow Earthicans, as I have explained in my book "Earth in Balance", and the much more popular "Harry Potter and the Balance of Earth" we need to defend our planet against pollution. Also <a href="/browse/">request a new code</a>', 'alphasss-register') . '</p>';
							$confirmation['message'] .= '<a class="button" href="/browse/">' . __('Request Invitation Code', 'alphasss-register') . '</a>';

							$form['confirmations'][key($form['confirmations'])] = $confirmation;

							$validation_result['is_valid'] = true;
							$field['failed_validation']    = false;
							$validation_result['form']     = $form;

							return $validation_result;
						}
						//--

						$invitation_validation_result = alphasss_invitation()->validate_invitation_code($invite_code);

						if ( $invitation_validation_result['is_success'] ) {
							$validation_result['is_valid'] = true;
							$field['failed_validation']    = false;

							if ( $user = get_userdatabylogin(sanitize_text_field ( rgpost( 'input_3' ) ) ) ) {
								// Set Member Role to user
								wp_update_user( array (
									'ID'   => $user->ID, 
									'role' => 'member' ) 
								);

								bp_set_member_type($user->ID, 'member');

								alphasss_invitation()->update_invitation_code($invite_code, array('activated_member_id' => $user->ID));

								// Added custom action
								do_action('member_registered');
							} else {
								$validation_result['is_valid'] = false;
								$field['failed_validation']    = true;
								$field['validation_message']   = __('Please contact admin', 'alphasss-register');
							}
							
						} else {
							$_SESSION['guessing-attempts']++;

							$validation_result['is_valid'] = false;
							$field['failed_validation']    = true;
							$field['validation_message']   = $invitation_validation_result['message'];
						}
					}
				break;
			}
		}

		// Assign custom validation results back
		$validation_result['form'] = $form;

		return $validation_result;
	});

	// Adds custom java scripts to gf form
	add_filter('gform_register_init_scripts', function($form) {

		// Define the script
		$script = "(function($){" .
			"$('#input_9_20').bind('input', function(){
				$(this).val($(this).val().replace(/\s+/g, ''));
			});" .
			"$('#input_9_20').val($.cookie('invintation_code'));" .
			"$('#input_4_20').bind('input', function(){
				$(this).val($(this).val().replace(/\s+/g, ''));
			});".
			"$('#gform_submit_button_4').parent().parent().append(\"<h1>".__("Don't have any invitation code? Then ask the members.", 'alphasss-register')."</h1><p>".__("Get the code from one of the members currently online. Who said that? SURE you <a href='/browse/'>members online</a>! Ummm…to eBay? Stop! Don't shoot fire stick in space canoe! Cause explosive decompression!",'alphasss-register')."</p>\"); })(jQuery);";

		// Inject script into form
		GFFormDisplay::add_init_script($form['id'], 'gform_my_function', GFFormDisplay::ON_PAGE_RENDER, $script);
		
		return $form;
	});

	// Proccess form before render
	add_filter( 'gform_pre_render_9', function($form){

		foreach ( $form['fields'] as &$field ) {

			switch ( $field['id'] ) {

				case 21:
					$field['content'] = str_replace( '%%RegisterInvintationTitleUnder%%', __('Problems with the invitation code?', 'alphasss-register'), $field['content'] );
					$field['content'] = str_replace( '%%RegisterInvintationTextUnder%%', __("No, I'm Santa Claus! You, minion. Lift my arm. <a>Meh. No!</a>", 'alphasss-register'), $field['content'] );
				break;

				case 22:
					$field['content'] = str_replace( '%%RegisterInvintationTitle%%', __('Your Invitation Code', 'alphasss-register'), $field['content'] );
					$field['content'] = str_replace( '%%RegisterPageTextUnderTitle%%', __("Belligerent and numerous. And I'm his friend Jesus. With a warning label this big, you know they gotta be fun!", 'alphasss-register'), $field['content'] );
				break;

				case 20:
					$field['label'] = __('Invitation Code:', 'alphasss-register');
				break;
			}
		}

		$form['button']['text'] = __('Finish', 'alphasss-register');

		return $form;
	});

	// Proccess form before render
	add_filter( 'gform_pre_render_4', function($form){

		foreach ( $form['fields'] as &$field ) {

			switch ( $field['id'] ) {

				// Localize /register/ first step title
				case 1:
					$field['content'] = str_replace( '%%RegisterPageTitle%%', __('The Usual First Step', 'alphasss-register'), $field['content'] );
				break;

				// Localize /register/ first step, nickname block
				case 3:
					$field['label']       = str_replace( '%%RegisterNicknameLabel%%', __('Your Nickname:', 'alphasss-register'), $field['label'] );
					$field['description'] = str_replace( '%%RegisterNicknameDescription%%', __('The key to victory is discipline, and that means a well made bad. You will practice until you can make your bed in your sleep.', 'alphasss-register'), $field['description'] );
				break;

				// Localize /register/ first step, password block
				case 4:
					$field['label'] = str_replace( '%%RegisterPasswordLabel%%', __('Password:', 'alphasss-register'), $field['label'] );
				break;

				// Localize /register/ first step, text under password block 
				case 7:
					$field['content'] = str_replace( '%%RegisterPasswordDescription%%', __('Isn\'t it true that you have been paid for your testimony? Soothe us with sweet lies. Why would I want to know that?', 'alphasss-register'), $field['content'] );
				break;

				// Localize /register/ first step, age confirmation
				case 8:
					foreach ($field->choices as & $choice){
						$choice['text'] = str_replace('%%RegisterAgeConfirmation%%',__('Hey, quess that you\'re accessories to. File not found. Oh Sure! Blame the wizards! We\'ll need to have a look inside you with this camera.', 'alphasss-register'), $field['choices'][0]['text']);
					}
				break;

				case 12:
					$field['content'] = str_replace( '%%RegisterConfirmationTitle%%', __('Confirmation &amp; Dire Warning!', 'alphasss-register'), $field['content'] );
				break;

				case 2:
					$field['content'] = str_replace( '%%RegisterPageTextUnderTitle%%', __('I daresay that Fry has discovered the smelliest object is the known universe! Throw her in brig. Also Zoidberg. Oh God, what I have done! Just once I\'d like to eat dinner with a celebrity whi isn\'t bound and gagged. Daylight and everything.', 'alphasss-register'), $field['content'] );
				break;

				case 18:
					$field['content'] = str_replace( '%%RegisterInvintationTitle%%', __('Your Invitation Code', 'alphasss-register'), $field['content'] );
				break;

				case 19:
					$field['content'] = str_replace( '%%RegisterInvintationTextUnder%%', __("I meant 'physically'. Look, perhaps you could let me work for a little food? I could clean the floors or paint a fence, or service you sexually? Hello, little man. I will destroy you! But existing is basically all I do! So, how 'bout them Knicks?", 'alphasss-register'), $field['content'] );
				break;

				case 20:
					$field['label'] = __('Invitation Code:', 'alphasss-register');
				break;


				case 22:
					$field['label']       = str_replace( '%%RegisterEmailLabel%%', __('Your Email Address:', 'alphasss-register'), $field['label'] );
					$field['description'] = str_replace( '%%RegisterEmailDescription%%', __('We need rest. The spirit is willing, but the flesh is spongy and bruised. Oh sure! Blame the wizards! Ummm...to eBay? Leela, Bender, we\'re going grave robbing.', 'alphasss-register'), $field['description'] );
				break;
			}
		}

		$form['button']['text'] = __('Finish', 'alphasss-register');

		return $form;
	});
});

?>