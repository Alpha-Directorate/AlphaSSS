<?php
/*
Plugin Name: Alpha Register
Plugin URI:
Description: All filter and action hooks used for members register
Author: Fractal Overflow
Author URI:
Text Domain: alphasss-register
*/

load_plugin_textdomain( 'alphasss-register', false, basename( dirname( __FILE__ ) ) . '/languages' );

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
					}
				break;

				// Confirm user registration data
				case 15:
					// Isset user confirmed property and user not exists
					if ( isset( $_POST['input_15_1'] ) && ! username_exists( rgpost( 'input_3' ) ) ) {
						$is_register_data_approved = sanitize_text_field( $_POST['input_15_1'] ); // input var okay

						// Data confirmed?
						if ( 'Yes' == $is_register_data_approved ) {

							$email = md5( time() ) . '@alphasss.com';

							// Create a new user
							$user_id = wp_create_user(
								sanitize_text_field ( rgpost( 'input_3' ) ), 
								sanitize_text_field ( rgpost( 'input_4' ) ),
								$email
							);
							//--

							(new DmConfirmEmail_Models_Registration( rgpost( 'input_3' ), $email ))->register();
							
							// Set Pre Member Role to user
							wp_update_user( array ('ID' => $user_id, 'role' => 'pre_member' ) ) ;
						}
					} else if (isset( $_POST['input_15_1'] ) && !$_POST['input_15_1'] ){
						$field['validation_message']   = __('Please confirm you have written down or memorized your nickname/password? You are anonymous, and we cannot recover your lost login info.', 'alphasss-register');
						$validation_result['is_valid'] = false;
						$field['failed_validation']    = true;
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
			});})(jQuery);";

		// Inject script into form
		GFFormDisplay::add_init_script($form['id'], 'gform_my_function', GFFormDisplay::ON_PAGE_RENDER, $script);
		
		return $form;
	});

	// Proccess form before render
	add_filter( 'gform_pre_render_9', function($form){

		foreach ( $form['fields'] as &$field ) {

			switch ( $field['id'] ) {

				case 22:
					$field['content'] = str_replace( '%%RegisterInvintationTitle%%', __('Your Invitation Code', 'alphasss-register'), $field['content'] );
					$field['content'] = str_replace( '%%RegisterPageTextUnderTitle%%', __('I meant \'physically\'. Look, perhaps you could let me work for a little food? I could clean the floors or paint a fence, or service you sexually? Hello, little man. I will destroy you! But existing is basically all I do! So, how \'bout them Knicks?', 'alphasss-register'), $field['content'] );
				break;

				case 20:
					$field['label'] = __('Invitation Code:', 'alphasss-register');
				break;

				case 21:
					$field['content'] = str_replace( 
						[
							'%%RegisterInvintationTitleUnder%%',
							'%%RegisterInvintationTextUnder%%'
						], 
						[
							__('Don\'t have any invitation code? Then ask the members.', 'alphasss-register'),
							__('Get the code from one of the members currently online. Who said that? Sure you <a href="/browse/">members online</a>! Ummm...to eBay? Stop! Don\'t shoot fire stick in space canoe! Cause explosive decompression!', 'alphasss-register')
						],
						$field['content']
					);
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

				// Localize /register/ first step, text under title
				case 2:
					$field['content'] = str_replace( '%%RegisterPageTextUnderTitle%%', __('I daresay that Fry has discovered the smelliest object is the known universe! Throw her in brig. Also Zoidberg. Oh God, what I have done! Just once I\'d like to eat dinner with a celebrity whi isn\'t bound and gagged. Daylight and everything.', 'alphasss-register'), $field['content'] );
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

				case 13:
					$field['content'] = str_replace( '%%RegisterConfirmationTextUnderTitle%%', __('I daresay that Fry has discovered the smelliest object is the known universe! Throw her in brig. Also Zoidberg. Oh God, what I have done! Just once I\'d like to eat dinner with a celebrity whi isn\'t bound and gagged. Daylight and everything.', 'alphasss-register'), $field['content'] );
				break;

				case 15:
					foreach ($field->choices as & $choice){
						$choice['text'] = str_replace('%%RegisterDataConfirmation%%',__('You guys go on without me! I\'m going to go... look for more stuff to steal! No! The kind with looting and maybe starting a few fires!', 'alphasss-register'), $field['choices'][0]['text']);
					}
				break;

				case 17:
					$field['content'] = str_replace( 
						[
							'%%RegisterConfirmationNickNameIs%%',
							'%%nickname%%', 
							'%%password%%',
							'%%RegisterConfirmationTextUnderNickname%%',
							'%%RegisterConfirmationPasswordIs%%',
							'%%RegisterConfirmationTextUnderPassword%%',
						], 
						[
							__('Your Nickname is:', 'alphasss-register'),
							rgpost( 'input_3' ), 
							rgpost( 'input_4' ),
							__('I\'ve been there. My folks were always on me to groom myself and wear underpants. What am I, the pope?', 'alphasss-register'),
							__('Your Password is:','alphasss-register'),
							__('I barely knew Philip, bus as a clergyman I have no problem telling his most intimate friends all about him','alphasss-register'),
						], 
						$field['content'] 
					);
				break;

				case 18:
					$field['content'] = str_replace( '%%RegisterInvintationTitle%%', __('Your Invitation Code', 'alphasss-register'), $field['content'] );
				break;

				case 20:
					$field['label'] = __('Invitation Code:', 'alphasss-register');
				break;

				case 21:
					$field['content'] = str_replace( 
						[
							'%%RegisterInvintationTitleUnder%%',
							'%%RegisterInvintationTextUnder%%'
						], 
						[
							__('Don\'t have any invitation code?', 'alphasss-register'),
							__('Who said that? SURE you can die! You want to die?!', 'alphasss-register')
						],
						$field['content']
					);
				break;

				case 22:
					$field['label']       = str_replace( '%%RegisterEmailLabel%%', __('Your Email Address:', 'alphasss-register'), $field['label'] );
					$field['description'] = str_replace( '%%RegisterEmailDescription%%', __('The key to victory is discipline, and that means a well made bad. You will practice until you can make your bed in your sleep.', 'alphasss-register'), $field['description'] );
				break;
			}
		}

		$form['button']['text'] = __('Finish', 'alphasss-register');

		return $form;
	});
});

?>