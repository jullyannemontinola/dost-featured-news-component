# Modular Archived News Component

## Overview

The Archived News component is a fully modular and reusable WordPress component system designed for displaying news archives with interactive controls. It includes year-based filtering, pagination, and a progress slider for enhanced user experience.

## Features

- **Modular Architecture**: Easily reusable with different content types and configurations
- **Year-based Filtering**: Dropdown to filter news by year (auto-populated from posts)
- **Smart Pagination**: Page numbers with ellipsis for large datasets
- **Interactive Progress Slider**: Visual progress indicator with draggable slider
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile
- **AJAX Loading**: Dynamic content updates without page refresh
- **Accessible**: Screen reader friendly with proper ARIA labels
- **Design System Compliance**: Consistent with the established typography and color system

## File Structure

```
template-parts/
├── content-archive.php                 # Main archived news container
├── components/
│   ├── archived-news-controls.php     # Year dropdown + pagination controls
│   ├── archived-news-grid.php         # News items grid display
│   └── archived-progress-controls.php # Progress slider + start/end buttons
js/
└── archived-news.js                   # JavaScript functionality
```

## Usage

### Basic Implementation

```php
// In your template file (e.g., front-page.php)
get_template_part( 'template-parts/modular-content-box', null, array(
    'title' => 'ARCHIVED NEWS',
    'class' => 'archived-news-section',
    'content_class' => 'archived-news-content',
    'template_part' => 'template-parts/content-archive'
) );
```

### Custom Configuration

```php
// Pass custom configuration to the archived news component
get_template_part( 'template-parts/content-archive', null, array(
    'post_type' => 'custom_post_type',      // Default: 'post'
    'posts_per_page' => 6,                  // Default: 4
    'section_id' => 'custom-archive',       // Default: 'archived-news'
    'ajax_action' => 'load_custom_posts',   // Default: 'load_archived_posts'
    'default_year' => '2024'                // Auto-detected from posts
) );
```

### Using with Different Post Types

```php
// Example: Using with custom post type "events"
get_template_part( 'template-parts/content-archive', null, array(
    'post_type' => 'event',
    'posts_per_page' => 8,
    'section_id' => 'archived-events',
    'ajax_action' => 'load_archived_events'
) );
```

## Components Breakdown

### 1. Main Container (`content-archive.php`)
- Handles configuration and data preparation
- Auto-populates available years from posts
- Loads and coordinates all sub-components
- Manages JavaScript initialization

### 2. Controls (`archived-news-controls.php`)
- Year selection dropdown
- Pagination with prev/next buttons
- Smart page number display with ellipsis
- Responsive design for mobile

### 3. Grid (`archived-news-grid.php`)
- Displays news items in a responsive grid
- Shows featured images, titles, dates, views, and excerpts
- Includes read more buttons
- Handles empty states

### 4. Progress Controls (`archived-progress-controls.php`)
- Interactive progress slider
- Start/end navigation buttons
- Progress marks for key pages
- Tooltip showing current position

## JavaScript API

### Initialization
```javascript
// Auto-initializes on page load, or manually:
window.ArchivedNews.init('archived-news');
```

### Public Methods
```javascript
// Load specific year and page
window.ArchivedNews.loadContent('2024', 2);

// Navigate to specific page
window.ArchivedNews.navigateToPage(3);
```

## AJAX Integration

### WordPress Hooks Required

```php
// In functions.php
add_action('wp_ajax_load_archived_posts', 'load_archived_posts');
add_action('wp_ajax_nopriv_load_archived_posts', 'load_archived_posts');
```

### Custom AJAX Handler Example

```php
function load_custom_archived_posts() {
    // Custom implementation for different post types
    // Follow the same pattern as load_archived_posts()
}
add_action('wp_ajax_load_custom_posts', 'load_custom_archived_posts');
add_action('wp_ajax_nopriv_load_custom_posts', 'load_custom_archived_posts');
```

## Styling

The component uses CSS custom properties for easy theming:

```css
:root {
    --color-blue: #00AEEF;      /* Primary accent color */
    --color-black: #000000;     /* Text and borders */
    --color-white: #FFFFFF;     /* Backgrounds */
}
```

### Key CSS Classes

- `.archived-news-wrapper` - Main container
- `.archived-controls` - Top controls section
- `.archived-news-grid-items` - Grid container
- `.archived-news-item` - Individual news item
- `.archived-progress-controls` - Bottom progress section

## Responsive Breakpoints

- **Desktop**: Full feature set with all controls visible
- **Tablet** (768px): Stacked controls, simplified grid
- **Mobile** (480px): Single column grid, hidden progress marks

## Accessibility Features

- Proper ARIA labels on all interactive elements
- Keyboard navigation support
- Screen reader friendly content structure
- Focus management for dynamic content updates
- High contrast design with sufficient color ratios

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11+ (with graceful degradation)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Considerations

- Lazy loading for images
- Efficient AJAX requests with minimal payloads
- CSS animations with GPU acceleration
- Debounced slider interactions
- Optimized DOM queries with caching

## Troubleshooting

### Common Issues

1. **JavaScript not loading**: Check that archived-news.js is enqueued properly
2. **AJAX errors**: Verify nonce generation and AJAX action hooks
3. **Styling issues**: Ensure CSS custom properties are defined
4. **No posts showing**: Check post type and date query parameters

### Debug Mode

Add to wp-config.php for debugging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Then check browser console and WordPress debug logs for errors.

## Future Enhancements

- Category-based filtering
- Search functionality within archives
- Export/sharing capabilities
- Advanced sorting options
- Integration with caching plugins
- Progressive loading for large datasets
