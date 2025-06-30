<?php
/**
 * The template for displaying WooCommerce products
 * @package Attorg
 * @since 1.0.2
 */

get_header();
$page_class = is_product() ? 'woocommerce-single-product-page-content-area' : 'woocommerce-page-content-area';
?>

	<div id="primary" class="content-area <?php echo esc_attr($page_class);?> padding-top-120 padding-bottom-105">
		<main id="main" class="site-main">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="wc-page-content-inner">
							<?php woocommerce_content(); ?>
						</div>
					</div>
				</div>
			</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
