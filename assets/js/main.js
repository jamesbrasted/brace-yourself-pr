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

		// Background carousel
		initBackgroundCarousel();

		// Page transitions
		initPageTransitions();
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

	/**
	 * Background Carousel
	 * 
	 * Handles video autoplay detection and fallback to images.
	 * Ensures carousel continuity across page loads.
	 * Implements lazy loading for videos to improve performance.
	 */
	function initBackgroundCarousel() {
		const carousel = document.querySelector('.background-carousel');
		if (!carousel) {
			return;
		}

		const videos = carousel.querySelectorAll('.background-carousel__video');
		const images = carousel.querySelectorAll('.background-carousel__image');
		
		// Ensure first visible item is loaded before starting animation
		const firstVisibleItem = carousel.querySelector('[data-first-visible="true"]');
		if (firstVisibleItem) {
			// If it's an image, wait for it to load
			if (firstVisibleItem.tagName === 'IMG') {
				if (firstVisibleItem.complete && firstVisibleItem.naturalHeight !== 0) {
					// Image already loaded, proceed
					startCarouselAnimations(carousel, videos, images);
				} else {
					// Wait for image to load
					firstVisibleItem.addEventListener('load', function() {
						startCarouselAnimations(carousel, videos, images);
					}, { once: true });
					
					// Fallback: if image fails to load, start anyway after timeout
					setTimeout(() => {
						startCarouselAnimations(carousel, videos, images);
					}, 3000);
				}
			} else {
				// Video or other element, start immediately
				startCarouselAnimations(carousel, videos, images);
			}
		} else {
			// No first visible item, start normally
			startCarouselAnimations(carousel, videos, images);
		}
	}
	
	/**
	 * Start carousel animations and setup lazy loading
	 */
	function startCarouselAnimations(carousel, videos, images) {
		// Lazy load images - only load when about to be visible
		if (images.length > 0) {
			setupImageLazyLoading(images, carousel);
		}
		
		// Lazy load videos - only load when needed
		if (videos.length > 0) {
			setupVideoLazyLoading(videos, carousel);
			// Check video autoplay support
			checkVideoAutoplay(videos, images);
		}
		
		// Store current carousel state for continuity
		syncCarouselState(carousel);
	}

	/**
	 * Lazy load images using Intersection Observer
	 * Only loads images when they're about to become visible
	 */
	function setupImageLazyLoading(images, carousel) {
		if (images.length === 0) {
			return;
		}

		const slideDuration = parseInt(carousel.getAttribute('data-slide-duration'), 10) || 7000;
		
		// Use Intersection Observer if available
		if ('IntersectionObserver' in window) {
			const observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						const img = entry.target;
						const src = img.getAttribute('data-src');
						const srcset = img.getAttribute('data-srcset');
						const sizes = img.getAttribute('data-sizes');
						
						if (src && !img.src) {
							// Load the image
							img.src = src;
							if (srcset) {
								img.srcset = srcset;
							}
							if (sizes) {
								img.sizes = sizes;
							}
							img.removeAttribute('data-src');
							img.removeAttribute('data-srcset');
							img.removeAttribute('data-sizes');
							
							// Preload next image when current becomes visible
							const nextIndex = parseInt(img.getAttribute('data-image-index'), 10) + 1;
							const nextImg = Array.from(images).find(img => 
								parseInt(img.getAttribute('data-image-index'), 10) === nextIndex
							);
							if (nextImg) {
								const nextSrc = nextImg.getAttribute('data-src');
								if (nextSrc) {
									// Preload next image 2 seconds before it's needed
									setTimeout(() => {
										if (nextImg.getAttribute('data-src')) {
											nextImg.src = nextSrc;
											const nextSrcset = nextImg.getAttribute('data-srcset');
											const nextSizes = nextImg.getAttribute('data-sizes');
											if (nextSrcset) nextImg.srcset = nextSrcset;
											if (nextSizes) nextImg.sizes = nextSizes;
											nextImg.removeAttribute('data-src');
											nextImg.removeAttribute('data-srcset');
											nextImg.removeAttribute('data-sizes');
										}
									}, slideDuration - 2000);
								}
							}
						}
					}
				});
			}, {
				root: carousel,
				rootMargin: '50%', // Start loading when 50% visible
				threshold: 0.1
			});

			// Observe all images except the first (which loads immediately)
			images.forEach((img, index) => {
				if (index > 0 && img.getAttribute('data-src')) {
					observer.observe(img);
				}
			});
		} else {
			// Fallback: Load images progressively based on timing
			images.forEach((img, index) => {
				if (index === 0) {
					return; // First image already loaded
				}
				
				const src = img.getAttribute('data-src');
				if (!src) {
					return;
				}
				
				// Load image before it's needed (2 seconds before)
				const loadTime = index * slideDuration - 2000;
				setTimeout(() => {
					if (img.getAttribute('data-src')) {
						img.src = src;
						const srcset = img.getAttribute('data-srcset');
						const sizes = img.getAttribute('data-sizes');
						if (srcset) img.srcset = srcset;
						if (sizes) img.sizes = sizes;
						img.removeAttribute('data-src');
						img.removeAttribute('data-srcset');
						img.removeAttribute('data-sizes');
					}
				}, Math.max(0, loadTime));
			});
		}
	}

	/**
	 * Lazy load videos - only load when needed
	 * Uses Intersection Observer for better performance
	 */
	function setupVideoLazyLoading(videos, carousel) {
		if (videos.length === 0) {
			return;
		}

		const slideDuration = parseInt(carousel.getAttribute('data-slide-duration'), 10) || 7000;
		
		// Use Intersection Observer if available (better performance)
		if ('IntersectionObserver' in window) {
			const observer = new IntersectionObserver((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						const video = entry.target;
						const videoSrc = video.getAttribute('data-video-src');
						if (videoSrc && !video.querySelector('source[src="' + videoSrc + '"]')) {
							loadVideoSource(video, videoSrc);
							// Load next video when current becomes visible
							const nextIndex = parseInt(video.getAttribute('data-video-index'), 10) + 1;
							if (videos[nextIndex]) {
								const nextVideoSrc = videos[nextIndex].getAttribute('data-video-src');
								if (nextVideoSrc) {
									setTimeout(() => {
										loadVideoSource(videos[nextIndex], nextVideoSrc);
									}, slideDuration - 2000); // Load 2s before needed
								}
							}
						}
					}
				});
			}, {
				root: carousel,
				rootMargin: '50%', // Start loading when 50% visible
				threshold: 0.1
			});

			videos.forEach(video => {
				observer.observe(video);
			});
		} else {
			// Fallback for older browsers
			videos.forEach((video, index) => {
				const videoSrc = video.getAttribute('data-video-src');
				if (!videoSrc) {
					return;
				}

				// First video loads immediately
				if (index === 0) {
					return;
				}

				// Second video loads when first starts playing
				if (index === 1) {
					videos[0].addEventListener('play', function() {
						loadVideoSource(video, videoSrc);
					}, { once: true });
					return;
				}

				// Subsequent videos load before needed
				const loadTime = (index - 1) * slideDuration - 2000;
				setTimeout(() => {
					loadVideoSource(video, videoSrc);
				}, Math.max(0, loadTime));
			});
		}

		// Pause videos when not visible (performance optimization)
		setupVideoPauseOnHidden(videos, carousel);
	}

	/**
	 * Pause videos when they're not visible (opacity 0)
	 * Improves performance and battery life
	 */
	function setupVideoPauseOnHidden(videos, carousel) {
		if (videos.length === 0) {
			return;
		}

		// Check visibility periodically
		const checkVisibility = () => {
			videos.forEach((video, index) => {
				const computedStyle = window.getComputedStyle(video);
				const opacity = parseFloat(computedStyle.opacity);
				
				// Pause if opacity is 0 or very low
				if (opacity < 0.1 && !video.paused) {
					video.pause();
				} else if (opacity > 0.9 && video.paused && video.readyState >= 2) {
					// Resume if visible and loaded
					video.play().catch(() => {
						// Autoplay might fail, that's okay
					});
				}
			});
		};

		// Check every second
		setInterval(checkVisibility, 1000);
	}

	/**
	 * Load video source dynamically
	 */
	function loadVideoSource(video, videoSrc) {
		// Check if source already loaded
		if (video.querySelector('source[src="' + videoSrc + '"]')) {
			return;
		}

		const source = document.createElement('source');
		source.src = videoSrc;
		
		// Detect video format from extension
		const extension = videoSrc.split('.').pop().toLowerCase();
		if (extension === 'webm') {
			source.type = 'video/webm';
		} else {
			source.type = 'video/mp4';
		}
		
		video.appendChild(source);
		
		// Add error handling
		video.addEventListener('error', function() {
			console.warn('Video failed to load:', videoSrc);
			video.setAttribute('data-error', 'true');
			// Try to show image fallback if available
			const images = document.querySelectorAll('.background-carousel__image');
			if (images.length > 0) {
				const imageIndex = parseInt(video.getAttribute('data-video-index'), 10);
				if (images[imageIndex]) {
					images[imageIndex].style.display = 'block';
					images[imageIndex].style.opacity = '1';
				}
			}
		}, { once: true });
		
		// Load the video
		video.load();
	}

	/**
	 * Check if videos can autoplay and handle fallback
	 */
	function checkVideoAutoplay(videos, images) {
		let autoplaySupported = false;
		let checkedCount = 0;
		const totalVideos = videos.length;

		videos.forEach((video, index) => {
			// Try to play the video
			const playPromise = video.play();

			if (playPromise !== undefined) {
				playPromise
					.then(() => {
						// Autoplay works
						autoplaySupported = true;
						video.classList.add('is-playing');
					})
					.catch(() => {
						// Autoplay failed - hide video, show image fallback
						video.setAttribute('data-fallback', 'true');
						video.style.display = 'none';
						
						// Show corresponding image if available
						if (images[index]) {
							images[index].style.display = 'block';
							images[index].style.opacity = '1';
						} else if (images[0]) {
							// Fallback to first image
							images[0].style.display = 'block';
							images[0].style.opacity = '1';
						}
					})
					.finally(() => {
						checkedCount++;
						if (checkedCount === totalVideos && !autoplaySupported && images.length > 0) {
							// All videos failed - ensure first image is visible
							images[0].style.display = 'block';
							images[0].style.opacity = '1';
						}
					});
			} else {
				// Play promise not supported - assume autoplay works
				video.classList.add('is-playing');
				checkedCount++;
			}
		});
	}

	/**
	 * Sync carousel state for continuity across pages
	 */
	function syncCarouselState(carousel) {
		const slideDuration = parseInt(carousel.getAttribute('data-slide-duration'), 10) || 7000;
		const totalItems = parseInt(carousel.getAttribute('data-total-items'), 10) || 1;
		
		// Calculate current animation progress
		const now = Date.now();
		const animationProgress = (now % slideDuration) / slideDuration;
		const currentIndex = Math.floor(animationProgress * totalItems);
		
		// Store in sessionStorage for next page load
		try {
			sessionStorage.setItem('carousel_start', currentIndex.toString());
		} catch (e) {
			// SessionStorage not available - ignore
		}
	}

	/**
	 * Page Transition Overlay
	 * 
	 * Creates seamless page transitions by showing overlay during navigation.
	 */
	function initPageTransitions() {
		const overlay = document.querySelector('.background-carousel__overlay');
		if (!overlay) {
			return;
		}

		// Only enable if user hasn't requested reduced motion
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			return;
		}

		// Find all internal links
		const links = document.querySelectorAll('a[href^="' + window.location.origin + '"]');
		
		links.forEach(link => {
			link.addEventListener('click', function(e) {
				const href = this.getAttribute('href');
				
				// Skip if external, hash, or special protocols
				if (!href || href.includes('#') || href.includes('mailto:') || href.includes('tel:')) {
					return;
				}

				// Show overlay immediately
				overlay.classList.add('is-transitioning');
				
				// Hide overlay after navigation (if browser supports it)
				// Note: This may not work in all browsers, but provides visual feedback
				setTimeout(() => {
					overlay.classList.remove('is-transitioning');
				}, 300);
			});
		});

		// Hide overlay on page load
		window.addEventListener('load', () => {
			overlay.classList.remove('is-transitioning');
		});
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
