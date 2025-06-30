<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Appside
 */

get_header();

$page_layout_meta = Appside_Group_Fields_Value::page_layout('appside');
$page_container_meta = Appside_Group_Fields_Value::page_container('appside','container_options');
$full_width_class = $page_layout_meta['content_column_class'] == 'col-lg-12' ? ' full-width-content-area ' : '';
?>
<?php

if ('blank' == $page_layout_meta['layout']):

	while ( have_posts() ) :
		the_post();

		get_template_part( 'template-parts/content', 'page' );

	endwhile; // End of the loop.

else:
	?>
    <div id="primary" class="content-area appside-page-content-area padding-120 page-content-wrap-<?php the_ID(); ?> <?php echo esc_attr($full_width_class);?>">
        <main id="main" class="site-main">
            <div class="<?php echo esc_attr($page_container_meta['page_container_class'])?>">
                <div class="row">
                    <div class="<?php echo esc_attr($page_layout_meta['content_column_class']);?>">
                        <div class="page-content-inner-<?php the_ID(); ?>">
							<?php
							while ( have_posts() ) :
								the_post();
								get_template_part( 'template-parts/content', 'page' );

								// If comments are open or we have at least one comment, load up the comment template.
								if ( comments_open() || get_comments_number() ) :
									comments_template();
								endif;

							endwhile; // End of the loop.
							?>
                        </div>
                    </div>
					<?php if ($page_layout_meta['sidebar_enable']): ?>
                        <div class="<?php echo esc_attr($page_layout_meta['sidebar_column_class']);?>">
							<?php get_sidebar();?>
                        </div>
					<?php endif; ?>
                </div>
            </div>
        </main><!-- #main -->
    </div><!-- #primary -->
<?php

endif;
get_footer();
