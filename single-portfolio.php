<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package appside
 */
//portfolio settings
$portfolio_sidebar_title = cs_get_option('portfolio_sidebar_title') ? cs_get_option('portfolio_sidebar_title') : 'Information';
$portfolio_sidebar_clients = cs_get_option('portfolio_sidebar_clients') ? cs_get_option('portfolio_sidebar_clients') : 'Client';
$portfolio_sidebar_company = cs_get_option('portfolio_sidebar_company') ? cs_get_option('portfolio_sidebar_company') : 'Company';
$portfolio_sidebar_website = cs_get_option('portfolio_sidebar_website') ? cs_get_option('portfolio_sidebar_website') : 'Website';
$portfolio_sidebar_start_date = cs_get_option('portfolio_sidebar_start_date') ? cs_get_option('portfolio_sidebar_start_date') : 'Start Date';
$portfolio_sidebar_end_date = cs_get_option('portfolio_sidebar_end_date') ? cs_get_option('portfolio_sidebar_end_date') : 'End Date';
$portfolio_sidebar_category = cs_get_option('portfolio_sidebar_category') ? cs_get_option('portfolio_sidebar_category') : 'Category';

//get portfolio meta
$portfolio_options = get_post_meta(get_the_ID(),'appside_portfolio_options',true);
//category
$all_portfolio_cat = get_the_terms(get_the_ID(),'portfolio-cat');

get_header();
?>
	<div id="primary" class="content-area portfolio-details-page padding-120 ">
		<main id="main" class="site-main">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
	                    <?php
	                    while ( have_posts() ) :
		                    the_post();
		                    get_template_part( 'template-parts/content', 'single-portfolio' );
		                    // If comments are open or we have at least one comment, load up the comment template.
		                    if ( comments_open() || get_comments_number() || get_option( 'thread_comments' )) :
			                    comments_template();
		                    endif;
	                    endwhile; // End of the loop.
	                    ?>
                    </div>
                    <div class="col-lg-4">
                        <div class="widget-area portfolio-sidebar">
                            <h4 class="title"><?php echo esc_html($portfolio_sidebar_title);?></h4>
                            <ul class="portfolio-meta">
                                <?php
                                    if (!empty($portfolio_options['client'])){
                                        printf('<li><span class="label">%1$s</span> %2$s</li>',esc_html($portfolio_sidebar_clients),esc_html($portfolio_options['client']));
                                    }
                                    if (!empty($portfolio_options['company_name'])){
                                        printf('<li><span class="label">%1$s</span> %2$s</li>',esc_html($portfolio_sidebar_company),esc_html($portfolio_options['company_name']));
                                    }
                                    if (!empty($all_portfolio_cat)):?>
                                        <li>
                                            <span class="label"><?php echo esc_html($portfolio_sidebar_category) ?></span>
                                            <span class="cats">
                                                <?php
                                                    foreach ($all_portfolio_cat as $term){
                                                        printf('<a href="%1$s">%2$s</a>',get_term_link($term,'portfolio-cat'),esc_html($term->name));
                                                    }
                                                ?>
                                            </span>
                                        </li>
                                    <?php endif;
                                    if (!empty($portfolio_options['website'])){
                                        printf('<li><span class="label">%1$s</span> %2$s</li>',esc_html($portfolio_sidebar_website),esc_html($portfolio_options['website']));
                                    }
                                    if (!empty($portfolio_options['start'])){
                                        printf('<li><span class="label">%1$s</span> %2$s</li>',esc_html($portfolio_sidebar_start_date),esc_html($portfolio_options['start']));
                                    }
                                    if (!empty($portfolio_options['finish'])){
                                        printf('<li><span class="label">%1$s</span> %2$s</li>',esc_html($portfolio_sidebar_end_date),esc_html($portfolio_options['finish']));
                                    }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();
