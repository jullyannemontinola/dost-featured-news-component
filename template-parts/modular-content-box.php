<?php
/**
 * Template part for displaying modular content box
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

// get the section title from the passed arguments or use default
$section_title = isset($args['title']) ? $args['title'] : 'FEATURED NEWS';
$section_class = isset($args['class']) ? $args['class'] : 'featured-news-section';
$content_class = isset($args['content_class']) ? $args['content_class'] : 'content-box-inner';
?>

<div class="modular-content-box <?php echo esc_attr($section_class); ?>">
    <div class="content-box-header">
        <h2 class="content-box-title"><?php echo esc_html($section_title); ?></h2>
    </div>
    <div class="content-box-body <?php echo esc_attr($content_class); ?>">
        <?php 
        // this is where the inner content will be loaded
        // content will be passed through $args['content'] or loaded via action hooks
        if (isset($args['content'])) {
            echo $args['content'];
        } elseif (isset($args['template_part'])) {
            // load a specific template part for the content
            get_template_part($args['template_part']);
        } else {
            // allow other parts to hook into this content area
            do_action('modular_content_box_content', $args);
        }
        ?>
    </div>
</div>
