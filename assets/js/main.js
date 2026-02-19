/**
 * Brace Yourself Theme - Main JavaScript
 *
 * Minimal vanilla JS — only what CSS cannot handle.
 * Carousel: autoplay detection, lazy loading, pause-on-hidden.
 * Roster: hover preview (cursor follow) and viewport visibility on mobile.
 * Custom cursor: desktop homepage only (lerp, hover/click states); disabled on touch and reduced-motion.
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
		initCustomCursor();
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
				if (imgRect.width === 0 || imgRect.height === 0) return;
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

	/**
	 * Desktop custom cursor (homepage only).
	 * GPU-accelerated, lerp-smoothed, hover/click states. Disabled on touch and prefers-reduced-motion.
	 * Re-inits when user toggles back to fine pointer and no reduced motion.
	 */
	function initCustomCursor() {
		const body = document.body;
		if (!body.classList.contains('home')) return;

		const prefersFinePointer = window.matchMedia('(pointer: fine)');
		const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
		function shouldEnable() {
			return prefersFinePointer.matches && !prefersReducedMotion.matches;
		}

		let cleanupRef = null;

		function setupCursor() {
			if (!body.classList.contains('home')) return;
			if (!shouldEnable()) {
				if (cleanupRef) {
					cleanupRef();
					cleanupRef = null;
				}
				return;
			}
			if (cleanupRef) return; /* already active */

			const LERP_FACTOR = 0.15;
			const HOVER_SELECTOR = 'a, button, [role="button"], [data-cursor-hover]';

			let rafId = null;
			let mouseX = 0;
			let mouseY = 0;
			let currentX = 0;
			let currentY = 0;
			let hotspotX = 0;
			let hotspotY = 0;
			let hotspotSet = false;
			let isHover = false;
			let isClicking = false;
			let hasMoved = false;

			const cursorEl = document.createElement('div');
			cursorEl.className = 'custom-cursor';
			cursorEl.setAttribute('aria-hidden', 'true');
			cursorEl.innerHTML =
				'<svg xmlns="http://www.w3.org/2000/svg" width="193" height="281" fill="none" viewBox="0 0 193 281" focusable="false">' +
				'<path fill="#fff" d="M113.83 22.721c2.418.971 5.848-.33 8.511-.183 6.662.019 12.78 1.302 18.873 3.657 3.819 1.679 6.827 4.477 9.921 7.11 2.467 1.854 4.851 4.292 7.602 5.879 2.478 1.374 5.528.96 7.613 3.044 1.832 1.874 2.522 4.674 4.83 6.1 3.046 2.239 13.182 8.638 8.53 12.917-.984.624-2.519.693-3.631 1.174-1.718.684-2.628 1.919-4.234 3.078-2.634 1.847-5.46 3.37-8.043 5.272-4.686 3.353-9.002 7.378-14.31 10.083-2.385 1.21-4.943 1.66-7.307 2.926-2.335 1.242-4.31 3.533-6.559 4.419-2.111.792-4.304 1.466-6.414 2.379-4.735 2.02-9.543 2.39-14.212 4.014-2.361 1.007-2.397 4.226.393 3.613 5.298-1.832 10.636-3.64 16.296-3.124 2.951.536 6.088 1.006 8.841 2.24 3.006 1.678 6.03 3.463 8.995 5.471 1.303.899 2.507 2.095 3.969 2.685 3.318 1.154 6.685-2.634 9.964-3.704 7.5-3.487 16.122-1.649 22.27 3.498 7.706 7.828 6.882 20.072 5.492 30.155-1.375 6.315-3.989 12.332-5.416 18.679-.398 1.595-.868 3.201-1.873 4.483-.633.845-1.544 1.63-1.724 2.699-.321 1.478.742 3.546 1.47 5.575 1.421 4.309 4.497 8.292 5.454 12.521 1.432 6.351 3.749 14.036-.672 19.655-2.135 2.361-3.732 5.472-5.968 7.785-2.133 1.969-4.726 2.875-6.893 4.837-2.022 1.689-3.919 3.551-6.185 4.804-1.507 1.011-3.608 1.689-5.078 2.378-.703.365-1.148.753-1.277 1.301-.049 2.412 1.46 5.168 1.735 7.664 1.857 8.364 2.575 16.758 1.194 25.338-.336 2.566-.477 5.048-1.291 7.452-1.574 4.027-4.435 7.598-6.982 10.978-1.271 1.523-3.018 2.54-4.555 3.577-1.394.867-2.429 2.015-3.932 2.822-3.132 1.388-8.75 2.707-12.276 2.054-6.218-1.917-12.168-5.065-17.981-7.969-1.853-.728-4.381.221-6.336-.645-3.615-2.468-7.788-4.555-12.006-6.033-3.938-1.496-7.97-2.217-11.806-3.6-2.762-.899-5.502-2.101-8.292-2.867-3.416-.815-7.773-.124-9.039-4.292-3.317-7.344-5.049-15.221-7.42-22.919-1.405-4.765-2.335-9.569-3.721-14.323-.906-3.126-.84-6.785-1.332-9.806-.647-4.005-2.113-8.153-3.011-12.187-.81-4.4-1.057-9.032-2.292-13.331-.661-2.751-.43-5.65-.662-8.451-.792-5.302-1.017-11.047-.49-16.449.523-6.021 1.695-12.173 2.724-18.09.635-3.722.195-7.324.648-11.165 1.45-9.709 3.119-19.546 4.678-29.268.366-2.168.67-4.56-1.287-6.032-2.103-1.598-4.489-2.943-6.841-4.074-2.054-1.07-3.554-2.556-5.405-3.95-2.78-1.914-5.786-3.843-8.554-5.804-2.903-2.35-6.057-4.638-9.45-6.103-2.194-1.107-4.28-2.158-6.305-3.644-2.876-1.824-4.623-4.694-7.485-6.237-3.868-1.564-10.032.422-12.707-4.027-1.094-2-.89-4.471-1.647-6.401-.987-2.247-2.036-4.82 1.676-5.412 2.515-.517 5.123-1.668 7.773-2.46 2.196-.648 4.101-1.944 5.922-3.306 3.378-2.504 7.266-4.245 10.661-6.847 3.687-3.105 7.733-5.832 12.284-7.46 5.6-1.991 12.028-3.804 18.175-4.148 4.054.098 8.162-.02 12.161.44 2.223-.012 1.84-2.486 2.64-4.624.899-2.914 2.843-5.431 4.198-8.084C80.3 1.095 87.959-1.522 97.739.793c8.089 1.914 13.557 9.02 15.155 16.583.32 1.514-.377 4.443.861 5.293zM72.43 127.35c.975.331 2.198-1.141 2.996-1.772 2.102-1.989 4.62-3.669 7.183-5.307 2.605-1.61 5.065-3.262 7.453-5.102 1.416-1.05 2.928-1.829 3.794-3.319.555-.883.687-1.928.86-2.941.382-2.114 1.224-4.447 1.268-6.755.086-5.852.24-11.935.624-17.795.7-4.26 1.194-8.428 1.928-12.69.26-5.249.733-10.604 1.117-15.836-1.324-9.266-.728-18.764-1.805-27.856-.526-3.795-.379-3.913-1.237-7.623-.336-1.282-2.389-1.867-3.472-2.008-1.659-.21-4.106.617-4.758 2.182-1.369 3.015-1.234 3.21-2.19 6.35-.815 2.595-2.117 5.01-2.622 7.648-.434 2.756-1.398 5.448-1.618 8.237.067 3.003-.102 6.012-1.196 8.869-.382 1.356-.686 2.757-.608 4.162.113 2.429 1.123 4.532.32 6.765-1.669 4.39-1.93 9.39-1.518 13.986.172 2.09-.209 4.194-.482 6.275-.378 2.695.217 5.24 0 7.942-1.023 6.118-2.303 12.125-3.46 18.316-.487 2.692-.14 5.301-.689 7.894-.512 2.33-1.763 4.801-2.115 7.228-.28 1.42-.291 2.754.178 3.113zM31.936 52.748c.111.326.58.57 1.259.723 1.558.302 3.392.388 5.03.466 5.762.319 11.143-.34 16.767-.453 1.898-.006 3.856.108 5.766.14 2.227.028 4.924.176 6.025-2.065 1.246-2.4 1.814-5.048 2.05-7.823.549-3.084 1.474-9.089-3.138-8.73-12.24.323-21.37 8.64-30.987 15.031-.907.685-2.75 1.487-2.791 2.644zm122.372 1.616c-.069-.294-.305-.604-.514-.844-1.091-1.054-2.391-1.919-3.539-2.936-3.341-2.753-6.814-5.542-11.038-6.78-1.929-.465-3.826-1.542-5.693-1.828-2.711-.202-6.586-2.371-9.313-2.366-3.28.149-4.257.017-7.621 0-2.779.488-1.923 6.651-2.034 8.464.226 7.663-3.084 8.82 6.473 8.346 6.342.127 12.734 1.234 19.03.913 4.287.419 9.269 1.465 13.154-1.07.816-.553 1.253-1.178 1.118-1.804l-.022-.095zM45.03 64.294c-.016.62 1.42 1.195 1.961 1.59 3.092 1.43 13.244 10.574 16.05 9.059 1.374-.81 1.24-3.758 1.528-5.44.183-1.64 1.079-3.832.17-4.765-.942-.94-2.559-.648-3.81-.637-4.487.282-8.767.037-13.128-.034-.809 0-2.14-.193-2.745.192zm91.968 3.167c-.533-.833-2.148-.756-3.106-.91-4.553-.357-9.185-.48-13.753-.75a28 28 0 0 1-3.398-.407c-1.092-.201-2.481-.467-2.8.903-.361 3.275.225 6.467-.06 9.697.03 1.087-.289 2.585.368 3.456.581.535 1.694-.014 2.482-.293 2.899-1.157 5.65-2.33 8.244-3.975 3.059-1.877 6.17-3.886 9.374-5.52.84-.523 2.618-1.129 2.666-2.14zm6.847 48.388c.86-1.604-1.4-3.871-2.936-5.049-4.143-3.099-9.52-1.054-14.05.03-3.752 1.38-6.982 3.918-10.429 5.818-3.712 1.995-7.359 3.788-10.949 6.08-6.145 3.509-11.672 8.162-17.247 12.62-4.55 3.317-9.702 5.972-13.953 9.819-1.381 1.112-2.967 2.245-3.56 3.871-1.007 4.122-1.725 9.178-1.951 13.55.073 4.181.117 8.883.98 13.011.451 9.736 3.26 19.359 4.925 28.977 1.135 8.719 3.332 17.135 6.068 25.413 1.366 3.817.899 8.485 3.628 11.529 1.897 1.844 4.766 1.942 7.07 3.01 2.88 1.407 5.796 3.194 8.826 4.088 3.957 1.363 8.474 1.695 12.366 3.495 3.14 1.29 6.319 1.954 9.212 3.506 3.577 1.839 7.419 2.736 11.287 3.609 6.166 2.396 12.021-3.342 13.565-9.037 2.316-7.045 2.295-14.528 1.007-21.812-.747-3.653-2.505-7.209-3.27-10.91-2.153-6.683-5.712-13.143-6.479-20.247-.375-1.734-1.644-2.788-3.2-3.418-3.883-1.845-9.618-4.769-10.509-9.486-.3-4.862 6.687-6.162 10.093-7.216 3.586-1.314 7.383-1.598 11.009-2.734 3.101-1.272 5.245-3.649 8.719-3.3 5.354-.001 10.285 6.561 5.85 11.028-4.903 6.263-8.839 7.912-7.201 16.846.318 3.531 1.71 5.893 4.861 3.572 3.196-2.279 5.767-4.78 8.313-7.563 1.423-1.532 3.119-2.813 4.516-4.334 4.156-4.024 3.68-10.915 1.719-15.971-.392-1.422-.517-2.872-.89-4.294-.547-2.193-1.925-4.251-2.987-6.233-1.002-1.927-1.973-4.035-2.74-6.176-.553-1.432-1.221-3.164-2.652-3.802-1.498-.655-3.185.207-4.608.788-2.919 1.168-5.725 1.936-8.52 3.198-3.039 1.197-6.311 1.642-9.437 2.674-2.931.876-5.292 2.781-7.962 4.219-5.785 3.22-11.859 5.282-18.241 3.475-2.419-.531-6.313-3.132-3.872-5.753 1.549-1.455 3.811-2.182 5.612-3.288 5.529-3.604 11.161-7.05 17.231-9.59 2.578-1.325 4.809-3.284 7.485-4.363 4.511-1.725 8.736-4.146 13.187-5.755 5.645-1.714 2.063-6.469-1.285-8.375-2.308-1.18-4.948-.326-7.338.558-4.232 2.069-9 2.857-13.124 5.127-5.995 3.468-11.37 8.416-17.674 10.757-2.882.965-5.218 2.972-8.36 3.374-7.312.557-17.017-3.933-6.815-10.087 1.876-1.139 4.419-1.848 6.431-3.127 4.689-2.98 8.892-6.738 13.68-9.615 2.711-1.945 5.422-3.335 8.341-4.767 3.452-1.918 6.962-3.969 10.603-5.557 1.759-.908 4.312-.689 5.606-2.106l.05-.077zm27.163 21.045c3.504-1.876 3.808-13.566 4.456-17.491.102-.966.107-1.942-.304-2.823-1.067-2.17-3.677-3.596-5.672-1.692-2.123 1.846-4.558 4.487-4.598 7.391.4 3.477 2.402 6.69 3.615 9.866.645 1.321 1.212 2.775 1.818 4.088.195.371.393.607.617.653z"/>' +
				'</svg>';
			body.appendChild(cursorEl);
			body.classList.add('has-custom-cursor');

			function onMouseMove(e) {
				mouseX = e.clientX;
				mouseY = e.clientY;
				cursorEl.classList.remove('is-outside');
				if (!hasMoved) {
					hasMoved = true;
					cursorEl.classList.add('is-visible');
				}
			}
			function onMouseOut(e) {
				if (!e.relatedTarget || !document.contains(e.relatedTarget)) {
					cursorEl.classList.add('is-outside');
				}
			}
			function onMouseOver(e) {
				const hit = e.target.closest(HOVER_SELECTOR);
				isHover = !!hit;
			}
			function onMouseOutHover(e) {
				const hit = e.target.closest(HOVER_SELECTOR);
				if (!hit || !hit.contains(e.relatedTarget)) isHover = false;
			}
			function onMouseDown() { isClicking = true; }
			function onMouseUp() { isClicking = false; }

			function tick() {
				if (!hotspotSet) {
					hotspotX = cursorEl.offsetWidth * 0.5;
					hotspotY = 0;
					hotspotSet = true;
					currentX = mouseX - hotspotX;
					currentY = mouseY - hotspotY;
				}
				const targetX = mouseX - hotspotX;
				const targetY = mouseY - hotspotY;
				currentX += (targetX - currentX) * LERP_FACTOR;
				currentY += (targetY - currentY) * LERP_FACTOR;
				const scale = isClicking ? 0.92 : isHover ? 1.1 : 1;
				cursorEl.style.transform = 'translate3d(' + currentX + 'px,' + currentY + 'px,0) scale(' + scale + ')';
				cursorEl.classList.toggle('is-hover', isHover);
				cursorEl.classList.toggle('is-clicking', isClicking);
				rafId = requestAnimationFrame(tick);
			}

			document.addEventListener('mousemove', onMouseMove, { passive: true });
			document.addEventListener('mouseover', onMouseOver, { passive: true });
			document.addEventListener('mouseout', onMouseOutHover, { passive: true });
			document.addEventListener('mouseout', onMouseOut, { passive: true });
			document.addEventListener('mousedown', onMouseDown, { passive: true });
			document.addEventListener('mouseup', onMouseUp, { passive: true });
			document.addEventListener('mouseleave', onMouseUp, { passive: true });
			rafId = requestAnimationFrame(tick);

			cleanupRef = function cleanup() {
				if (rafId != null) {
					cancelAnimationFrame(rafId);
					rafId = null;
				}
				document.removeEventListener('mousemove', onMouseMove);
				document.removeEventListener('mouseover', onMouseOver);
				document.removeEventListener('mouseout', onMouseOutHover);
				document.removeEventListener('mouseout', onMouseOut);
				document.removeEventListener('mousedown', onMouseDown);
				document.removeEventListener('mouseup', onMouseUp);
				document.removeEventListener('mouseleave', onMouseUp);
				if (cursorEl.parentNode) cursorEl.parentNode.removeChild(cursorEl);
				body.classList.remove('has-custom-cursor');
			};
		}

		setupCursor();
		/* Keep listeners so we can re-init when user toggles back to fine pointer / no reduced motion */
		prefersReducedMotion.addEventListener('change', setupCursor);
		prefersFinePointer.addEventListener('change', setupCursor);
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
