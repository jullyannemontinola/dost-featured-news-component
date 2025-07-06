<?php
/**
 * Archived News Grid Component
 * Displays news posts in a grid layout with pagination
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

// Get configuration passed from parent
$config = isset($config) ? $config : array();
$current_year = isset($_GET['archive_year']) ? sanitize_text_field($_GET['archive_year']) : $config['default_year'];
$current_page = isset($_GET['archive_page']) ? max(1, intval($_GET['archive_page'])) : 1;

// Query posts for the current year and page
$posts_query = new WP_Query(array(
    'post_type' => $config['post_type'],
    'posts_per_page' => $config['posts_per_page'],
    'paged' => $current_page,
    'post_status' => 'publish',
    'date_query' => array(
        array(
            'year' => $current_year
        )
    ),
    'orderby' => 'date',
    'order' => 'DESC'
));

// Function to get post views (same as featured news)
function get_post_views($post_id) {
    $views = get_post_meta($post_id, 'post_views', true);
    return $views ? intval($views) : 0;
}

// Function to format views count
function format_views_count($views) {
    if ($views >= 1000000) {
        return number_format($views / 1000000, 1) . 'M';
    } elseif ($views >= 1000) {
        return number_format($views / 1000, 1) . 'K';
    }
    return number_format($views);
}
?>

<div class="archived-news-grid-container">
    <?php if ($posts_query->have_posts()) : ?>
        <div class="archived-news-grid-items">
            <?php while ($posts_query->have_posts()) : $posts_query->the_post(); ?>
                <?php
                $post_id = get_the_ID();
                $featured_image = get_the_post_thumbnail_url($post_id, 'medium_large');
                $post_views = get_post_views($post_id);
                $formatted_views = format_views_count($post_views);
                $excerpt = get_the_excerpt();
                if (empty($excerpt)) {
                    $excerpt = wp_trim_words(get_the_content(), 25, '...');
                }
                // Limit excerpt to 100 characters for better consistency
                if (strlen($excerpt) > 100) {
                    $excerpt = substr($excerpt, 0, 97) . '...';
                }
                ?>
                
                <article class="archived-news-item" data-post-id="<?php echo esc_attr($post_id); ?>">
                    <!-- Featured Image -->
                    <div class="archived-news-image">
                        <?php if ($featured_image) : ?>
                            <img src="<?php echo esc_url($featured_image); ?>" 
                                 alt="<?php echo esc_attr(get_the_title()); ?>"
                                 loading="lazy">
                        <?php else : ?>
                            <div class="archived-news-placeholder">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 19V5C21 3.9 20.1 3 19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19ZM8.5 13.5L11 16.51L14.5 12L19 18H5L8.5 13.5Z" fill="currentColor"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div class="archived-news-content">
                        <h3 class="archived-news-title">
                            <a href="<?php the_permalink(); ?>" rel="bookmark">
                                <?php the_title(); ?>
                            </a>
                        </h3>

                        <!-- Meta Information -->
                        <div class="archived-news-meta">
                            <span class="archived-news-date">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19 3H18V1H16V3H8V1H6V3H5C3.89 3 3.01 3.9 3.01 5L3 19C3 20.1 3.89 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM19 19H5V8H19V19Z" fill="currentColor"/>
                                </svg>
                                <?php echo get_the_date('M j, Y'); ?>
                            </span>
                            
                            <span class="archived-news-views">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12C2.73 16.39 7 19.5 12 19.5S21.27 16.39 23 12C21.27 7.61 17 4.5 12 4.5ZM12 17C9.24 17 7 14.76 7 12S9.24 7 12 7S17 9.24 17 12S14.76 17 12 17ZM12 9C10.34 9 9 10.34 9 12S10.34 15 12 15S15 13.66 15 12S13.66 9 12 9Z" fill="currentColor"/>
                                </svg>
                                <?php echo esc_html($formatted_views); ?> views
                            </span>
                        </div>

                        <!-- Excerpt -->
                        <div class="archived-news-excerpt">
                            <?php echo wp_kses_post($excerpt); ?>
                        </div>

                        <!-- Read More Button -->
                        <div class="archived-news-action">
                            <?php 
                            // Use the modular read more button component
                            $button_args = array(
                                'url' => get_permalink(),
                                'text' => 'Read More →',
                                'class' => 'btn-read-more-archived',
                                'target' => '_self',
                                'aria_label' => 'Read more about ' . get_the_title()
                            );
                            include(locate_template('template-parts/components/read-more-button.php'));
                            ?>
                        </div>
                    </div>
                </article>
                
            <?php endwhile; ?>
        </div>
        
        <?php wp_reset_postdata(); ?>
        
    <?php else : ?>
        <div class="archived-news-empty">
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 7H11V11H7V13H11V17H13V13H17V11H13V7ZM12 2C6.48 2 2 6.48 2 12S6.48 22 12 22S22 17.52 22 12S17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12S7.59 4 12 4S20 7.59 20 12S16.41 20 12 20Z" fill="currentColor"/>
                </svg>
                <h3>No News Found</h3>
                <p>There are no news articles available for <?php echo esc_html($current_year); ?>.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
