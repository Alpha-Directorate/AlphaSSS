<?php
/*
Template Name: Purchase Credits
*/
?>

<?php get_header(); ?>

<div class="page-three-columns">

	<div id="secondary" class="widget-area left-widget-area" role="complementary">
		<aside class="widget widget-error">
			<img src="https://avatars3.githubusercontent.com/u/432548?v=3&s=460">
		</aside>
	</div>

	<div id="primary" class="site-content">
	
		<div id="content" role="main">

			<h1 class="entry-title"><?php _e('Pay with BitPay'); ?></h1>
			<br />
			<p id="credit-balance"><?php printf( __( 'Your current balance is %d credits' ), get_user_meta( get_current_user_id( ), 'credit_balance', true ) ); ?></p>

			<iframe></iframe>
		</div><!-- #content -->
	</div><!-- #primary -->

	<div id="secondary" class="widget-area" role="complementary">
		The Day The Earth Stood Stoopid
	</div>

</div><!-- .page-left-column -->
<?php get_footer(); ?>