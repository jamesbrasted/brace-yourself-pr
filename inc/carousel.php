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
 * 
 * Note: With randomization, this preloads the first DOM item, which may not
 * be the first visible item. However, since all images load eagerly via src
 * attributes, this is a minor optimization and doesn't affect functionality.
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

	// Preload first DOM item (all images load eagerly anyway, this is just a hint)
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
	// Crossfade duration as % of cycle.
	// Even shorter for a very quick transition, while keeping a small overlap.
	$fade = min( 3, $ivp / 15 );

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

/** Transient key for carousel config. Invalidated when carousel options/settings are saved. */
define( 'BRACE_YOURSELF_CAROUSEL_TRANSIENT', 'brace_yourself_carousel_data' );

/** Transient TTL: 1 hour. */
define( 'BRACE_YOURSELF_CAROUSEL_TRANSIENT_TTL', HOUR_IN_SECONDS );

/**
 * Get carousel data from ACF (Free: Carousel Settings page only).
 * Cached per request (static) and across requests (transient) to avoid repeated get_field() calls.
 *
 * @return array Carousel configuration array.
 */
function brace_yourself_get_carousel_data() {
	static $cached = null;
	if ( $cached !== null ) {
		return $cached;
	}

	if ( ! brace_yourself_acf_active() ) {
		$cached = array(
			'images'        => array(),
			'videos'        => array(),
			'slide_duration' => 7,
		);
		return $cached;
	}

	// Use transient when available (avoids DB/meta work for anonymous traffic).
	$stored = get_transient( BRACE_YOURSELF_CAROUSEL_TRANSIENT );
	if ( is_array( $stored ) && isset( $stored['images'], $stored['videos'], $stored['slide_duration'] ) ) {
		$cached = $stored;
		return $cached;
	}

	// ACF Free: get data from Carousel Settings page only
	$images = array();
	$videos = array();
	$settings_page_id = brace_yourself_get_carousel_settings_page_id();

	if ( $settings_page_id ) {
		for ( $i = 1; $i <= 4; $i++ ) {
			$desktop_image = get_field( 'carousel_image_' . $i . '_desktop', $settings_page_id );
			if ( $desktop_image ) {
				$mobile_image = get_field( 'carousel_image_' . $i . '_mobile', $settings_page_id );
				$images[] = array(
					'desktop' => $desktop_image,
					'mobile'  => $mobile_image ? $mobile_image : null,
				);
			}
		}
		foreach ( array( 1, 2 ) as $n ) {
			$desktop = get_field( 'video_' . $n . '_desktop', $settings_page_id );
			if ( $desktop ) {
				$mobile = get_field( 'video_' . $n . '_mobile', $settings_page_id );
				$videos[] = array(
					'video_desktop' => $desktop,
					'video_mobile'  => $mobile ? $mobile : null,
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
	// Force a fixed duration of 7 seconds for stability.
	// This prevents extremely long cycles that would slow transitions.
	$duration = 7;

	$cached = array(
		'images'        => $images,
		'videos'        => $videos,
		'slide_duration' => absint( $duration ),
	);
	set_transient( BRACE_YOURSELF_CAROUSEL_TRANSIENT, $cached, BRACE_YOURSELF_CAROUSEL_TRANSIENT_TTL );
	return $cached;
}

/**
 * Invalidate carousel transient when Carousel Settings page is saved.
 *
 * @param int|string $post_id Post ID.
 */
function brace_yourself_invalidate_carousel_transient( $post_id ) {
	$settings_page_id = brace_yourself_get_carousel_settings_page_id();
	if ( $settings_page_id && (int) $post_id === (int) $settings_page_id ) {
		delete_transient( BRACE_YOURSELF_CAROUSEL_TRANSIENT );
	}
}
add_action( 'acf/save_post', 'brace_yourself_invalidate_carousel_transient', 20 );

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
 * Cached per request so preload, styles, and template share one computation.
 *
 * @return array Formatted carousel items.
 */
function brace_yourself_get_carousel_items() {
	static $cached_items = null;
	if ( $cached_items !== null ) {
		return $cached_items;
	}

	$data  = brace_yourself_get_carousel_data();
	$items = array();

	// Add videos first (they take priority if autoplay works). ACF Free: array of { video_desktop, video_mobile }.
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

	// Add images (ACF Free: each item is { desktop, mobile }).
	foreach ( $data['images'] as $index => $image ) {
		if ( ! is_array( $image ) || ! isset( $image['desktop'] ) ) {
			continue;
		}
		$desktop_img = $image['desktop'];
		$mobile_img  = isset( $image['mobile'] ) && ! empty( $image['mobile'] ) ? $image['mobile'] : null;
		$is_mobile   = brace_yourself_is_mobile();
		$selected_image = ( $is_mobile && $mobile_img ) ? $mobile_img : $desktop_img;

		if ( is_array( $selected_image ) && isset( $selected_image['url'] ) ) {
			$attachment_id = isset( $selected_image['ID'] ) ? absint( $selected_image['ID'] ) : 0;
			$srcset        = $attachment_id ? wp_get_attachment_image_srcset( $attachment_id, 'full' ) : '';
			$items[] = array(
				'type'   => 'image',
				'url'    => esc_url( $selected_image['url'] ),
				'srcset' => $srcset ? $srcset : '',
				'alt'    => '',
				'index'  => count( $data['videos'] ) + $index,
			);
		} elseif ( is_numeric( $selected_image ) ) {
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
	}

	$cached_items = $items;
	return $cached_items;
}
