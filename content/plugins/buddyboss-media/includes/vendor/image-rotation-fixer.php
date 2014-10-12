<?php
/*  Copyright 2012 Mert Yazicioglu  (email : mert@mertyazicioglu.com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

function buddyboss_media_merty_attachment_uploaded( $id )
{
	$attachment = get_post( $id );
  $path = get_attached_file( $id );

	if ( 'image/jpeg' == $attachment->post_mime_type && !empty( $path ) && file_exists( $path ) )
  {
    $status = buddyboss_media_merty_fix_rotation( $path );

    if ( $status != false && ! empty( $status ) )
    {
      $attachment_meta = wp_generate_attachment_metadata( $id, $path );

      wp_update_attachment_metadata( $id, $attachment_meta );
    }
  }
}
add_action( 'buddyboss_media_add_attachment', 'buddyboss_media_merty_attachment_uploaded' );

function buddyboss_media_merty_fix_rotation( $source )
{
  if ( ! file_exists( $source ) )
    return false;

	$filename = basename( $source );

	$destination = $source;

	$size = getimagesize( $source );

	$width = $size[0];
	$height = $size[1];

	$sourceImage = imagecreatefromjpeg( $source );

	$destinationImage = imagecreatetruecolor( $width, $height );

	imagecopyresampled( $destinationImage, $sourceImage, 0, 0, 0, 0, $width, $height, $width, $height );

	if( function_exists( 'exif_read_data' ) ){
		$exif = exif_read_data( $source );

		$ort = $exif['Orientation'];

		switch ( $ort ) {

			case 2:
				buddyboss_media_merty_flip_image( $dimg );
				break;
			case 3:
				$destinationImage = imagerotate( $destinationImage, 180, -1 );
				break;
			case 4:
				buddyboss_media_merty_flip_image( $dimg );
				break;
			case 5:
				buddyboss_media_merty_flip_image( $destinationImage );
				$destinationImage = imagerotate( $destinationImage, -90, -1 );
				break;
			case 6:
				$destinationImage = imagerotate( $destinationImage, -90, -1 );
				break;
			case 7:
				buddyboss_media_merty_flip_image( $destinationImage );
				$destinationImage = imagerotate( $destinationImage, -90, -1 );
				break;
			case 8:
				$destinationImage = imagerotate( $destinationImage, 90, -1 );
				break;
		}
	}

	return imagejpeg( $destinationImage, $destination, 100 );
}

function buddyboss_media_merty_flip_image( &$image )
{
	$x = 0;
	$y = 0;
	$height = null;
	$width = null;

  if ( $width  < 1 )
  	$width  = imagesx( $image );

  if ( $height < 1 )
  	$height = imagesy( $image );

  if ( function_exists('imageistruecolor') && imageistruecolor( $image ) )
      $tmp = imagecreatetruecolor( 1, $height );
  else
      $tmp = imagecreate( 1, $height );

  $x2 = $x + $width - 1;

  for ( $i = (int)floor( ( $width - 1 ) / 2 ); $i >= 0; $i-- ) {
      imagecopy( $tmp, $image, 0, 0, $x2 - $i, $y, 1, $height );
      imagecopy( $image, $image, $x2 - $i, $y, $x + $i, $y, 1, $height );
      imagecopy( $image, $tmp, $x + $i,  $y, 0, 0, 1, $height );
  }

  imagedestroy( $tmp );

  return true;
}