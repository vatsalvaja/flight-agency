/**
 * Wings Global Flight Aviation Loader
 * Manages loading state overlays, form submits, link navigation, pageshow restoration,
 * and AJAX/Fetch interception with debounce delays to prevent flashes.
 */

(function() {
    'use strict';

    // Settings
    const DEBOUNCE_DELAY = 400; // ms to wait before showing loader for AJAX
    const FAILSAFE_TIMEOUT = 10000; // 10 seconds failsafe to prevent getting stuck
    const DEFAULT_NAV_TEXT = 'Preparing flight details...';
    const DEFAULT_AJAX_TEXT = 'Syncing navigation data...';

    // Blacklist keywords for URL bypass (background tasks, notifications, status checks, etc.)
    const BYPASS_KEYWORDS = [
        'notification',
        'poll',
        'background',
        'telemetry',
        'log-event',
        'check-status',
        'livewire/message' // Skip Livewire background delta updates if they are fast
    ];

    let loaderEl = null;
    let statusEl = null;
    let activeAjaxCount = 0;
    let ajaxTimeoutId = null;
    let failsafeTimeoutId = null;
    let navClickTimeoutId = null;

    // Initialize DOM references
    function initLoaderDOM() {
        if (!loaderEl) {
            loaderEl = document.getElementById('wings-global-loader');
        }
        if (!statusEl && loaderEl) {
            statusEl = document.getElementById('wings-loader-status');
        }
    }

    /**
     * Show the global loader overlay
     * @param {string} statusText - Text to display underneath the aviation spinner
     */
    function showAppLoader(statusText) {
        initLoaderDOM();
        if (!loaderEl) return;

        if (statusEl) {
            statusEl.textContent = statusText || DEFAULT_NAV_TEXT;
        }

        loaderEl.classList.add('active');
        
        // Start failsafe timer
        startFailsafe();
    }

    /**
     * Hide the global loader overlay
     */
    function hideAppLoader() {
        initLoaderDOM();
        if (!loaderEl) return;

        loaderEl.classList.remove('active');
        stopFailsafe();
    }

    // Expose APIs globally
    window.showAppLoader = showAppLoader;
    window.hideAppLoader = hideAppLoader;

    // Failsafe timer logic
    function startFailsafe() {
        stopFailsafe();
        failsafeTimeoutId = setTimeout(function() {
            hideAppLoader();
        }, FAILSAFE_TIMEOUT);
    }

    function stopFailsafe() {
        if (failsafeTimeoutId) {
            clearTimeout(failsafeTimeoutId);
            failsafeTimeoutId = null;
        }
    }

    // Check if a URL should bypass the loader
    function shouldBypass(url) {
        if (!url) return false;
        const lowercaseUrl = url.toString().toLowerCase();
        return BYPASS_KEYWORDS.some(keyword => lowercaseUrl.includes(keyword));
    }

    // ==========================================================================
    // 1. Navigation / Link Click Interception
    // ==========================================================================
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;

        // Ignore link clicks with modifiers (Ctrl, Cmd, Shift, Alt)
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        // Ignore if default is prevented
        if (e.defaultPrevented) return;

        // Ignore blank targets
        if (link.target && link.target.toLowerCase() === '_blank') return;

        const href = link.getAttribute('href');
        if (!href) return;

        // Ignore common non-navigation hrefs
        if (
            href.startsWith('#') ||
            href.startsWith('javascript:') ||
            href.startsWith('tel:') ||
            href.startsWith('mailto:') ||
            href === ''
        ) {
            return;
        }

        // Ignore download links
        if (link.hasAttribute('download')) return;

        // Ignore specific custom skip attributes
        if (link.hasAttribute('data-no-loader') || link.closest('[data-no-loader]')) return;

        // Ignore external hosts
        if (link.host !== window.location.host) return;

        // Debounce showing the loader (wait 250ms to prevent flashes on fast responses)
        if (navClickTimeoutId) clearTimeout(navClickTimeoutId);
        navClickTimeoutId = setTimeout(function() {
            showAppLoader(DEFAULT_NAV_TEXT);
        }, 250);
    });

    // ==========================================================================
    // 2. Form Submission Interception
    // ==========================================================================
    document.addEventListener('submit', function(e) {
        const form = e.target;

        // Ignore forms marked to skip the loader
        if (form.hasAttribute('data-no-loader') || form.closest('[data-no-loader]')) return;

        // If default is prevented (e.g. ajax validation failed), don't show loader
        if (e.defaultPrevented) return;

        showAppLoader('Processing navigation data...');
    });

    // ==========================================================================
    // 3. Browser Back/Forward Cache (bfcache) Restoration and Page Load Handlers
    // ==========================================================================
    window.addEventListener('pageshow', function(event) {
        if (navClickTimeoutId) {
            clearTimeout(navClickTimeoutId);
            navClickTimeoutId = null;
        }
        // If the page was loaded from history cache, make sure loader is hidden
        hideAppLoader();
    });

    // Make sure we stop failsafes when leaving the page to keep clean states
    window.addEventListener('pagehide', function() {
        stopFailsafe();
    });

    // Set a debounced initial page load timer (e.g. 200ms)
    // If the page loads faster than 200ms, the loader is not shown
    const initialLoadTimeoutId = setTimeout(function() {
        if (document.readyState !== 'complete') {
            showAppLoader(DEFAULT_NAV_TEXT);
        }
    }, 200);

    // Automatically hide the loader when the page has fully loaded
    if (document.readyState === 'complete') {
        clearTimeout(initialLoadTimeoutId);
        hideAppLoader();
    } else {
        window.addEventListener('load', function() {
            clearTimeout(initialLoadTimeoutId);
            if (navClickTimeoutId) {
                clearTimeout(navClickTimeoutId);
                navClickTimeoutId = null;
            }
            hideAppLoader();
        });
    }

    // ==========================================================================
    // 4. AJAX & Fetch Global Interception (with Debounce) - DISABLED
    // (Disabled to prevent fullscreen loader flashes on background AJAX requests,
    // which leads to poor UX and double/triple loader triggers)
    // ==========================================================================
    /*
    function incrementAjax() {
        activeAjaxCount++;
        if (activeAjaxCount === 1) {
            // Schedule loader after debounce delay
            ajaxTimeoutId = setTimeout(function() {
                showAppLoader(DEFAULT_AJAX_TEXT);
            }, DEBOUNCE_DELAY);
        }
    }

    function decrementAjax() {
        activeAjaxCount--;
        if (activeAjaxCount <= 0) {
            activeAjaxCount = 0;
            if (ajaxTimeoutId) {
                clearTimeout(ajaxTimeoutId);
                ajaxTimeoutId = null;
            }
            hideAppLoader();
        }
    }

    // Intercept native Fetch API
    if (window.fetch) {
        const originalFetch = window.fetch;
        window.fetch = function(input, init) {
            let url = '';
            if (typeof input === 'string') {
                url = input;
            } else if (input && input.url) {
                url = input.url;
            }

            // Check headers for manual bypass
            let hasBypassHeader = false;
            if (init && init.headers) {
                if (init.headers instanceof Headers) {
                    hasBypassHeader = init.headers.has('X-No-Loader') || init.headers.get('X-No-Loader') === 'true';
                } else if (typeof init.headers === 'object') {
                    hasBypassHeader = init.headers['X-No-Loader'] === 'true' || init.headers['x-no-loader'] === 'true';
                }
            }

            const isBypassed = shouldBypass(url) || hasBypassHeader;

            if (!isBypassed) {
                incrementAjax();
            }

            return originalFetch.apply(this, arguments)
                .then(function(response) {
                    if (!isBypassed) decrementAjax();
                    return response;
                })
                .catch(function(error) {
                    if (!isBypassed) decrementAjax();
                    throw error;
                });
        };
    }

    // Intercept XMLHttpRequest
    const origOpen = XMLHttpRequest.prototype.open;
    const origSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.open = function(method, url) {
        this._wingsUrl = url;
        this._wingsBypass = shouldBypass(url);
        return origOpen.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function() {
        const self = this;
        let isBypassed = self._wingsBypass;

        // We can inspect request headers during send if setRequestHeader was hooked
        // But checking the custom property is usually sufficient. 
        if (!isBypassed) {
            incrementAjax();
            
            this.addEventListener('loadend', function() {
                decrementAjax();
            });
            
            // Failsafe event in case of unexpected abort/error
            this.addEventListener('error', function() {
                // loadend is always called, but just in case
                if (activeAjaxCount > 0) activeAjaxCount--; 
            });
        }
        
        return origSend.apply(this, arguments);
    };
    */

})();
