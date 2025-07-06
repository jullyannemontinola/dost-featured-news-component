<?php
/**
 * Single Post Template
 * Template for displaying individual blog posts and news articles
 * 
 * @package GWT Child Theme
 * @since 1.0.0
 */

get_header(); ?>

<div class="single-post-wrapper">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php
            // Get post data
            $post_id = get_the_ID();
            $featured_image = get_the_post_thumbnail_url($post_id, 'large');
            $post_views = get_post_meta($post_id, 'post_views_count', true) ?: 0;
            
            // Track this post view
            if (!is_user_logged_in() || !current_user_can('edit_posts')) {
                $views = intval($post_views);
                $views++;
                update_post_meta($post_id, 'post_views_count', $views);
                $post_views = $views;
            }
            
            // Format views count
            function format_single_views_count($views) {
                $views = intval($views);
                if ($views >= 1000000) {
                    return number_format($views / 1000000, 1) . 'M';
                } elseif ($views >= 1000) {
                    return number_format($views / 1000, 1) . 'K';
                }
                return number_format($views);
            }
            $formatted_views = format_single_views_count($post_views);
            ?>
            
            <article class="single-post-article" id="post-<?php the_ID(); ?>">
                
                <!-- News Title -->
                <header class="single-post-header">
                    <h1 class="single-post-title"><?php the_title(); ?></h1>
                </header>

                <!-- Print Button -->
                <div class="single-post-print-section">
                    <?php 
                    $button_args = array(
                        'url' => '#',
                        'text' => '🖨️ Print Article',
                        'class' => 'btn-print-article',
                        'target' => '_self',
                        'aria_label' => 'Print this article'
                    );
                    include(locate_template('template-parts/components/read-more-button.php'));
                    ?>
                </div>

                <!-- Meta Information: Published Date | Number of Views -->
                <div class="single-post-meta">
                    <span class="single-post-date">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 3H18V1H16V3H8V1H6V3H5C3.89 3 3.01 3.9 3.01 5L3 19C3 20.1 3.89 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM19 19H5V8H19V19Z" fill="currentColor"/>
                        </svg>
                        <?php echo get_the_date('F j, Y'); ?>
                    </span>
                    
                    <span class="meta-divider">|</span>
                    
                    <span class="single-post-views">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12C2.73 16.39 7 19.5 12 19.5S21.27 16.39 23 12C21.27 7.61 17 4.5 12 4.5ZM12 17C9.24 17 7 14.76 7 12S9.24 7 12 7S17 9.24 17 12S14.76 17 12 17ZM12 9C10.34 9 9 10.34 9 12S10.34 15 12 15S15 13.66 15 12S13.66 9 12 9Z" fill="currentColor"/>
                        </svg>
                        <?php echo esc_html($formatted_views); ?> views
                    </span>
                </div>

                <!-- Line Separator -->
                <div class="single-post-separator"></div>

                <!-- Featured Image -->
                <?php if ($featured_image) : ?>
                    <div class="single-post-featured-image">
                        <img src="<?php echo esc_url($featured_image); ?>" 
                             alt="<?php echo esc_attr(get_the_title()); ?>"
                             class="single-featured-image">
                    </div>
                <?php endif; ?>

                <!-- News Content Text -->
                <div class="single-post-content">
                    <?php 
                    the_content();
                    
                    wp_link_pages(array(
                        'before' => '<div class="page-links">',
                        'after'  => '</div>',
                        'pagelink' => '<span class="page-link">%</span>',
                    ));
                    ?>
                </div>

            </article>

        <?php endwhile; ?>
    <?php else : ?>
        <div class="single-post-not-found">
            <h1>Post Not Found</h1>
            <p>Sorry, the post you are looking for could not be found.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Print functionality for the print button
document.addEventListener('DOMContentLoaded', function() {
    const printButton = document.querySelector('.btn-print-article');
    if (printButton) {
        printButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    }
});
</script>

<?php get_footer(); ?>
