<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package appside
 */

get_header();
$page_layout_options = Appside_Group_Fields_Value::page_layout_options('search');
?>

    <section id="primary" class="content-area search-page-content-area padding-120">
        <main id="main" class="site-main">
            <div class="container">
                <div class="row">
                    <div class="<?php echo esc_attr($page_layout_options['content_column_class']);?>">
						<?php if ( have_posts() ) : ?>
							<?php
							/* Start the Loop */
							while ( have_posts() ) :
								the_post();

								/**
								 * Run the loop for the search to output the results.
								 * If you want to overload this in a child theme then include a file
								 * called content-search.php and that will be used instead.
								 */
								get_template_part( 'template-parts/content', 'search' );

							endwhile;
							?>
                            <div class="blog-pagination">
								<?php Appside()->post_pagination(); ?>
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
    </section><!-- #primary -->

<?php

get_footer();
