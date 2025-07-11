<?php
/**
 * Template part for displaying archived news content
 * This contains the main structure that loads modular archived news components
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

// get configuration from arguments or use defaults for news
$config = isset($args) ? $args : array();
$default_config = array(
    'post_type' => 'post',
    'posts_per_page' => 4,
    'dropdown_options' => array(
        '2025' => '2025 News',
        '2024' => '2024 News',
        '2023' => '2023 News',
        '2022' => '2022 News',
        '2021' => '2021 News'
    ),
    'default_year' => '2025',
    'section_id' => 'archived-news',
    'ajax_action' => 'load_archived_posts'
);

$config = array_merge($default_config, $config);

// get available years from posts
$available_years = array();
$years_query = new WP_Query(array(
    'post_type' => $config['post_type'],
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'fields' => 'ids'
));

if ($years_query->have_posts()) {
    foreach ($years_query->posts as $post_id) {
        $year = get_the_date('Y', $post_id);
        if (!in_array($year, $available_years)) {
            $available_years[] = $year;
        }
    }
}
wp_reset_postdata();

// sort years in descending order
rsort($available_years);

// create dropdown options from available years
$dropdown_options = array();
foreach ($available_years as $year) {
    $dropdown_options[$year] = $year . ' News';
}

if (!empty($dropdown_options)) {
    $config['dropdown_options'] = $dropdown_options;
    $config['default_year'] = $available_years[0];
}
?>

<div class="archived-news-wrapper" 
     data-config='<?php echo json_encode($config); ?>'
     data-section-id="<?php echo esc_attr($config['section_id']); ?>">
     
    <!-- Controls Section (Dropdown + Pagination) -->
    <div class="archived-controls-section">
        <?php 
        // load the controls component
        include(locate_template('template-parts/components/archived-news-controls.php'));
        ?>
    </div>
    
    <!-- News Grid Section -->
    <div class="archived-news-grid" id="<?php echo esc_attr($config['section_id']); ?>-grid">
        <?php 
        // load the news grid component
        include(locate_template('template-parts/components/archived-news-grid.php'));
        ?>
    </div>
    
    <!-- Progress Controls Section (Slider + Start/End buttons) -->
    <div class="archived-progress-section">
        <?php 
        // load the progress controls component
        include(locate_template('template-parts/components/archived-progress-controls.php'));
        ?>
    </div>
</div>

<script>
// initialize archived news functionality
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.ArchivedNews !== 'undefined') {
        window.ArchivedNews.init('<?php echo esc_js($config['section_id']); ?>');
    }
});
</script>
