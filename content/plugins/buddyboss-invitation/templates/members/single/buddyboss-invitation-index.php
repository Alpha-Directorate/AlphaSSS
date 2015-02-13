<?php if ( ! defined( 'ABSPATH' ) ) exit;?>

<link rel="stylesheet" type="text/css" href="<?php echo buddyboss_invitation()->css_path(); ?>buddyboss-invitation.css">

<div id="buddypress">
	<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
		<ul>
			<li class="selected" id="photos-all"><a href="#"><?php _e( 'Invite Someone', 'buddyboss-invitation' );?></a></li>
		</ul>
	</div>

	<h2>Space Pilot 3000</h2>
	
	<p>I've got to find a way to escape the horrible ravages of youth. Suddenly, I'm going to the bathroom like clockwork, every three hours. And those jerks at Social Security stopped sending me checks. Now 'I" have to pay "them'! Please, Don-Bot... look into your hard drive, and open your mercy file!</p>

	<div id="invitation_code_message" class="buddyboss-invitation-hidden in-place-message">
		<p>Here's the invitation code that you can use:<br />

		<span id="invitation-code"></span> <br />

		The code is valid for 24 hours since the time it was generated.</p>
	</div>

	<center>
		<input type="button" id="generate_code" class="button" value="<?php _e( 'Generate Invitation Code', 'buddyboss-invitation' );?>" />
	</center>
</div>

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