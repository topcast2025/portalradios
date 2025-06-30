<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package appside
 */

get_header();
$page_layout_meta = Appside_Group_Fields_Value::page_layout_options('blog_single');
$full_width_class = $page_layout_meta['content_column_class'] == 'col-lg-12' ? ' full-width-content-area ' : '';
?>
	<div id="primary" class="content-area blog-details-page padding-120 <?php echo esc_attr($full_width_class);?>">
		<main id="main" class="site-main">
            <div class="container">
                <div class="row">
                    <div class="<?php echo esc_attr($page_layout_meta['content_column_class']);?>">
	                    <?php
	                    while ( have_posts() ) :
		                    the_post();
		                    get_template_part( 'template-parts/content', 'single' );
		                    // If comments are open or we have at least one comment, load up the comment template.
		                    if ( comments_open() || get_comments_number() || get_option( 'thread_comments' )) :
			                    comments_template();
		                    endif;
	                    endwhile; // End of the loop.
	                    ?>
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
get_footer();
