<?php
/*
Plugin Name: Alpha Hooks
Plugin URI:
Description: All filter and action hooks used on the site, gathered together as one happy family.
Version: 1.0.0
Author: Andrew Voyticky
Author URI:
*/

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
						wp_create_user( rgpost( 'input_3' ), rgpost( 'input_4' ), md5( time() ) . '@alphasss.com' );
					}
				}
			break;

			// Invitation code validation
			case 20:
				if ( isset( $_POST['input_20'] ) && $invite_code = rgpost( 'input_20' ) ) {

					$invitation_validation_result  = buddyboss_invitation()->validate_invitation_code($invite_code);

					if ( $invitation_validation_result['is_success'] ) {
						$validation_result['is_valid'] = true;
						$field['failed_validation']    = false;

						$users = new WP_User(NULL, rgpost( 'input_3' ));
						
					} else {
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