<?php
/**
 * Plugin Name: Alphasss Profile
 * Plugin URI:  http://alphasss.com/
 * Description: Alphasss Profile
 * Author:      AlphaSSS
 * Author URI:  http://alphasss.com
 * Version:     0.0.1
 * Text Domain: alphasss
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

load_textdomain( 'alphasss', WP_LANG_DIR . '/plugins/alphasss/alphasss-' . get_locale() . '.mo' );

add_action( 'plugins_loaded', function(){
	add_action('xprofile_data_after_save', function($field){

		if ($field->field_id == 45) {
			//Set display name

			wp_update_user( [
				'ID'           => $field->user_id,
				'display_name' => $field->value
			] );
		}
	});

	add_action( 'xprofile_data_before_save', function($field){

		switch ($field->field_id) {
			case 45:
				if ( ! preg_match( '/^[a-z0-9\'_.-]+$/i', $field->value ) ) {

					$field->field_id = 0;
					bp_core_add_message(__("You may use only the following characters: letters (a-z), numbers (0-9), dashes (-), underscores (_), apostrophes ('), and periods (.). Try again please.", 'alphasss'), 'error');
				}

				$display_name = bp_get_profile_field_data( [
					'user_id' => bp_loggedin_user_id(),
					'field'   => 45
				] );

				$nickname = bp_get_profile_field_data( [
					'user_id' => bp_loggedin_user_id(),
					'field'   => 1
				] );

				if ( $display_name != $field->value OR $nickname != $field->value) {
					// User exists? Show validation error
					if ( username_exists( $field->value ) ) {

						$field->field_id = 0;
						bp_core_add_message(__('This nickname is already taken. Please choose another one.', 'alphasss'), 'error');
					}
				}
			break;
		}
	}, 1, 1 );

	/**
	 * This action validate user profile edit
	 */
	add_action( 'wp_ajax_validate_user_profile', function(){

		header('Content-Type: application/json');

		$data = [];

		if (isset($_POST['display_name'])) {
			$dispaly_name = $_POST['display_name'];

			if ( ! preg_match( '/^[a-z0-9\'_.-]+$/i', $dispaly_name ) ) {
				$data['error'] = __("You may use only the following characters: letters (a-z), numbers (0-9), dashes (-), underscores (_), apostrophes ('), and periods (.). Try again please.", 'alphasss');
			}

			$exists_display_name = bp_get_profile_field_data( [
				'user_id' => bp_loggedin_user_id(),
				'field'   => 45
			] );

			$nickname = bp_get_profile_field_data( [
				'user_id' => bp_loggedin_user_id(),
				'field'   => 1
			] );

			if ( $exists_display_name != $dispaly_name OR $nickname != $dispaly_name) {
				// User exists? Show validation error
				if ( username_exists( $dispaly_name ) ) {

					$data['error'] = __('This nickname is already taken. Please choose another one.', 'alphasss');
				}
			}

			if (! trim($dispaly_name)) {
				$data['error'] = __('Please choose your nickname.', 'alphasss');
			}
		}

		echo json_encode($data);

		wp_die();
	});

}, 10, 2);
?>