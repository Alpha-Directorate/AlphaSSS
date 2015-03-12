<?php
/*
Template Name: Complete registration
*/
?>

<?php 
	global $wp_query;

	// Only logged in users can see this page
	if ( ! is_user_logged_in()) {
		$wp_query->set_404();
		status_header( 404 );
		get_template_part( 404 ); exit();
	} else {
		$user = wp_get_current_user();

		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			// Show 404 for all no member users
			if ( in_array( 'member', $user->roles ) ) {
				$wp_query->set_404();
				status_header( 404 );
				get_template_part( 404 ); exit();
			} 
		}
	}
?>

<?php get_header(); ?>

<div id="content" role="main">
	<div id="alerts">

		<?php if ( $received_codes = alphasss_invitation()->get_user_received_codes( get_current_user_id() ) ):?>
			<?php foreach (alphasss_invitation()->get_user_received_codes( get_current_user_id() ) as $invitation_code): ?>
				<div role="alert" class="alert alert-dismissible <?php echo $invitation_code['is_expired'] ? 'alert-danger': 'alert-success';?> fade in">
					<div class="alert-content">
						<p><?php printf(__('%s has sent you the following invitation code %s ago:'), $invitation_code['nickname'], $invitation_code['date']); ?></p>
						<?php if ($invitation_code['is_expired']):?>
							<h1><b><strike><?php echo $invitation_code['invitation_code']; ?></strike></b></h1>
							<p><?php _e('This code older than 24 hrs, therefore, it is no longer valid.'); ?></p><br />
						<?php else:?>
							<h1><b><?php echo $invitation_code['invitation_code']; ?></b></h1>
							<p><?php _e('Use it for register now. This code is valid only 24 hours'); ?></p><br />
						<?php endif;?>
					</div>
				</div>	
			<?php endforeach; ?>
		<?php else:?>
			<div role="alert" class="alert alert-dismissible alert-success fade in">
				<div class="alert-content">
					<?php _e('There are two ways to get an invitation code to join to the AlphaSSS:'); ?><br /><br />
					<p>&nbsp;&nbsp;<?php _e('1. The fastest: Request invitation from anybody who is online. You\'ll your code within seconds.'); ?></p>
					<p>&nbsp;&nbsp;<?php _e('2. Post your invitation request in the general forum. Someone will read it and send you invitation.'); ?></p><br />
					<?php _e('Easy Peasy!'); ?>
				</div>
			</div>
		<?php endif;?>
	</div>

	<article>
		<?php  echo do_shortcode('[gravityform id="9" title="false" description="false" ajax="true"]');?>
	</article>
</div>
<?php get_footer(); ?>