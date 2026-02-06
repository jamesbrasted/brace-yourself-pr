<?php
/**
 * Component: Background Carousel
 *
 * Global background carousel for all pages. Displays images and videos
 * with CSS animations. Intensely blurred on inner pages, fully visible on homepage.
 *
 * ACF Fields (Options Page):
 * - carousel_images (gallery) - Fallback images if video autoplay fails
 * - carousel_videos (repeater) - Video items with desktop/mobile variants
 *   - video_desktop (file) - Desktop video file
 *   - video_mobile (file, optional) - Mobile video file
 * - carousel_slide_duration (number) - Slide duration in seconds (default: 7)
 *
 * @package Brace_Yourself
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get carousel data
$carousel_data = brace_yourself_get_carousel_data();
$carousel_items = brace_yourself_get_carousel_items();

// Don't render if no items
if ( empty( $carousel_items ) ) {
	return;
}

// Determine if homepage
$is_homepage = is_front_page();

// Get slide duration (convert to milliseconds for CSS)
$slide_duration = $carousel_data['slide_duration'] * 1000;

// Calculate total items
$total_items = count( $carousel_items );

// Randomize starting item for continuity across pages
$carousel_start_cookie = isset( $_COOKIE['carousel_start'] ) ? absint( $_COOKIE['carousel_start'] ) : null;
$start_index = ( $carousel_start_cookie !== null && $carousel_start_cookie < $total_items ) ? $carousel_start_cookie : wp_rand( 0, max( 0, $total_items - 1 ) );
?>
<div 
	class="background-carousel <?php echo $is_homepage ? 'background-carousel--homepage' : 'background-carousel--inner'; ?>"
	data-slide-duration="<?php echo esc_attr( $slide_duration ); ?>"
	data-total-items="<?php echo esc_attr( $total_items ); ?>"
	data-start-index="<?php echo esc_attr( $start_index ); ?>"
	style="--slide-duration: <?php echo esc_attr( $slide_duration ); ?>ms; --total-duration: <?php echo esc_attr( $total_items * $slide_duration ); ?>ms;"
	aria-hidden="true"
>
	<div class="background-carousel__container">
		<?php foreach ( $carousel_items as $index => $item ) : ?>
			<?php
			// Calculate animation delay so items appear sequentially with overlap
			// Each item starts BEFORE the previous one finishes to ensure continuous coverage
			$adjusted_index = ( $index + $start_index ) % $total_items;
			// Subtract overlap amount (12% of slide duration) so next image starts fading in
			// while current image is still fading out, creating smooth crossfade
			// This matches the fade duration percentage in the keyframes
			$overlap_ms = $slide_duration * 0.12; // 12% overlap matches fade duration
			$animation_delay = max( 0, ( $adjusted_index * $slide_duration ) - $overlap_ms );
			// First visible item should start visible (opacity 1)
			$is_first_visible = ( $adjusted_index === 0 );
			?>
			
			<?php if ( 'video' === $item['type'] ) : ?>
				<video
					class="background-carousel__item background-carousel__video"
					<?php if ( $is_first_visible ) : ?>
						data-first-visible="true"
					<?php endif; ?>
					<?php if ( $index === 0 ) : ?>
						autoplay
					<?php endif; ?>
					loop
					muted
					playsinline
					preload="<?php echo $index === 0 ? 'auto' : 'none'; ?>"
					data-video-index="<?php echo esc_attr( $index ); ?>"
					data-video-src="<?php echo esc_url( $item['url'] ); ?>"
					style="--animation-delay: <?php echo esc_attr( $animation_delay ); ?>ms;"
				>
					<?php if ( $index === 0 ) : ?>
						<?php
						// Detect video format for proper MIME type
						$video_url = esc_url( $item['url'] );
						$extension = strtolower( pathinfo( $video_url, PATHINFO_EXTENSION ) );
						$mime_type = ( 'webm' === $extension ) ? 'video/webm' : 'video/mp4';
						?>
						<source src="<?php echo $video_url; ?>" type="<?php echo esc_attr( $mime_type ); ?>">
					<?php endif; ?>
					<?php esc_html_e( 'Your browser does not support the video tag.', 'brace-yourself' ); ?>
				</video>
			<?php else : ?>
				<img
					class="background-carousel__item background-carousel__image"
					<?php if ( $is_first_visible ) : ?>
						data-first-visible="true"
					<?php endif; ?>
					<?php if ( $index === 0 ) : ?>
						src="<?php echo esc_url( $item['url'] ); ?>"
						<?php if ( ! empty( $item['srcset'] ) ) : ?>
							srcset="<?php echo esc_attr( $item['srcset'] ); ?>"
							sizes="100vw"
						<?php endif; ?>
						fetchpriority="high"
					<?php else : ?>
						data-src="<?php echo esc_url( $item['url'] ); ?>"
						<?php if ( ! empty( $item['srcset'] ) ) : ?>
							data-srcset="<?php echo esc_attr( $item['srcset'] ); ?>"
							data-sizes="100vw"
						<?php endif; ?>
					<?php endif; ?>
					alt="<?php echo esc_attr( $item['alt'] ); ?>"
					loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
					decoding="async"
					data-image-index="<?php echo esc_attr( $index ); ?>"
					style="--animation-delay: <?php echo esc_attr( $animation_delay ); ?>ms;<?php echo $is_first_visible ? ' opacity: 1;' : ''; ?>"
					onerror="this.setAttribute('data-error', 'true'); this.style.display='none'; this.style.opacity='0';"
					onload="this.setAttribute('data-loaded', 'true');"
				/>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	
	<!-- Page transition overlay -->
	<div class="background-carousel__overlay" aria-hidden="true"></div>
</div>
