<?php
require ICL_PLUGIN_PATH . '/inc/post-translation/wpml-post-duplication.class.php';
require 'wpml-post-synchronization.class.php';
require_once 'wpml-wordpress-actions.class.php';

/**
 * Class WPML_Post_Translation
 *
 * @package    wpml-core
 * @subpackage post-translation
 */
abstract class WPML_Post_Translation extends WPML_Element_Translation {

	protected $settings;
	protected $post_translation_sync;

	public function __construct( &$settings ) {
		parent::__construct ();
		$this->settings = $settings;
	}
	
	protected function is_setup_complete( ) {
		return isset( $this->settings[ 'setup_complete' ]) && $this->settings[ 'setup_complete' ];
	}

	public function init() {
		if ( $this->is_setup_complete() ) {
			add_action ( 'save_post', array( $this, 'save_post_actions' ), 100, 2 );
		}
	}

	public function get_original_post_status( $trid, $source_lang_code = null ) {

		return $this->get_original_post_attr ( $trid, 'post_status', $source_lang_code );
	}

	public function get_original_post_date( $trid, $source_lang_code = null ) {

		return $this->get_original_post_attr ( $trid, 'post_date', $source_lang_code );
	}

	public function get_original_post_ID( $trid, $source_lang_code = null ) {

		return $this->get_original_post_attr ( $trid, 'ID', $source_lang_code );
	}

	public function get_original_menu_order( $trid, $source_lang_code = null ) {

		return $this->get_original_post_attr ( $trid, 'menu_order', $source_lang_code );
	}

	public function get_original_comment_status( $trid, $source_lang_code = null ) {

		return $this->get_original_post_attr ( $trid, 'comment_status', $source_lang_code );
	}

	public function get_original_ping_status( $trid, $source_lang_code = null ) {

		return $this->get_original_post_attr ( $trid, 'ping_status', $source_lang_code );
	}

	public function get_original_post_format( $trid, $source_lang_code = null ) {

		return get_post_format ( $this->get_original_post_ID ( $trid, $source_lang_code ) );
	}

	/**
	 * @param int     $pidd
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public abstract function save_post_actions( $pidd, $post );

	public function trash_translation ( $trans_id ) {
		if ( WPML_WordPress_Actions::is_bulk_trash( $trans_id ) ) {
			// Do nothing as the translation is part of the bulk trash.
		} else {
			wp_trash_post( $trans_id );
		}
	}

	public function untrash_translation ( $trans_id ) {

		if ( WPML_WordPress_Actions::is_bulk_untrash( $trans_id ) ) {
			// Do nothing as the translation is part of the bulk untrash.
		} else {
			wp_untrash_post( $trans_id );
		}
	}

	function untrashed_post_actions( $post_id ) {
		$translation_sync = $this->get_sync_helper ();

		$translation_sync->untrashed_post_actions ( $post_id );
	}

	public function delete_post_translation_entry( $post_id ) {
		global $wpdb;

		$sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}icl_translations
								WHERE element_id = %d
									AND element_type LIKE 'post%%'
								LIMIT 1",
		                       $post_id );
		$res = $wpdb->query( $sql );

		return $res;
	}

	public function trashed_post_actions( $post_id ) {
		$this->delete_post_actions( $post_id, true );
	}

	/**
	 * This function holds all actions to be run after deleting a post.
	 * 1. Delete the posts entry in icl_translations.
	 * 2. Set one of the posts translations or delete all translations of the post, depending on sitepress settings.
	 *
	 * @param Integer $post_id
	 * @param bool $keep_db_entries Sets whether icl_translations entries are to be deleted or kept, when hooking this to
	 * post trashing we want them to be kept.
	 */
	public function delete_post_actions( $post_id, $keep_db_entries = false ) {
		$translation_sync = $this->get_sync_helper ();

		$translation_sync->delete_post_actions ( $post_id, $keep_db_entries );
	}

	/**
	 * @param integer $post_id
	 * @param SitePress $sitepress
	 * @return bool|mixed|null|string|void
	 */
	protected function get_save_post_lang( $post_id, $sitepress ) {
		$language_code = $this->get_element_lang_code ( $post_id );
		$language_code = $language_code ? $language_code : $sitepress->get_current_language ();
		$language_code = $sitepress->is_active_language ( $language_code ) ? $language_code
			: $sitepress->get_default_language ();

		return apply_filters ( 'wpml_save_post_lang', $language_code );
	}

