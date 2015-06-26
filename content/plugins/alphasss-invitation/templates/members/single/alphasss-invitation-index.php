<?php if ( ! defined( 'ABSPATH' ) ) exit;?>

<link rel="stylesheet" type="text/css" href="<?php echo alphasss_invitation()->css_path(); ?>alphasss-invitation.css">

	<h2><?php _e('Space Pilot 3000', 'alphasss');?></h2>
	
	<p><?php _e('I\'ve got to find a way to escape the horrible ravages of youth. Suddenly, I\'m going to the bathroom like clockwork, every three hours. And those jerks at Social Security stopped sending me checks. Now I have to pay them! Please, Don-Bot... look into your hard drive, and open your mercy file!', 'alphasss');?></p>

	<div id="invitation_code_message" class="alphasss-invitation-hidden in-place-message">
		<p><?php _e('Here\'s the invitation code that you can use:', 'alphasss'); ?><br />

		<span id="invitation-code"></span> <br />

		<?php _e('The code is valid for 24 hours since the time it was generated.', 'alphasss');?></p>
	</div>

	<center>
		<input type="button" id="generate_code" class="button" value="<?php _e( 'Generate Invitation Code', 'alphasss' );?>" />
	</center>

<script type="text/javascript">
	$(document).ready(function(){
		$('#generate_code').click(function(){

			data = {
				action: "get_invitation_code",
				member_id: "<?php echo get_current_user_id( ) ?>"
			};

			$.post(ajaxurl, data, function(data){
				$('#generate_code').hide();
				$('#invitation-code').text(data.data.invitation_code)
				$('#invitation_code_message').show();
				
			},"json");
		});
	});
</script>