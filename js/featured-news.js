/**
 * Featured News JavaScript Functionality
 * Handles navigation between featured stories and dynamic content updates
 * 
 * @package GWT
 * @since Government Website Template 2.0
 */

window.FeaturedNews = (function() {
    'use strict';
    
    let currentIndex = 0;
    let featuredPosts = [];
    let postsData = {};
    let isLoading = false;
    
    function init() {
        // Get the featured posts data
        const wrapper = document.querySelector('.featured-news-wrapper');
        if (!wrapper) {
            console.log('Featured news wrapper not found');
            return;
        }
        
        try {
            featuredPosts = JSON.parse(wrapper.dataset.featuredPosts || '[]');
            
            // Get the detailed post data if available
            if (wrapper.dataset.postsData) {
                const postsArray = JSON.parse(wrapper.dataset.postsData);
                postsData = {};
                postsArray.forEach((post, index) => {
                    postsData[index] = post;
                });
            }
        } catch (e) {
            console.error('Error parsing featured posts data:', e);
            // Use demo data for testing
            featuredPosts = [1, 2, 3, 4, 5];
            initializeDemoData();
        }
        
        if (featuredPosts.length === 0) {
            // Use demo data for testing
            featuredPosts = [1, 2, 3, 4, 5];
            initializeDemoData();
        }
        
        console.log('Featured posts:', featuredPosts);
        console.log('Posts data:', postsData);
        
        // Initialize event listeners
        bindEvents();
        
        // Set initial current story
        updateCurrentStory(0);
    }
    
    function initializeDemoData() {
        postsData = {
            0: {
                title: "DOST Launches Revolutionary Science Initiative for 2025",
                date: "July 5, 2025",
                excerpt: "The Department of Science and Technology announces a groundbreaking initiative that will transform the Philippine science landscape.",
                views: "0",
                image: "https://via.placeholder.com/800x400/4a90e2/ffffff?text=Science+Initiative"
            },
            1: {
                title: "New Agricultural Technology Boosts Farmer Productivity", 
                date: "July 4, 2025",
                excerpt: "Innovative farming techniques developed by DOST researchers are helping farmers increase their crop yields significantly.",
                views: "15",
                image: "https://via.placeholder.com/800x400/7ed321/ffffff?text=Agricultural+Tech"
            },
            2: {
                title: "Filipino Scientists Achieve Solar Power Breakthrough",
                date: "July 3, 2025", 
                excerpt: "Local researchers have developed more efficient solar panels that could revolutionize renewable energy in the Philippines.",
                views: "32",
                image: "https://via.placeholder.com/800x400/f5a623/ffffff?text=Solar+Power"
            },
            3: {
                title: "DOST Establishes Tech Innovation Hubs Nationwide",
                date: "July 2, 2025",
                excerpt: "New technology centers will provide support and resources for startups and entrepreneurs across the country.",
                views: "18",
                image: "https://via.placeholder.com/800x400/bd10e0/ffffff?text=Innovation+Hubs"
            },
            4: {
                title: "DOST Launches Scholarship Program for 1,000 Students", 
                date: "July 1, 2025",
                excerpt: "A comprehensive scholarship program aims to support the next generation of Filipino scientists and technologists.",
                views: "45",
                image: "https://via.placeholder.com/800x400/50e3c2/ffffff?text=Scholarship+Program"
            }
        };
    }
    
    function bindEvents() {
        // Navigation chevron buttons
        const prevBtn = document.querySelector('.hero-nav-prev');
        const nextBtn = document.querySelector('.hero-nav-next');
        
        console.log('Binding events to navigation buttons:', { prevBtn, nextBtn });
        
        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('Previous button clicked');
                navigateStory('prev');
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('Next button clicked');
                navigateStory('next');
            });
        }
        
        // Story item clicks in sidebar
        const storyItems = document.querySelectorAll('.story-item');
        console.log('Found story items:', storyItems.length);
        
        storyItems.forEach((item, index) => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                console.log('Story item clicked:', index);
                if (!isLoading) {
                    updateCurrentStory(index);
                }
            });
        });
    }
    
    function navigateStory(direction) {
        if (isLoading) return;
        
        let newIndex = currentIndex;
        
        if (direction === 'prev') {
            newIndex = currentIndex > 0 ? currentIndex - 1 : featuredPosts.length - 1;
        } else if (direction === 'next') {
            newIndex = currentIndex < featuredPosts.length - 1 ? currentIndex + 1 : 0;
        }
        
        updateCurrentStory(newIndex);
    }
    
    function updateCurrentStory(index) {
        if (index < 0 || index >= featuredPosts.length || isLoading) return;
        
        isLoading = true;
        currentIndex = index;
        const postId = featuredPosts[index];
        
        // Update hero section via AJAX
        updateHeroSection(postId);
        
        // Update sidebar highlighting
        updateSidebarHighlight(index);
    }
    
    function updateHeroSection(postId) {
        // Show loading state
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.classList.add('loading');
        }
        
        // Check if we have WordPress AJAX available
        const ajaxUrl = window.featuredNewsAjax?.ajaxurl || window.ajaxurl;
        const nonce = window.featuredNewsAjax?.nonce || window.featuredNewsNonce;
        
        if (ajaxUrl && nonce) {
            // Make AJAX request to get real post data
            const formData = new FormData();
            formData.append('action', 'get_featured_post_data');
            formData.append('post_id', postId);
            formData.append('nonce', nonce);
            
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateHeroContent(data.data);
                } else {
                    console.error('Error loading post data:', data.message);
                    // Fallback to demo data
                    updateHeroWithDemoData();
                }
            })
            .catch(error => {
                console.error('AJAX error:', error);
                // Fallback to demo data
                updateHeroWithDemoData();
            })
            .finally(() => {
                isLoading = false;
                if (heroSection) {
                    heroSection.classList.remove('loading');
                }
            });
        } else {
            // Fallback to demo data if WordPress AJAX is not available
            updateHeroWithDemoData();
        }
    }
    
    function updateHeroWithDemoData() {
        // Use the data we have (either from WordPress or demo)
        const data = postsData[currentIndex];
        
        if (data) {
            updateHeroContent(data);
        } else {
            console.error('No data found for index:', currentIndex);
        }
        
        setTimeout(() => {
            isLoading = false;
            const heroSection = document.querySelector('.hero-section');
            if (heroSection) {
                heroSection.classList.remove('loading');
            }
        }, 300);
    }
    
    function updateHeroContent(postData) {
        // Update hero image
        const heroImage = document.querySelector('.hero-image');
        if (heroImage && postData.image) {
            heroImage.src = postData.image;
            heroImage.alt = postData.title;
        }
        
        // Update hero title
        const heroTitle = document.querySelector('.hero-title');
        if (heroTitle && postData.title) {
            heroTitle.textContent = postData.title;
        }
        
        // Update hero date
        const heroDate = document.querySelector('.hero-date');
        if (heroDate && postData.date) {
            heroDate.textContent = 'Published ' + postData.date;
        }
        
        // Update hero views
        const heroViews = document.querySelector('.hero-views');
        if (heroViews && postData.views) {
            heroViews.textContent = postData.views + ' Views';
        }
        
        // Update hero excerpt
        const heroExcerpt = document.querySelector('.hero-excerpt p');
        if (heroExcerpt && postData.excerpt) {
            heroExcerpt.textContent = postData.excerpt;
        }
        
        // Update read more button
        const readMoreBtn = document.querySelector('.read-more-button');
        if (readMoreBtn && postData.url) {
            readMoreBtn.href = postData.url;
        }
        
        // Update data attribute
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.dataset.currentPost = postData.id;
        }
    }
    
    function updateSidebarHighlight(index) {
        // Remove current highlighting
        const currentItems = document.querySelectorAll('.story-item.story-current');
        currentItems.forEach(item => item.classList.remove('story-current'));
        
        // Add highlighting to new current item
        const storyItems = document.querySelectorAll('.story-item');
        if (storyItems[index]) {
            storyItems[index].classList.add('story-current');
        }
        
        // Update sidebar data attribute
        const sidebar = document.querySelector('.featured-stories-list');
        if (sidebar) {
            sidebar.dataset.currentPost = featuredPosts[index];
        }
    }
    
    // Public API
    return {
        init: init,
        navigateStory: navigateStory,
        updateCurrentStory: updateCurrentStory
    };
})();
