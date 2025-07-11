<?php
/**
 * Hero Section Component
 * Displays the main featured news with image, title, meta info, and navigation
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

// get the first post as default or from AJAX request
global $featured_posts;
if (empty($featured_posts)) {
    $featured_posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => 5,
        'post_status' => 'publish'
    ));
}

$current_post = isset($_POST['post_id']) ? get_post($_POST['post_id']) : $featured_posts[0];
$post_id = $current_post->ID;
$post_url = get_permalink($post_id);
$post_image = get_the_post_thumbnail_url($post_id, 'large') ?: get_template_directory_uri() . '/images/placeholder-featured.jpg';
$post_title = get_the_title($post_id);
$post_date = get_the_date('F j, Y', $post_id);
$post_excerpt = get_the_excerpt($post_id);
$post_views = get_post_meta($post_id, 'post_views_count', true) ?: '0';
?>

<div class="hero-section" data-current-post="<?php echo $post_id; ?>">
    <div class="hero-image-container">
        <img src="<?php echo esc_url($post_image); ?>" alt="<?php echo esc_attr($post_title); ?>" class="hero-image">
        
        <!-- Navigation Chevrons -->
        <button class="hero-nav hero-nav-prev" aria-label="Previous News" data-direction="prev">
            &#8249;
        </button>
        <button class="hero-nav hero-nav-next" aria-label="Next News" data-direction="next">
            &#8250;
        </button>
    </div>
    
    <div class="hero-content">
        <h3 class="hero-title"><?php echo esc_html($post_title); ?></h3>
        
        <div class="hero-meta">
            <span class="hero-date">Published <?php echo esc_html($post_date); ?></span>
            <span class="hero-divider">|</span>
            <span class="hero-views"><?php echo esc_html($post_views); ?> Views</span>
        </div>
        
        <div class="hero-excerpt">
            <p><?php echo esc_html($post_excerpt); ?></p>
        </div>
        
        <?php 
        // load the Read More Button component
        include(locate_template('template-parts/components/read-more-button.php'));
        ?>
    </div>
</div>
