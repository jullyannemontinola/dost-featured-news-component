<?php
/**
 * Archived News Controls Component
 * Contains year dropdown and pagination controls
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

// Get configuration passed from parent
$config = isset($config) ? $config : array();
$current_year = isset($_GET['archive_year']) ? sanitize_text_field($_GET['archive_year']) : $config['default_year'];
$current_page = isset($_GET['archive_page']) ? max(1, intval($_GET['archive_page'])) : 1;

// Get total posts for the current year to calculate pagination
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
?>

<div class="archived-controls">
    <!-- Year Dropdown -->
    <div class="archived-dropdown-wrapper">
        <select class="archived-year-dropdown" 
                data-section-id="<?php echo esc_attr($config['section_id']); ?>"
                aria-label="Select year to view archived news">
            <?php foreach ($config['dropdown_options'] as $year => $label) : ?>
                <option value="<?php echo esc_attr($year); ?>" 
                        <?php selected($current_year, $year); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="dropdown-arrow">
            <svg width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </div>

    <!-- Pagination Controls -->
    <div class="archived-pagination" 
         data-current-page="<?php echo esc_attr($current_page); ?>"
         data-total-pages="<?php echo esc_attr($total_pages); ?>"
         data-section-id="<?php echo esc_attr($config['section_id']); ?>">
        
        <!-- Previous Button -->
        <button class="pagination-btn pagination-prev" 
                <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>
                aria-label="Previous page">
            <svg width="10" height="16" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 1L1 7L7 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <!-- Page Info -->
        <div class="pagination-info">
            Page <?php echo esc_html($current_page); ?> of <?php echo esc_html($total_pages); ?>
        </div>

        <!-- Next Button -->
        <button class="pagination-btn pagination-next" 
                <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>
                aria-label="Next page">
            <svg width="10" height="16" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1L7 7L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
</div>
