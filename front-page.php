<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @package GWT
 * @since Government Website Template 2.0
 * eol-60
 */

get_header(); 
include_once('inc/banner.php');
?>
		<?php govph_displayoptions( 'govph_panel_top' ); ?>

		<!-- Featured News Section -->
		<div class="container-main" role="document">
			<div class="row">
				<div class="large-12 columns">
					<?php 
					// Display the Featured News modular content box
					get_template_part( 'template-parts/modular-content-box', null, array(
						'title' => 'FEATURED NEWS',
						'class' => 'featured-news-section',
						'content_class' => 'featured-news-content',
						'template_part' => 'template-parts/content-featured'
					) ); 
					?>
				</div>
			</div>
		</div>



<?php govph_displayoptions( 'govph_panel_bottom' ); ?>

<?php get_footer(); ?>