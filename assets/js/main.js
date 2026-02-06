/**
 * Brace Yourself Theme - Main JavaScript
 *
 * Vanilla JavaScript only - no jQuery.
 * Single entry file, no globals, loaded in footer with defer.
 *
 * @package Brace_Yourself
 */

(function() {
	'use strict';

	/**
	 * Initialize theme functionality
	 */
	function init() {
		// Mobile menu toggle (if needed)
		initMobileMenu();

		// Smooth scroll for anchor links
		initSmoothScroll();

		// Lazy load enhancement (if needed)
		initLazyLoad();

		// Any other initialization
	}

	/**
	 * Mobile menu toggle
	 */
	function initMobileMenu() {
		const menuToggle = document.querySelector('.menu-toggle');
		const primaryMenu = document.querySelector('.primary-menu');

		if (!menuToggle || !primaryMenu) {
			return;
		}

		menuToggle.addEventListener('click', function(e) {
			e.preventDefault();
			primaryMenu.classList.toggle('is-open');
			menuToggle.setAttribute('aria-expanded', 
				primaryMenu.classList.contains('is-open') ? 'true' : 'false'
			);
		});
	}

	/**
	 * Smooth scroll for anchor links
	 */
	function initSmoothScroll() {
		// Only apply if user hasn't requested reduced motion
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			return;
		}

		document.querySelectorAll('a[href^="#"]').forEach(anchor => {
			anchor.addEventListener('click', function(e) {
				const href = this.getAttribute('href');
				
				// Skip empty hash or just #
				if (!href || href === '#') {
					return;
				}

				const target = document.querySelector(href);
				if (target) {
					e.preventDefault();
					target.scrollIntoView({
						behavior: 'smooth',
						block: 'start'
					});
				}
			});
		});
	}

	/**
	 * Enhanced lazy loading
	 */
	function initLazyLoad() {
		// Native lazy loading is handled by WordPress
		// This can be used for additional enhancements if needed
		
		if ('loading' in HTMLImageElement.prototype) {
			// Browser supports native lazy loading
			return;
		}

		// Fallback for older browsers (if needed)
		// Consider using a lightweight library like lazysizes if required
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
