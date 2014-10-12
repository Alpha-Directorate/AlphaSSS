<?php

/**
 * BuddyPress - Group Header
 *
 * @package BuddyPress
 * @subpackage BuddyBoss
 */

?>

<?php do_action( 'bp_before_group_header' ); ?>

<div id="item-actions">

	<?php if ( bp_group_is_visible() ) : ?>

		<h3><?php _e( 'Group Admins', 'buddyboss' ); ?></h3>

		<?php bp_group_list_admins();

		do_action( 'bp_after_group_menu_admins' );

		if ( bp_group_has_moderators() ) :
			do_action( 'bp_before_group_menu_mods' ); ?>

			<h3><?php _e( 'Group Mods' , 'buddyboss' ); ?></h3>

			<?php bp_group_list_mods();

			do_action( 'bp_after_group_menu_mods' );

		endif;

	endif; ?>

</div><!-- #item-actions -->
<div id="item-header-avatar">
	<a href="<?php bp_group_permalink(); ?>" title="<?php bp_group_name(); ?>">

		<?php bp_group_avatar(); ?>

	</a>
</div><!-- #item-header-avatar -->

<div id="item-header-content">
	
	<span class="highlight"><?php bp_group_type(); ?></span>
	<span class="activity"><?php printf( __( 'active %s', 'buddyboss' ), bp_get_group_last_active() ); ?></span>

	<?php do_action( 'bp_before_group_header_meta' ); ?>

	<div id="item-meta">
		
		<?php bp_group_description(); ?>
		<?php do_action( 'bp_group_header_meta' ); ?>

	</div>
</div><!-- #item-header-content -->
<div id="item-buttons" class="group">

			<?php do_action( 'bp_group_header_actions' ); ?>

		</div><!-- #item-buttons -->
<?php
do_action( 'bp_after_group_header' );
do_action( 'template_notices' );
?>