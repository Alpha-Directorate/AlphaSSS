<?php
/*
Plugin Name: Alpha Hooks
Plugin URI:
Description: All filter and action hooks used on the site, gathered together as one happy family.
Version: 1.0.0
Author: Andrew Voyticky
Author URI:
*/

wp_enqueue_script( 'bootstrap-js', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js', array('jquery') );
wp_enqueue_style( 'bootstrap-css', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css' );

// Add login/logout to the navigation menu
add_filter('wp_nav_menu_items', function($items, $args) {
	ob_start();
	wp_loginout('/');
	$loginoutlink = str_replace('Log in', __('Login'), ob_get_contents());
	ob_end_clean();

	if ( is_user_logged_in() ) {
		$items .= '<li class="channel-logout">'. $loginoutlink .'</li>';
	} else {
		$items .= '<li>'. $loginoutlink .'</li>';
	}

	return $items;
}, 10, 2);

// Adds custom script to gf form
add_filter('gform_register_init_scripts', function($form) {

	// Define the script
	$script = "(function($){" .
		"$('#input_9_20').bind('input', function(){
			$(this).val($(this).val().replace(/\s+/g, ''));
		});" .
		"$('#input_4_20').bind('input', function(){
			$(this).val($(this).val().replace(/\s+/g, ''));
		});})(jQuery);";

	// Inject script into form
	GFFormDisplay::add_init_script($form['id'], 'gform_my_function', GFFormDisplay::ON_PAGE_RENDER, $script);
	
	return $form;
});

// Redirect after user looged in
add_filter( 'login_redirect', function($redirect_to, $request, $user){
	global $user;

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			//check for pre_member role
			if ( in_array( 'pre_member', $user->roles ) ) {
				// Return activation process
				return site_url( '/activate/' );
			} else {
				return home_url();
			}
		}
	}
}, 10, 3);
//--

