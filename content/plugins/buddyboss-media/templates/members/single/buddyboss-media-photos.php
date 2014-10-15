<?php
/**
 * BuddyPress Media - Users Photos
 *
 * @package WordPress
 * @subpackage BuddyBoss Media
 */
?>

    <?php if ( bp_is_my_profile() ) : ?>

      <?php bp_get_template_part( 'activity/post-form' ) ?>

    <?php endif; ?>

    <?php if ( buddyboss_has_photos() ) : ?>
      <div class="gallery has-sidebar" id="buddyboss-media-grid">
      <?php while ( buddyboss_has_photos() ) : buddyboss_the_photo(); ?>

        <?php
          $image = get_buddyboss_media_photo_image();
          $tn = get_buddyboss_media_photo_tn();
          if ( is_array( $image ) && !empty( $image ) && is_array( $tn ) && !empty( $tn ) ):
        ?>
          <dl class="gallery-item">
            <dt class="gallery-icon">
              <a rel="gal_item" href="<?php echo get_buddyboss_media_photo_link(); ?>">
                <img src="<?php echo esc_url( $tn[0] ); ?>" width="<?php echo (int)$tn[1]; ?>" height="<?php echo (int)$tn[2]; ?>" data-permalink="<?php echo get_buddyboss_media_photo_permalink(); ?>" />
              </a>
              <?php echo get_buddyboss_media_photo_action(); ?>
            </dt>
          </dl>
        <?php endif; ?>

      <?php endwhile; ?>
      </div>

      <?php buddyboss_media_pagination(); ?>

    <?php else: ?>

      <div class="info" id="message"><p><?php _e( 'There were no photos found.', 'buddyboss-media' ); ?></p></div>

    <?php endif; ?>

    <div id="is-buddyboss-media-grid" data-url="<?php echo $user_photos_url; ?>"></div>