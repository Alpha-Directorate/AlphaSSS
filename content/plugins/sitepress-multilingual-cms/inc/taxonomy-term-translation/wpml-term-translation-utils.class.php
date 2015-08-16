<?php

class WPML_Term_Translation_Utils {

	public function sync_terms( $original_post_id, $lang ) {
		$this->synchronize_terms ( $original_post_id, $lang, false );
		clean_object_term_cache ( $original_post_id, get_post_type ( $original_post_id ) );
	}

	private function synchronize_terms( $original_post_id, $lang, $duplicate ) {
		global $wpml_post_translations;

		$wpml_post_translations->reload ();
		$translated_post_id = $wpml_post_translations->element_id_in ( $original_post_id, $lang );
		if ( (bool) $translated_post_id === true ) {
			$taxonomies = get_post_taxonomies($original_post_id);

			foreach ( $taxonomies as $tax ) {
				$terms_on_original = wp_get_object_terms ( $original_post_id, $tax );
				/** @var int[] $translated_terms translated term_ids */
				$translated_terms = $this->get_translated_term_ids ( $terms_on_original, $lang, $tax, $duplicate );
				wp_set_object_terms ( $translated_post_id, $translated_terms, $tax );
			}
		}
	}

	private function get_translated_term_ids( $terms, $lang, $taxonomy, $duplicate ) {
		/** @var WPML_Term_Translation $wpml_term_translations */
		global $wpml_term_translations;

		$wpml_term_translations->reload ();
		$translated_terms = array();
		foreach ( $terms as $orig_term ) {
			$translated_id = (int) $wpml_term_translations->term_id_in ( $orig_term->term_id, $lang );

			if ( !$translated_id && $duplicate ) {
				$translation   = WPML_Terms_Translations::create_automatic_translation (
					array(
						'lang_code'       => $lang,
						'taxonomy'        => $taxonomy,
						'trid'            => $wpml_term_translations->get_element_trid ( $orig_term->term_taxonomy_id ),
						'source_language' => $wpml_term_translations->get_element_lang_code (
							$orig_term->term_taxonomy_id
						)
					)
				);
				$translated_id = isset( $translation[ 'term_id' ] ) ? $translation[ 'term_id' ] : false;

			}
			if ( $translated_id ) {
				$translated_terms[ ] = $translated_id;
			}
		}

		return $translated_terms;
	}

	public function duplicate_terms( $original_post_id, $lang ) {
		$this->synchronize_terms ( $original_post_id, $lang, true );
		clean_object_term_cache ( $original_post_id, get_post_type ( $original_post_id ) );
	}
}