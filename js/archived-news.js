/**
 * Archived News JavaScript Functionality
 * Handles year dropdown, pagination, progress slider, and dynamic content updates
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

window.ArchivedNews = (function() {
    'use strict';
    
    let currentConfig = {};
    let isLoading = false;
    let sectionId = '';
    
    function init(id) {
        sectionId = id || 'archived-news';
        
        // Get the archived news wrapper
        const wrapper = document.querySelector(`[data-section-id="${sectionId}"]`);
        if (!wrapper) {
            console.log(`Archived news wrapper with section ID '${sectionId}' not found`);
            return;
        }
        
        try {
            currentConfig = JSON.parse(wrapper.dataset.config || '{}');
        } catch (e) {
            console.error('Error parsing archived news config:', e);
            currentConfig = {};
        }
        
        console.log('Archived News initialized with config:', currentConfig);
        
        // Initialize event listeners
        initEventListeners();
        
        // Initialize progress slider
        initProgressSlider();
    }
    
    function initEventListeners() {
        // Year dropdown change
        const yearDropdown = document.querySelector(`[data-section-id="${sectionId}"].archived-year-dropdown`);
        if (yearDropdown) {
            yearDropdown.addEventListener('change', handleYearChange);
        }
        
        // Pagination controls
        const paginationContainer = document.querySelector(`[data-section-id="${sectionId}"].archived-pagination`);
        if (paginationContainer) {
            // Pagination buttons
            paginationContainer.addEventListener('click', handlePaginationClick);
        }
        
        // Progress controls
        const progressControls = document.querySelector(`[data-section-id="${sectionId}"].archived-progress-controls`);
        if (progressControls) {
            // Start/End buttons
            const startBtn = progressControls.querySelector('.progress-start');
            const endBtn = progressControls.querySelector('.progress-end');
            
            if (startBtn) {
                startBtn.addEventListener('click', () => navigateToPage(1));
            }
            if (endBtn) {
                const totalPages = parseInt(progressControls.dataset.totalPages) || 1;
                endBtn.addEventListener('click', () => navigateToPage(totalPages));
            }
        }
    }
    
    function initProgressSlider() {
        const sliderHandle = document.querySelector(`[data-section-id="${sectionId}"] .progress-slider-handle`);
        const sliderTrack = document.querySelector(`[data-section-id="${sectionId}"] .progress-slider-track`);
        
        if (!sliderHandle || !sliderTrack) return;
        
        let isDragging = false;
        
        // Mouse events
        sliderHandle.addEventListener('mousedown', startDrag);
        document.addEventListener('mousemove', handleDrag);
        document.addEventListener('mouseup', endDrag);
        
        // Touch events for mobile
        sliderHandle.addEventListener('touchstart', startDrag);
        document.addEventListener('touchmove', handleDrag);
        document.addEventListener('touchend', endDrag);
        
        // Click on track to jump to position
        sliderTrack.addEventListener('click', handleTrackClick);
        
        function startDrag(e) {
            e.preventDefault();
            isDragging = true;
            sliderHandle.classList.add('dragging');
        }
        
        function handleDrag(e) {
            if (!isDragging) return;
            
            e.preventDefault();
            const rect = sliderTrack.getBoundingClientRect();
            const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
            const percentage = Math.max(0, Math.min(100, ((clientX - rect.left) / rect.width) * 100));
            
            updateSliderPosition(percentage);
        }
        
        function endDrag(e) {
            if (!isDragging) return;
            
            isDragging = false;
            sliderHandle.classList.remove('dragging');
            
            // Calculate which page to navigate to based on final position
            const progressControls = document.querySelector(`[data-section-id="${sectionId}"].archived-progress-controls`);
            const totalPages = parseInt(progressControls?.dataset.totalPages) || 1;
            const percentage = parseFloat(sliderHandle.style.left) || 0;
            const targetPage = Math.max(1, Math.min(totalPages, Math.round((percentage / 100) * totalPages)));
            
            if (targetPage !== getCurrentPage()) {
                navigateToPage(targetPage);
            }
        }
        
        function handleTrackClick(e) {
            if (isDragging) return;
            
            const rect = sliderTrack.getBoundingClientRect();
            const percentage = ((e.clientX - rect.left) / rect.width) * 100;
            
            const progressControls = document.querySelector(`[data-section-id="${sectionId}"].archived-progress-controls`);
            const totalPages = parseInt(progressControls?.dataset.totalPages) || 1;
            const targetPage = Math.max(1, Math.min(totalPages, Math.round((percentage / 100) * totalPages)));
            
            navigateToPage(targetPage);
        }
    }
    
    function updateSliderPosition(percentage) {
        const sliderFill = document.querySelector(`[data-section-id="${sectionId}"] .progress-slider-fill`);
        const sliderHandle = document.querySelector(`[data-section-id="${sectionId}"] .progress-slider-handle`);
        
        if (sliderFill) {
            sliderFill.style.width = percentage + '%';
        }
        if (sliderHandle) {
            sliderHandle.style.left = percentage + '%';
        }
    }
    
    function handleYearChange(e) {
        const selectedYear = e.target.value;
        console.log('Year changed to:', selectedYear);
        
        // Reset to page 1 when year changes
        loadContent(selectedYear, 1);
    }
    
    function handlePaginationClick(e) {
        e.preventDefault();
        
        const target = e.target.closest('button');
        if (!target) return;
        
        const currentPage = getCurrentPage();
        const totalPages = getTotalPages();
        
        if (target.classList.contains('pagination-prev')) {
            if (currentPage > 1) {
                navigateToPage(currentPage - 1);
            }
        } else if (target.classList.contains('pagination-next')) {
            if (currentPage < totalPages) {
                navigateToPage(currentPage + 1);
            }
        }
    }
    
    function navigateToPage(page) {
        const yearDropdown = document.querySelector(`[data-section-id="${sectionId}"].archived-year-dropdown`);
        const selectedYear = yearDropdown ? yearDropdown.value : currentConfig.default_year;
        
        loadContent(selectedYear, page);
    }
    
    function getCurrentPage() {
        const paginationContainer = document.querySelector(`[data-section-id="${sectionId}"].archived-pagination`);
        return parseInt(paginationContainer?.dataset.currentPage) || 1;
    }
    
    function getTotalPages() {
        const paginationContainer = document.querySelector(`[data-section-id="${sectionId}"].archived-pagination`);
        return parseInt(paginationContainer?.dataset.totalPages) || 1;
    }
    
    function loadContent(year, page) {
        if (isLoading) return;
        
        console.log(`Loading content for year: ${year}, page: ${page}`);
        
        isLoading = true;
        showLoadingState();
        
        // Use AJAX to load new content
        const data = new FormData();
        data.append('action', currentConfig.ajax_action || 'load_archived_posts');
        data.append('year', year);
        data.append('page', page);
        data.append('section_id', sectionId);
        data.append('posts_per_page', currentConfig.posts_per_page || 4);
        data.append('nonce', window.ajax_object?.nonce || '');
        
        fetch(window.ajax_object?.ajax_url || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(handleLoadResponse)
        .catch(handleLoadError)
        .finally(() => {
            isLoading = false;
            hideLoadingState();
        });
    }
    
    function handleLoadResponse(response) {
        if (!response.success) {
            throw new Error(response.data?.message || 'Failed to load content');
        }
        
        const data = response.data;
        
        // Update the grid content
        const gridContainer = document.querySelector(`#${sectionId}-grid`);
        if (gridContainer && data.html) {
            gridContainer.innerHTML = data.html;
        }
        
        // Update pagination controls
        updatePaginationControls(data.current_page, data.total_pages);
        
        // Update progress controls
        updateProgressControls(data.current_page, data.total_pages);
        
        // Update URL without page reload
        updateURL(data.year, data.current_page);
        
        // Scroll to the content area
        scrollToContent();
    }
    
    function handleLoadError(error) {
        console.error('Error loading archived news content:', error);
        
        // Show error message
        const gridContainer = document.querySelector(`#${sectionId}-grid`);
        if (gridContainer) {
            gridContainer.innerHTML = `
                <div class="archived-news-error">
                    <div class="error-state">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12S6.48 22 12 22S22 17.52 22 12S17.52 2 12 2ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"/>
                        </svg>
                        <h3>Error Loading Content</h3>
                        <p>There was an error loading the archived news. Please try again.</p>
                        <button class="retry-btn" onclick="location.reload()">Retry</button>
                    </div>
                </div>
            `;
        }
    }
    
    function updatePaginationControls(currentPage, totalPages) {
        const paginationContainer = document.querySelector(`[data-section-id="${sectionId}"].archived-pagination`);
        if (!paginationContainer) return;
        
        paginationContainer.dataset.currentPage = currentPage;
        paginationContainer.dataset.totalPages = totalPages;
        
        // Update prev/next buttons
        const prevBtn = paginationContainer.querySelector('.pagination-prev');
        const nextBtn = paginationContainer.querySelector('.pagination-next');
        
        if (prevBtn) {
            prevBtn.disabled = currentPage <= 1;
        }
        if (nextBtn) {
            nextBtn.disabled = currentPage >= totalPages;
        }
        
        // Update page info
        const pageInfo = paginationContainer.querySelector('.pagination-info');
        if (pageInfo) {
            pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        }
    }
    
    function updateProgressControls(currentPage, totalPages) {
        const progressControls = document.querySelector(`[data-section-id="${sectionId}"].archived-progress-controls`);
        if (!progressControls) return;
        
        progressControls.dataset.currentPage = currentPage;
        progressControls.dataset.totalPages = totalPages;
        
        // Update start/end buttons
        const startBtn = progressControls.querySelector('.progress-start');
        const endBtn = progressControls.querySelector('.progress-end');
        
        if (startBtn) {
            startBtn.disabled = currentPage <= 1;
        }
        if (endBtn) {
            endBtn.disabled = currentPage >= totalPages;
        }
        
        // Update progress slider
        const percentage = totalPages > 0 ? (currentPage / totalPages) * 100 : 0;
        updateSliderPosition(percentage);
        
        // Update tooltip
        const tooltip = progressControls.querySelector('.progress-tooltip');
        if (tooltip) {
            tooltip.textContent = `Page ${currentPage} of ${totalPages}`;
        }
        
        // Update progress info
        const progressInfo = document.querySelector(`[data-section-id="${sectionId}"] .archived-progress-info .progress-text`);
        if (progressInfo) {
            const postsPerPage = currentConfig.posts_per_page || 4;
            const totalPosts = totalPages * postsPerPage; // Approximate
            const showingCount = Math.min(postsPerPage, totalPosts - ((currentPage - 1) * postsPerPage));
            const yearDropdown = document.querySelector(`[data-section-id="${sectionId}"].archived-year-dropdown`);
            const currentYear = yearDropdown ? yearDropdown.value : currentConfig.default_year;
            
            progressInfo.textContent = `Showing ${showingCount} of ${totalPosts} articles from ${currentYear}`;
        }
    }
    
    function updateURL(year, page) {
        const url = new URL(window.location);
        url.searchParams.set('archive_year', year);
        url.searchParams.set('archive_page', page);
        
        window.history.pushState(null, '', url.toString());
    }
    
    function scrollToContent() {
        const wrapper = document.querySelector(`[data-section-id="${sectionId}"]`);
        if (wrapper) {
            wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    
    function showLoadingState() {
        const gridContainer = document.querySelector(`#${sectionId}-grid`);
        if (gridContainer) {
            gridContainer.classList.add('loading');
        }
    }
    
    function hideLoadingState() {
        const gridContainer = document.querySelector(`#${sectionId}-grid`);
        if (gridContainer) {
            gridContainer.classList.remove('loading');
        }
    }
    
    // Public API
    return {
        init: init,
        loadContent: loadContent,
        navigateToPage: navigateToPage
    };
})();

// Auto-initialize if wrapper exists
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.querySelector('.archived-news-wrapper');
    if (wrapper && wrapper.dataset.sectionId) {
        window.ArchivedNews.init(wrapper.dataset.sectionId);
    }
});
