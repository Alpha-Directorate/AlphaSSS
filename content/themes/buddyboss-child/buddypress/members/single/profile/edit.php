<?php do_action( 'bp_before_profile_edit_content' );?>

<?php $ud = get_userdata( bp_displayed_user_id() ); ?>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#field_45').change(function(){
			dislay_name = $.trim( $('#field_45').val() );
			if (! dislay_name.length ){
				$(this).val($('#field_1').val());
			} 
		});
	});
</script>

<form action="<?php bp_the_profile_group_edit_form_action(); ?>1/" method="post" id="profile-edit-form" class="standard-form <?php bp_the_profile_group_slug(); ?>">

	<?php do_action( 'bp_before_profile_field_content' ); ?>

		<h4><?php _e('Non-Editable Info', 'buddypress'); ?></h4>

		<ul class="profile-edit-list">
			<li>
				<label for="nickname">
					<ul>
						<li><?php _e('Nickname', 'buddypress'); ?></li>
						<li><?php echo tooltip(__('Text about nickname'));?></li>
					</ul>
				</label>
			</li>
			<li><?php echo $ud->user_login; ?></li>
		</ul>

		<hr />

		<h4><?php _e('Optional, Editable Info', 'buddypress'); ?></h4>

		<div class="clear"></div>

		<div<?php bp_field_css_class( 'editfield' ); ?>>

			<label for="field_45">
				<ul>
					<li><?php _e('Dislpay Name', 'buddypress'); ?></li>
					<li><?php echo tooltip(__('Text about display name'));?></li>
				</ul>
			</label>
			<input type="text" name="field_45" id="field_45" value="<?php echo htmlentities(bp_get_profile_field_data( [
					'user_id' => bp_loggedin_user_id(),
					'field'   => 45
				] )); ?>" aria-required="true" />

			<label for="field_46">
				<ul>
					<li><?php _e('About', 'buddypress'); ?></li>
					<li><?php echo tooltip(__('Text about member'));?></li>
				<ul>
			</label>
			<textarea rows="5" cols="40" name="field_46" id="field_46" aria-required="true" placeholder="<?php _e('Write a little blurb about yourself. (140 characters or less)'); ?>"><?php echo htmlentities(bp_get_profile_field_data( [
					'user_id' => bp_loggedin_user_id(),
					'field'   => 'About'
				] )); ?></textarea>

		</div>

	<?php do_action( 'bp_after_profile_field_content' ); ?>

	<div class="submit">
		<input type="submit" name="profile-group-edit-submit" id="profile-group-edit-submit" value="<?php esc_attr_e( 'Save Changes', 'buddypress' ); ?> " />
	</div>

	<input type="hidden" name="field_ids" id="field_ids" value="1 45 46" />
	<input type="hidden" id="field_1" name="field_1" value="<?php echo htmlentities($ud->user_login); ?>" />
	<?php wp_nonce_field( 'bp_xprofile_edit' ); ?>

</form>

<?php do_action( 'bp_after_profile_edit_content' ); ?>
