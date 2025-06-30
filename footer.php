<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package appside
 */
$page_container_meta = Appside_Group_Fields_Value::page_container( 'appside', 'header_options' );
$footer_variant = !empty($page_container_meta['footer_build_type']) ? $page_container_meta['footer_build_type'] : 'default';
$footer_variant = ($page_container_meta['footer_build_type'] == 'footer_builder' && !empty($page_container_meta['footer_builder_style']) ) ? 'builder' : $footer_variant;
if($footer_variant == 'default' && cs_get_option('global_footer_build_type') == 'footer_builder'){
	$footer_variant = 'builder';
}
?>
	</div><!-- #content -->

 <?php get_template_part('template-parts/footer/footer',$footer_variant); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
