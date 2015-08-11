<?php if ( ! defined( 'ABSPATH' ) ) exit;

	final class CEI_Option extends WP_Customize_Setting {
	
		/**
		 * Import an option value for this setting.
		 *
		 * @since 0.3
		 * @param mixed $value The option value.
		 * @return void
		 */
		public function import( $value ) 
		{
			$this->update( $value );	
		}
	}
?>