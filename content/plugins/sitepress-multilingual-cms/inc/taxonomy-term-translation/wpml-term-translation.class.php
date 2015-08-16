<?php

/**
 * @since      3.2
 *
 * Class WPML_Term_Translation
 *
 * Provides APIs for translating taxonomy terms
 *
 * @package    wpml-core
 * @subpackage taxonomy-term-translation
 */
class WPML_Term_Translation extends WPML_Element_Translation {

	protected $ttids;
	protected $term_ids;

	public function lang_code_by_termid( $term_id ) {

		return $this->get_element_lang_code( $this->adjust_ttid_for_term_id( $term_id ) );
	}

	public function reload() {
		parent::reload();
		$this->term_ids = null;
		$this->ttids    = null;
	}

	private function maybe_warm_term_id_cache() {
		global $wpdb;

		if ( ! isset( $this->ttids ) || ! isset( $this->term_ids ) ) {
			$data           = $wpdb->get_results( "	SELECT t.element_id, tax.term_id
													 {$this->element_join}
													 JOIN {$wpdb->terms} terms
													  ON terms.term_id = tax.term_id
													 WHERE tax.term_id != tax.term_taxonomy_id",
												  ARRAY_A );
			$this->term_ids = array();
			$this->ttids    = array();
			foreach ( $data as $row ) {
				$this->ttids[ $row[ 'term_id' ] ]       = $row[ 'element_id' ];
				$this->term_ids[ $row[ 'element_id' ] ] = $row[ 'term_id' ];
			}
		}
	}

	private function adjust_ttid_for_term_id( $term_id ) {
		$this->maybe_warm_term_id_cache();

		return $term_id && isset( $this->ttids[ $term_id ] ) ? $this->ttids[ $term_id ] : $term_id;
	}

	private function adjust_term_id_for_ttid( $ttid ) {
		$this->maybe_warm_term_id_cache();

		return $ttid && isset( $this->term_ids[ $ttid ] ) ? $this->term_ids[ $ttid ] : $ttid;
	}

	public function term_id_in( $term_id, $lang_code ) {

		return $this->adjust_term_id_for_ttid(
			$this->element_id_in( $this->adjust_ttid_for_term_id( $term_id ), $lang_code )
		);
	}

	protected function get_element_join() {
		global $wpdb;

		return "FROM {$wpdb->prefix}icl_translations t
				JOIN {$wpdb->term_taxonomy} tax
					ON t.element_id = tax.term_taxonomy_id
						AND t.element_type = CONCAT('tax_', tax.taxonomy)";
	}

	public function is_translated_type( $element_type ) {
		is_taxonomy_translated( $element_type );
	}
}
