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
