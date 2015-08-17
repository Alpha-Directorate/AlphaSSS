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

<div class="page-three-columns">

	<div id="secondary" class="widget-area left-widget-area" role="complementary">
		<aside class="widget widget-error">
			<img src="https://avatars3.githubusercontent.com/u/432541?v=3&s=460">
		</aside>
	</div>

	<div id="primary" class="site-content">
	
		<div id="content" role="main">

			<h1 class="entry-title"><?php _e('Select Credits', 'boss'); ?></h1>
			<br />
			<p><?php _e('How many Credits would you like to buy?', 'boss'); ?></p>

			<form id="submit-credits" action="<?php echo str_replace( '/wp', '', site_url( '/pay-with-bitpay/', \AlphaSSS\HTTP\HTTP::protocol() ) );?>" method="POST">
				<select class="form-control" id="credit-selection" name="credits-amount">
					<option value=""><?php _e('Any amount you choose:', 'boss'); ?></option>
					<?php foreach(\AlphaSSS\Repositories\Credit::creditList() as $amount):?>
						<option value="<?php echo $amount; ?>"><?php printf(__('%.2f Credits ($%.2f USD)', 'boss'), $amount, $amount);?></option>
					<?php endforeach;?>
				</select>
				
				<div class="submit">
					<button id="purchase-credits" class="button" type="submit"><?php _e('Proceed to Payment', 'boss');?></button>
				</div>
			</form>

		</div><!-- #content -->
	</div><!-- #primary -->

	<div id="secondary" class="widget-area" role="complementary">
		<div class="information-box">
			<h3><?php _e("What are Bitcoins?", 'boss'); ?></h3>
			<p>
				<?php _e("Oh sure! Blame the wizards! You know the worst thing about being a slave? They make you work, but they don't pay you or let you go. Ah, yes! John Quincy Adding Machine. He struck a chord with the voters when he pledged not to go on a killing spree.", 'boss');?>
			</p>
		</div>

		<div class="information-box">
			<h3><?php _e("What are Credits?", 'boss'); ?></h3>
			<p>
				<?php _e("You don't know how to do any of those. Doomsday device? Ah, now the ball's in Farnsworth's court! Good news, everyone! There's a report on TV with some very bad news! That's a popular name today. Little \"e\", big \"B\"? Moving along… Isn't it true that you have been paid for your testimony?", 'boss');?>
			</p>
		</div>
	</div>

</div><!-- .page-left-column -->
<script type="text/javascript">
	$(document).ready(function(){
		var credit_selection = $('#credit-selection').val();

		if (! credit_selection) {
			$('#purchase-credits').attr('disabled', true);
		}

		$('#credit-selection').change(function(){
			if ($(this).val()) {
				$('#purchase-credits').attr('disabled', false);
			} else {
				$('#purchase-credits').attr('disabled', true);
			}
		});
	});
</script>
<?php get_footer(); ?>