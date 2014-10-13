<?php
/**
 * @package WordPress
 * @subpackage BuddyBoss Wall
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function buddyboss_wall_gettext_filter( $translated_text, $text, $domain )
{
  if ( $domain === 'buddypress' && $text === 'Favorite' )
  {
    $translated_text = __( 'Like', 'buddyboss-wall' );
  }
  else if ( $domain === 'buddypress' && $text === 'Remove Favorite' )
  {
    $translated_text = __( 'Unlike', 'buddyboss-wall' );
  }
  
  if( $domain === 'buddypress' && $text === 'Mark as Favorite' ){
	  $translated_text = __( 'Like this', 'buddyboss-wall' );
  }

  return $translated_text;
}
add_filter( 'gettext', 'buddyboss_wall_gettext_filter', 10000, 3 );


?>