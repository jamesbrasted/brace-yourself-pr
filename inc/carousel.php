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
 * Preload the first carousel image to prevent background flash.
 * Runs early in wp_head to start downloading immediately.
 */
function brace_yourself_preload_carousel_image() {
	// Only on frontend, not admin
	if ( is_admin() ) {
		return;
	}

	$carousel_data = brace_yourself_get_carousel_data();
	$carousel_items = brace_yourself_get_carousel_items();
	
	if ( empty( $carousel_items ) ) {
		return;
	}

	// Get the first visible item (could be randomized)
	$first_item = $carousel_items[0];
	
	// Only preload if it's an image (videos handle their own preloading)
	if ( isset( $first_item['type'] ) && 'image' === $first_item['type'] && ! empty( $first_item['url'] ) ) {
		$image_url = esc_url( $first_item['url'] );
		$srcset = ! empty( $first_item['srcset'] ) ? esc_attr( $first_item['srcset'] ) : '';
		
		// Preload the image with highest priority
		echo '<link rel="preload" as="image" href="' . $image_url . '"';
		if ( $srcset ) {
			echo ' imagesrcset="' . $srcset . '" imagesizes="100vw"';
		}
		echo ' fetchpriority="high">' . "\n";
	}
}
add_action( 'wp_head', 'brace_yourself_preload_carousel_image', 1 );

/**
 * Output dynamic carousel keyframe styles in wp_head.
 *
 * Uses "fade on top" approach with z-index control:
 * - During each crossfade, the INCOMING image fades in ON TOP (z-index: 10)
 *   while the OUTGOING image stays at full opacity underneath (z-index: 0).
 * - This guarantees ZERO background bleed-through because the outgoing image
 *   is always fully opaque beneath the partially-transparent incoming image.
 * - CSS alpha compositing: incoming_alpha * img_in + (1 - incoming_alpha) * img_out
 *   This is a perfect blend of two images with NO background contribution.
 *
 * Previous approach (simultaneous crossfade) was fundamentally flawed:
 * - Two overlapping elements at 50% opacity each = 75% opaque (25% background bleed)
 * - CSS alpha compositing is multiplicative, NOT additive
 */
function brace_yourself_carousel_styles() {
	// Only output if carousel has items
	$carousel_data = brace_yourself_get_carousel_data();
	$carousel_items = brace_yourself_get_carousel_items();

	if ( empty( $carousel_items ) ) {
		return;
	}

	$total_items = count( $carousel_items );

	// Single item: no animation needed (handled by CSS rule in main.css)
	if ( $total_items <= 1 ) {
		return;
	}

	$ivp  = 100 / $total_items; // Item visibility percent of total cycle
	$fade = min( 15, $ivp / 3 ); // Crossfade duration as % of cycle

	// Format helper: clamp to 0-100 and format with 2 decimal places
	$fmt = function( $val ) {
		$val = max( 0, min( 100, $val ) );
		return number_format( $val, 2, '.', '' );
	};

	?>
	<style id="brace-yourself-carousel-styles">
	<?php for ( $pos = 0; $pos < $total_items; $pos++ ) : ?>
	<?php
		$vis_start = $pos * $ivp;
		$vis_end   = ( $pos + 1 ) * $ivp;
	?>
	@keyframes carousel-pos-<?php echo esc_attr( $pos ); ?> {
	<?php if ( $pos === 0 ) : ?>
		<?php
		// Position 0: visible at cycle start. Fade-in wraps around end of cycle.
		// Timeline: [visible 0→vis_end] [hidden vis_end→(100-fade)] [fade-in (100-fade)→100]
		$fi_start = 100 - $fade;
		$drop_at  = $vis_end;
		?>
		/* Pos 0: visible at start, fades back in at cycle end (wrap-around) */
		0% { opacity: 1; z-index: 10; }
		<?php echo $fmt( 1 ); ?>% { opacity: 1; z-index: 0; }
		<?php echo $fmt( $drop_at ); ?>% { opacity: 1; z-index: 0; }
		<?php echo $fmt( $drop_at + 0.1 ); ?>% { opacity: 0; z-index: 0; }
		<?php echo $fmt( $fi_start - 1 ); ?>% { opacity: 0; z-index: 0; }
		<?php echo $fmt( $fi_start ); ?>% { opacity: 0; z-index: 10; }
		100% { opacity: 1; z-index: 10; }
	<?php elseif ( $pos === $total_items - 1 ) : ?>
		<?php
		// Last position: stays visible through cycle end. Drop happens at iteration boundary
		// (100% opacity=1 → 0% opacity=0 jump, invisible because pos 0 covers it at z:10).
		$fi_start = $vis_start - $fade;
		$fi_end   = $vis_start;
		?>
		/* Pos <?php echo $pos; ?>: last item, visible until cycle end, drops at loop boundary */
		0% { opacity: 0; z-index: 0; }
		<?php echo $fmt( $fi_start - 1 ); ?>% { opacity: 0; z-index: 0; }
		<?php echo $fmt( $fi_start ); ?>% { opacity: 0; z-index: 10; }
		<?php echo $fmt( $fi_end ); ?>% { opacity: 1; z-index: 10; }
		<?php echo $fmt( $fi_end + 1 ); ?>% { opacity: 1; z-index: 0; }
		100% { opacity: 1; z-index: 0; }
	<?php else : ?>
		<?php
		// Middle position: standard fade-in, stay visible, drop when next item covers.
		$fi_start = $vis_start - $fade;
		$fi_end   = $vis_start;
		$drop_at  = $vis_end;
		?>
		/* Pos <?php echo $pos; ?>: middle item */
		0% { opacity: 0; z-index: 0; }
		<?php echo $fmt( $fi_start - 1 ); ?>% { opacity: 0; z-index: 0; }
		<?php echo $fmt( $fi_start ); ?>% { opacity: 0; z-index: 10; }
		<?php echo $fmt( $fi_end ); ?>% { opacity: 1; z-index: 10; }
		<?php echo $fmt( $fi_end + 1 ); ?>% { opacity: 1; z-index: 0; }
		<?php echo $fmt( $drop_at ); ?>% { opacity: 1; z-index: 0; }
		<?php echo $fmt( $drop_at + 0.1 ); ?>% { opacity: 0; z-index: 0; }
		100% { opacity: 0; z-index: 0; }
	<?php endif; ?>
	}
	<?php endfor; ?>
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
	// Validate and sanitize duration (minimum 3 seconds, maximum 30 seconds)
	if ( ! is_numeric( $duration ) || $duration < 3 || $duration > 30 ) {
		$duration = 7;
	}
	$duration = absint( $duration );

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
