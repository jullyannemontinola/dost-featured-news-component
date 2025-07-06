<?php
/**
 * Child Theme Functions
 * 
 * @package GWT Child Theme
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue child theme styles and scripts
 */
function gwt_child_enqueue_styles() {
    // Enqueue parent theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    
    // Enqueue child theme styles
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'), wp_get_theme()->get('Version'));
    
    // Enqueue featured news JavaScript
    wp_enqueue_script('featured-news-js', get_stylesheet_directory_uri() . '/js/featured-news.js', array('jquery'), '1.0.0', true);
    
    // Enqueue archived news JavaScript
    wp_enqueue_script('archived-news-js', get_stylesheet_directory_uri() . '/js/archived-news.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('featured-news-js', 'featuredNewsAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('featured_news_nonce')
    ));
    
    // Localize script for archived news AJAX
    wp_localize_script('archived-news-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('archived_news_nonce')
    ));
    
    // Also add global variables for compatibility
    wp_add_inline_script('featured-news-js', '
        window.ajaxurl = "' . admin_url('admin-ajax.php') . '";
        window.featuredNewsNonce = "' . wp_create_nonce('featured_news_nonce') . '";
    ', 'before');
    
    // Add global variables for archived news
    wp_add_inline_script('archived-news-js', '
        window.ajax_object = window.ajax_object || {};
        window.ajax_object.ajax_url = "' . admin_url('admin-ajax.php') . '";
        window.ajax_object.nonce = "' . wp_create_nonce('archived_news_nonce') . '";
    ', 'before');
}
add_action('wp_enqueue_scripts', 'gwt_child_enqueue_styles');

/**
 * AJAX handler to get featured post data
 */
function get_featured_post_data() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'featured_news_nonce')) {
        wp_die('Security check failed');
    }
    
    $post_id = intval($_POST['post_id']);
    
    if (!$post_id) {
        wp_send_json_error('Invalid post ID');
        return;
    }
    
    $post = get_post($post_id);
    
    if (!$post) {
        wp_send_json_error('Post not found');
        return;
    }
    
    // Get post data
    $post_data = array(
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'date' => get_the_date('F j, Y', $post_id),
        'excerpt' => get_the_excerpt($post_id),
        'url' => get_permalink($post_id),
        'image' => get_the_post_thumbnail_url($post_id, 'large') ?: get_template_directory_uri() . '/images/placeholder-featured.jpg',
        'views' => get_post_meta($post_id, 'post_views_count', true) ?: '0'
    );
    
    wp_send_json_success($post_data);
}
add_action('wp_ajax_get_featured_post_data', 'get_featured_post_data');
add_action('wp_ajax_nopriv_get_featured_post_data', 'get_featured_post_data');

/**
 * Add custom meta box for featured news
 */
function add_featured_news_meta_box() {
    add_meta_box(
        'featured_news_meta',
        'Featured News Settings',
        'featured_news_meta_box_callback',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_featured_news_meta_box');

/**
 * Featured news meta box callback
 */
function featured_news_meta_box_callback($post) {
    wp_nonce_field('featured_news_meta_box', 'featured_news_meta_box_nonce');
    
    $featured = get_post_meta($post->ID, '_featured_news', true);
    ?>
    <label for="featured_news_checkbox">
        <input type="checkbox" id="featured_news_checkbox" name="featured_news" value="yes" <?php checked($featured, 'yes'); ?> />
        Mark as Featured News
    </label>
    <?php
}

/**
 * Save featured news meta
 */
function save_featured_news_meta($post_id) {
    if (!isset($_POST['featured_news_meta_box_nonce'])) {
        return;
    }
    
    if (!wp_verify_nonce($_POST['featured_news_meta_box_nonce'], 'featured_news_meta_box')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $featured = isset($_POST['featured_news']) ? 'yes' : 'no';
    update_post_meta($post_id, '_featured_news', $featured);
}
add_action('save_post', 'save_featured_news_meta');

/**
 * Track post views
 */
function track_post_views($post_id) {
    if (!is_single()) return;
    
    if (empty($post_id)) {
        global $post;
        $post_id = $post->ID;
    }
    
    $views = get_post_meta($post_id, 'post_views_count', true);
    $views = $views ? $views : 0;
    $views++;
    
    update_post_meta($post_id, 'post_views_count', $views);
}
add_action('wp_head', 'track_post_views');

/**
 * AJAX handler to load archived posts
 */
function load_archived_posts() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'archived_news_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }
    
    $year = sanitize_text_field($_POST['year']);
    $page = max(1, intval($_POST['page']));
    $section_id = sanitize_text_field($_POST['section_id']);
    $posts_per_page = max(1, intval($_POST['posts_per_page']));
    
    if (!$year) {
        wp_send_json_error(array('message' => 'Year is required'));
        return;
    }
    
    // Query posts for the specified year and page
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'post_status' => 'publish',
        'date_query' => array(
            array(
                'year' => $year
            )
        ),
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    $query = new WP_Query($args);
    $total_pages = $query->max_num_pages;
    
    // Generate HTML for the grid
    ob_start();
    
    // Set up global query data for template parts
    global $wp_query;
    $original_query = $wp_query;
    $wp_query = $query;
    
    // Use the archived news grid component to generate HTML
    $config = array(
        'post_type' => 'post',
        'posts_per_page' => $posts_per_page,
        'section_id' => $section_id
    );
    
    if ($query->have_posts()) {
        echo '<div class="archived-news-grid-items">';
        while ($query->have_posts()) {
            $query->the_post();
            
            $post_id = get_the_ID();
            $featured_image = get_the_post_thumbnail_url($post_id, 'medium_large');
            $post_views = get_post_meta($post_id, 'post_views_count', true) ?: 0;
            $formatted_views = format_archived_views_count($post_views);
            $excerpt = get_the_excerpt();
            if (empty($excerpt)) {
                $excerpt = wp_trim_words(get_the_content(), 25, '...');
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
                        // Set up button arguments for the modular component
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
            
            <?php
        }
        echo '</div>';
    } else {
        echo '<div class="archived-news-empty">
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 7H11V11H7V13H11V17H13V13H17V11H13V7ZM12 2C6.48 2 2 6.48 2 12S6.48 22 12 22S22 17.52 22 12S17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12S7.59 4 12 4S20 7.59 20 12S16.41 20 12 20Z" fill="currentColor"/>
                </svg>
                <h3>No News Found</h3>
                <p>There are no news articles available for ' . esc_html($year) . '.</p>
            </div>
        </div>';
    }
    
    $html = ob_get_clean();
    
    // Restore original query
    $wp_query = $original_query;
    wp_reset_postdata();
    
    // Calculate total posts for this year
    $total_query = new WP_Query(array(
        'post_type' => 'post',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'date_query' => array(
            array(
                'year' => $year
            )
        ),
        'fields' => 'ids'
    ));
    $total_posts = $total_query->found_posts;
    wp_reset_postdata();
    
    // Send response
    wp_send_json_success(array(
        'html' => $html,
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_posts' => $total_posts,
        'year' => $year,
        'posts_per_page' => $posts_per_page
    ));
}
add_action('wp_ajax_load_archived_posts', 'load_archived_posts');
add_action('wp_ajax_nopriv_load_archived_posts', 'load_archived_posts');

/**
 * Helper function to format views count for archived news
 */
function format_archived_views_count($views) {
    if ($views >= 1000000) {
        return number_format($views / 1000000, 1) . 'M';
    } elseif ($views >= 1000) {
        return number_format($views / 1000, 1) . 'K';
    }
    return number_format($views);
}
