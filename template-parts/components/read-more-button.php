<?php
/**
 * Read More Button Component
 * Reusable button component that can be used throughout the site
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

// Default button settings, can be overridden by passing $button_args
$default_args = array(
    'url' => isset($post_url) ? $post_url : '#',
    'text' => 'Read More →',
    'class' => 'btn-read-more',
    'target' => '_self',
    'aria_label' => 'Read more about this article'
);

// Merge with passed arguments if any
$button_args = isset($button_args) ? array_merge($default_args, $button_args) : $default_args;
?>

<a href="<?php echo esc_url($button_args['url']); ?>" 
   class="read-more-button <?php echo esc_attr($button_args['class']); ?>" 
   target="<?php echo esc_attr($button_args['target']); ?>"
   aria-label="<?php echo esc_attr($button_args['aria_label']); ?>">
    <?php echo esc_html($button_args['text']); ?>
</a>
