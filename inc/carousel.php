<?php
/**
 * Background Carousel Logic
 *
 * Handles carousel data retrieval, video autoplay detection, and mobile detection.
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output dynamic carousel keyframe styles in wp_head.
 * This is better for performance and caching than inline styles.
 */
function brace_yourself_carousel_styles() {
	// Only output if carousel has items
	$carousel_data = brace_yourself_get_carousel_data();
	$carousel_items = brace_yourself_get_carousel_items();
	
	if ( empty( $carousel_items ) ) {
		return;
	}
	
	$total_items = count( $carousel_items );
	
	// Calculate visibility percentages for dynamic keyframes
	// Handle single item case (no animation needed)
	if ( $total_items === 1 ) {
		$item_visibility_percent = 100;
		$fade_in_end_percent = 0;
		$fade_out_start_percent = 100;
		$fade_duration_percent = 0; // No fade needed for single item
		$wrap_around_fade_start = 0;
		$fade_out_end_percent = 100;
	} else {
		$item_visibility_percent = 100 / $total_items;
		// Faster fade duration (12% of cycle) for quicker transitions
		// Ensures images overlap during transition to prevent black background
		$fade_duration_percent = min( 12, $item_visibility_percent * 0.24 ); // 12% or 24% of item visibility
		$fade_in_end_percent = $fade_duration_percent;
		// Fade out starts before next item's visibility window to create overlap
		// This ensures next image is already fading in before current finishes fading out
		$fade_out_start_percent = max( $fade_in_end_percent, $item_visibility_percent - $fade_duration_percent );
		// Extend fade out to ensure overlap with next item
		$fade_out_end_percent = min( 100, $item_visibility_percent + $fade_duration_percent );
		// For smooth wrap-around: Item 1 needs to start fading in before cycle ends
		// The wrap-around ensures Item 1 is visible when the animation loops from 100% back to 0%
		// Start wrap-around fade-in early enough to ensure it's fully visible by 100%
		// This prevents any black background when transitioning from last item back to first
		// Use a slightly longer fade duration for wrap-around to guarantee smooth transition
		$wrap_around_fade_duration = $fade_duration_percent * 1.2; // 20% longer for extra safety
		$wrap_around_fade_start = max( $fade_out_end_percent, 100 - $wrap_around_fade_duration );
	}
	
	?>
	<style id="brace-yourself-carousel-styles">
	/* Keyframes for Item 1 (with wrap-around) */
	@keyframes carousel-fade-<?php echo esc_attr( $total_items ); ?>-first {
		/* Start visible (from wrap-around) */
		0% { opacity: 1; }
		<?php if ( $fade_in_end_percent > 0 ) : ?>
		<?php echo esc_attr( $fade_in_end_percent ); ?>% { opacity: 1; }
		<?php endif; ?>
		/* Stay fully visible during visibility window */
		<?php echo esc_attr( $fade_out_start_percent ); ?>% { opacity: 1; }
		/* Fade out - overlaps with next item's fade in */
		<?php echo esc_attr( $fade_out_end_percent ); ?>% { opacity: 0; }
		<?php if ( $total_items > 1 ) : ?>
		/* Wrap-around: start fading in before cycle ends to ensure seamless loop */
		/* Always include wrap-around start keyframe to ensure smooth transition */
		<?php if ( $wrap_around_fade_start < 100 ) : ?>
			<?php if ( $wrap_around_fade_start >= $fade_out_end_percent ) : ?>
			/* Wrap-around fade-in starts here - ensures smooth transition from fade-out */
			<?php echo esc_attr( $wrap_around_fade_start ); ?>% { opacity: 0; }
			<?php endif; ?>
		<?php endif; ?>
		/* Complete wrap-around fade-in by 100% so Item 1 is fully visible when animation loops */
		/* This ensures seamless transition from 100% back to 0% */
		100% { opacity: 1; }
		<?php endif; ?>
	}
	
	/* Keyframes for Items 2+ (no wrap-around, stay invisible after fade-out) */
	<?php if ( $total_items > 1 ) : ?>
	@keyframes carousel-fade-<?php echo esc_attr( $total_items ); ?>-other {
		/* Start invisible */
		0% { opacity: 0; }
		/* Fade in quickly */
		<?php if ( $fade_in_end_percent > 0 ) : ?>
		<?php echo esc_attr( $fade_in_end_percent ); ?>% { opacity: 1; }
		<?php endif; ?>
		/* Stay fully visible during visibility window */
		<?php echo esc_attr( $fade_out_start_percent ); ?>% { opacity: 1; }
		/* Fade out - overlaps with next item's fade in */
		<?php echo esc_attr( $fade_out_end_percent ); ?>% { opacity: 0; }
		/* Stay invisible for the rest of the cycle (no wrap-around) */
		100% { opacity: 0; }
	}
	<?php endif; ?>
	
	/* Apply first-item animation to the first visible item (with wrap-around) */
	.background-carousel[data-total-items="<?php echo esc_attr( $total_items ); ?>"] .background-carousel__item[data-first-visible="true"] {
		animation-name: carousel-fade-<?php echo esc_attr( $total_items ); ?>-first !important;
	}
	
	<?php if ( $total_items > 1 ) : ?>
	/* Apply other-item animation to all other items (no wrap-around) */
	.background-carousel[data-total-items="<?php echo esc_attr( $total_items ); ?>"] .background-carousel__item:not([data-first-visible="true"]) {
		animation-name: carousel-fade-<?php echo esc_attr( $total_items ); ?>-other !important;
	}
	<?php endif; ?>
	</style>
	<?php
}
add_action( 'wp_head', 'brace_yourself_carousel_styles', 5 );

