<?php
/**
 * The template for displaying WordPress pages.
 *
 * @package WordPress
 * @subpackage Boss
 * @since Boss 1.0
 */
get_header(); ?>

<?php if ( is_active_sidebar('woo_sidebar') ) : ?>
<div class="page-right-sidebar">
<?php else : ?>
<div class="page-full-width">
<?php endif; ?>

	<div id="primary" class="site-content">
		<div id="woo-content" role="main">

			<?php woocommerce_content(); ?>

		</div><!-- #content -->
	</div><!-- #primary -->
    
    <div id="secondary" class="widget-area">
		<?php 
        if (is_active_sidebar('woo_sidebar')):
            dynamic_sidebar('woo_sidebar');
        endif;
        ?>
	</div>

</div><!-- .page-full-width -->

<?php get_footer(); ?>
