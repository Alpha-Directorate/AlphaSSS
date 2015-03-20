<?php do_action( 'bp_before_profile_edit_content' );?>

<?php $ud = get_userdata( bp_displayed_user_id() ); ?>

<form action="<?php bp_the_profile_group_edit_form_action(); ?>" method="post" id="profile-edit-form" class="standard-form <?php bp_the_profile_group_slug(); ?>">

	<?php do_action( 'bp_before_profile_field_content' ); ?>

		<h4><?php _e('Non-Editable Info', 'buddypress'); ?></h4>

		<label for="nickname"><?php _e('Nickname', 'buddypress'); ?> <?php echo tooltip(__('Text about nickname'));?></label>

		<?php echo $ud->user_login; ?>

		<h4><?php _e('Optional, Editable Info', 'buddypress'); ?></h4>

		<div class="clear"></div>

		<div<?php bp_field_css_class( 'editfield' ); ?>>

			<label for="field_45"><?php _e('Dislpay Name', 'buddypress'); ?> <?php echo tooltip(__('Text about display name'));?></label>
			<input type="text" name="field_45" id="field_45" value="<?php echo htmlentities($ud->display_name); ?>" aria-required="true" />

			<label for="field_46"><?php _e('About', 'buddypress'); ?> <?php echo tooltip(__('Text about member'));?></label>
			<textarea rows="5" cols="40" name="field_46" id="field_46" aria-required="true" placeholder="<?php _e('Write a little blurb about yourself. (140 characters or less)'); ?>"><?php echo htmlentities($ud->description); ?></textarea>

		</div>

	<?php do_action( 'bp_after_profile_field_content' ); ?>

	<div class="submit">
		<input type="submit" name="profile-group-edit-submit" id="profile-group-edit-submit" value="<?php esc_attr_e( 'Save Changes', 'buddypress' ); ?> " />
	</div>

	<input type="hidden" name="field_ids" id="field_ids" value="<?php bp_the_profile_group_field_ids(); ?>" />
	<input type="hidden" name="field_1" value="<?php echo htmlentities($ud->user_login); ?>" />
	<?php wp_nonce_field( 'bp_xprofile_edit' ); ?>

</form>

<?php do_action( 'bp_after_profile_edit_content' ); ?>