/**
 * Get carousel data from ACF.
 * Works with both ACF Pro (options page) and ACF Free (settings page).
 *
 * @return array Carousel configuration array.
 */
function brace_yourself_get_carousel_data() {
	if ( ! brace_yourself_acf_active() ) {
		return array(
			'images'        => array(),
			'videos'        => array(),
			'slide_duration' => 7,
		);
	}

	// Try ACF Pro options page first
	$images = get_field( 'carousel_images', 'option' );
	$videos = get_field( 'carousel_videos', 'option' );
	$duration = get_field( 'carousel_slide_duration', 'option' );

	// If no data from options page, try settings page (ACF Free)
	if ( empty( $images ) && empty( $videos ) ) {
		$settings_page_id = brace_yourself_get_carousel_settings_page_id();
		if ( $settings_page_id ) {
			$duration = get_field( 'carousel_slide_duration', $settings_page_id );
			
			// Check if images is gallery (Pro) or separate image fields (Free)
			$images_raw = get_field( 'carousel_images', $settings_page_id );
			if ( $images_raw && is_array( $images_raw ) && isset( $images_raw[0]['url'] ) ) {
				// ACF Pro: Gallery format (array of images)
				$images = $images_raw;
			} else {
				// ACF Free: Collect images from separate fields
				$images = array();
				for ( $i = 1; $i <= 3; $i++ ) {
					$desktop_image = get_field( 'carousel_image_' . $i . '_desktop', $settings_page_id );
					if ( $desktop_image ) {
						$mobile_image = get_field( 'carousel_image_' . $i . '_mobile', $settings_page_id );
						$images[] = array(
							'desktop' => $desktop_image,
							'mobile'  => $mobile_image ? $mobile_image : null,
						);
					}
				}
			}
			
			// Check if ACF Pro (repeater) or Free (separate fields)
			$is_pro = get_field( 'carousel_videos', $settings_page_id );
			if ( $is_pro && is_array( $is_pro ) && isset( $is_pro[0]['video_desktop'] ) ) {
				// ACF Pro format
				$videos = $is_pro;
			} else {
				// ACF Free: Collect video from separate fields
				$videos = array();
				$desktop = get_field( 'video_1_desktop', $settings_page_id );
				if ( $desktop ) {
					$mobile = get_field( 'video_1_mobile', $settings_page_id );
					$videos[] = array(
						'video_desktop' => $desktop,
						'video_mobile'  => $mobile ? $mobile : null,
					);
				}
			}
		}
	} else {
		// Options page - check if images need conversion (Free format)
		if ( empty( $images ) || ( is_array( $images ) && ! isset( $images[0]['url'] ) && ! isset( $images[0]['sizes'] ) && ! isset( $images[0]['desktop'] ) ) ) {
			// ACF Free: Collect images from separate fields
			$converted_images = array();
			for ( $i = 1; $i <= 3; $i++ ) {
				$desktop_image = get_field( 'carousel_image_' . $i . '_desktop', 'option' );
				if ( $desktop_image ) {
					$mobile_image = get_field( 'carousel_image_' . $i . '_mobile', 'option' );
					$converted_images[] = array(
						'desktop' => $desktop_image,
						'mobile'  => $mobile_image ? $mobile_image : null,
					);
				}
			}
			if ( ! empty( $converted_images ) ) {
				$images = $converted_images;
			}
		}
		
		// Check if videos need conversion (Free format)
		if ( empty( $videos ) || ( is_array( $videos ) && ! isset( $videos[0]['video_desktop'] ) ) ) {
			// Options page but might be ACF Free format - check for separate fields
			$desktop = get_field( 'video_1_desktop', 'option' );
			if ( $desktop ) {
				$mobile = get_field( 'video_1_mobile', 'option' );
				$videos = array(
					array(
						'video_desktop' => $desktop,
						'video_mobile'  => $mobile ? $mobile : null,
					),
				);
			}
		}
	}

	// Ensure we have arrays
	if ( ! is_array( $images ) ) {
		$images = array();
	}
	if ( ! is_array( $videos ) ) {
		$videos = array();
	}
	if ( ! is_numeric( $duration ) || $duration < 3 ) {
		$duration = 7;
	}

	return array(
		'images'        => $images,
		'videos'        => $videos,
		'slide_duration' => absint( $duration ),
	);
}

/**
 * Check if device is mobile.
 *
 * @return bool True if mobile device, false if desktop/tablet.
 */
