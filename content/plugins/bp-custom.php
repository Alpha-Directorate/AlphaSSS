<?php
// Remove gavatar from buddypress
add_filter( 'bp_core_fetch_avatar_no_grav', '__return_true' );

// Load costumized buddypress translations
$locale_file = WP_LANG_DIR . '/plugins/buddypress/buddypress-' . get_locale() . '.mo';

if ( file_exists( $locale_file ) ) {
	load_textdomain( 'buddypress', $locale_file );
}
?>