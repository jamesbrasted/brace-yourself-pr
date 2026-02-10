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
 * Get front page ID.
 * Returns the ID of the page set as the static front page.
 *
 * @return int|false Page ID or false if no static front page is set.
 */
function brace_yourself_get_front_page_id() {
	$front_page_id = get_option( 'page_on_front' );
	return $front_page_id ? absint( $front_page_id ) : false;
}

/**
 * Get carousel settings page ID.
 * Creates the page if it doesn't exist (for ACF Free compatibility).
 *
 * NOTE: This page is automatically excluded from navigation menus via
 * brace_yourself_exclude_carousel_settings_from_menus() filter.
 * It should never appear in site navigation as it's only for admin use.
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
	
	// Mark page to exclude from search (optional, but helpful)
	if ( $page_id ) {
		update_post_meta( $page_id, '_exclude_from_search', '1' );
	}
	
	return $page_id ? $page_id : false;
}

/**
 * Get footer settings page ID.
 * Creates the page if it doesn't exist (for ACF Free compatibility).
 *
 * This page is for configuring footer columns (address, email, social links, etc.)
 * and should not appear in site navigation.
 *
 * @return int|false Page ID or false on failure.
 */
function brace_yourself_get_footer_settings_page_id() {
	// Check if page already exists.
	$page = get_page_by_path( 'footer-settings', OBJECT, 'page' );

	if ( $page ) {
		return $page->ID;
	}

	// Create the page if it doesn't exist.
	$page_data = array(
		'post_title'   => __( 'Footer Settings', 'brace-yourself' ),
		'post_name'    => 'footer-settings',
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => __( 'This page is used to configure the footer columns. Edit the fields below to manage footer content.', 'brace-yourself' ),
	);

	$page_id = wp_insert_post( $page_data );

	// Mark page to exclude from search (optional, but helpful).
	if ( $page_id ) {
		update_post_meta( $page_id, '_exclude_from_search', '1' );
	}

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

	// Footer Settings Field Group
	// Location: Footer Settings page only (ACF Free settings page).
	$footer_page_id = brace_yourself_get_footer_settings_page_id();
	if ( $footer_page_id ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_footer_settings',
				'title'                 => 'Footer Settings',
				'fields'                => array(
					array(
						'key'               => 'field_footer_column_1',
						'label'             => 'Footer Column 1',
						'name'              => 'footer_column_1',
						'type'              => 'wysiwyg',
						'instructions'      => 'Content for the first footer column (typically address). You can include links.',
						'required'          => 0,
						'tabs'              => 'all',
						'toolbar'           => 'basic',
						'media_upload'      => 0,
					),
					array(
						'key'               => 'field_footer_column_2',
						'label'             => 'Footer Column 2',
						'name'              => 'footer_column_2',
						'type'              => 'wysiwyg',
						'instructions'      => 'Content for the second footer column (e.g. email, social links). You can include links.',
						'required'          => 0,
						'tabs'              => 'all',
						'toolbar'           => 'basic',
						'media_upload'      => 0,
					),
					array(
						'key'               => 'field_footer_column_3',
						'label'             => 'Footer Column 3',
						'name'              => 'footer_column_3',
						'type'              => 'wysiwyg',
						'instructions'      => 'Optional third footer column.',
						'required'          => 0,
						'tabs'              => 'all',
						'toolbar'           => 'basic',
						'media_upload'      => 0,
					),
					array(
						'key'               => 'field_footer_column_4',
						'label'             => 'Footer Column 4',
						'name'              => 'footer_column_4',
						'type'              => 'wysiwyg',
						'instructions'      => 'Optional fourth footer column.',
						'required'          => 0,
						'tabs'              => 'all',
						'toolbar'           => 'basic',
						'media_upload'      => 0,
					),
				),
				'location'              => array(
					array(
						array(
							'param'    => 'page',
							'operator' => '==',
							'value'    => $footer_page_id,
						),
					),
				),
				'active'                => 1,
				'menu_order'            => 10,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
			)
		);
	}

	// Homepage Intro Field Group
	// Location: Front page (Homepage page) only
	$front_page_id = brace_yourself_get_front_page_id();
	$homepage_intro_location = array();
	
	if ( $front_page_id ) {
		$homepage_intro_location[] = array(
			array(
				'param'    => 'page',
				'operator' => '==',
				'value'    => $front_page_id,
			),
		);
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_homepage_intro',
			'title'                 => 'Homepage Intro',
			'fields'                => array(
				array(
					'key'               => 'field_homepage_intro_text',
					'label'             => 'Intro Text',
					'name'              => 'homepage_intro_text',
					'type'              => 'text',
					'instructions'      => 'A line of copy displayed at the bottom of the viewport on the homepage.',
					'required'          => 0,
					'placeholder'       => 'Enter homepage intro text',
					'default_value'     => '',
				),
			),
			'location'              => $homepage_intro_location,
			'active'                => 1,
			'menu_order'            => 1,
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