function brace_yourself_is_mobile() {
	if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
		return false;
	}

	$user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
	
	// Mobile device indicators
	$mobile_agents = array( 
		'Mobile',
		'Android', 
		'iPhone', 
		'iPad', 
		'iPod', 
		'BlackBerry', 
		'Windows Phone',
		'Opera Mini',
		'IEMobile'
	);

	foreach ( $mobile_agents as $agent ) {
		if ( stripos( $user_agent, $agent ) !== false ) {
			return true;
		}
	}

	return false;
}

/**
 * Get appropriate video URL for current device.
 *
 * @param array $video_data Video data from ACF.
 * @return string|false Video URL or false if not available.
 */
function brace_yourself_get_video_url( $video_data ) {
	if ( ! is_array( $video_data ) ) {
		return false;
	}

	$is_mobile = brace_yourself_is_mobile();
	$mobile_video = isset( $video_data['video_mobile'] ) ? $video_data['video_mobile'] : null;
	$desktop_video = isset( $video_data['video_desktop'] ) ? $video_data['video_desktop'] : null;

	// Prefer mobile video on mobile if available, otherwise desktop
	if ( $is_mobile && $mobile_video && isset( $mobile_video['url'] ) ) {
		return esc_url( $mobile_video['url'] );
	}

	if ( $desktop_video && isset( $desktop_video['url'] ) ) {
		return esc_url( $desktop_video['url'] );
	}

	return false;
}

/**
 * Get carousel items (images and videos) with proper formatting.
 *
 * @return array Formatted carousel items.
 */
function brace_yourself_get_carousel_items() {
	$data = brace_yourself_get_carousel_data();
	$items = array();

	// Check if videos is in repeater format (has video_desktop key)
	$is_repeater_format = ! empty( $data['videos'] ) && is_array( $data['videos'] ) && isset( $data['videos'][0]['video_desktop'] );

	// Add videos first (they take priority if autoplay works)
	if ( $is_repeater_format ) {
		foreach ( $data['videos'] as $index => $video_data ) {
			$video_url = brace_yourself_get_video_url( $video_data );
			if ( $video_url ) {
				$items[] = array(
					'type'  => 'video',
					'url'   => $video_url,
					'index' => $index,
				);
			}
		}
	}

	// Add images as fallback
	foreach ( $data['images'] as $index => $image ) {
		// Check if image has desktop/mobile structure (ACF Free format)
		if ( is_array( $image ) && isset( $image['desktop'] ) ) {
			// ACF Free: Desktop/mobile image structure
			$desktop_img = $image['desktop'];
			$mobile_img = isset( $image['mobile'] ) && ! empty( $image['mobile'] ) ? $image['mobile'] : null;
			
			// Get appropriate image for current device
			$is_mobile = brace_yourself_is_mobile();
			if ( $is_mobile && $mobile_img ) {
				$selected_image = $mobile_img;
			} else {
				$selected_image = $desktop_img;
			}
			
			if ( is_array( $selected_image ) && isset( $selected_image['url'] ) ) {
				// Get attachment ID to generate proper srcset
				$attachment_id = isset( $selected_image['ID'] ) ? absint( $selected_image['ID'] ) : 0;
				$srcset = '';
				if ( $attachment_id ) {
					$srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' );
				}
				
				$items[] = array(
					'type'   => 'image',
					'url'    => esc_url( $selected_image['url'] ),
					'srcset' => $srcset ? $srcset : '',
					'alt'    => '',
					'index'  => count( $data['videos'] ) + $index,
				);
			} elseif ( is_numeric( $selected_image ) ) {
				// Handle case where image is just an ID
				$image_array = wp_get_attachment_image_src( $selected_image, 'full' );
				if ( $image_array ) {
					$items[] = array(
						'type'   => 'image',
						'url'    => esc_url( $image_array[0] ),
						'srcset' => wp_get_attachment_image_srcset( $selected_image, 'full' ),
						'alt'    => '',
						'index'  => count( $data['videos'] ) + $index,
					);
				}
			}
		} elseif ( is_array( $image ) && isset( $image['url'] ) ) {
			// ACF Pro: Gallery format (direct image array)
			$attachment_id = isset( $image['ID'] ) ? absint( $image['ID'] ) : 0;
			$srcset = '';
			if ( $attachment_id ) {
				$srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' );
			}
			
			$items[] = array(
				'type'   => 'image',
				'url'    => esc_url( $image['url'] ),
				'srcset' => $srcset ? $srcset : '',
				'alt'    => '',
				'index'  => count( $data['videos'] ) + $index,
			);
		} elseif ( is_numeric( $image ) ) {
			// Handle case where image is just an ID
			$image_array = wp_get_attachment_image_src( $image, 'full' );
			if ( $image_array ) {
				$items[] = array(
					'type'   => 'image',
					'url'    => esc_url( $image_array[0] ),
					'srcset' => wp_get_attachment_image_srcset( $image, 'full' ),
					'alt'    => '',
					'index'  => count( $data['videos'] ) + $index,
				);
			}
		}
	}

	return $items;
}
