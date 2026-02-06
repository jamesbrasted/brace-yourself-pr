<?php
/**
 * Advanced Custom Fields Configuration
 *
 * Registers ACF field groups and provides helper functions.
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if ACF is active.
 *
 * @return bool True if ACF is active.
 */
function brace_yourself_acf_active() {
	return function_exists( 'acf_add_local_field_group' );
}

/**
 * Get carousel settings page ID.
 * Creates the page if it doesn't exist (for ACF Free compatibility).
 *
 * @return int|false Page ID or false on failure.
 */
function brace_yourself_get_carousel_settings_page_id() {
	// Check if page already exists
	$page = get_page_by_path( 'carousel-settings', OBJECT, 'page' );
	
	if ( $page ) {
		return $page->ID;
	}

	// Create the page if it doesn't exist
	$page_data = array(
		'post_title'   => __( 'Carousel Settings', 'brace-yourself' ),
		'post_name'    => 'carousel-settings',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => __( 'This page is used to configure the background carousel. Edit the fields below to manage carousel images and videos.', 'brace-yourself' ),
	);

	$page_id = wp_insert_post( $page_data );
	
	return $page_id ? $page_id : false;
}

/**
 * Get carousel fields based on ACF version (Pro vs Free).
 *
 * @return array Array of field definitions.
 */
function brace_yourself_get_carousel_fields() {
	$fields = array();

	// Check if ACF Pro is available (repeater and gallery fields exist)
	$is_acf_pro = function_exists( 'acf_get_field_type' ) && acf_get_field_type( 'repeater' );
	$has_gallery = function_exists( 'acf_get_field_type' ) && acf_get_field_type( 'gallery' );

	// Carousel Images - Gallery for Pro, multiple Image fields for Free
	if ( $has_gallery ) {
		// ACF Pro: Gallery field
		$fields[] = array(
			'key'               => 'field_carousel_images',
			'label'             => 'Carousel Images',
			'name'              => 'carousel_images',
			'type'              => 'gallery',
			'instructions'      => 'Click "Add to gallery" to upload or select images. These will be used as fallback if videos cannot autoplay.',
			'return_format'     => 'array',
			'preview_size'      => 'medium',
			'insert'            => 'append',
			'library'           => 'all',
			'min'               => 0,
			'max'               => 0,
		);
	} else {
		// ACF Free: 3 images, each with desktop and mobile variants
		for ( $i = 1; $i <= 3; $i++ ) {
			// Desktop image field
			$fields[] = array(
				'key'               => 'field_carousel_image_' . $i . '_desktop',
				'label'             => 'Image ' . $i . ' - Desktop',
				'name'              => 'carousel_image_' . $i . '_desktop',
				'type'              => 'image',
				'instructions'      => $i === 1 ? 'Desktop image for the carousel. This will be used as fallback if videos cannot autoplay.' : 'Optional: Additional desktop image.',
				'return_format'     => 'array',
				'preview_size'      => 'medium',
				'library'           => 'all',
				'required'          => $i === 1 ? 1 : 0,
			);
			
			// Mobile image field
			$fields[] = array(
				'key'               => 'field_carousel_image_' . $i . '_mobile',
				'label'             => 'Image ' . $i . ' - Mobile (Optional)',
				'name'              => 'carousel_image_' . $i . '_mobile',
				'type'              => 'image',
				'instructions'      => 'Optional: Smaller mobile image. If not provided, desktop image will be used on mobile.',
				'return_format'     => 'array',
				'preview_size'      => 'medium',
				'library'           => 'all',
				'required'          => 0,
			);
		}
	}

	if ( $is_acf_pro ) {
		// ACF Pro: Repeater field with desktop/mobile variants
		$fields[] = array(
			'key'               => 'field_carousel_videos',
			'label'             => 'Carousel Videos',
			'name'              => 'carousel_videos',
			'type'              => 'repeater',
			'instructions'      => 'Add videos for the background carousel. Videos will autoplay, loop, and mute. If autoplay fails, images will be used.',
			'layout'            => 'block',
			'button_label'      => 'Add Video',
			'sub_fields'        => array(
				array(
					'key'               => 'field_video_desktop',
					'label'             => 'Desktop Video',
					'name'              => 'video_desktop',
					'type'              => 'file',
					'instructions'      => 'Video file for desktop/tablet devices (MP4 recommended)',
					'return_format'     => 'array',
					'library'           => 'all',
					'min_size'          => '',
					'max_size'          => '',
					'mime_types'        => 'mp4,webm',
					'required'          => 1,
				),
				array(
					'key'               => 'field_video_mobile',
					'label'             => 'Mobile Video (Optional)',
					'name'              => 'video_mobile',
					'type'              => 'file',
					'instructions'      => 'Optional: Smaller video file for mobile devices. If not provided, desktop video will be used.',
					'return_format'     => 'array',
					'library'           => 'all',
					'min_size'          => '',
					'max_size'          => '',
					'mime_types'        => 'mp4,webm',
					'required'          => 0,
				),
			),
		);
	} else {
		// ACF Free: Single video with desktop and mobile variants
		// Desktop video field
		$fields[] = array(
			'key'               => 'field_video_1_desktop',
			'label'             => 'Video 1 - Desktop',
			'name'              => 'video_1_desktop',
			'type'              => 'file',
			'instructions'      => 'Desktop video file (MP4 recommended). Leave empty if not using videos.',
			'return_format'     => 'array',
			'library'           => 'all',
			'mime_types'        => 'mp4,webm',
			'required'          => 0,
		);
		
		// Mobile video field
		$fields[] = array(
			'key'               => 'field_video_1_mobile',
			'label'             => 'Video 1 - Mobile (Optional)',
			'name'              => 'video_1_mobile',
			'type'              => 'file',
			'instructions'      => 'Optional: Smaller mobile video. If not provided, desktop video will be used on mobile.',
			'return_format'     => 'array',
			'library'           => 'all',
			'mime_types'        => 'mp4,webm',
			'required'          => 0,
		);
	}

	// Slide duration (available in both)
	$fields[] = array(
		'key'               => 'field_carousel_slide_duration',
		'label'             => 'Slide Duration (seconds)',
		'name'              => 'carousel_slide_duration',
		'type'              => 'number',
		'instructions'      => 'How long each slide displays (default: 7 seconds)',
		'default_value'     => 7,
		'min'               => 3,
		'max'               => 15,
		'step'              => 1,
	);

	return $fields;
}

