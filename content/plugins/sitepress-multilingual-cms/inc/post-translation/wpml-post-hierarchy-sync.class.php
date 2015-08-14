<?php

class WPML_Post_Hierarchy_Sync extends WPML_Hierarchy_Sync {

	protected $element_id_column        = 'ID';
	protected $parent_element_id_column = 'ID';
	protected $parent_id_column         = 'post_parent';
	protected $element_type_column      = 'post_type';
	protected $element_type_prefix      = 'post_';

	public function __construct() {
		global $wpdb;

		parent::__construct();
		$this->elements_table = $wpdb->posts;
	}
}