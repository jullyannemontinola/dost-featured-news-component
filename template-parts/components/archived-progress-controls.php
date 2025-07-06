<?php
/**
 * Archived Progress Controls Component
 * Contains progress slider and start/end navigation buttons
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

// Get configuration passed from parent
$config = isset($config) ? $config : array();
$current_year = isset($_GET['archive_year']) ? sanitize_text_field($_GET['archive_year']) : $config['default_year'];
$current_page = isset($_GET['archive_page']) ? max(1, intval($_GET['archive_page'])) : 1;

// Get total posts for progress calculation
$posts_query = new WP_Query(array(
    'post_type' => $config['post_type'],
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'date_query' => array(
        array(
            'year' => $current_year
        )
    ),
    'fields' => 'ids'
));

$total_posts = $posts_query->found_posts;
$total_pages = ceil($total_posts / $config['posts_per_page']);
wp_reset_postdata();

// Calculate progress percentage
$progress_percentage = $total_pages > 0 ? ($current_page / $total_pages) * 100 : 0;
?>

<div class="archived-progress-controls" 
     data-section-id="<?php echo esc_attr($config['section_id']); ?>"
     data-current-page="<?php echo esc_attr($current_page); ?>"
     data-total-pages="<?php echo esc_attr($total_pages); ?>">

    <!-- Start Button -->
    <button class="progress-btn progress-start" 
            <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>
            aria-label="Go to first page"
            title="First Page">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 6H4V18H6V6ZM9.5 12L18.5 18V6L9.5 12Z" fill="currentColor"/>
        </svg>
    </button>

    <!-- Progress Slider Container -->
    <div class="progress-slider-container">
        <div class="progress-slider-track">
            <div class="progress-slider-fill" 
                 style="width: <?php echo esc_attr($progress_percentage); ?>%"></div>
            <div class="progress-slider-handle" 
                 style="left: <?php echo esc_attr($progress_percentage); ?>%"
                 data-current-progress="<?php echo esc_attr($progress_percentage); ?>">
                <div class="progress-tooltip">
                    Page <?php echo esc_html($current_page); ?> of <?php echo esc_html($total_pages); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- End Button -->
    <button class="progress-btn progress-end" 
            <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>
            aria-label="Go to last page"
            title="Last Page">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5.5 18L14.5 12L5.5 6V18ZM16 6V18H18V6H16Z" fill="currentColor"/>
        </svg>
    </button>
</div>

<!-- Progress Info -->
<div class="archived-progress-info">
    <span class="progress-text">
        Showing <?php echo esc_html(min($config['posts_per_page'], $total_posts - (($current_page - 1) * $config['posts_per_page']))); ?> 
        of <?php echo esc_html($total_posts); ?> articles from <?php echo esc_html($current_year); ?>
    </span>
</div>
