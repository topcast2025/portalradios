<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package appside
 */

get_header();
$page_layout_options = Appside_Group_Fields_Value::page_layout_options('archive');
?>

    <div id="primary" class="content-area archive-page-content-area padding-120">
        <main id="main" class="site-main">
            <div class="container">
                <div class="row">
                    <div class="<?php echo esc_attr($page_layout_options['content_column_class']);?>">
						<?php if ( have_posts() ) : ?>

							<?php
							/* Start the Loop */
							while ( have_posts() ) :
								the_post();

								/*
								 * Include the Post-Type-specific template for the content.
								 * If you want to override this in a child theme, then include a file
								 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
								 */
								get_template_part( 'template-parts/content', get_post_format() );

							endwhile;
							?>
                            <div class="blog-pagination">
								<?php Appside()->post_pagination();?>
                            </div>

						<?php
						else :

							get_template_part( 'template-parts/content', 'none' );

						endif;
						?>
                    </div>
					<?php if ($page_layout_options['sidebar_enable']):?>
                        <div class="<?php echo esc_attr($page_layout_options['sidebar_column_class']);?>">
							<?php get_sidebar();?>
                        </div>
					<?php endif;?>
                </div>
            </div>
        </main><!-- #main -->
    </div><!-- #primary -->

<?php

get_footer();
