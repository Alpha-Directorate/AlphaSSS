<?php
/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss/buddyboss-inc/theme-functions.php
 * Add your own functions in this file.
 */

/**
 * Sets up theme defaults
 *
 * @since BuddyBoss 3.0
 */
function buddyboss_child_setup()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   * Read more at: http://www.buddyboss.com/tutorials/language-translations/
   */

  // Translate text from the PARENT theme.
  load_theme_textdomain( 'buddyboss', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'buddyboss' instances in all child theme files to 'buddyboss_child'.
  // load_theme_textdomain( 'buddyboss_child', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'buddyboss_child_setup' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since BuddyBoss 3.0
 */
function buddyboss_child_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

  wp_enqueue_script( 'bootstrap-js', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js', array('jquery') );
  wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array('bootstrap-js') );

    /*
   * Styles
   */
  wp_enqueue_style( 'buddyboss-child-custom', get_stylesheet_directory_uri().'/css/custom.css' );
  wp_enqueue_style( 'bootstrap-css', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css' );
}
add_action( 'wp_enqueue_scripts', 'buddyboss_child_scripts_styles', 9999 );

add_filter('alphasss_top_alerts', function(){

  echo '<div id="top-alerts"></div>';

});

/****************************** CUSTOM FUNCTIONS ******************************/

// Add your own custom functions here


function tooltip($translation, $position = 'top')
{
 	return sprintf('<div class="alphasss-tooltip" data-delay-show="100" data-toggle="tooltip" data-placement="top" title="%s"></div>', $translation);
}


?>