// Gravity forms custom validation filter hook.
add_filter( 'gform_validation_9', function($validation_result){

	$form = $validation_result['form'];

	foreach ( $form['fields'] as &$field ) {

		switch ( $field['id'] ) {

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

					if ( $_SESSION['guessing-attempts'] >= buddyboss_invitation()->option( 'guessing-attempts-limit' ) ) {

						$confirmation = reset($form['confirmations']);

						$confirmation['message'] = '<h3>' . __('The Late Philip J. Fry') .'</h3>';
						$confirmation['message'] .= '<h1>' . __('Your Invitation Code') . '</h1>';
						$confirmation['message'] .= '<p>' . __('My fellow Earthicans, as I have explained in my book "Earth in Balance", and the much more popular "Harry Potter and the Balance of Earth" we need to defend our planet against pollution. Also <a href="/browse/">request a new code</a>') . '</p>';
						$confirmation['message'] .= '<a class="button" href="/browse/">' . __('Request Invitation Code') . '</a>';

						$form['confirmations'][key($form['confirmations'])] = $confirmation;

						$validation_result['is_valid'] = true;
						$field['failed_validation']    = false;
						$validation_result['form']     = $form;

						return $validation_result;
					}
					//--

					$invitation_validation_result = buddyboss_invitation()->validate_invitation_code($invite_code);

					if ( $invitation_validation_result['is_success'] ) {
						$validation_result['is_valid'] = true;
						$field['failed_validation']    = false;

						wp_update_user( array(
							'ID'   => get_current_user_id(), 
							'role' => 'member' ) 
						);

						buddyboss_invitation()->update_invitation_code($invite_code, array('activated_member_id' => $user->ID));
						
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
						$field['validation_message']  = "You may use only the following characters: letters (a-z), numbers (0-9), dashes (-), underscores (_), apostrophes ('), and periods (.). Try again please.";
					}

					// User exists? Show validation error
					if ( username_exists( $username ) ) {

						$is_username_validation_error = true;
						$field['validation_message']  = 'This nickname is already taken. Please choose another one.';
					}
				}

				// Mark form validation as failed
				if ( $is_username_validation_error ) {
					$validation_result['is_valid'] = false;
					$field['failed_validation']    = true;
				}
			break;

			case 4:
				$password          = rgpost( 'input_4' );
				$confirm_password  = rgpost( 'input_4_2' );
				$password_strength = rgpost( 'input_4_strength' );

				if ( $password != $confirm_password ) {
					$field['validation_message']   = 'The 2 passwords do not match. Please try again.';
					$validation_result['is_valid'] = false;
					$field['failed_validation']    = true;
				}

				if ( isset( $field['validation_message'] ) && ! $field['validation_message'] && 'strong' != $password_strength ) {
					$field['validation_message']   = 'Your password must be strong. It\'s for your own protection.';
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

						// Create a new user
						$user_id = wp_create_user(
							sanitize_text_field ( rgpost( 'input_3' ) ), 
							sanitize_text_field ( rgpost( 'input_4' ) ),
							md5( time() ) . '@alphasss.com'
						);
						//--
						
						// Set Pre Member Role to user
						wp_update_user( array ('ID' => $user_id, 'role' => 'pre_member' ) ) ;
					}
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

					if ( $_SESSION['guessing-attempts'] >= buddyboss_invitation()->option( 'guessing-attempts-limit' ) ) {

						$confirmation = reset($form['confirmations']);

						$confirmation['message'] = '<h3>' . __('The Late Philip J. Fry') .'</h3>';
						$confirmation['message'] .= '<h1>' . __('Your Invitation Code') . '</h1>';
						$confirmation['message'] .= '<p>' . __('My fellow Earthicans, as I have explained in my book "Earth in Balance", and the much more popular "Harry Potter and the Balance of Earth" we need to defend our planet against pollution. Also <a href="/browse/">request a new code</a>') . '</p>';
						$confirmation['message'] .= '<a class="button" href="/browse/">' . __('Request Invitation Code') . '</a>';

						$form['confirmations'][key($form['confirmations'])] = $confirmation;

						$validation_result['is_valid'] = true;
						$field['failed_validation']    = false;
						$validation_result['form']     = $form;

						return $validation_result;
					}
					//--

					$invitation_validation_result = buddyboss_invitation()->validate_invitation_code($invite_code);

					if ( $invitation_validation_result['is_success'] ) {
						$validation_result['is_valid'] = true;
						$field['failed_validation']    = false;

						if ( $user = get_userdatabylogin(sanitize_text_field ( rgpost( 'input_3' ) ) ) ) {
							// Set Member Role to user
							wp_update_user( array (
								'ID'   => $user->ID, 
								'role' => 'member' ) 
							);

							buddyboss_invitation()->update_invitation_code($invite_code, array('activated_member_id' => $user->ID));
						} else {
							$validation_result['is_valid'] = false;
							$field['failed_validation']    = true;
							$field['validation_message']   = __('Please contact admin');
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

add_filter( 'gform_pre_render_4', function($form){

	foreach ( $form['fields'] as &$field ) {

		switch ( $field['id'] ) {

			case 17:
				$field['content'] = str_replace( '%%nickname%%', rgpost( 'input_3' ), $field['content'] );
				$field['content'] = str_replace( '%%password%%', rgpost( 'input_4' ), $field['content'] );
			break;
		}
	}

	return $form;
});

/*
* This functions uses strcasecmp for case-insensitive comparison of the user
* inputted string with the existing valid codes.
*/
function is_the_code_correct( $field_value, $invitation_code ) {

	// Get the current, valid invitation code from the file below.
	require 'includes/invitation-codes.php';

	$code_confirmed = false;

	// Loop through the array of the codes to find if any one is matching.
	foreach ( $invitation_code as $inviter => $code ) {

		// strcasecmp returns zero if two strings are case-insensitive equal.
		if ( 0 == strcasecmp( $field_value, $code ) ) {
			$code_confirmed = true;
				break;
		}
	}

	return $code_confirmed;
}