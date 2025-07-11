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
        // get the featured posts data
        const wrapper = document.querySelector('.featured-news-wrapper');
        if (!wrapper) {
            console.log('Featured news wrapper not found');
            return;
        }
        
        try {
            featuredPosts = JSON.parse(wrapper.dataset.featuredPosts || '[]');
            
            // get the detailed post data if available
            if (wrapper.dataset.postsData) {
                const postsArray = JSON.parse(wrapper.dataset.postsData);
                postsData = {};
                postsArray.forEach((post, index) => {
                    postsData[index] = post;
                });
            }
        } catch (e) {
            console.error('Error parsing featured posts data:', e);
            featuredPosts = [];
        }
        
        // use demo data if no posts available
        if (featuredPosts.length === 0) {
            featuredPosts = [1, 2, 3, 4, 5];
            initializeDemoData();
        }
        
        console.log('Featured posts:', featuredPosts);
        console.log('Posts data:', postsData);
        
        // initialize event listeners
        bindEvents();
        
        // set initial current story
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
        // navigation chevron buttons
        const prevBtn = document.querySelector('.hero-nav-prev');
        const nextBtn = document.querySelector('.hero-nav-next');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                navigateStory('prev');
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                navigateStory('next');
            });
        }
        
        // story item clicks in sidebar
        const storyItems = document.querySelectorAll('.story-item');
        
        storyItems.forEach((item, index) => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
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
        
        // update hero section via AJAX
        updateHeroSection(postId);
        
        // update sidebar highlighting
        updateSidebarHighlight(index);
    }
    
    function updateHeroSection(postId) {
        // show loading state
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.classList.add('loading');
        }
        
        // check if we have WordPress AJAX available
        const ajaxUrl = window.featuredNewsAjax?.ajaxurl || window.ajaxurl;
        const nonce = window.featuredNewsAjax?.nonce || window.featuredNewsNonce;
        
        if (ajaxUrl && nonce) {
            // make AJAX request to get real post data
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
                    updateHeroWithDemoData();
                }
            })
            .catch(error => {
                console.error('AJAX error:', error);
                updateHeroWithDemoData();
            })
            .finally(() => {
                finishLoading();
            });
        } else {
            // fallback to demo data if WordPress AJAX is not available
            updateHeroWithDemoData();
        }
    }
    
    function updateHeroWithDemoData() {
        // use the data we have (either from WordPress or demo)
        const data = postsData[currentIndex];
        
        if (data) {
            updateHeroContent(data);
        } else {
            console.error('No data found for index:', currentIndex);
        }
        
        setTimeout(() => {
            finishLoading();
        }, 300);
    }
    
    function finishLoading() {
        isLoading = false;
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.classList.remove('loading');
        }
    }
    
    function updateHeroContent(postData) {
        // define all selectors and their corresponding data properties
        const updates = [
            { selector: '.hero-image', property: 'image', attr: 'src', altAttr: 'alt' },
            { selector: '.hero-title', property: 'title', attr: 'textContent' },
            { selector: '.hero-date', property: 'date', attr: 'textContent', prefix: 'Published ' },
            { selector: '.hero-views', property: 'views', attr: 'textContent', suffix: ' Views' },
            { selector: '.hero-excerpt p', property: 'excerpt', attr: 'textContent' },
            { selector: '.read-more-button', property: 'url', attr: 'href' }
        ];
        
        // apply updates efficiently
        updates.forEach(update => {
            const element = document.querySelector(update.selector);
            if (element && postData[update.property]) {
                const value = (update.prefix || '') + postData[update.property] + (update.suffix || '');
                
                if (update.attr === 'src' && update.altAttr) {
                    element.src = postData[update.property];
                    element.alt = postData.title || '';
                } else {
                    element[update.attr] = value;
                }
            }
        });
        
        // update data attribute
        const heroSection = document.querySelector('.hero-section');
        if (heroSection && postData.id) {
            heroSection.dataset.currentPost = postData.id;
        }
    }
    
    function updateSidebarHighlight(index) {
        // update all story items in one pass
        const storyItems = document.querySelectorAll('.story-item');
        storyItems.forEach((item, i) => {
            if (i === index) {
                item.classList.add('story-current');
            } else {
                item.classList.remove('story-current');
            }
        });
        
        // update sidebar data attribute
        const sidebar = document.querySelector('.featured-stories-list');
        if (sidebar) {
            sidebar.dataset.currentPost = featuredPosts[index];
        }
    }
    
    // public API
    return {
        init: init,
        navigateStory: navigateStory,
        updateCurrentStory: updateCurrentStory
    };
})();
