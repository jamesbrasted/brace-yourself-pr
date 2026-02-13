/**
 * Brace Yourself Theme - Main JavaScript
 *
 * Minimal vanilla JS — only what CSS cannot handle.
 * Carousel: autoplay detection, lazy loading, pause-on-hidden.
 * Roster: hover preview (cursor follow) and viewport visibility on mobile.
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
		initRoster();
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

				if (opacity < 0.05 && !video.paused) {
					// Fully (or almost fully) hidden: ensure video is paused to save resources.
					video.pause();
				} else if (opacity > 0.15 && video.paused && video.readyState >= 2) {
					// Start playback very early in the fade-in so video is
					// already running by the time the slide is noticeably visible.
					video.play().catch(() => {});
				}
			});
		};

		// Check a bit more frequently so playback syncs more closely
		// with the visual fade-in, without a noticeable perf impact.
		setInterval(checkVisibility, 250);
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

	/**
	 * Roster (artist list): hover preview follows cursor on desktop,
	 * IntersectionObserver toggles .is-visible on mobile.
	 */
	function initRoster() {
		const rosterEl = document.querySelector('.roster[data-module="roster"]');
		if (!rosterEl) return;

		const rosterItems = Array.from(rosterEl.querySelectorAll('.artist__item'));
		const itemsWithPreview = rosterItems
			.map((li) => ({ li, preview: li.querySelector('.artist__preview') }))
			.filter(({ preview }) => preview);

		if (itemsWithPreview.length === 0) return;

		const alignments = ['preview-align-left', 'preview-align-center', 'preview-align-right'];
		let prevAlignment = null;
		itemsWithPreview.forEach(({ li }) => {
			const choices = prevAlignment
				? alignments.filter((a) => a !== prevAlignment)
				: alignments;
			const alignment = choices[Math.floor(Math.random() * choices.length)];
			li.classList.add(alignment);
			prevAlignment = alignment;
		});

		const desktop = window.matchMedia('(hover: hover) and (pointer: fine)');
		const mobile = window.matchMedia('(hover: none)');
		let teardownDesktop = null;
		let teardownMobile = null;

		function setupDesktop() {
			if (teardownMobile) {
				teardownMobile();
				teardownMobile = null;
			}
			let rafId = null;

			function updatePreviewTransform(li, preview, x, y) {
				const liRect = li.getBoundingClientRect();
				const imgRect = preview.getBoundingClientRect();
				const relX = x - liRect.left;
				const relY = y - liRect.top;
				const tx = relX - imgRect.width / 2;
				const ty = relY - imgRect.height / 2;
				preview.style.transform = `translate3d(${tx}px, ${ty}px, 0)`;
			}

			function onMove(e) {
				const li = e.target.closest('.artist__item');
				if (!li) return;
				const pair = itemsWithPreview.find(({ li: l }) => l === li);
				if (!pair) return;
				if (rafId !== null) cancelAnimationFrame(rafId);
				rafId = requestAnimationFrame(() => {
					updatePreviewTransform(pair.li, pair.preview, e.clientX, e.clientY);
					rafId = null;
				});
			}

			rosterEl.addEventListener('mousemove', onMove, { passive: true });
			teardownDesktop = () => {
				rosterEl.removeEventListener('mousemove', onMove);
			};
		}

		function setupMobile() {
			if (teardownDesktop) {
				teardownDesktop();
				teardownDesktop = null;
			}
			const observer = new IntersectionObserver(
				(entries) => {
					entries.forEach((entry) => {
						entry.target.classList.toggle('is-visible', entry.isIntersecting);
					});
				},
				{ root: null, rootMargin: '0px', threshold: 0.1 }
			);
			itemsWithPreview.forEach(({ li }) => observer.observe(li));
			teardownMobile = () => {
				observer.disconnect();
				itemsWithPreview.forEach(({ li }) => li.classList.remove('is-visible'));
			};
		}

		if (desktop.matches) setupDesktop();
		else if (mobile.matches) setupMobile();

		desktop.addEventListener('change', (e) => { if (e.matches) setupDesktop(); });
		mobile.addEventListener('change', (e) => { if (e.matches) setupMobile(); });
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