	/**
	 * @param int    $trid
	 * @param string $language_code
	 * @param string $default_language
	 *
	 * @return string|null
	 */
	protected abstract function get_save_post_source_lang( $trid, $language_code, $default_language );

	/**
	 * @param int    $post_id
	 * @param string $post_status
	 *
	 * @return int|null
	 */
	protected abstract function get_save_post_trid( $post_id, $post_status );

	private function get_original_post_attr( $trid, $attribute, $source_lang_code ) {
		global $wpdb;

		$legal_attributes = array(
			'post_status',
			'post_date',
			'menu_order',
			'comment_status',
			'ping_status',
			'ID'
		);
		$res              = false;
		if ( in_array ( $attribute, $legal_attributes, true ) ) {
			$attribute      = 'p.' . $attribute;
			$source_snippet = $source_lang_code === null
				? " AND t.source_language_code IS NULL "
				: $wpdb->prepare ( " AND t.language_code = %s ", $source_lang_code );
			$res            = $wpdb->get_var (
				$wpdb->prepare (
					"SELECT {$attribute}
					 {$this->element_join}
					 WHERE t.trid=%d
					{$source_snippet}
					LIMIT 1",
					$trid
				)
			);
		}

		return $res;
	}

	protected function has_save_post_action( $post ) {

		return !( !$this->is_translated_type ( $post->post_type )
		          || ( isset( $post->post_status ) && $post->post_status === "auto-draft" )
		          || isset( $_POST[ 'autosave' ] )
		          || isset( $_POST[ 'skip_sitepress_actions' ] )
		          || ( isset( $_POST[ 'post_ID' ] )
		               && $_POST[ 'post_ID' ] != $post->ID )
		          || ( isset( $_POST[ 'post_type' ] )
		               && $_POST[ 'post_type' ] === 'revision' )
		          || $post->post_type === 'revision'
		          || get_post_meta ( $post->ID, '_wp_trash_meta_status', true )
		          || ( isset( $_GET[ 'action' ] )
		               && $_GET[ 'action' ] === 'untrash' ) );
	}

	public function get_sync_helper() {
		$this->post_translation_sync = $this->post_translation_sync
			? $this->post_translation_sync : new WPML_Post_Synchronization( $this->settings, $this );

		return $this->post_translation_sync;
	}

	protected function get_element_join() {
		global $wpdb;

		return "FROM {$wpdb->prefix}icl_translations t
				JOIN {$wpdb->posts} p
					ON t.element_id = p.ID
						AND t.element_type = CONCAT('post_', p.post_type)";
	}

	public function is_translated_type( $post_type ) {
		global $sitepress;

		return $sitepress->is_translated_post_type ( $post_type );
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return string[] all language codes the post can be translated into
	 */
	public function get_allowed_target_langs( $post ) {
		global $sitepress;

		$active_languages = $sitepress->get_active_languages ();
		$can_translate    = array_keys ( $active_languages );
		$can_translate    = array_diff (
			$can_translate,
			array( $this->get_element_lang_code ( $post->ID ) )
		);

		return apply_filters ( 'wpml_allowed_target_langs', $can_translate, $post->ID, 'post' );
	}

	/** Before setting the language of the post to be saved, check if a translation in this language already exists
	 * This check is necessary, so that synchronization actions like thrashing or un-trashing of posts, do not lead to
	 * database corruption, due to erroneously changing a posts language into a state,
	 * where it collides with an existing translation. While the UI prevents this sort of action for the most part,
	 * this is not necessarily the case for other plugins like TM.
	 * The logic here first of all checks if an existing translation id is present in the desired language_code.
	 * If so but this translation is actually not the currently to be saved post,
	 * then this post will be saved to its current language. If the translation already exists,
	 * the existing translation id will be used. In all other cases a new entry in icl_translations will be created.
	 *
	 * @param Integer $trid
	 * @param String  $post_type
	 * @param String  $language_code
	 * @param Integer $post_id
	 * @param String  $source_language
	 */
	protected function maybe_set_elid( $trid, $post_type, $language_code, $post_id, $source_language ) {
		global $sitepress;

		$element_type = 'post_' . $post_type;
		$sitepress->set_element_language_details (
			$post_id,
			$element_type,
			$trid,
			$language_code,
			$source_language
		);
	}
}