/**
 * Register ACF field groups.
 *
 * All field groups should be registered here using acf_add_local_field_group().
 * This keeps field definitions version-controlled and avoids database dependencies.
 */
function brace_yourself_register_acf_field_groups() {
	if ( ! brace_yourself_acf_active() ) {
		return;
	}

	// Get carousel settings page ID for exclusion
	$carousel_page_id = brace_yourself_get_carousel_settings_page_id();

	// Example: Hero Section Field Group
	acf_add_local_field_group(
		array(
			'key'                   => 'group_hero_section',
			'title'                 => 'Hero Section',
			'fields'                => array(
				array(
					'key'               => 'field_hero_heading',
					'label'             => 'Heading',
					'name'              => 'heading',
					'type'              => 'text',
					'required'          => 1,
					'placeholder'       => 'Enter hero heading',
				),
				array(
					'key'               => 'field_hero_subheading',
					'label'             => 'Subheading',
					'name'              => 'subheading',
					'type'              => 'textarea',
					'rows'              => 3,
				),
				array(
					'key'               => 'field_hero_image',
					'label'             => 'Hero Image',
					'name'              => 'image',
					'type'              => 'image',
					'return_format'     => 'array',
					'preview_size'      => 'medium',
					'library'          => 'all',
				),
				array(
					'key'               => 'field_hero_cta_text',
					'label'             => 'CTA Button Text',
					'name'              => 'cta_text',
					'type'              => 'text',
				),
				array(
					'key'               => 'field_hero_cta_link',
					'label'             => 'CTA Button Link',
					'name'              => 'cta_link',
					'type'              => 'link',
				),
			),
			'location'              => $carousel_page_id ? array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
					array(
						'param'    => 'page',
						'operator' => '!=',
						'value'    => $carousel_page_id,
					),
				),
			) : array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
		)
	);

	// Background Carousel Field Group
	// Location: Options page (ACF Pro) OR Carousel Settings page only (ACF Free)
	$carousel_location = array();
	
	// Add options page location (ACF Pro)
	if ( function_exists( 'acf_add_options_page' ) ) {
		$carousel_location[] = array(
			array(
				'param'    => 'options_page',
				'operator' => '==',
				'value'    => 'theme-options',
			),
		);
	}
	
	// Add Carousel Settings page location (ACF Free - only on settings page)
	$carousel_page_id = brace_yourself_get_carousel_settings_page_id();
	if ( $carousel_page_id ) {
		$carousel_location[] = array(
			array(
				'param'    => 'page',
				'operator' => '==',
				'value'    => $carousel_page_id,
			),
		);
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_background_carousel',
			'title'                 => 'Background Carousel',
			'fields'                => brace_yourself_get_carousel_fields(),
			'location'              => $carousel_location,
			'active'                => 1,
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
		)
	);

	// Add more field groups here as needed
}
add_action( 'acf/init', 'brace_yourself_register_acf_field_groups' );

