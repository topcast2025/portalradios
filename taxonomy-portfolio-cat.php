<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package appside
 */

get_header();
?>
    <div id="primary" class="content-area archive-page-content-area padding-120">
        <main id="main" class="site-main">
            <div class="container">
                <div class="row">
						<?php if ( have_posts() ) : ?>
							<?php
							/* Start the Loop */
							while ( have_posts() ) :
								the_post();
							?>
                            <div class="col-lg-4 col-md-6">
                                <div class="single-portfolio-style-01">
                                    <?php if (has_post_thumbnail()):?>
                                    <div class="thumbnail">
	                                    <?php
	                                    the_post_thumbnail( 'appside_portfolio', array(
		                                    'alt' => the_title_attribute( array(
			                                    'echo' => false,
		                                    ) ),
	                                    ) );
	                                    ?>
                                    </div>
                                    <?php endif;?>
                                    <div class="content">
                                        <a href="<?php the_permalink();?>"> <h4 class="title"><?php the_title();?></h4></a>
                                        <div class="cats">
                                            <?php
                                            $all_portfolio_cat = get_the_terms(get_the_ID(),'portfolio-cat');
                                                foreach ( $all_portfolio_cat as $term ) {
                                                    printf( '<a href="%1$s">%2$s</a>', get_term_link( $term, 'portfolio-cat' ), esc_html( $term->name ) );
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
							<?php endwhile;?>
                            <div class="col-lg-12 text-center">
                                <div class="blog-pagination">
		                            <?php Appside()->post_pagination();?>
                                </div>
                            </div>
						<?php
						else :
							get_template_part( 'template-parts/content', 'none' );
						endif;
						?>
                </div>
            </div>
        </main><!-- #main -->
    </div><!-- #primary -->

<?php

get_footer();
