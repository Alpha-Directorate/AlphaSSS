<?php do_action( 'bp_before_profile_loop_content' ); ?>

<?php $ud = get_userdata( bp_displayed_user_id() ); ?>

<?php if ( bp_has_profile() ) : ?>

	<?php do_action( 'bp_before_profile_field_content' ); ?>

	<div class="bp-widget required-info" id="profile-view">

		<h4><?php _e('Non-Editable Info', 'buddypress'); ?></h4>

		<table class="profile-fields">

			<tr>

				<td class="label"><?php _e('Nickname', 'buddypress'); ?> <?php echo tooltip(__('Text about nickname'));?></td>

				<td class="data"><?php echo $ud->user_login; ?></td>

			</tr>

		</table>

		<ht />

		<h4><?php _e('Optional, Editable Info', 'buddypress'); ?></h4>

		<table class="profile-fields">

			<tr>

				<td class="label"><?php _e('Dislpay Name', 'buddypress'); ?> <?php echo tooltip(__('Text about display name'));?></td>

				<td class="data"><?php echo $ud->display_name; ?></td>

			</tr>

			<tr>

				<td class="label"><?php _e('About', 'buddypress'); ?> <?php echo tooltip(__('Text about member'));?></td>

				<td class="data"><?php echo $ud->user_description; ?></td>

			</tr>

		</table>
	</div>

	<?php do_action( 'bp_after_profile_field_content' ); ?>

	<?php do_action( 'bp_profile_field_buttons' ); ?>

<?php endif; ?>

<?php do_action( 'bp_after_profile_loop_content' ); ?>