/**
 * Hide Hero fields on Carousel Settings page.
 * Uses ACF filter to remove field group when editing carousel settings page.
 */
function brace_yourself_hide_hero_on_carousel_settings( $field_groups, $options = array() ) {
	if ( ! is_admin() ) {
		return $field_groups;
	}

	// Check if we're editing a page
	$post_id = 0;
	if ( isset( $options['post_id'] ) ) {
		$post_id = absint( $options['post_id'] );
	} elseif ( isset( $_GET['post'] ) ) {
		$post_id = absint( $_GET['post'] );
	} elseif ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] ) {
		$post_id = $GLOBALS['post']->ID;
	}

	if ( ! $post_id ) {
		return $field_groups;
	}

	// Check if this is the carousel settings page (by ID or slug)
	$carousel_page = get_page_by_path( 'carousel-settings', OBJECT, 'page' );
	$is_carousel_page = false;
	
	if ( $carousel_page && $post_id === $carousel_page->ID ) {
		$is_carousel_page = true;
	} else {
		// Also check by slug from post object
		$post_obj = get_post( $post_id );
		if ( $post_obj && 'carousel-settings' === $post_obj->post_name ) {
			$is_carousel_page = true;
		}
	}

	if ( ! $is_carousel_page ) {
		return $field_groups;
	}

	// Remove Hero Section field group
	if ( is_array( $field_groups ) ) {
		$field_groups = array_filter( $field_groups, function( $field_group ) {
			return ! isset( $field_group['key'] ) || 'group_hero_section' !== $field_group['key'];
		} );
		// Re-index array
		$field_groups = array_values( $field_groups );
	}

	return $field_groups;
}
add_filter( 'acf/get_field_groups', 'brace_yourself_hide_hero_on_carousel_settings', 20, 2 );

/**
 * Register ACF Options Page for global settings.
 * 
 * This creates an "Options" page in WordPress admin where
 * global theme settings (like carousel) can be configured.
 */
function brace_yourself_register_acf_options_page() {
	if ( ! brace_yourself_acf_active() ) {
		return;
	}

	// Check if function exists (ACF Pro feature)
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page(
			array(
				'page_title' => __( 'Theme Options', 'brace-yourself' ),
				'menu_title' => __( 'Theme Options', 'brace-yourself' ),
				'menu_slug'  => 'theme-options',
				'capability' => 'edit_posts',
				'icon_url'   => 'dashicons-admin-settings',
			)
		);
	}
}
add_action( 'acf/init', 'brace_yourself_register_acf_options_page' );

/**
 * Get ACF field value with fallback.
 *
 * @param string $field_name Field name.
 * @param mixed  $fallback   Fallback value.
 * @param int    $post_id    Post ID (optional).
 * @return mixed Field value or fallback.
 */
function brace_yourself_get_field( $field_name, $fallback = '', $post_id = null ) {
	if ( ! brace_yourself_acf_active() ) {
		return $fallback;
	}

	$value = get_field( $field_name, $post_id );
	return $value !== false && $value !== null ? $value : $fallback;
}

/**
 * Additional filter: Hide Hero fields using prepare_field_group hook.
 * This runs earlier in the ACF process and modifies location rules.
 */
function brace_yourself_prepare_hero_field_group( $field_group ) {
	if ( ! isset( $field_group['key'] ) || 'group_hero_section' !== $field_group['key'] ) {
		return $field_group;
	}

	if ( ! is_admin() ) {
		return $field_group;
	}

	// Get post ID
	$post_id = 0;
	if ( isset( $_GET['post'] ) ) {
		$post_id = absint( $_GET['post'] );
	} elseif ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] ) {
		$post_id = $GLOBALS['post']->ID;
	}

	if ( ! $post_id ) {
		return $field_group;
	}

	// Check if this is carousel settings page
	$post_obj = get_post( $post_id );
	if ( $post_obj && 'carousel-settings' === $post_obj->post_name ) {
		// Make location rule impossible to match
		$field_group['location'] = array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'nonexistent_post_type',
				),
			),
		);
	}

	return $field_group;
}
add_filter( 'acf/prepare_field_group', 'brace_yourself_prepare_hero_field_group', 20 );
