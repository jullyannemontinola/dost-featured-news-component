<?php
/**
 * Template part for displaying featured news content
 * This contains the main structure that loads modular components
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

// get featured posts for the news section
$featured_posts = get_posts(array(
    'post_type' => 'post',
    'posts_per_page' => 5,
    'meta_key' => '_featured_news',
    'meta_value' => 'yes',
    'post_status' => 'publish'
));

// if no featured posts found, get latest posts
// might delete this to ensure only featured posts are shown
if (empty($featured_posts)) {
    $featured_posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => 5,
        'post_status' => 'publish'
    ));
}

// prepare post data for JavaScript
$post_data = array();
foreach ($featured_posts as $post) {
    $post_data[] = array(
        'id' => $post->ID,
        'title' => get_the_title($post->ID),
        'date' => get_the_date('F j, Y', $post->ID),
        'excerpt' => get_the_excerpt($post->ID),
        'url' => get_permalink($post->ID),
        'image' => get_the_post_thumbnail_url($post->ID, 'large') ?: get_template_directory_uri() . '/images/placeholder-featured.jpg',
        'views' => get_post_meta($post->ID, 'post_views_count', true) ?: '0'
    );
}
?>

<div class="featured-news-wrapper" 
     data-featured-posts='<?php echo json_encode(wp_list_pluck($featured_posts, 'ID')); ?>'
     data-posts-data='<?php echo json_encode($post_data); ?>'>
    <div class="featured-news-inner">
        <div class="featured-news-main">
            <?php 
            // load the hero section component
            include(locate_template('template-parts/components/hero-section.php'));
            ?>
        </div>
        
        <div class="featured-news-sidebar">
            <?php 
            // load the featured stories sidebar component
            include(locate_template('template-parts/components/featured-stories-sidebar.php'));
            ?>
        </div>
    </div>
</div>

<script>
// initialize featured news functionality
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.FeaturedNews !== 'undefined') {
        window.FeaturedNews.init();
    }
});
</script>
