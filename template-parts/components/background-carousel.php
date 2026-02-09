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

// Determine if homepage (supports both static front page and posts index).
// This ensures the carousel is unblurred on the main landing view.
$is_homepage = is_front_page() || is_home();

// Get slide duration (convert to milliseconds for CSS)
$slide_duration = $carousel_data['slide_duration'] * 1000;

// Calculate total items
$total_items = count( $carousel_items );

// Randomize starting position for variety across page loads.
// A negative animation-delay fast-forwards all items to the same random point in the cycle.
// animation-fill-mode: both ensures the correct visual state renders on the very first frame.
$random_offset = ( $total_items > 1 ) ? wp_rand( 0, $total_items - 1 ) * $slide_duration : 0;
?>
<div 
	class="background-carousel <?php echo $is_homepage ? 'background-carousel--homepage' : 'background-carousel--inner'; ?>"
	data-slide-duration="<?php echo esc_attr( $slide_duration ); ?>"
	data-total-items="<?php echo esc_attr( $total_items ); ?>"
	style="--slide-duration: <?php echo esc_attr( $slide_duration ); ?>ms; --total-duration: <?php echo esc_attr( $total_items * $slide_duration ); ?>ms; --carousel-offset: -<?php echo esc_attr( $random_offset ); ?>ms;"
	aria-hidden="true"
>
	<div class="background-carousel__container">
		<?php foreach ( $carousel_items as $index => $item ) : ?>
			<?php
			// Each item gets carousel-pos-{index} — keyframes handle all timing
			$animation_name = 'carousel-pos-' . $index;
			$is_first       = ( 0 === $index );
			?>
			
			<?php if ( 'video' === $item['type'] ) : ?>
				<video
					class="background-carousel__item background-carousel__video"
					<?php if ( $is_first ) : ?>
						autoplay
					<?php endif; ?>
					loop
					muted
					playsinline
					preload="<?php echo $is_first ? 'auto' : 'none'; ?>"
					data-video-index="<?php echo esc_attr( $index ); ?>"
					data-video-src="<?php echo esc_url( $item['url'] ); ?>"
					style="animation-name: <?php echo esc_attr( $animation_name ); ?>;"
				>
					<?php if ( $is_first ) : ?>
						<?php
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
					src="<?php echo esc_url( $item['url'] ); ?>"
					<?php if ( ! empty( $item['srcset'] ) ) : ?>
						srcset="<?php echo esc_attr( $item['srcset'] ); ?>"
						sizes="100vw"
					<?php endif; ?>
					alt=""
					loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
					decoding="<?php echo $is_first ? 'sync' : 'async'; ?>"
					<?php if ( $is_first ) : ?>
						fetchpriority="high"
					<?php endif; ?>
					data-image-index="<?php echo esc_attr( $index ); ?>"
					style="animation-name: <?php echo esc_attr( $animation_name ); ?>;"
				/>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	
	<!-- Page transition overlay -->
	<div class="background-carousel__overlay" aria-hidden="true"></div>
</div>
