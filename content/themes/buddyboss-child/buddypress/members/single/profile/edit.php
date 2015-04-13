<?php do_action( 'bp_before_profile_edit_content' );?>

<link rel='stylesheet' id='gforms_reset_css-css'  href='https://development.alphasss.com/content/plugins/gravityforms/css/formreset.css?ver=1.9.3' type='text/css' media='all' />
<link rel='stylesheet' id='gforms_formsmain_css-css'  href='https://development.alphasss.com/content/plugins/gravityforms/css/formsmain.css?ver=1.9.3' type='text/css' media='all' />
<link rel='stylesheet' id='gforms_ready_class_css-css'  href='https://development.alphasss.com/content/plugins/gravityforms/css/readyclass.css?ver=1.9.3' type='text/css' media='all' />
<link rel='stylesheet' id='gforms_browsers_css-css'  href='https://development.alphasss.com/content/plugins/gravityforms/css/browsers.css?ver=1.9.3' type='text/css' media='all' />

<?php $ud = get_userdata( bp_displayed_user_id() ); ?>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$("#about-count").text((140 - $('#field_46').val().length) + " characters left" )

		$("#field_46").keyup(function(){
		  $("#about-count").text((140 - $(this).val().length) + " characters left" );
		});

		$('#profile-edit-form').submit(function(event){

			var el    = $(this);
			var event = event;

			data = {
				action: "validate_user_profile",
				display_name: $('#field_45').val()
			};

			$.post(ajaxurl, data, function(data){
				
				if (data.error) {
					$('#display-name-error').text(data.error);
					$('#field_45').parent().addClass('profile-error');
				} else {
					$('#field_45').parent().removeClass('profile-error');
					$('#display-name-error').text('');
					document.forms['profile-edit-form'].submit();
				}
			},"json");

			return false;
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

			<div>
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

				<div id="display-name-error" style="color: #790000;font-weight: bold;letter-spacing: normal;"></div>
			</div>

			<label for="field_46">
				<ul>
					<li><?php _e('About', 'buddypress'); ?></li>
					<li><?php echo tooltip(__('Text about member'));?></li>
				<ul>
			</label>
			<textarea rows="5" cols="40" name="field_46" id="field_46" aria-required="true" placeholder="<?php _e('Write a little blurb about yourself. (140 characters or less)'); ?>"><?php echo htmlentities(bp_get_profile_field_data( [
					'user_id' => bp_loggedin_user_id(),
					'field'   => 'About'
				] )); ?></textarea><br />
			<span id="about-count" style="float:right"></span>

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
