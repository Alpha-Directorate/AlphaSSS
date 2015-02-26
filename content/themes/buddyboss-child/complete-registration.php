<?php
/*
Template Name: Complete registration
*/
?>

<?php 
	if ( ! is_user_logged_in() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		get_template_part( 404 ); exit();
	}
?>

<?php get_header(); ?>

<div id="content" role="main">
	<div id="alerts">
		<div role="alert" class="alert alert-dismissible alert-success fade in">
			<div class="alert-content">
				<?php _e('There are two ways to get an invitation code to join to the AlphaSSS:'); ?><br /><br />
				<p>&nbsp;&nbsp;<?php _e('1. The fastest: Request invitation from anybody who is online. You\'ll your code within seconds.'); ?></p>
				<p>&nbsp;&nbsp;<?php _e('2. Post your invitation request in the general forum. Someone will read it and send you invitation.'); ?></p><br />
				<?php _e('Easy Peasy!'); ?>
			</div>
		</div>
	</div>

	<article>
		<?php  echo do_shortcode('[gravityform id="9" title="false" description="false" ajax="true"]');?>
	</article>
</div>
<?php get_footer(); ?>