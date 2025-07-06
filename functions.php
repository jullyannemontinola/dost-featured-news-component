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
    
    // Localize script for AJAX
    wp_localize_script('featured-news-js', 'featuredNewsAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('featured_news_nonce')
    ));
    
    // Also add global variables for compatibility
    wp_add_inline_script('featured-news-js', '
        window.ajaxurl = "' . admin_url('admin-ajax.php') . '";
        window.featuredNewsNonce = "' . wp_create_nonce('featured_news_nonce') . '";
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
