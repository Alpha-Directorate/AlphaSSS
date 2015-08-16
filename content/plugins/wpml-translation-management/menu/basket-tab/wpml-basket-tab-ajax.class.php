<?php

class WPML_Basket_Tab_Ajax {

    public function init() {
        $request = filter_input( INPUT_POST, 'action' );
        $nonce = filter_input( INPUT_POST, '_icl_nonce' );
        if ( $request && $nonce && wp_verify_nonce( $nonce, $request . '_nonce' ) ) {
            add_action( 'wp_ajax_send_basket_items', array( $this, 'send_basket_items' ) );
            add_action( 'wp_ajax_send_basket_item', array( $this, 'send_basket_item' ) );
            add_action( 'wp_ajax_send_basket_commit', array( $this, 'send_basket_commit' ) );
            add_action( 'wp_ajax_check_basket_name', array( $this, 'check_basket_name' ) );
        }
    }

    public function send_basket_item() {
        static $basket;
        $batch = isset( $_POST[ 'batch' ] ) ? $_POST[ 'batch' ] : array();
        $translators = isset( $_POST[ 'translators' ] ) ? $_POST[ 'translators' ] : array();
        $basket_name = isset( $_POST[ 'basket_name' ] ) ? $_POST[ 'basket_name' ] : array();

        if ( !$batch ) {
            wp_send_json_error( array( 'Batch is empty' ) );
        }

        if ( !isset( $basket ) || !$basket ) {
            $basket = TranslationProxy_Basket::get_basket();
        }
        foreach ( $batch as $batch_item ) {
            $element_type = $batch_item[ 'type' ];
            $post_id = $batch_item[ 'post_id' ];
            if ( !isset( $basket[ $element_type ][ $post_id ] ) ) {
                continue;
            }

        }
        global $iclTranslationManagement;
        $data = array(
            'basket_name' => $basket_name,
            'translators' => $translators,
            'batch' => $batch,
        );
        $result = $iclTranslationManagement->send_all_jobs( $data );
        $error_messages = $iclTranslationManagement->messages_by_type( 'error' );
        if ( $error_messages ) {
            $this->rollback_basket_commit();
            $result[ 'message' ] = "";
            $result[ 'additional_messages' ] = $error_messages;
            wp_send_json_error( $error_messages );
        } else {
            wp_send_json_success( $result );
        }
    }

    public function send_basket_items() {
        $basket_name = filter_input( INPUT_POST, 'basket_name', FILTER_SANITIZE_STRING );
        if ( $basket_name ) {
            TranslationProxy_Basket::set_basket_name( $basket_name );
        }
        $basket = TranslationProxy_Basket::get_basket();
        $basket_items_types = TranslationProxy_Basket::get_basket_items_types();
        if ( !$basket ) {
            $message_content = __( 'No items found in basket', 'sitepress' );
        } else {
            $total_count = 0;
            $message_content_details = '';
            foreach ( $basket_items_types as $item_type_name => $item_type ) {
                if ( isset( $basket[ $item_type_name ] ) ) {
                    $count_item_type = count( $basket[ $item_type_name ] );
                    $total_count += $count_item_type;
                    $message_content_details .= '<br/>';
                    $message_content_details .= '- ' . $item_type_name . '(s): ' . $count_item_type;
                }
            }
            $message_content = sprintf( __( '%s items in basket:', 'sitepress' ), $total_count );
            $message_content .= $message_content_details;
        }
        $container = $message_content;
        $result = array(
            'message' => $container,
            'basket' => $basket,
            'allowed_item_types' => array_keys( $basket_items_types )
        );

        wp_send_json_success( $result );
    }

