<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package appside
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
do_action( 'appside_after_body' );
$page_container_meta = Appside_Group_Fields_Value::page_container( 'appside', 'header_options' );
$header_variant = !empty($page_container_meta['navbar_type']) ? $page_container_meta['navbar_type'] : 'default';
$header_variant = ($page_container_meta['navbar_build_type'] == 'header_builder' && !empty($page_container_meta['header_builder_style']) ) ? 'builder' : $header_variant;
if ($header_variant == 'default' && cs_get_option('global_navbar_build_type') == 'default'){
	$header_variant = cs_get_option('global_header_style');
}elseif($header_variant == 'default' && cs_get_option('global_navbar_build_type') == 'header_builder'){
	$header_variant = 'builder';
}
?>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'aapside' ); ?></a>
    <header id="masthead" class="site-header header-style-<?php echo esc_attr( $header_variant ); ?>">
    <?php get_template_part('template-parts/header/header',$header_variant); ?>
    </header><!-- #masthead -->
	<?php do_action( 'appside_before_page_content' ) ?>
    <div id="content" class="site-content">
