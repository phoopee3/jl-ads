/**
 * JL Ads Frontend JavaScript
 * Handles responsive ad display based on viewport width
 */
(function() {
    'use strict';

    // Breakpoints for ad versions
    var BREAKPOINTS = {
        desktop: 1024,  // >= 1024px shows desktop
        tablet: 768     // >= 768px and < 1024px shows tablet
                        // < 768px shows mobile
    };

    /**
     * Get the current device type based on viewport width
     */
    function getCurrentDevice() {
        var width = window.innerWidth;
        
        if (width >= BREAKPOINTS.desktop) {
            return 'desktop';
        } else if (width >= BREAKPOINTS.tablet) {
            return 'tablet';
        } else {
            return 'mobile';
        }
    }

    /**
     * Update visibility of ad versions
     */
    function updateAdVisibility() {
        var currentDevice = getCurrentDevice();
        var containers = document.querySelectorAll('.jl-ad-container');
        
        containers.forEach(function(container) {
            var versions = container.querySelectorAll('.jl-ad-version');
            var hasShownVersion = false;
            
            versions.forEach(function(version) {
                // Get the version type from class
                var isDesktop = version.classList.contains('jl-ad-desktop');
                var isTablet = version.classList.contains('jl-ad-tablet');
                var isMobile = version.classList.contains('jl-ad-mobile');
                
                var shouldShow = false;
                
                if (currentDevice === 'desktop' && isDesktop) {
                    shouldShow = true;
                } else if (currentDevice === 'tablet' && isTablet) {
                    shouldShow = true;
                } else if (currentDevice === 'mobile' && isMobile) {
                    shouldShow = true;
                }
                
                if (shouldShow) {
                    version.style.display = 'block';
                    hasShownVersion = true;
                } else {
                    version.style.display = 'none';
                }
            });
            
            // Fallback: if no version was shown, try to show the best available
            if (!hasShownVersion) {
                showFallbackVersion(container, currentDevice);
            }
        });
    }

    /**
     * Show fallback version if the preferred version is not available
     */
    function showFallbackVersion(container, currentDevice) {
        var fallbackOrder;
        
        // Define fallback order based on current device
        if (currentDevice === 'desktop') {
            fallbackOrder = ['desktop', 'tablet', 'mobile'];
        } else if (currentDevice === 'tablet') {
            fallbackOrder = ['tablet', 'desktop', 'mobile'];
        } else {
            fallbackOrder = ['mobile', 'tablet', 'desktop'];
        }
        
        for (var i = 0; i < fallbackOrder.length; i++) {
            var version = container.querySelector('.jl-ad-' + fallbackOrder[i]);
            if (version) {
                version.style.display = 'block';
                break;
            }
        }
    }

    /**
     * Debounce function to limit resize event firing
     */
    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this;
            var args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    /**
     * Initialize
     */
    function init() {
        // Initial update
        updateAdVisibility();
        
        // Listen for resize events with debouncing
        window.addEventListener('resize', debounce(updateAdVisibility, 150));
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