    public function send_basket_commit() {
        $has_remote_translators = false;
        try {
            $response = false;
            $errors = array();
            if ( TranslationProxy::is_batch_mode() ) {
                $project                = TranslationProxy::get_current_project();
                $translators            = $_POST[ 'translators' ];
                $has_remote_translators = false;
                if ( is_array( $translators ) ) {
                    foreach ( $translators as $translator ) {
                        if ( !TranslationProxy_Service::is_local_translator( $translator ) ) {
                            $has_remote_translators = true;
                            break;
                        }
                    }
                } else {
                    $has_remote_translators = true;
                }
                if ( $project && $has_remote_translators ) {
                    $response = $project->commit_batch_job();
                    if ( !empty( $project->errors ) ) {
                        $response = false;
                    }
                } else {
                    //Local translation only: return true
                    $response = true;
                }
            }
            if ( $response ) {
                $is_error = false;
                TranslationProxy_Basket::delete_all_items_from_basket();
                $service_name = TranslationProxy::get_current_service_name();
                if ( isset( $has_remote_translators ) && $has_remote_translators ) {
                    $response->call_to_action = '<strong>' . sprintf(
                            __(
                                'You have sent items to %s. Please check if additional steps are required on their end',
                                'wpml-translation-management'
                            ),
                            $service_name
                        ) . '</strong>';
                }
            } else {
                $response = false;
                $is_error = true;
                if ( isset( $project ) && $project ) {
                    $errors = $project->errors;
                }
            }
        } catch ( Exception $e ) {
            $response = false;
            $is_error = true;
            $errors[ ] = $e->getMessage();
        }

        $result = array( 'result' => $response, 'is_error' => $is_error, 'errors' => $errors );
        if ( !empty( $errors ) ) {
            $this->rollback_basket_commit();
            wp_send_json_error( $result );
        } else {
            wp_send_json_success( $result );
        }
    }

    public function check_basket_name() {
        $basket_name = filter_input( INPUT_POST, 'basket_name', FILTER_SANITIZE_STRING );

        $basket_name_max_length = TranslationProxy::get_current_service_batch_name_max_length();

        $result = array(
            'modified' => false,
            'valid' => true,
            'message' => '',
            'new_value' => '',
        );

        $old_value = $basket_name;
        $basket_name = strip_tags( $basket_name );
        if ( $old_value != $basket_name ) {
            $result[ 'modified' ] = true;
            $result[ 'new_value' ] = $basket_name;
        }

        if ( strlen( $basket_name ) > $basket_name_max_length ) {
            $result[ 'valid' ] = false;
            $result[ 'message' ] = sprintf(
                __( 'The length of the batch name exceeds the maximum length of %s', 'wpml-translation-management' ),
                $basket_name_max_length
            );
        } elseif ( TranslationProxy::get_batch_id_from_name( $basket_name ) ) {
            $result[ 'valid' ] = true;
            $result[ 'modified' ] = true;
            $result[ 'new_value' ] = TranslationProxy_Basket::get_unique_basket_name(
                $basket_name,
                $basket_name_max_length
            );
            $result[ 'message' ] = __(
                'This batch name already exists and was modified to ensure unique naming',
                'wpml-translation-management'
            );
        } elseif ( count( $basket_name_array = explode( '|', $basket_name ) ) == 1 ) {
            $result[ 'valid' ] = true;
            $result[ 'modified' ] = true;
            $result[ 'new_value' ] = TranslationProxy_Basket::get_unique_basket_name(
                $basket_name,
                $basket_name_max_length
            );
            $result[ 'message' ] = __(
                'The batch name was appended with the source language of its elements.',
                'wpml-translation-management'
            );
        }

        wp_send_json_success( $result );
    }

    public function rollback_basket_commit() {
        TranslationProxy_Basket::get_basket( true );
        $basket_name = TranslationProxy_Basket::get_basket_name();
        $basket_name = $basket_name ? $basket_name : filter_input( INPUT_POST, 'basket_name', FILTER_SANITIZE_STRING );
        $batch_id = TranslationProxy::get_batch_id_from_name( $basket_name );
        if ( $batch_id ) {
            $batch = new WPML_Translation_Batch( $batch_id );
            $batch->cancel_all_remote_jobs();
        }
    }
}