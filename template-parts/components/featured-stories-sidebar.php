<?php
/**
 * Featured Stories Sidebar Component
 * Displays a list of featured stories with highlighting for current selection
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

global $featured_posts;
if (empty($featured_posts)) {
    $featured_posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => 5,
        'post_status' => 'publish'
    ));
}

$current_post_id = isset($current_post) ? $current_post->ID : $featured_posts[0]->ID;
?>

<div class="featured-stories-section">
    <h4 class="featured-stories-title">FEATURED STORIES</h4>
    
    <div class="featured-stories-list" data-current-post="<?php echo $current_post_id; ?>">
        <?php foreach ($featured_posts as $index => $post): 
            $post_id = $post->ID;
            $post_title = get_the_title($post_id);
            $post_date = get_the_date('F j, Y', $post_id);
            $is_current = ($post_id == $current_post_id);
        ?>
            <div class="story-item <?php echo $is_current ? 'story-current' : ''; ?>" 
                 data-post-id="<?php echo $post_id; ?>" 
                 data-index="<?php echo $index; ?>">
                <h5 class="story-title"><?php echo esc_html($post_title); ?></h5>
                <span class="story-date"><?php echo esc_html($post_date); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
