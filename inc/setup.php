<?php
/**
 * Theme Setup
 *
 * Handles theme support, navigation menus, image sizes, and other core setup.
 *
 * @package Brace_Yourself
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function brace_yourself_setup() {
	// Make theme available for translation
	load_theme_textdomain( 'brace-yourself', BRACE_YOURSELF_TEMPLATE_DIR . '/languages' );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails
	add_theme_support( 'post-thumbnails' );

	// Register navigation menus
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'brace-yourself' ),
			'footer'  => esc_html__( 'Footer Menu', 'brace-yourself' ),
		)
	);

	// Switch default core markup to output valid HTML5
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Add theme support for selective refresh for widgets
	add_theme_support( 'customize-selective-refresh-widgets' );

	// Add support for editor styles
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor-style.css' );

	// Add support for responsive embedded content
	add_theme_support( 'responsive-embeds' );

	// Add support for custom logo
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'brace_yourself_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function brace_yourself_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'brace_yourself_content_width', 1200 );
}
add_action( 'after_setup_theme', 'brace_yourself_content_width', 0 );

/**
 * Register widget areas.
 */
function brace_yourself_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'brace-yourself' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'brace-yourself' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'brace_yourself_widgets_init' );

/**
 * Add custom image sizes.
 *
 * These sizes are intentionally defined for performance.
 * Never output full-size images - use these sizes with srcset.
 */
function brace_yourself_image_sizes() {
	// Hero images
	add_image_size( 'hero-large', 1920, 1080, true );
	add_image_size( 'hero-medium', 1280, 720, true );
	add_image_size( 'hero-small', 768, 432, true );

	// Content images
	add_image_size( 'content-large', 1200, 800, false );
	add_image_size( 'content-medium', 800, 600, false );
	add_image_size( 'content-small', 400, 300, false );

	// Thumbnails
	add_image_size( 'thumbnail-large', 400, 400, true );
	add_image_size( 'thumbnail-medium', 300, 300, true );
	add_image_size( 'thumbnail-small', 150, 150, true );
}
add_action( 'after_setup_theme', 'brace_yourself_image_sizes' );

/**
 * Limit primary navigation menu to maximum of 4 items.
 * This ensures the menu stays clean and manageable for 2-4 items.
 *
 * @param array $items An array of menu item objects.
 * @param object $menu The menu object.
 * @return array Filtered array of menu items.
 */
function brace_yourself_limit_primary_menu_items( $items, $menu ) {
	// Only limit the primary menu location
	if ( isset( $menu->term_id ) ) {
		$locations = get_nav_menu_locations();
		if ( isset( $locations['primary'] ) && $locations['primary'] === $menu->term_id ) {
			// Limit to first 4 items
			return array_slice( $items, 0, 4 );
		}
	}
	
	return $items;
}
add_filter( 'wp_get_nav_menu_items', 'brace_yourself_limit_primary_menu_items', 10, 2 );

/**
 * Limit sidebar-1 (main widget area) to maximum of 3 widgets.
 * Extra widgets assigned in Appearance > Widgets are not displayed.
 *
 * @param array $value The sidebars_widgets option value.
 * @return array Filtered value with sidebar-1 limited to 3 items.
 */
function brace_yourself_limit_sidebar_widgets( $value ) {
	if ( ! is_array( $value ) || empty( $value['sidebar-1'] ) || ! is_array( $value['sidebar-1'] ) ) {
		return $value;
	}
	$value         = array_merge( array(), $value );
	$value['sidebar-1'] = array_slice( $value['sidebar-1'], 0, 3 );
	return $value;
}
add_filter( 'option_sidebars_widgets', 'brace_yourself_limit_sidebar_widgets' );
