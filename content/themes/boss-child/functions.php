<?php
/**
 * @package Boss Child Theme
 * The parent theme functions are located at /boss/buddyboss-inc/theme-functions.php
 * Add your own functions in this file.
 */

/**
 * Sets up theme defaults
 *
 * @since Boss Child Theme 1.0.0
 */
function boss_child_theme_setup()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   * Read more at: http://www.buddyboss.com/tutorials/language-translations/
   */

  // Translate text from the PARENT theme.
  load_theme_textdomain( 'boss', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'boss' instances in all child theme files to 'boss_child_theme'.
  // load_theme_textdomain( 'boss_child_theme', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'boss_child_theme_setup' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function boss_child_theme_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

  /*
   * Styles
   */
  wp_enqueue_style( 'boss-child-custom', get_stylesheet_directory_uri().'/css/custom.css' );

  wp_enqueue_script( 'bootstrap-js', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js', array('jquery') );
  wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array('bootstrap-js') );

    /*
   * Styles
   */
  wp_enqueue_style( 'buddyboss-child-custom', get_stylesheet_directory_uri().'/css/custom.css' );
  wp_enqueue_style( 'bootstrap-css', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css' );
}
add_action( 'wp_enqueue_scripts', 'boss_child_theme_scripts_styles', 9999 );

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