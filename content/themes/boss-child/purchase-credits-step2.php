<?php
/*
Template Name: Purchase Credits
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
			if ( in_array( [ 'member', 'gf', 'administrator' ], $user->roles ) ) {
				$wp_query->set_404();
				status_header( 404 );
				get_template_part( 404 ); exit();
			} 
		}
	}
?>

<?php get_header(); ?>

<div class="page-three-columns page-three-columns-custom">

	<div id="secondary" class="widget-area left-widget-area secondary-custom" role="complementary">
		<aside class="widget widget-error">
			<img src="https://avatars3.githubusercontent.com/u/432548?v=3&s=460">
		</aside>
	</div>

	<div id="primary" class="site-content primary-custom">
	
		<div id="content" role="main">

			<h1 class="entry-title"><?php _e('Pay with BitPay'); ?></h1>
			<br />
			<p id="credit-balance"><?php printf( __( 'Your current balance is %d credits', 'boss' ), get_user_meta( get_current_user_id( ), 'credit_balance', true ) ); ?></p>

			<?php $order = \AlphaSSS\Repositories\Order::getLastUserOrder( get_current_user_id( ) );?>

			<iframe src="<?php echo $order['url'];?>" name="bitpay_checkout" id="bitpay-iframe"></iframe>
		</div><!-- #content -->
	</div><!-- #primary -->

	<div id="secondary" class="widget-area secondary-custom-right" role="complementary">
		<div class="information-box">
			<h3><?php _e("The Day The Earth Stood Stoopid", 'boss'); ?></h3>
			<p>
				<?php _e("Of all the friends I've had… you're the first. Dear God, they'll be killed on our doorstep! And there's no trash pickup until January 3rd. Goodbye, friends. I never thought I'd die like this. But I always really hoped. Yeah. Give a little credit to our public schools. You'll have all the Slurm you can drink when you're partying with Slurms McKenzie", 'boss');?>
			</p>
		</div>
	</div>

</div><!-- .page-left-column -->

<?php get_footer(); ?>