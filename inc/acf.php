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
 * Get about page ID.
 * Returns the ID of the page with slug "about" (the canonical About page).
 * Same pattern as the front page: one specific page is the About page.
 *
 * @return int|false Page ID or false if no page with slug "about" exists.
 */
function brace_yourself_get_about_page_id() {
	$page = get_page_by_path( 'about', OBJECT, 'page' );
	return $page ? (int) $page->ID : false;
}

/**
 * Get carousel fields (ACF Free: image and file fields only).
 *
 * @return array Array of field definitions.
 */
function brace_yourself_get_carousel_fields() {
	$fields = array();

	// Images 1–4, each with Desktop and Mobile (Optional)
	for ( $i = 1; $i <= 4; $i++ ) {
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

	// Video 1 and Video 2, each with Desktop and Mobile (Optional)
	foreach ( array( 1, 2 ) as $n ) {
		$fields[] = array(
			'key'               => 'field_video_' . $n . '_desktop',
			'label'             => 'Video ' . $n . ' - Desktop',
			'name'              => 'video_' . $n . '_desktop',
			'type'              => 'file',
			'instructions'      => 1 === $n ? 'Desktop video file (MP4 recommended). Leave empty if not using videos.' : 'Optional: Second desktop video. Leave empty if not using a second video.',
			'return_format'     => 'array',
			'library'           => 'all',
			'mime_types'        => 'mp4,webm',
			'required'          => 0,
		);
		$fields[] = array(
			'key'               => 'field_video_' . $n . '_mobile',
			'label'             => 'Video ' . $n . ' - Mobile (Optional)',
			'name'              => 'video_' . $n . '_mobile',
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

	// Background Carousel Field Group (ACF Free: Carousel Settings page only)
	$carousel_page_id = brace_yourself_get_carousel_settings_page_id();
	if ( $carousel_page_id ) {
		acf_add_local_field_group(
			array(
				'key'                   => 'group_background_carousel',
				'title'                 => 'Background Carousel',
				'fields'                => brace_yourself_get_carousel_fields(),
				'location'              => array(
					array(
						array(
							'param'    => 'page',
							'operator' => '==',
							'value'    => $carousel_page_id,
						),
					),
				),
				'active'                => 1,
				'menu_order'            => 0,
				'position'              => 'normal',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'instruction_placement' => 'label',
			)
		);
	}

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
					'instructions'      => 'A line of copy displayed at the bottom of the viewport on the homepage. Maximum 200 characters.',
					'required'          => 0,
					'maxlength'         => 200,
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

	// About Page Field Group
	// Location: About page only (page with slug "about") — same pattern as Homepage Intro
	$about_page_id = brace_yourself_get_about_page_id();
	$about_page_location = array();
	if ( $about_page_id ) {
		$about_page_location[] = array(
			array(
				'param'    => 'page',
				'operator' => '==',
				'value'    => $about_page_id,
			),
		);
	}
	acf_add_local_field_group(
		array(
			'key'                   => 'group_about_page',
			'title'                 => 'About Page',
			'fields'                => array(
				array(
					'key'               => 'field_about_intro_text',
					'label'             => 'About Text',
					'name'              => 'about_intro_text',
					'type'              => 'text',
					'instructions'      => 'A line of copy displayed in the center of the About page. Maximum 220 characters.',
					'required'          => 0,
					'maxlength'         => 220,
					'placeholder'       => 'Enter about page intro text',
					'default_value'     => '',
				),
			),
			'location'              => $about_page_location,
			'active'                => 1,
			'menu_order'            => 1,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
		)
	);

	// Artist Details (artist post type only — ACF Free: text + image only)
	acf_add_local_field_group(
		array(
			'key'                   => 'group_artist_details',
			'title'                 => 'Artist Details',
			'fields'                => array(
				array(
					'key'               => 'field_artist_subtitle',
					'label'             => __( 'Subtitle', 'brace-yourself' ),
					'name'              => 'subtitle',
					'type'              => 'text',
					'instructions'      => __( 'Optional line of text shown under the artist name on the Roster page.', 'brace-yourself' ),
					'required'          => 0,
					'placeholder'       => '',
					'default_value'     => '',
				),
				array(
					'key'               => 'field_artist_link',
					'label'             => __( 'Link', 'brace-yourself' ),
					'name'              => 'link',
					'type'              => 'url',
					'instructions'      => __( 'Optional URL. When set, the artist name on the Roster page becomes a link to this URL.', 'brace-yourself' ),
					'required'          => 0,
					'placeholder'       => 'https://',
				),
				array(
					'key'               => 'field_artist_hover_image',
					'label'             => __( 'Hover preview image', 'brace-yourself' ),
					'name'              => 'hover_image',
					'type'              => 'image',
					'instructions'      => __( 'Optional image shown on hover (desktop) or when in view (mobile) on the Roster page.', 'brace-yourself' ),
					'required'          => 0,
					'return_format'     => 'array',
					'preview_size'      => 'medium',
					'library'           => 'all',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'artist',
					),
				),
			),
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

