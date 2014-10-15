<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Media
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function buddyboss_media_content_all_media( $content ){
	if( !is_main_query() )
		return $content;
	
	if( buddyboss_media()->option('all-media-page') && is_page( buddyboss_media()->option('all-media-page') ) ){
		//this is the page that was set in admin to display all media content
		//lets generate the html for all media content
		add_filter( 'buddyboss_media_screen_content_pages_sql',		'buddyboss_media_screen_content_pages_sql' );
		add_filter( 'buddyboss_media_screen_content_sql',			'buddyboss_media_screen_content_sql' );
		
		$content .= buddyboss_media_buffer_template_part( 'global-media', false );
		
		remove_filter( 'buddyboss_media_screen_content_sql',		'buddyboss_media_screen_content_sql' );
		remove_filter( 'buddyboss_media_screen_content_pages_sql',	'buddyboss_media_screen_content_pages_sql' );
	}
	return $content;
}
add_filter( 'the_content', 'buddyboss_media_content_all_media' );

/**
 * load the template file by looking into childtheme, parent theme, plugin's template folder, in that order.
 * looks for buddyboss-media/$template.php inside child/parent themes.
 * 
 * @param string $template name of the template file, without '.php'
 */
function buddyboss_media_load_template($template){
	$template .= '.php';
    if(file_exists(STYLESHEETPATH.'/buddyboss-media/'.$template))
        include_once(STYLESHEETPATH.'/buddyboss-media/'.$template);
    else if(file_exists(TEMPLATEPATH.'buddyboss-media/'.$template))
        include_once (TEMPLATEPATH.'/buddyboss-media/'.$template);
    else 
        include_once buddyboss_media()->templates_dir.'/'.$template;
}

function buddyboss_media_buffer_template_part( $template, $echo=true ){
	ob_start();
	
	buddyboss_media_load_template( $template );
	// Get the output buffer contents
	$output = ob_get_clean();

	// Echo or return the output buffer contents
	if ( true === $echo ) {
		echo $output;
	} else {
		return $output;
	}
}