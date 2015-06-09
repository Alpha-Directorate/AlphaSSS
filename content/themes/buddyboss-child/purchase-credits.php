<?php
/*
Template Name: Purchase Credits
*/
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

			<h1 class="entry-title"><?php _e('Select Credits'); ?></h1>

			<p><?php _e('How many Credits would you like to buy?'); ?></p>

			<form >
				<select>
					
				</select>

				<button>Proceed to Payment</button>
			</form>

		</div><!-- #content -->
	</div><!-- #primary -->

	<div id="secondary" class="widget-area" role="complementary">
		What are Bitcoins?

		What are Credits?
	</div>

</div><!-- .page-left-column -->
<?php get_footer(); ?>