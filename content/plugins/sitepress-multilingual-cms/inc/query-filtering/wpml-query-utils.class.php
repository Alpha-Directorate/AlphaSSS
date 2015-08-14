<?php

class WPML_Query_Utils {

	/**
	 * @param array|string $post_type
	 * @param WP_User      $author_data
	 * @param array        $lang
	 *
	 * @return int
	 */
	public function author_query_has_posts( $post_type, $author_data, $lang ) {
		global $wpdb;

		$post_types        = (array) $post_type;
		$post_type_snippet = (bool) $post_types ? " AND post_type IN (" . wpml_prepare_in( $post_types ) . ") " : "";

		return $wpdb->get_var( $wpdb->prepare( "
                        SELECT COUNT(p.ID) FROM {$wpdb->posts} p
						JOIN {$wpdb->prefix}icl_translations t
							ON p.ID=t.element_id AND t.element_type = CONCAT('post_', p.post_type)
						WHERE p.post_author=%d
						  " . $post_type_snippet . "
						  AND post_status='publish'
						  AND language_code=%s",
											   $author_data->ID,
											   $lang[ 'code' ] ) );
	}
}