/**
 * Brace Yourself Theme - Main JavaScript
 *
 * Minimal vanilla JS — only what CSS cannot handle.
 * Currently: video carousel management (autoplay detection, lazy loading, pause-on-hidden).
 * Smooth scrolling and lazy loading are handled by CSS and native browser features.
 *
 * @package Brace_Yourself
 */

(function() {
	'use strict';

	/**
	 * Initialize theme functionality
	 */
	function init() {
		initBackgroundCarousel();
	}

	/**
	 * Background Carousel
	 * 
	 * Handles video autoplay detection and fallback to images.
	 * All carousel images load eagerly via src attributes (they're in-viewport).
	 * Videos are lazy-loaded for performance.
	 */
	function initBackgroundCarousel() {
		const carousel = document.querySelector('.background-carousel');
		if (!carousel) {
			return;
		}

		const videos = carousel.querySelectorAll('.background-carousel__video');
		const images = carousel.querySelectorAll('.background-carousel__image');

		// Only run if there are videos to manage
		if (videos.length > 0) {
			setupVideoLazyLoading(videos, carousel);
			checkVideoAutoplay(videos, images);
		}
	}

	/**
	 * Lazy load videos - only load when needed
	 * Uses Intersection Observer for better performance
	 */
	function setupVideoLazyLoading(videos, carousel) {
		const slideDuration = parseInt(carousel.getAttribute('data-slide-duration'), 10) || 7000;

		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					const video = entry.target;
					const videoSrc = video.getAttribute('data-video-src');
					const existingSource = Array.from(video.querySelectorAll('source')).find(source => source.src === videoSrc);
					if (videoSrc && !existingSource) {
						loadVideoSource(video, videoSrc);
						// Preload next video before it's needed
						const nextIndex = parseInt(video.getAttribute('data-video-index'), 10) + 1;
						if (videos[nextIndex]) {
							const nextVideoSrc = videos[nextIndex].getAttribute('data-video-src');
							if (nextVideoSrc) {
								setTimeout(() => {
									loadVideoSource(videos[nextIndex], nextVideoSrc);
								}, slideDuration - 2000);
							}
						}
					}
				}
			});
		}, {
			root: carousel,
			rootMargin: '50%',
			threshold: 0.1
		});

		videos.forEach(video => observer.observe(video));

		// Pause videos when not visible (performance + battery)
		setupVideoPauseOnHidden(videos);
	}

	/**
	 * Pause videos when they're not visible (opacity 0)
	 * Improves performance and battery life
	 */
	function setupVideoPauseOnHidden(videos) {
		const checkVisibility = () => {
			videos.forEach(video => {
				const opacity = parseFloat(window.getComputedStyle(video).opacity);

				if (opacity < 0.1 && !video.paused) {
					video.pause();
				} else if (opacity > 0.9 && video.paused && video.readyState >= 2) {
					video.play().catch(() => {});
				}
			});
		};

		setInterval(checkVisibility, 1000);
	}

	/**
	 * Load video source dynamically
	 */
	function loadVideoSource(video, videoSrc) {
		const existingSource = Array.from(video.querySelectorAll('source')).find(source => source.src === videoSrc);
		if (existingSource) {
			return;
		}

		const source = document.createElement('source');
		source.src = videoSrc;
		source.type = videoSrc.split('.').pop().toLowerCase() === 'webm' ? 'video/webm' : 'video/mp4';
		video.appendChild(source);

		video.addEventListener('error', function() {
			video.setAttribute('data-error', 'true');
			const images = document.querySelectorAll('.background-carousel__image');
			const imageIndex = parseInt(video.getAttribute('data-video-index'), 10);
			if (images[imageIndex]) {
				images[imageIndex].style.display = 'block';
				images[imageIndex].style.opacity = '1';
			}
		}, { once: true });

		video.load();
	}

	/**
	 * Check if videos can autoplay and handle fallback
	 */
	function checkVideoAutoplay(videos, images) {
		let autoplaySupported = false;
		let checkedCount = 0;

		videos.forEach((video, index) => {
			const playPromise = video.play();

			if (playPromise !== undefined) {
				playPromise
					.then(() => {
						autoplaySupported = true;
						video.classList.add('is-playing');
					})
					.catch(() => {
						video.setAttribute('data-fallback', 'true');
						video.style.display = 'none';

						if (images[index]) {
							images[index].style.display = 'block';
							images[index].style.opacity = '1';
						} else if (images[0]) {
							images[0].style.display = 'block';
							images[0].style.opacity = '1';
						}
					})
					.finally(() => {
						checkedCount++;
						if (checkedCount === videos.length && !autoplaySupported && images.length > 0) {
							images[0].style.display = 'block';
							images[0].style.opacity = '1';
						}
					});
			} else {
				video.classList.add('is-playing');
			}
		});
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
